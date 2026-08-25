-- Campus Link V2 - Phase 4 admin control plane
-- Global admins + institution-scoped sub-admins, school management and review queues.

create or replace function private.has_institution_admin_role(
  target_institution uuid,
  required_roles text[] default null
)
returns boolean
language sql
stable
security definer
set search_path = ''
as $$
  select exists (
    select 1
    from public.institution_admin_assignments a
    where a.user_id = (select auth.uid())
      and a.institution_id = target_institution
      and a.is_active = true
      and (required_roles is null or a.role = any(required_roles))
  );
$$;

revoke all on function private.has_institution_admin_role(uuid,text[]) from public;
grant usage on schema private to authenticated;
grant execute on function private.has_institution_admin_role(uuid,text[]) to authenticated;

-- Admin visibility for active and archived schools.
drop policy if exists institutions_admin_read on public.institutions;
create policy institutions_admin_read on public.institutions
for select to authenticated
using (
  (select private.has_admin_role(null))
  or (select private.has_institution_admin_role(id, null))
);

-- Global operations admins can create/manage schools directly.
drop policy if exists institutions_global_admin_insert on public.institutions;
create policy institutions_global_admin_insert on public.institutions
for insert to authenticated
with check ((select private.has_admin_role(array['super_admin','operations_admin','content_admin'])));

drop policy if exists institutions_global_admin_update on public.institutions;
create policy institutions_global_admin_update on public.institutions
for update to authenticated
using ((select private.has_admin_role(array['super_admin','operations_admin','content_admin'])))
with check ((select private.has_admin_role(array['super_admin','operations_admin','content_admin'])));

grant insert on public.institutions to authenticated;
grant update (name,slug,email_domain,city,state,country,is_active,verification_mode,allowed_student_email_domains,verification_instructions) on public.institutions to authenticated;

-- Admin visibility into student profiles is always scoped.
drop policy if exists profiles_admin_read on public.profiles;
create policy profiles_admin_read on public.profiles
for select to authenticated
using (
  (select private.has_admin_role(array['super_admin','operations_admin','verification_admin','support_admin','analyst']))
  or (institution_id is not null and (select private.has_institution_admin_role(institution_id, array['school_admin','school_verifier','school_support'])))
);

-- Scoped student verification review.
drop policy if exists student_verifications_school_admin_read on public.student_verifications;
create policy student_verifications_school_admin_read on public.student_verifications
for select to authenticated
using (
  exists (
    select 1 from public.profiles p
    where p.id = student_verifications.student_id
      and p.institution_id is not null
      and (select private.has_institution_admin_role(p.institution_id, array['school_admin','school_verifier']))
  )
);

-- Admin visibility into vendors and campus approvals.
drop policy if exists vendor_profiles_admin_read on public.vendor_profiles;
create policy vendor_profiles_admin_read on public.vendor_profiles
for select to authenticated
using (
  (select private.has_admin_role(array['super_admin','operations_admin','verification_admin','support_admin','analyst']))
  or exists (
    select 1 from public.vendor_institutions vi
    where vi.vendor_id = vendor_profiles.id
      and (select private.has_institution_admin_role(vi.institution_id, array['school_admin','school_verifier','school_support']))
  )
);

drop policy if exists vendor_institutions_school_admin_read on public.vendor_institutions;
create policy vendor_institutions_school_admin_read on public.vendor_institutions
for select to authenticated
using ((select private.has_institution_admin_role(institution_id, array['school_admin','school_verifier','school_support'])));

-- Complaints/reviews are visible only to the appropriate support/content scope.
drop policy if exists complaints_admin_read on public.complaints;
create policy complaints_admin_read on public.complaints
for select to authenticated
using (
  (select private.has_admin_role(array['super_admin','operations_admin','support_admin','analyst']))
  or (
    vendor_id is not null and exists (
      select 1 from public.vendor_institutions vi
      where vi.vendor_id = complaints.vendor_id
        and (select private.has_institution_admin_role(vi.institution_id, array['school_admin','school_support']))
    )
  )
);

drop policy if exists reviews_admin_read on public.reviews;
create policy reviews_admin_read on public.reviews
for select to authenticated
using (
  (select private.has_admin_role(array['super_admin','operations_admin','support_admin','content_admin','analyst']))
  or exists (
    select 1 from public.vendor_institutions vi
    where vi.vendor_id = reviews.vendor_id
      and (select private.has_institution_admin_role(vi.institution_id, array['school_admin','school_support']))
  )
);

grant select on public.student_verifications, public.student_documents, public.school_requests,
  public.vendor_profiles, public.vendor_institutions, public.vendor_documents,
  public.complaints, public.reviews, public.audit_logs, public.institution_admin_assignments to authenticated;

-- Create a school. Removal is intentionally a soft archive (is_active=false)
-- so historical students/vendors never lose referential integrity.
create or replace function public.admin_create_institution(
  school_name text,
  school_slug text,
  school_city text default null,
  school_state text default null,
  school_country text default 'Nigeria',
  school_email_domain text default null,
  school_verification_mode text default 'hybrid',
  school_email_domains text[] default '{}',
  school_instructions text default null
)
returns uuid
language plpgsql
security definer
set search_path = ''
as $$
declare
  new_id uuid;
begin
  if not (select private.has_admin_role(array['super_admin','operations_admin','content_admin'])) then
    raise exception 'Not authorised to create institutions';
  end if;

  if trim(coalesce(school_name,'')) = '' or trim(coalesce(school_slug,'')) = '' then
    raise exception 'School name and slug are required';
  end if;

  if school_verification_mode not in ('institution_email','manual','hybrid') then
    raise exception 'Invalid verification mode';
  end if;

  insert into public.institutions (
    name, slug, city, state, country, email_domain, is_active,
    verification_mode, allowed_student_email_domains, verification_instructions
  ) values (
    trim(school_name), lower(trim(school_slug)), nullif(trim(school_city),''),
    nullif(trim(school_state),''), coalesce(nullif(trim(school_country),''),'Nigeria'),
    nullif(lower(trim(school_email_domain)),''), true,
    school_verification_mode, coalesce(school_email_domains,'{}'::text[]),
    nullif(trim(school_instructions),'')
  )
  returning id into new_id;

  insert into public.audit_logs(actor_id,action,entity_type,entity_id,metadata)
  values ((select auth.uid()),'institution.create','institution',new_id::text,jsonb_build_object('name',school_name));

  return new_id;
end;
$$;

revoke all on function public.admin_create_institution(text,text,text,text,text,text,text,text[],text) from public, anon;
grant execute on function public.admin_create_institution(text,text,text,text,text,text,text,text[],text) to authenticated;

create or replace function public.admin_set_institution_active(target_id uuid, active boolean)
returns void
language plpgsql
security definer
set search_path = ''
as $$
begin
  if not (select private.has_admin_role(array['super_admin','operations_admin','content_admin'])) then
    raise exception 'Not authorised to manage institutions';
  end if;

  update public.institutions set is_active = active where id = target_id;
  if not found then raise exception 'Institution not found'; end if;

  insert into public.audit_logs(actor_id,action,entity_type,entity_id,metadata)
  values ((select auth.uid()),case when active then 'institution.restore' else 'institution.archive' end,'institution',target_id::text,'{}'::jsonb);
end;
$$;

revoke all on function public.admin_set_institution_active(uuid,boolean) from public, anon;
grant execute on function public.admin_set_institution_active(uuid,boolean) to authenticated;

create or replace function public.admin_review_student(
  target_student uuid,
  decision text,
  note text default null
)
returns void
language plpgsql
security definer
set search_path = ''
as $$
declare
  institution uuid;
  next_status text;
begin
  select institution_id into institution from public.profiles where id = target_student;
  if institution is null then raise exception 'Student has no institution'; end if;

  if not (
    (select private.has_admin_role(array['super_admin','operations_admin','verification_admin']))
    or (select private.has_institution_admin_role(institution,array['school_admin','school_verifier']))
  ) then raise exception 'Not authorised to review this student'; end if;

  next_status := case decision when 'approve' then 'verified' when 'reject' then 'rejected' else null end;
  if next_status is null then raise exception 'Decision must be approve or reject'; end if;

  update public.student_verifications
  set status = next_status,
      reviewed_by = (select auth.uid()),
      reviewed_at = now(),
      review_note = nullif(trim(note),'')
  where student_id = target_student;

  if not found then raise exception 'Student verification not found'; end if;

  update public.profiles
  set student_verification_status = next_status,
      updated_at = now()
  where id = target_student;

  insert into public.audit_logs(actor_id,action,entity_type,entity_id,metadata)
  values ((select auth.uid()),'student_verification.'||next_status,'student',target_student::text,jsonb_build_object('institution_id',institution,'note',note));
end;
$$;

revoke all on function public.admin_review_student(uuid,text,text) from public, anon;
grant execute on function public.admin_review_student(uuid,text,text) to authenticated;

-- Vendor identity approval is global; school sub-admins cannot grant identity trust.
create or replace function public.admin_review_vendor_identity(
  target_vendor uuid,
  decision text,
  note text default null
)
returns void
language plpgsql
security definer
set search_path = ''
as $$
declare
  next_status text;
begin
  if not (select private.has_admin_role(array['super_admin','operations_admin','verification_admin'])) then
    raise exception 'Not authorised to review vendor identity';
  end if;

  next_status := case decision when 'approve' then 'approved' when 'reject' then 'rejected' when 'suspend' then 'suspended' else null end;
  if next_status is null then raise exception 'Invalid vendor decision'; end if;

  update public.vendor_profiles
  set verification_status = next_status,
      updated_at = now()
  where id = target_vendor;
  if not found then raise exception 'Vendor not found'; end if;

  insert into public.audit_logs(actor_id,action,entity_type,entity_id,metadata)
  values ((select auth.uid()),'vendor_identity.'||next_status,'vendor',target_vendor::text,jsonb_build_object('note',note));
end;
$$;

revoke all on function public.admin_review_vendor_identity(uuid,text,text) from public, anon;
grant execute on function public.admin_review_vendor_identity(uuid,text,text) to authenticated;

-- Campus approval is scoped to the school. Public discovery still requires BOTH
-- global vendor identity approval and approved campus membership.
create or replace function public.admin_review_vendor_campus(
  target_vendor uuid,
  target_institution uuid,
  decision text,
  note text default null
)
returns void
language plpgsql
security definer
set search_path = ''
as $$
declare
  next_status text;
begin
  if not (
    (select private.has_admin_role(array['super_admin','operations_admin','verification_admin']))
    or (select private.has_institution_admin_role(target_institution,array['school_admin','school_verifier']))
  ) then raise exception 'Not authorised for this campus'; end if;

  next_status := case decision when 'approve' then 'approved' when 'reject' then 'rejected' when 'suspend' then 'suspended' else null end;
  if next_status is null then raise exception 'Invalid campus decision'; end if;

  update public.vendor_institutions
  set status = next_status,
      reviewed_by = (select auth.uid()),
      reviewed_at = now(),
      review_note = nullif(trim(note),'')
  where vendor_id = target_vendor and institution_id = target_institution;
  if not found then raise exception 'Vendor campus request not found'; end if;

  insert into public.audit_logs(actor_id,action,entity_type,entity_id,metadata)
  values ((select auth.uid()),'vendor_campus.'||next_status,'vendor',target_vendor::text,jsonb_build_object('institution_id',target_institution,'note',note));
end;
$$;

revoke all on function public.admin_review_vendor_campus(uuid,uuid,text,text) from public, anon;
grant execute on function public.admin_review_vendor_campus(uuid,uuid,text,text) to authenticated;

create or replace function public.admin_update_complaint(
  complaint_id uuid,
  next_status text
)
returns void
language plpgsql
security definer
set search_path = ''
as $$
declare
  complaint_vendor uuid;
begin
  if next_status not in ('open','reviewing','resolved','closed') then raise exception 'Invalid complaint status'; end if;
  select vendor_id into complaint_vendor from public.complaints where id = complaint_id;

  if not (
    (select private.has_admin_role(array['super_admin','operations_admin','support_admin']))
    or (complaint_vendor is not null and exists (
      select 1 from public.vendor_institutions vi
      where vi.vendor_id = complaint_vendor
        and (select private.has_institution_admin_role(vi.institution_id,array['school_admin','school_support']))
    ))
  ) then raise exception 'Not authorised to manage this complaint'; end if;

  update public.complaints set status = next_status, updated_at = now() where id = complaint_id;
  if not found then raise exception 'Complaint not found'; end if;

  insert into public.audit_logs(actor_id,action,entity_type,entity_id,metadata)
  values ((select auth.uid()),'complaint.'||next_status,'complaint',complaint_id::text,'{}'::jsonb);
end;
$$;

revoke all on function public.admin_update_complaint(uuid,text) from public, anon;
grant execute on function public.admin_update_complaint(uuid,text) to authenticated;

-- Super/operations admins can assign school-specific sub-admins without exposing auth.users.
create or replace function public.admin_assign_school_admin_by_email(
  target_email text,
  target_institution uuid,
  assignment_role text default 'school_verifier'
)
returns void
language plpgsql
security definer
set search_path = ''
as $$
declare
  target_user uuid;
begin
  if not (select private.has_admin_role(array['super_admin','operations_admin'])) then
    raise exception 'Not authorised to assign school admins';
  end if;
  if assignment_role not in ('school_admin','school_verifier','school_support') then
    raise exception 'Invalid school admin role';
  end if;

  select id into target_user from auth.users where lower(email)=lower(trim(target_email));
  if target_user is null then raise exception 'No Campus Link account exists for that email'; end if;

  insert into public.institution_admin_assignments(user_id,institution_id,role,is_active,created_by)
  values(target_user,target_institution,assignment_role,true,(select auth.uid()))
  on conflict(user_id,institution_id) do update set
    role=excluded.role,is_active=true,created_by=excluded.created_by;

  insert into public.audit_logs(actor_id,action,entity_type,entity_id,metadata)
  values ((select auth.uid()),'school_admin.assign','institution',target_institution::text,jsonb_build_object('user_id',target_user,'role',assignment_role));
end;
$$;

revoke all on function public.admin_assign_school_admin_by_email(text,uuid,text) from public, anon;
grant execute on function public.admin_assign_school_admin_by_email(text,uuid,text) to authenticated;

-- Global role assignment stays super-admin only.
create or replace function public.admin_assign_global_role_by_email(
  target_email text,
  target_role text
)
returns void
language plpgsql
security definer
set search_path = ''
as $$
declare
  target_user uuid;
begin
  if not (select private.has_admin_role(array['super_admin'])) then
    raise exception 'Only a super admin can assign global roles';
  end if;
  if target_role not in ('super_admin','operations_admin','verification_admin','support_admin','finance_admin','content_admin','analyst') then
    raise exception 'Invalid global admin role';
  end if;

  select id into target_user from auth.users where lower(email)=lower(trim(target_email));
  if target_user is null then raise exception 'No Campus Link account exists for that email'; end if;

  insert into public.admin_memberships(user_id,role,is_active,created_by)
  values(target_user,target_role,true,(select auth.uid()))
  on conflict(user_id) do update set role=excluded.role,is_active=true,created_by=excluded.created_by,updated_at=now();

  insert into public.audit_logs(actor_id,action,entity_type,entity_id,metadata)
  values ((select auth.uid()),'global_admin.assign','admin_membership',target_user::text,jsonb_build_object('role',target_role));
end;
$$;

revoke all on function public.admin_assign_global_role_by_email(text,text) from public, anon;
grant execute on function public.admin_assign_global_role_by_email(text,text) to authenticated;

-- Helpful indexes for the admin queues.
create index if not exists profiles_institution_type_idx on public.profiles(institution_id,account_type);
create index if not exists student_verifications_status_submitted_idx on public.student_verifications(status,submitted_at desc);
create index if not exists vendor_profiles_verification_idx on public.vendor_profiles(verification_status,created_at desc);
create index if not exists complaints_status_created_idx on public.complaints(status,created_at desc);
create index if not exists institution_admin_assignments_institution_active_idx on public.institution_admin_assignments(institution_id,is_active);
