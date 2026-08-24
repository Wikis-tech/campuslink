-- Campus Link V2 - Phase 2 security hardening.

-- A vendor is public only after both identity/business verification and at least one campus approval.
drop policy if exists "public can view approved vendors" on public.vendor_profiles;
drop policy if exists vendor_profiles_public_read on public.vendor_profiles;
create policy vendor_profiles_public_read on public.vendor_profiles
for select to anon, authenticated
using (
  verification_status = 'approved'
  and exists (
    select 1 from public.vendor_institutions vi
    where vi.vendor_id = id and vi.status = 'approved'
  )
);

drop policy if exists "public can view active services" on public.vendor_services;
drop policy if exists vendor_services_public_read on public.vendor_services;
create policy vendor_services_public_read on public.vendor_services
for select to anon, authenticated
using (
  is_active = true
  and exists (
    select 1 from public.vendor_profiles vp
    where vp.id = vendor_id and vp.verification_status = 'approved'
  )
  and exists (
    select 1 from public.vendor_institutions vi
    where vi.vendor_id = vendor_id and vi.status = 'approved'
  )
);

-- Vendors may create their own profile, but approval state must remain the database default/pending.
drop policy if exists "vendors can create own vendor profile" on public.vendor_profiles;
drop policy if exists vendor_profiles_owner_insert on public.vendor_profiles;
create policy vendor_profiles_owner_insert on public.vendor_profiles
for insert to authenticated
with check (
  (select auth.uid()) = id
  and verification_status = 'pending'
  and coalesce(status, 'pending') = 'pending'
);

revoke insert on public.vendor_profiles from authenticated;
grant insert (
  id,business_name,slug,description,whatsapp_number,business_email,location_text,logo_url,cover_url,
  vendor_type,website_url,onboarding_completed_at,verification_submitted_at
) on public.vendor_profiles to authenticated;

-- Verification evidence cannot self-populate review fields.
drop policy if exists vendor_documents_owner_insert on public.vendor_documents;
create policy vendor_documents_owner_insert on public.vendor_documents
for insert to authenticated
with check (
  (select auth.uid()) = vendor_id
  and status = 'pending'
  and reviewed_by is null
  and reviewed_at is null
  and rejection_reason is null
);

-- Only verified student accounts can publish a review.
drop policy if exists "students create own reviews" on public.reviews;
drop policy if exists reviews_student_insert on public.reviews;
create policy reviews_student_insert on public.reviews
for insert to authenticated
with check (
  (select auth.uid()) = student_id
  and exists (
    select 1 from public.profiles p
    where p.id = (select auth.uid())
      and p.account_type = 'student'
      and p.student_verification_status = 'verified'
  )
);

-- Prevent repeated pending school-request spam by the same account for the same name.
create unique index if not exists school_requests_one_pending_name_per_user
on public.school_requests (requested_by, lower(school_name))
where status = 'pending';
