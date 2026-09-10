-- Campus Link V2 - repair auth/profile integrity and make profile creation idempotent.

-- Every existing auth user must have exactly one matching public profile.
insert into public.profiles (id, account_type, first_name, last_name)
select
  u.id,
  case when u.raw_user_meta_data ->> 'account_type' = 'vendor' then 'vendor' else 'student' end,
  nullif(trim(u.raw_user_meta_data ->> 'first_name'), ''),
  nullif(trim(u.raw_user_meta_data ->> 'last_name'), '')
from auth.users u
left join public.profiles p on p.id = u.id
where p.id is null
on conflict (id) do nothing;

-- Keep the trigger deterministic and recreate it explicitly so future sign-ups cannot miss profile creation.
create or replace function public.handle_new_user()
returns trigger
language plpgsql
security definer
set search_path = ''
as $$
declare
  desired_type text;
begin
  desired_type := case
    when new.raw_user_meta_data ->> 'account_type' = 'vendor' then 'vendor'
    else 'student'
  end;

  insert into public.profiles (id, account_type, first_name, last_name)
  values (
    new.id,
    desired_type,
    nullif(trim(new.raw_user_meta_data ->> 'first_name'), ''),
    nullif(trim(new.raw_user_meta_data ->> 'last_name'), '')
  )
  on conflict (id) do update set
    first_name = coalesce(public.profiles.first_name, excluded.first_name),
    last_name = coalesce(public.profiles.last_name, excluded.last_name);

  return new;
end;
$$;

revoke all on function public.handle_new_user() from public;
revoke execute on function public.handle_new_user() from anon, authenticated;

drop trigger if exists on_auth_user_created on auth.users;
create trigger on_auth_user_created
after insert on auth.users
for each row execute procedure public.handle_new_user();

-- Make sure authenticated users can always read their own profile row.
drop policy if exists "users can view own profile" on public.profiles;
drop policy if exists profiles_own_read on public.profiles;
create policy profiles_own_read on public.profiles
for select to authenticated
using ((select auth.uid()) = id);

grant select on public.profiles to authenticated;

-- Safe recovery path for historical accounts that pre-date/fell through the trigger.
-- The caller can only create/repair the profile matching their own auth.uid().
create or replace function public.ensure_current_user_profile()
returns public.profiles
language plpgsql
security definer
set search_path = ''
as $$
declare
  current_id uuid := auth.uid();
  auth_row auth.users%rowtype;
  result_row public.profiles%rowtype;
begin
  if current_id is null then
    raise exception 'Authentication required';
  end if;

  select * into auth_row
  from auth.users
  where id = current_id;

  if auth_row.id is null then
    raise exception 'Authenticated user was not found';
  end if;

  insert into public.profiles (id, account_type, first_name, last_name)
  values (
    auth_row.id,
    case when auth_row.raw_user_meta_data ->> 'account_type' = 'vendor' then 'vendor' else 'student' end,
    nullif(trim(auth_row.raw_user_meta_data ->> 'first_name'), ''),
    nullif(trim(auth_row.raw_user_meta_data ->> 'last_name'), '')
  )
  on conflict (id) do update set
    first_name = coalesce(public.profiles.first_name, excluded.first_name),
    last_name = coalesce(public.profiles.last_name, excluded.last_name)
  returning * into result_row;

  return result_row;
end;
$$;

revoke all on function public.ensure_current_user_profile() from public, anon;
grant execute on function public.ensure_current_user_profile() to authenticated;
