-- Phase 6 authority-column hardening.

revoke insert, update on public.vendor_profiles from authenticated;

grant insert (
  id,business_name,slug,description,whatsapp_number,business_email,website_url,
  location_text,vendor_type,logo_url,cover_url,onboarding_completed_at,
  verification_submitted_at,updated_at
) on public.vendor_profiles to authenticated;

grant update (
  business_name,slug,description,whatsapp_number,business_email,website_url,
  location_text,vendor_type,logo_url,cover_url,onboarding_completed_at,
  verification_submitted_at,updated_at
) on public.vendor_profiles to authenticated;

revoke update (institution_id, school_email, onboarding_completed_at)
on public.profiles from authenticated;

create or replace function public.student_prepare_onboarding_profile(
  target_institution uuid,
  student_phone text,
  student_course text,
  student_level text,
  student_school_email text default null
)
returns void
language plpgsql
security definer
set search_path = ''
as $$
declare
  actor uuid := (select auth.uid());
  current_institution uuid;
  current_status text;
begin
  if actor is null then raise exception 'Authentication required'; end if;

  select p.institution_id, p.student_verification_status
  into current_institution, current_status
  from public.profiles p
  where p.id = actor and p.account_type = 'student';

  if not found then raise exception 'Student account required'; end if;

  if not exists (
    select 1 from public.institutions i
    where i.id = target_institution and i.is_active = true
  ) then raise exception 'Invalid institution'; end if;

  if current_status = 'verified'
     and current_institution is not null
     and current_institution is distinct from target_institution then
    raise exception 'Verified campus cannot be changed without administrator review';
  end if;

  update public.profiles
  set institution_id = target_institution,
      phone = nullif(trim(student_phone),''),
      course_of_study = nullif(trim(student_course),''),
      study_level = nullif(trim(student_level),''),
      school_email = nullif(lower(trim(coalesce(student_school_email,''))),''),
      updated_at = now()
  where id = actor;
end;
$$;

revoke all on function public.student_prepare_onboarding_profile(uuid,text,text,text,text)
from public, anon;
grant execute on function public.student_prepare_onboarding_profile(uuid,text,text,text,text)
to authenticated;

create or replace function public.student_mark_onboarding_complete()
returns void
language plpgsql
security definer
set search_path = ''
as $$
declare
  actor uuid := (select auth.uid());
begin
  if actor is null then raise exception 'Authentication required'; end if;

  if not exists (
    select 1
    from public.profiles p
    join public.student_verifications sv on sv.student_id = p.id
    where p.id = actor
      and p.account_type = 'student'
      and p.institution_id is not null
      and sv.status in ('pending','verified')
  ) then raise exception 'Student verification submission required'; end if;

  update public.profiles
  set onboarding_completed_at = coalesce(onboarding_completed_at, now()),
      updated_at = now()
  where id = actor and account_type = 'student';
end;
$$;

revoke all on function public.student_mark_onboarding_complete()
from public, anon;
grant execute on function public.student_mark_onboarding_complete()
to authenticated;

alter table public.profiles drop constraint if exists profiles_account_type_check;
alter table public.profiles
  add constraint profiles_account_type_check
  check (account_type in ('student','vendor','admin'));

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
    when coalesce((new.raw_user_meta_data ->> 'campuslink_admin_account')::boolean,false) then 'admin'
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

revoke all on function public.handle_new_user() from public, anon, authenticated;
grant execute on function public.handle_new_user() to service_role;

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
  if current_id is null then raise exception 'Authentication required'; end if;

  select raw_user_meta_data into auth_meta
  from auth.users
  where id = current_id;

  if auth_meta is null then raise exception 'Authenticated user was not found'; end if;

  desired_type := case
    when coalesce((auth_meta ->> 'campuslink_admin_account')::boolean,false) then 'admin'
    when auth_meta ->> 'account_type' = 'vendor' then 'vendor'
    else 'student'
  end;

  insert into public.profiles (id, account_type, first_name, last_name)
  select
    u.id,
    desired_type,
    nullif(trim(u.raw_user_meta_data ->> 'first_name'), ''),
    nullif(trim(u.raw_user_meta_data ->> 'last_name'), '')
  from auth.users u
  where u.id = current_id
  on conflict (id) do update set
    first_name = coalesce(public.profiles.first_name, excluded.first_name),
    last_name = coalesce(public.profiles.last_name, excluded.last_name);

  select account_type into resolved_type
  from public.profiles
  where id = current_id;

  if resolved_type not in ('student','vendor','admin') then
    resolved_type := desired_type;
    update public.profiles set account_type = desired_type where id = current_id;
  end if;

  return resolved_type;
end;
$$;

revoke all on function public.resolve_current_account_type() from public, anon;
grant execute on function public.resolve_current_account_type() to authenticated;
