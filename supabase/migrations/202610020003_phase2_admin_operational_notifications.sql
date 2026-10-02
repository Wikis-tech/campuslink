-- Phase 2: lightweight operational notifications for Admins.
-- These triggers notify only the Admin roles that can act on each queue.

create or replace function private.notify_admins_student_verification()
returns trigger
language plpgsql
security definer
set search_path = ''
as $$
declare
  target_school uuid;
begin
  if new.submitted_at is null or new.status not in ('pending','under_review') then
    return new;
  end if;
  if tg_op = 'UPDATE' and old.status is not distinct from new.status and old.submitted_at is not distinct from new.submitted_at then
    return new;
  end if;

  select institution_id into target_school
  from public.profiles
  where id = new.student_id;

  insert into public.notifications(user_id,title,body,type,action_url,source,metadata)
  select distinct recipient_id,
    'Student verification submitted',
    'A Student verification is waiting for Admin review.',
    'admin_operation',
    '/control-center/students?status=awaiting',
    'system',
    jsonb_build_object('event','student_verification_submitted','student_id',new.student_id,'institution_id',target_school)
  from (
    select am.user_id as recipient_id
    from public.admin_memberships am
    where am.is_active and am.role in ('super_admin','operations_admin','verification_admin')
    union
    select ia.user_id
    from public.institution_admin_assignments ia
    where ia.is_active and ia.role='school_admin' and ia.institution_id=target_school
  ) recipients;

  return new;
end;
$$;

create or replace function private.notify_admins_vendor_identity()
returns trigger
language plpgsql
security definer
set search_path = ''
as $$
begin
  if new.verification_submitted_at is null or new.verification_status not in ('pending','under_review') then
    return new;
  end if;
  if tg_op = 'UPDATE' and old.verification_status is not distinct from new.verification_status and old.verification_submitted_at is not distinct from new.verification_submitted_at then
    return new;
  end if;

  insert into public.notifications(user_id,title,body,type,action_url,source,metadata)
  select am.user_id,
    'Vendor identity application submitted',
    'A Vendor identity application is waiting for platform verification.',
    'admin_operation',
    '/control-center/vendors?status=awaiting_identity',
    'system',
    jsonb_build_object('event','vendor_identity_submitted','vendor_id',new.id)
  from public.admin_memberships am
  where am.is_active and am.role in ('super_admin','operations_admin','verification_admin');

  return new;
end;
$$;

create or replace function private.notify_admins_vendor_campus()
returns trigger
language plpgsql
security definer
set search_path = ''
as $$
begin
  if new.status <> 'pending' then
    return new;
  end if;
  if tg_op = 'UPDATE' and old.status is not distinct from new.status then
    return new;
  end if;

  insert into public.notifications(user_id,title,body,type,action_url,source,metadata)
  select distinct recipient_id,
    'Vendor requested Campus access',
    'A Vendor is waiting for Campus approval.',
    'admin_operation',
    '/control-center/vendors?status=awaiting_campus&school=' || new.institution_id::text,
    'system',
    jsonb_build_object('event','vendor_campus_requested','vendor_id',new.vendor_id,'institution_id',new.institution_id)
  from (
    select am.user_id as recipient_id
    from public.admin_memberships am
    where am.is_active and am.role in ('super_admin','operations_admin')
    union
    select ia.user_id
    from public.institution_admin_assignments ia
    where ia.is_active and ia.role='school_admin' and ia.institution_id=new.institution_id
  ) recipients;

  return new;
end;
$$;

create or replace function private.notify_admins_new_safety_report()
returns trigger
language plpgsql
security definer
set search_path = ''
as $$
begin
  insert into public.notifications(user_id,title,body,type,action_url,source,metadata)
  select distinct recipient_id,
    case when new.severity in ('high','critical') then 'Urgent safety report received' else 'New safety report received' end,
    'A new CampusLink safety report requires review.',
    'admin_operation',
    '/control-center/safety',
    'system',
    jsonb_build_object('event','safety_report_created','complaint_id',new.id,'vendor_id',new.vendor_id,'severity',new.severity)
  from (
    select am.user_id as recipient_id
    from public.admin_memberships am
    where am.is_active and am.role in ('super_admin','operations_admin','support_admin')
    union
    select ia.user_id
    from public.institution_admin_assignments ia
    join public.vendor_institutions vi on vi.institution_id=ia.institution_id
    where ia.is_active and ia.role='school_admin' and new.vendor_id is not null and vi.vendor_id=new.vendor_id
  ) recipients;

  return new;
end;
$$;

revoke all on function private.notify_admins_student_verification() from public, anon, authenticated;
revoke all on function private.notify_admins_vendor_identity() from public, anon, authenticated;
revoke all on function private.notify_admins_vendor_campus() from public, anon, authenticated;
revoke all on function private.notify_admins_new_safety_report() from public, anon, authenticated;

drop trigger if exists trg_admin_notify_student_verification on public.student_verifications;
create trigger trg_admin_notify_student_verification
after insert or update of status,submitted_at on public.student_verifications
for each row execute function private.notify_admins_student_verification();

drop trigger if exists trg_admin_notify_vendor_identity on public.vendor_profiles;
create trigger trg_admin_notify_vendor_identity
after insert or update of verification_status,verification_submitted_at on public.vendor_profiles
for each row execute function private.notify_admins_vendor_identity();

drop trigger if exists trg_admin_notify_vendor_campus on public.vendor_institutions;
create trigger trg_admin_notify_vendor_campus
after insert or update of status on public.vendor_institutions
for each row execute function private.notify_admins_vendor_campus();

drop trigger if exists trg_admin_notify_safety_report on public.complaints;
create trigger trg_admin_notify_safety_report
after insert on public.complaints
for each row execute function private.notify_admins_new_safety_report();
