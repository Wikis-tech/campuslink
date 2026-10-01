-- Phase 1 launch readiness: collapse school-scoped roles to one School Admin role.
-- Preserve global roles and existing authorization boundaries.

update public.institution_admin_assignments
set role = 'school_admin'
where role in ('school_verifier','school_support');

alter table public.institution_admin_assignments
  drop constraint if exists institution_admin_assignments_role_check;

alter table public.institution_admin_assignments
  add constraint institution_admin_assignments_role_check
  check (role = 'school_admin');

create or replace function public.admin_assign_school_admin_by_email(
  target_email text,
  target_institution uuid,
  assignment_role text default 'school_admin'
)
returns void
language plpgsql
security definer
set search_path to ''
as $function$
declare
  target_user uuid;
begin
  if not (select private.has_admin_role(array['super_admin','operations_admin'])) then
    raise exception 'Not authorised to assign school admins';
  end if;
  if assignment_role <> 'school_admin' then
    raise exception 'Invalid school admin role';
  end if;

  select id into target_user
  from auth.users
  where lower(email)=lower(trim(target_email));

  if target_user is null then
    raise exception 'No Campus Link account exists for that email';
  end if;

  insert into public.institution_admin_assignments(
    user_id,institution_id,role,is_active,created_by
  )
  values(
    target_user,target_institution,'school_admin',true,(select auth.uid())
  )
  on conflict(user_id,institution_id) do update set
    role='school_admin',
    is_active=true,
    created_by=excluded.created_by;

  insert into public.audit_logs(actor_id,action,entity_type,entity_id,metadata)
  values(
    (select auth.uid()),
    'school_admin.assign',
    'institution',
    target_institution::text,
    jsonb_build_object('user_id',target_user,'role','school_admin')
  );
end;
$function$;

create or replace function private.admin_can_access_vendor_safety(target_vendor uuid)
returns boolean
language sql
stable
security definer
set search_path to ''
as $function$
  select coalesce((select auth.jwt()->>'aal'),'aal1') = 'aal2'
    and (
      exists (
        select 1
        from public.admin_memberships am
        where am.user_id = (select auth.uid())
          and am.is_active = true
          and am.role in ('super_admin','operations_admin','support_admin','verification_admin')
      )
      or exists (
        select 1
        from public.institution_admin_assignments ia
        join public.vendor_institutions vi on vi.institution_id = ia.institution_id
        where ia.user_id = (select auth.uid())
          and ia.is_active = true
          and ia.role = 'school_admin'
          and vi.vendor_id = target_vendor
      )
    );
$function$;

create or replace function private.can_manage_campus_intelligence(target_institution uuid)
returns boolean
language sql
stable
set search_path to 'public', 'pg_temp'
as $function$
  select exists (
    select 1
    from public.admin_memberships am
    where am.user_id = (select auth.uid())
      and am.is_active = true
      and am.role in ('super_admin','operations_admin','content_admin')
  ) or exists (
    select 1
    from public.institution_admin_assignments ia
    where ia.user_id = (select auth.uid())
      and ia.institution_id = target_institution
      and ia.is_active = true
      and ia.role = 'school_admin'
  );
$function$;

create or replace function public.admin_review_student(
  target_student uuid,
  decision text,
  note text default null
)
returns void
language plpgsql
security definer
set search_path to ''
as $function$
declare
  institution uuid;
  next_status text;
begin
  select institution_id into institution
  from public.profiles
  where id = target_student;

  if institution is null then
    raise exception 'Student has no institution';
  end if;

  if not (
    (select private.has_admin_role(array['super_admin','operations_admin','verification_admin']))
    or (select private.has_institution_admin_role(institution,array['school_admin']))
  ) then
    raise exception 'Not authorised to review this student';
  end if;

  next_status := case decision
    when 'approve' then 'verified'
    when 'reject' then 'rejected'
    else null
  end;

  if next_status is null then
    raise exception 'Decision must be approve or reject';
  end if;

  update public.student_verifications
  set status = next_status,
      reviewed_by = (select auth.uid()),
      reviewed_at = now(),
      review_note = nullif(trim(note),'')
  where student_id = target_student;

  if not found then
    raise exception 'Student verification not found';
  end if;

  update public.profiles
  set student_verification_status = next_status,
      updated_at = now()
  where id = target_student;

  insert into public.audit_logs(actor_id,action,entity_type,entity_id,metadata)
  values(
    (select auth.uid()),
    'student_verification.'||next_status,
    'student',
    target_student::text,
    jsonb_build_object('institution_id',institution,'note',note)
  );
end;
$function$;

create or replace function public.admin_review_vendor_campus(
  target_vendor uuid,
  target_institution uuid,
  decision text,
  note text default null
)
returns void
language plpgsql
security definer
set search_path to ''
as $function$
declare
  next_status text;
begin
  if not (
    (select private.has_admin_role(array['super_admin','operations_admin','verification_admin']))
    or (select private.has_institution_admin_role(target_institution,array['school_admin']))
  ) then
    raise exception 'Not authorised for this campus';
  end if;

  next_status := case decision
    when 'approve' then 'approved'
    when 'reject' then 'rejected'
    when 'suspend' then 'suspended'
    else null
  end;

  if next_status is null then
    raise exception 'Invalid campus decision';
  end if;

  update public.vendor_institutions
  set status = next_status,
      reviewed_by = (select auth.uid()),
      reviewed_at = now(),
      review_note = nullif(trim(note),'')
  where vendor_id = target_vendor
    and institution_id = target_institution;

  if not found then
    raise exception 'Vendor campus request not found';
  end if;

  insert into public.audit_logs(actor_id,action,entity_type,entity_id,metadata)
  values(
    (select auth.uid()),
    'vendor_campus.'||next_status,
    'vendor',
    target_vendor::text,
    jsonb_build_object('institution_id',target_institution,'note',note)
  );
end;
$function$;

create or replace function public.admin_moderate_review(
  review_id uuid,
  next_status text
)
returns void
language plpgsql
security definer
set search_path to ''
as $function$
declare
  target_vendor uuid;
begin
  if next_status not in ('published','hidden','reported') then
    raise exception 'Invalid review status';
  end if;

  select vendor_id into target_vendor
  from public.reviews
  where id = review_id;

  if target_vendor is null then
    raise exception 'Review not found';
  end if;

  if not (
    (select private.has_admin_role(array['super_admin','operations_admin','support_admin','content_admin']))
    or exists (
      select 1
      from public.vendor_institutions vi
      where vi.vendor_id = target_vendor
        and (select private.has_institution_admin_role(vi.institution_id,array['school_admin']))
    )
  ) then
    raise exception 'Not authorised to moderate this review';
  end if;

  update public.reviews
  set status=next_status,updated_at=now()
  where id=review_id;

  insert into public.audit_logs(actor_id,action,entity_type,entity_id,metadata)
  values(
    (select auth.uid()),
    'review.'||next_status,
    'review',
    review_id::text,
    jsonb_build_object('vendor_id',target_vendor)
  );
end;
$function$;

create or replace function public.admin_update_complaint(
  complaint_id uuid,
  next_status text
)
returns void
language plpgsql
security definer
set search_path to ''
as $function$
declare
  complaint_vendor uuid;
begin
  if next_status not in ('open','reviewing','resolved','closed') then
    raise exception 'Invalid complaint status';
  end if;

  select vendor_id into complaint_vendor
  from public.complaints
  where id = complaint_id;

  if not (
    (select private.has_admin_role(array['super_admin','operations_admin','support_admin']))
    or (
      complaint_vendor is not null
      and exists (
        select 1
        from public.vendor_institutions vi
        where vi.vendor_id = complaint_vendor
          and (select private.has_institution_admin_role(vi.institution_id,array['school_admin']))
      )
    )
  ) then
    raise exception 'Not authorised to manage this complaint';
  end if;

  update public.complaints
  set status = next_status, updated_at = now()
  where id = complaint_id;

  if not found then
    raise exception 'Complaint not found';
  end if;

  insert into public.audit_logs(actor_id,action,entity_type,entity_id,metadata)
  values(
    (select auth.uid()),
    'complaint.'||next_status,
    'complaint',
    complaint_id::text,
    '{}'::jsonb
  );
end;
$function$;

drop policy if exists complaints_admin_read on public.complaints;
create policy complaints_admin_read on public.complaints
for select to authenticated
using (
  (select private.has_admin_role(array['super_admin','operations_admin','support_admin','analyst']))
  or (
    vendor_id is not null
    and exists (
      select 1
      from public.vendor_institutions vi
      where vi.vendor_id = complaints.vendor_id
        and (select private.has_institution_admin_role(vi.institution_id,array['school_admin']))
    )
  )
);

drop policy if exists profiles_admin_read on public.profiles;
create policy profiles_admin_read on public.profiles
for select to authenticated
using (
  (select private.has_admin_role(array['super_admin','operations_admin','verification_admin','support_admin','analyst']))
  or (
    institution_id is not null
    and (select private.has_institution_admin_role(profiles.institution_id,array['school_admin']))
  )
);

drop policy if exists reviews_admin_read on public.reviews;
create policy reviews_admin_read on public.reviews
for select to authenticated
using (
  (select private.has_admin_role(array['super_admin','operations_admin','support_admin','content_admin','analyst']))
  or exists (
    select 1
    from public.vendor_institutions vi
    where vi.vendor_id = reviews.vendor_id
      and (select private.has_institution_admin_role(vi.institution_id,array['school_admin']))
  )
);

drop policy if exists student_documents_school_admin_read on public.student_documents;
create policy student_documents_school_admin_read on public.student_documents
for select to authenticated
using (
  exists (
    select 1
    from public.profiles p
    where p.id = student_documents.student_id
      and p.institution_id is not null
      and (select private.has_institution_admin_role(p.institution_id,array['school_admin']))
  )
);

drop policy if exists student_verifications_school_admin_read on public.student_verifications;
create policy student_verifications_school_admin_read on public.student_verifications
for select to authenticated
using (
  exists (
    select 1
    from public.profiles p
    where p.id = student_verifications.student_id
      and p.institution_id is not null
      and (select private.has_institution_admin_role(p.institution_id,array['school_admin']))
  )
);

drop policy if exists vendor_institutions_school_admin_read on public.vendor_institutions;
create policy vendor_institutions_school_admin_read on public.vendor_institutions
for select to authenticated
using (
  (select private.has_institution_admin_role(vendor_institutions.institution_id,array['school_admin']))
);

drop policy if exists vendor_profiles_admin_read on public.vendor_profiles;
create policy vendor_profiles_admin_read on public.vendor_profiles
for select to authenticated
using (
  (select private.has_admin_role(array['super_admin','operations_admin','verification_admin','support_admin','analyst']))
  or exists (
    select 1
    from public.vendor_institutions vi
    where vi.vendor_id = vendor_profiles.id
      and (select private.has_institution_admin_role(vi.institution_id,array['school_admin']))
  )
);
