-- Campus Link V2 - Phase 4 student verification compatibility bridge.
-- Safe to run after 0095/0096 and before 010 on live databases where the
-- Phase 2 onboarding migration was only partially applied. Intentionally idempotent.

-- Columns used by the student onboarding/verification UI.
alter table public.profiles
  add column if not exists school_email text,
  add column if not exists course_of_study text,
  add column if not exists study_level text,
  add column if not exists onboarding_completed_at timestamptz;

alter table public.vendor_profiles
  add column if not exists vendor_type text,
  add column if not exists website_url text,
  add column if not exists onboarding_completed_at timestamptz,
  add column if not exists verification_submitted_at timestamptz;

-- Add the vendor_type check only if it is not already present.
do $$
begin
  if not exists (
    select 1 from pg_constraint
    where conrelid = 'public.vendor_profiles'::regclass
      and conname = 'vendor_profiles_vendor_type_check'
  ) then
    alter table public.vendor_profiles
      add constraint vendor_profiles_vendor_type_check
      check (vendor_type is null or vendor_type in ('student_vendor','community_vendor','registered_business'));
  end if;
end
$$;

-- Student verification state expected by Phase 4 review queues.
create table if not exists public.student_verifications (
  student_id uuid primary key references public.profiles(id) on delete cascade,
  matric_number text,
  school_email text,
  verification_method text not null default 'manual',
  status text not null default 'pending',
  submitted_at timestamptz,
  reviewed_by uuid references auth.users(id) on delete set null,
  reviewed_at timestamptz,
  review_note text,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

do $$
begin
  if not exists (
    select 1 from pg_constraint
    where conrelid = 'public.student_verifications'::regclass
      and conname = 'student_verifications_verification_method_check'
  ) then
    alter table public.student_verifications
      add constraint student_verifications_verification_method_check
      check (verification_method in ('school_email','student_id','manual'));
  end if;
  if not exists (
    select 1 from pg_constraint
    where conrelid = 'public.student_verifications'::regclass
      and conname = 'student_verifications_status_check'
  ) then
    alter table public.student_verifications
      add constraint student_verifications_status_check
      check (status in ('pending','under_review','verified','rejected','suspended'));
  end if;
end
$$;

-- Private verification document metadata expected by 011.
create table if not exists public.student_documents (
  id uuid primary key default gen_random_uuid(),
  student_id uuid not null references public.profiles(id) on delete cascade,
  document_type text not null,
  storage_path text not null,
  status text not null default 'pending',
  rejection_reason text,
  reviewed_by uuid references auth.users(id) on delete set null,
  reviewed_at timestamptz,
  created_at timestamptz not null default now()
);

do $$
begin
  if not exists (
    select 1 from pg_constraint
    where conrelid = 'public.student_documents'::regclass
      and conname = 'student_documents_document_type_check'
  ) then
    alter table public.student_documents
      add constraint student_documents_document_type_check
      check (document_type in ('student_id','admission_letter','school_portal_evidence','other'));
  end if;
  if not exists (
    select 1 from pg_constraint
    where conrelid = 'public.student_documents'::regclass
      and conname = 'student_documents_status_check'
  ) then
    alter table public.student_documents
      add constraint student_documents_status_check
      check (status in ('pending','approved','rejected'));
  end if;
end
$$;

alter table public.student_verifications enable row level security;
alter table public.student_documents enable row level security;

-- Student ownership policies.
drop policy if exists student_verifications_owner_read on public.student_verifications;
create policy student_verifications_owner_read on public.student_verifications
for select to authenticated
using ((select auth.uid()) = student_id);

drop policy if exists student_verifications_owner_insert on public.student_verifications;
create policy student_verifications_owner_insert on public.student_verifications
for insert to authenticated
with check (
  (select auth.uid()) = student_id
  and status = 'pending'
  and reviewed_by is null
  and reviewed_at is null
);

drop policy if exists student_verifications_owner_update_pending on public.student_verifications;
create policy student_verifications_owner_update_pending on public.student_verifications
for update to authenticated
using ((select auth.uid()) = student_id and status in ('pending','rejected'))
with check ((select auth.uid()) = student_id and status = 'pending' and reviewed_by is null and reviewed_at is null);

drop policy if exists student_documents_owner_read on public.student_documents;
create policy student_documents_owner_read on public.student_documents
for select to authenticated
using ((select auth.uid()) = student_id);

drop policy if exists student_documents_owner_insert on public.student_documents;
create policy student_documents_owner_insert on public.student_documents
for insert to authenticated
with check (
  (select auth.uid()) = student_id
  and status = 'pending'
  and reviewed_by is null
  and reviewed_at is null
);

drop policy if exists student_documents_owner_delete_pending on public.student_documents;
create policy student_documents_owner_delete_pending on public.student_documents
for delete to authenticated
using ((select auth.uid()) = student_id and status = 'pending');

-- Global verification/operations admins retain review access. School-scoped access
-- is added later by migrations 010 and 011.
drop policy if exists student_verifications_admin_manage on public.student_verifications;
create policy student_verifications_admin_manage on public.student_verifications
for all to authenticated
using ((select private.has_admin_role(array['operations_admin','verification_admin'])))
with check ((select private.has_admin_role(array['operations_admin','verification_admin'])));

drop policy if exists student_documents_admin_manage on public.student_documents;
create policy student_documents_admin_manage on public.student_documents
for all to authenticated
using ((select private.has_admin_role(array['operations_admin','verification_admin'])))
with check ((select private.has_admin_role(array['operations_admin','verification_admin'])));

revoke all on public.student_verifications, public.student_documents from anon, authenticated;
grant select, insert, update on public.student_verifications to authenticated;
grant select, insert, delete on public.student_documents to authenticated;

-- Restore the narrow column grants required by onboarding after foundation_sync
-- revoked broad UPDATE access.
grant update (school_email, course_of_study, study_level, onboarding_completed_at)
  on public.profiles to authenticated;
grant update (vendor_type, website_url, onboarding_completed_at, verification_submitted_at)
  on public.vendor_profiles to authenticated;

create index if not exists student_documents_student_status_idx
  on public.student_documents(student_id, status);
create index if not exists student_verifications_status_idx
  on public.student_verifications(status);

-- Private storage for verification evidence. This is safe if the bucket already exists.
insert into storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
values (
  'verification-documents',
  'verification-documents',
  false,
  5242880,
  array['image/jpeg','image/png','image/webp','application/pdf']
)
on conflict (id) do update set
  public = excluded.public,
  file_size_limit = excluded.file_size_limit,
  allowed_mime_types = excluded.allowed_mime_types;

drop policy if exists verification_documents_owner_select on storage.objects;
create policy verification_documents_owner_select on storage.objects
for select to authenticated
using (
  bucket_id = 'verification-documents'
  and (storage.foldername(name))[1] = (select auth.uid())::text
);

drop policy if exists verification_documents_owner_insert on storage.objects;
create policy verification_documents_owner_insert on storage.objects
for insert to authenticated
with check (
  bucket_id = 'verification-documents'
  and (storage.foldername(name))[1] = (select auth.uid())::text
);

drop policy if exists verification_documents_owner_delete on storage.objects;
create policy verification_documents_owner_delete on storage.objects
for delete to authenticated
using (
  bucket_id = 'verification-documents'
  and (storage.foldername(name))[1] = (select auth.uid())::text
);

drop policy if exists verification_documents_admin_select on storage.objects;
create policy verification_documents_admin_select on storage.objects
for select to authenticated
using (
  bucket_id = 'verification-documents'
  and (select private.has_admin_role(array['operations_admin','verification_admin']))
);
