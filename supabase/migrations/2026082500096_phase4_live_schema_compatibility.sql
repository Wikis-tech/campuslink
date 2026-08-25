-- Campus Link V2 - Phase 4 live schema compatibility bridge.
-- Safe to run after 0095 and before 010 on databases where earlier Phase 2/verification
-- migrations were only partially applied. This migration is intentionally idempotent.

-- Phase 4 school management expects these institution verification fields.
alter table public.institutions
  add column if not exists verification_mode text not null default 'hybrid',
  add column if not exists allowed_student_email_domains text[] not null default '{}'::text[],
  add column if not exists verification_instructions text;

-- Validate verification_mode values without failing on an already-existing constraint.
do $$
begin
  if not exists (
    select 1
    from pg_constraint
    where conrelid = 'public.institutions'::regclass
      and conname = 'institutions_verification_mode_check'
  ) then
    alter table public.institutions
      add constraint institutions_verification_mode_check
      check (verification_mode in ('institution_email','manual','hybrid'));
  end if;
end
$$;

-- Student school requests are required by both the onboarding page and Phase 4 moderation.
create table if not exists public.school_requests (
  id uuid primary key default gen_random_uuid(),
  requested_by uuid not null references auth.users(id) on delete cascade,
  school_name text not null,
  city text,
  state text,
  website text,
  status text not null default 'pending'
    check (status in ('pending','approved','rejected')),
  reviewed_by uuid references auth.users(id) on delete set null,
  reviewed_at timestamptz,
  created_at timestamptz not null default now()
);

alter table public.school_requests enable row level security;

drop policy if exists school_requests_owner_read on public.school_requests;
create policy school_requests_owner_read on public.school_requests
for select to authenticated
using ((select auth.uid()) = requested_by);

drop policy if exists school_requests_owner_insert on public.school_requests;
create policy school_requests_owner_insert on public.school_requests
for insert to authenticated
with check ((select auth.uid()) = requested_by and status = 'pending');

-- Global admins can inspect/process requests. School-scoped admins should not manage
-- the platform-wide school directory.
drop policy if exists school_requests_admin_read on public.school_requests;
create policy school_requests_admin_read on public.school_requests
for select to authenticated
using ((select private.has_admin_role(array['super_admin','operations_admin','content_admin'])));

revoke all on public.school_requests from anon, authenticated;
grant select, insert on public.school_requests to authenticated;

create index if not exists school_requests_status_created_idx
  on public.school_requests(status, created_at desc);

-- Ensure institution columns used by the admin server actions are writable by the
-- authenticated role; the RLS policies in 010 still decide who is actually allowed.
grant update (verification_mode, allowed_student_email_domains, verification_instructions)
  on public.institutions to authenticated;
