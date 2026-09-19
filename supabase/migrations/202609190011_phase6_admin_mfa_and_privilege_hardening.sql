-- Phase 6: Admin MFA, least privilege, brute-force protection and grant hardening.

create or replace function private.has_admin_role(required_roles text[] default null)
returns boolean
language sql
stable
security definer
set search_path = ''
as $$
  select coalesce((select auth.jwt()->>'aal'),'aal1') = 'aal2'
    and exists (
      select 1
      from public.admin_memberships a
      where a.user_id = (select auth.uid())
        and a.is_active = true
        and (a.role = 'super_admin' or required_roles is null or a.role = any(required_roles))
    );
$$;

create or replace function private.has_institution_admin_role(target_institution uuid, required_roles text[] default null)
returns boolean
language sql
stable
security definer
set search_path = ''
as $$
  select coalesce((select auth.jwt()->>'aal'),'aal1') = 'aal2'
    and exists (
      select 1
      from public.institution_admin_assignments a
      where a.user_id = (select auth.uid())
        and a.institution_id = target_institution
        and a.is_active = true
        and (required_roles is null or a.role = any(required_roles))
    );
$$;

create or replace function private.admin_can_access_vendor_safety(target_vendor uuid)
returns boolean
language sql
stable
security definer
set search_path = ''
as $$
  select coalesce((select auth.jwt()->>'aal'),'aal1') = 'aal2'
    and (
      exists (
        select 1 from public.admin_memberships am
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
          and ia.role in ('school_admin','school_support','school_verifier')
          and vi.vendor_id = target_vendor
      )
    );
$$;

drop policy if exists admin_memberships_admin_read on public.admin_memberships;
create policy admin_memberships_own_read on public.admin_memberships
for select to authenticated
using (
  user_id = (select auth.uid())
  and coalesce((select auth.jwt()->>'aal'),'aal1') = 'aal2'
);

create policy admin_memberships_control_plane_read on public.admin_memberships
for select to authenticated
using ((select private.has_admin_role(array['super_admin','operations_admin'])));

drop policy if exists institution_admin_assignments_own_read on public.institution_admin_assignments;
create policy institution_admin_assignments_own_read on public.institution_admin_assignments
for select to authenticated
using (
  user_id = (select auth.uid())
  and coalesce((select auth.jwt()->>'aal'),'aal1') = 'aal2'
);

create table if not exists public.admin_login_attempts (
  id bigint generated always as identity primary key,
  attempt_key text not null,
  success boolean not null default false,
  attempted_at timestamptz not null default now()
);
alter table public.admin_login_attempts enable row level security;
revoke all on public.admin_login_attempts from anon, authenticated;
grant select, insert, delete on public.admin_login_attempts to service_role;
create index if not exists admin_login_attempts_key_time_idx
  on public.admin_login_attempts(attempt_key, attempted_at desc);

revoke truncate, trigger, references on public.payment_events from anon, authenticated;
revoke truncate, trigger, references on public.promotion_campaigns from anon, authenticated;
revoke truncate, trigger, references on public.subscription_events from anon, authenticated;
revoke truncate, trigger, references on public.vendor_analytics_daily from anon, authenticated;
revoke truncate, trigger, references on public.vendor_analytics_events from anon, authenticated;
revoke truncate, trigger, references on public.vendor_entitlements from anon, authenticated;
revoke truncate, trigger, references on public.vendor_listing_analytics_daily from anon, authenticated;

comment on table public.admin_login_attempts is
'Server-only Phase 6 brute-force throttle ledger. Stores only a SHA-256 attempt key, never raw credentials or IP addresses.';
