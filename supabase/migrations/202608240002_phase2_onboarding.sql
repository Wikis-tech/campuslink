-- Campus Link V2 - Phase 2: onboarding, school verification and private documents

alter table public.profiles
  add column if not exists school_email text,
  add column if not exists course_of_study text,
  add column if not exists study_level text,
  add column if not exists onboarding_completed_at timestamptz;

alter table public.vendor_profiles
  add column if not exists vendor_type text check (vendor_type in ('student_vendor','community_vendor','registered_business')),
  add column if not exists website_url text,
  add column if not exists onboarding_completed_at timestamptz,
  add column if not exists verification_submitted_at timestamptz;

create table if not exists public.student_verifications (
  student_id uuid primary key references public.profiles(id) on delete cascade,
  matric_number text,
  school_email text,
  verification_method text not null default 'manual' check (verification_method in ('school_email','student_id','manual')),
  status text not null default 'pending' check (status in ('pending','under_review','verified','rejected','suspended')),
  submitted_at timestamptz,
  reviewed_by uuid references auth.users(id) on delete set null,
  reviewed_at timestamptz,
  review_note text,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create table if not exists public.student_documents (
  id uuid primary key default gen_random_uuid(),
  student_id uuid not null references public.profiles(id) on delete cascade,
  document_type text not null check (document_type in ('student_id','admission_letter','school_portal_evidence','other')),
  storage_path text not null,
  status text not null default 'pending' check (status in ('pending','approved','rejected')),
  rejection_reason text,
  reviewed_by uuid references auth.users(id) on delete set null,
  reviewed_at timestamptz,
  created_at timestamptz not null default now()
);

create table if not exists public.school_requests (
  id uuid primary key default gen_random_uuid(),
  requested_by uuid not null references auth.users(id) on delete cascade,
  school_name text not null,
  city text,
  state text,
  website text,
  status text not null default 'pending' check (status in ('pending','approved','rejected')),
  reviewed_by uuid references auth.users(id) on delete set null,
  reviewed_at timestamptz,
  created_at timestamptz not null default now()
);

alter table public.student_verifications enable row level security;
alter table public.student_documents enable row level security;
alter table public.school_requests enable row level security;

drop policy if exists student_verifications_owner_read on public.student_verifications;
create policy student_verifications_owner_read on public.student_verifications
  for select to authenticated using ((select auth.uid()) = student_id);

drop policy if exists student_verifications_owner_insert on public.student_verifications;
create policy student_verifications_owner_insert on public.student_verifications
  for insert to authenticated with check (
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

drop policy if exists student_verifications_admin_manage on public.student_verifications;
create policy student_verifications_admin_manage on public.student_verifications
  for all to authenticated
  using ((select private.has_admin_role(array['operations_admin','verification_admin'])))
  with check ((select private.has_admin_role(array['operations_admin','verification_admin'])));

drop policy if exists student_documents_owner_read on public.student_documents;
create policy student_documents_owner_read on public.student_documents
  for select to authenticated using ((select auth.uid()) = student_id);

drop policy if exists student_documents_owner_insert on public.student_documents;
create policy student_documents_owner_insert on public.student_documents
  for insert to authenticated with check (
    (select auth.uid()) = student_id
    and status = 'pending'
    and reviewed_by is null
    and reviewed_at is null
  );

drop policy if exists student_documents_owner_delete_pending on public.student_documents;
create policy student_documents_owner_delete_pending on public.student_documents
  for delete to authenticated using ((select auth.uid()) = student_id and status = 'pending');

drop policy if exists student_documents_admin_manage on public.student_documents;
create policy student_documents_admin_manage on public.student_documents
  for all to authenticated
  using ((select private.has_admin_role(array['operations_admin','verification_admin'])))
  with check ((select private.has_admin_role(array['operations_admin','verification_admin'])));

drop policy if exists school_requests_owner_read on public.school_requests;
create policy school_requests_owner_read on public.school_requests
  for select to authenticated using ((select auth.uid()) = requested_by);

drop policy if exists school_requests_owner_insert on public.school_requests;
create policy school_requests_owner_insert on public.school_requests
  for insert to authenticated with check ((select auth.uid()) = requested_by and status = 'pending');

drop policy if exists school_requests_admin_manage on public.school_requests;
create policy school_requests_admin_manage on public.school_requests
  for all to authenticated
  using ((select private.has_admin_role(array['operations_admin','verification_admin'])))
  with check ((select private.has_admin_role(array['operations_admin','verification_admin'])));

revoke all on public.student_verifications, public.student_documents, public.school_requests from anon, authenticated;
grant select, insert, update on public.student_verifications to authenticated;
grant select, insert, delete on public.student_documents to authenticated;
grant select, insert on public.school_requests to authenticated;

grant update (school_email, course_of_study, study_level, onboarding_completed_at) on public.profiles to authenticated;
grant update (vendor_type, website_url, onboarding_completed_at, verification_submitted_at) on public.vendor_profiles to authenticated;

create index if not exists student_documents_student_status_idx on public.student_documents(student_id, status);
create index if not exists student_verifications_status_idx on public.student_verifications(status);
create index if not exists school_requests_status_idx on public.school_requests(status);

insert into public.institutions (name, slug, city, state, country)
values
  ('University of Lagos', 'unilag', 'Lagos', 'Lagos', 'Nigeria'),
  ('Lagos State University', 'lasu', 'Ojo', 'Lagos', 'Nigeria'),
  ('Yaba College of Technology', 'yabatech', 'Yaba', 'Lagos', 'Nigeria'),
  ('University of Ibadan', 'ui', 'Ibadan', 'Oyo', 'Nigeria'),
  ('Obafemi Awolowo University', 'oau', 'Ile-Ife', 'Osun', 'Nigeria'),
  ('University of Ilorin', 'unilorin', 'Ilorin', 'Kwara', 'Nigeria'),
  ('University of Nigeria, Nsukka', 'unn', 'Nsukka', 'Enugu', 'Nigeria'),
  ('University of Benin', 'uniben', 'Benin City', 'Edo', 'Nigeria'),
  ('Federal University of Technology, Akure', 'futa', 'Akure', 'Ondo', 'Nigeria'),
  ('Federal University of Technology, Owerri', 'futo', 'Owerri', 'Imo', 'Nigeria'),
  ('Federal University of Agriculture, Abeokuta', 'funaab', 'Abeokuta', 'Ogun', 'Nigeria'),
  ('Ahmadu Bello University', 'abu-zaria', 'Zaria', 'Kaduna', 'Nigeria'),
  ('Babcock University', 'babcock', 'Ilishan-Remo', 'Ogun', 'Nigeria'),
  ('Covenant University', 'covenant', 'Ota', 'Ogun', 'Nigeria'),
  ('Pan-Atlantic University', 'pau', 'Ibeju-Lekki', 'Lagos', 'Nigeria'),
  ('Caleb University', 'caleb', 'Imota', 'Lagos', 'Nigeria'),
  ('Bells University of Technology', 'bells', 'Ota', 'Ogun', 'Nigeria')
on conflict (slug) do nothing;

insert into public.categories (name, slug, description)
values
  ('Hair & Beauty', 'hair-beauty', 'Hair styling, barbering, nails and beauty services'),
  ('Phone & Laptop Repair', 'phone-laptop-repair', 'Device diagnosis, repairs and technical support'),
  ('Tutoring', 'tutoring', 'Academic tutoring and lesson support'),
  ('Food & Catering', 'food-catering', 'Meals, snacks, baking and catering services'),
  ('Photography', 'photography', 'Photography and creative media services'),
  ('Laundry & Cleaning', 'laundry-cleaning', 'Laundry, ironing and cleaning services'),
  ('Design & Printing', 'design-printing', 'Graphic design, printing and branding services'),
  ('Fashion & Tailoring', 'fashion-tailoring', 'Clothing, alterations and fashion services')
on conflict (slug) do nothing;

insert into storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
values ('verification-documents', 'verification-documents', false, 5242880, array['image/jpeg','image/png','image/webp','application/pdf'])
on conflict (id) do update set
  public = excluded.public,
  file_size_limit = excluded.file_size_limit,
  allowed_mime_types = excluded.allowed_mime_types;

drop policy if exists verification_documents_owner_select on storage.objects;
create policy verification_documents_owner_select on storage.objects
  for select to authenticated
  using (bucket_id = 'verification-documents' and (storage.foldername(name))[1] = (select auth.uid())::text);

drop policy if exists verification_documents_owner_insert on storage.objects;
create policy verification_documents_owner_insert on storage.objects
  for insert to authenticated
  with check (bucket_id = 'verification-documents' and (storage.foldername(name))[1] = (select auth.uid())::text);

drop policy if exists verification_documents_owner_delete on storage.objects;
create policy verification_documents_owner_delete on storage.objects
  for delete to authenticated
  using (bucket_id = 'verification-documents' and (storage.foldername(name))[1] = (select auth.uid())::text);

drop policy if exists verification_documents_admin_select on storage.objects;
create policy verification_documents_admin_select on storage.objects
  for select to authenticated
  using (bucket_id = 'verification-documents' and (select private.has_admin_role(array['operations_admin','verification_admin'])));
