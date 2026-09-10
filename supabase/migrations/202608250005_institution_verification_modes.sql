-- Campus Link: school-specific student verification and institution coordinators

alter table public.institutions
  add column if not exists verification_mode text not null default 'hybrid'
    check (verification_mode in ('institution_email','manual','hybrid')),
  add column if not exists allowed_student_email_domains text[] not null default '{}'::text[],
  add column if not exists verification_instructions text;

create table if not exists public.institution_admin_assignments (
  user_id uuid not null references auth.users(id) on delete cascade,
  institution_id uuid not null references public.institutions(id) on delete cascade,
  role text not null default 'school_verifier'
    check (role in ('school_admin','school_verifier','school_support')),
  is_active boolean not null default true,
  created_by uuid references auth.users(id) on delete set null,
  created_at timestamptz not null default now(),
  primary key (user_id, institution_id)
);

alter table public.institution_admin_assignments enable row level security;

create policy institution_admin_assignments_self_read
  on public.institution_admin_assignments
  for select to authenticated
  using ((select auth.uid()) = user_id);

create policy institution_admin_assignments_global_admin_manage
  on public.institution_admin_assignments
  for all to authenticated
  using ((select private.has_admin_role(array['super_admin','operations_admin'])))
  with check ((select private.has_admin_role(array['super_admin','operations_admin'])));

revoke all on public.institution_admin_assignments from anon, authenticated;
grant select on public.institution_admin_assignments to authenticated;
grant insert, update, delete on public.institution_admin_assignments to authenticated;

grant update (verification_mode, allowed_student_email_domains, verification_instructions)
  on public.institutions to authenticated;

-- UAT can use its institutional student email as a fast verification path.
update public.institutions
set verification_mode = 'hybrid',
    allowed_student_email_domains = array['student.uat.edu.ng'],
    verification_instructions = 'Students may verify with a student.uat.edu.ng address. Students without access to the school email can submit student ID, matric/portal evidence for manual review.'
where lower(name) like '%university of africa%'
   or slug in ('uat','university-of-africa-toru-orua');
