-- Campus Link V2 - make auth/profile routing resilient across legacy and V2 data.

alter table public.profiles
  add column if not exists account_type text not null default 'student' check (account_type in ('student','vendor')),
  add column if not exists student_verification_status text not null default 'pending' check (student_verification_status in ('pending','verified','rejected','suspended')),
  add column if not exists school_email text,
  add column if not exists course_of_study text,
  add column if not exists study_level text,
  add column if not exists onboarding_completed_at timestamptz;

-- Backfill every Auth user and preserve the account type captured at signup.
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

update public.profiles p
set account_type = case
  when u.raw_user_meta_data ->> 'account_type' = 'vendor' then 'vendor'
  else 'student'
end
from auth.users u
where p.id = u.id
  and p.account_type is distinct from case
    when u.raw_user_meta_data ->> 'account_type' = 'vendor' then 'vendor'
    else 'student'
  end;

create or replace function public.handle_new_user()
returns trigger
language plpgsql
security definer
set search_path = ''
as $$
begin
  insert into public.profiles (id, account_type, first_name, last_name)
  values (
    new.id,
    case when new.raw_user_meta_data ->> 'account_type' = 'vendor' then 'vendor' else 'student' end,
    nullif(trim(new.raw_user_meta_data ->> 'first_name'), ''),
    nullif(trim(new.raw_user_meta_data ->> 'last_name'), '')
  )
  on conflict (id) do update set
    account_type = excluded.account_type,
    first_name = coalesce(public.profiles.first_name, excluded.first_name),
    last_name = coalesce(public.profiles.last_name, excluded.last_name);
  return new;
end;
$$;

revoke all on function public.handle_new_user() from public, anon, authenticated;

drop trigger if exists on_auth_user_created on auth.users;
create trigger on_auth_user_created
after insert on auth.users
for each row execute procedure public.handle_new_user();

alter table public.profiles enable row level security;
drop policy if exists "users can view own profile" on public.profiles;
drop policy if exists profiles_own_read on public.profiles;
create policy profiles_own_read on public.profiles
for select to authenticated
using ((select auth.uid()) = id);

grant select on public.profiles to authenticated;

-- Routing RPC: repairs only the currently authenticated user's row, then returns
-- a tiny scalar account type. This avoids profile-routing failures caused by
-- legacy rows, RLS drift or clients selecting columns not yet present.
create or replace function public.resolve_current_account_type()
returns text
language plpgsql
security definer
set search_path = ''
as $$
declare
  current_id uuid := auth.uid();
  auth_meta jsonb;
  desired_type text;
  resolved_type text;
begin
  if current_id is null then
    raise exception 'Authentication required';
  end if;

  select raw_user_meta_data
  into auth_meta
  from auth.users
  where id = current_id;

  if auth_meta is null then
    raise exception 'Authenticated user was not found';
  end if;

  desired_type := case when auth_meta ->> 'account_type' = 'vendor' then 'vendor' else 'student' end;

  insert into public.profiles (id, account_type, first_name, last_name)
  select
    u.id,
    desired_type,
    nullif(trim(u.raw_user_meta_data ->> 'first_name'), ''),
    nullif(trim(u.raw_user_meta_data ->> 'last_name'), '')
  from auth.users u
  where u.id = current_id
  on conflict (id) do update set
    account_type = coalesce(public.profiles.account_type, excluded.account_type),
    first_name = coalesce(public.profiles.first_name, excluded.first_name),
    last_name = coalesce(public.profiles.last_name, excluded.last_name);

  select account_type into resolved_type
  from public.profiles
  where id = current_id;

  if resolved_type not in ('student','vendor') then
    resolved_type := desired_type;
    update public.profiles set account_type = desired_type where id = current_id;
  end if;

  return resolved_type;
end;
$$;

revoke all on function public.resolve_current_account_type() from public, anon;
grant execute on function public.resolve_current_account_type() to authenticated;

-- Keep the earlier repair RPC available for profile pages that need the full row.
create or replace function public.ensure_current_user_profile()
returns public.profiles
language plpgsql
security definer
set search_path = ''
as $$
declare
  current_id uuid := auth.uid();
  result_row public.profiles%rowtype;
begin
  if current_id is null then
    raise exception 'Authentication required';
  end if;

  perform public.resolve_current_account_type();

  select * into result_row
  from public.profiles
  where id = current_id;

  return result_row;
end;
$$;

revoke all on function public.ensure_current_user_profile() from public, anon;
grant execute on function public.ensure_current_user_profile() to authenticated;
