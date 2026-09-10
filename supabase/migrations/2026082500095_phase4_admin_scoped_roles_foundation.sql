-- Campus Link V2 - Phase 4 school-scoped admin foundation.
-- This migration must run before 202608250010_phase4_admin_control_plane.sql.

create table if not exists public.institution_admin_assignments (
  user_id uuid not null references auth.users(id) on delete cascade,
  institution_id uuid not null references public.institutions(id) on delete cascade,
  role text not null check (role in ('school_admin','school_verifier','school_support')),
  is_active boolean not null default true,
  created_by uuid references auth.users(id) on delete set null,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now(),
  primary key (user_id, institution_id)
);

alter table public.institution_admin_assignments enable row level security;

drop policy if exists institution_admin_assignments_own_read on public.institution_admin_assignments;
create policy institution_admin_assignments_own_read on public.institution_admin_assignments
for select to authenticated
using ((select auth.uid()) = user_id);

drop policy if exists institution_admin_assignments_global_admin_read on public.institution_admin_assignments;
create policy institution_admin_assignments_global_admin_read on public.institution_admin_assignments
for select to authenticated
using ((select private.has_admin_role(null)));

revoke all on public.institution_admin_assignments from anon, authenticated;
grant select on public.institution_admin_assignments to authenticated;

create index if not exists institution_admin_assignments_institution_active_idx
  on public.institution_admin_assignments(institution_id, is_active);
create index if not exists institution_admin_assignments_user_active_idx
  on public.institution_admin_assignments(user_id, is_active);
