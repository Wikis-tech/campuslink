-- Campus Link V2 - synchronize the legacy bootstrap schema with the secure V2 foundation.
-- This migration intentionally supports both a fresh legacy install and the already-created V2 database.

create schema if not exists private;

alter table public.profiles
  add column if not exists account_type text not null default 'student' check (account_type in ('student','vendor')),
  add column if not exists student_verification_status text not null default 'pending' check (student_verification_status in ('pending','verified','rejected','suspended'));

-- Copy legacy values only when the legacy columns exist. Dynamic SQL avoids parse failures on the live V2 schema.
do $$
begin
  if exists (
    select 1 from information_schema.columns
    where table_schema = 'public' and table_name = 'profiles' and column_name = 'role'
  ) then
    execute $sql$
      update public.profiles
      set account_type = case when role = 'vendor' then 'vendor' else 'student' end
      where role in ('student','vendor','user')
    $sql$;
  end if;

  if exists (
    select 1 from information_schema.columns
    where table_schema = 'public' and table_name = 'profiles' and column_name = 'verification_status'
  ) then
    execute $sql$
      update public.profiles
      set student_verification_status = case
        when verification_status in ('verified','rejected','suspended') then verification_status
        else 'pending'
      end
    $sql$;
  end if;
end
$$;

alter table public.vendor_profiles
  add column if not exists business_email text,
  add column if not exists verification_status text not null default 'pending' check (verification_status in ('pending','under_review','approved','rejected','suspended')),
  add column if not exists total_contacts bigint not null default 0 check (total_contacts >= 0);

do $$
begin
  if exists (
    select 1 from information_schema.columns
    where table_schema = 'public' and table_name = 'vendor_profiles' and column_name = 'status'
  ) then
    execute $sql$
      update public.vendor_profiles
      set verification_status = case
        when status = 'approved' then 'approved'
        when status = 'rejected' then 'rejected'
        when status = 'suspended' then 'suspended'
        else 'pending'
      end
    $sql$;
  end if;
end
$$;

create table if not exists public.admin_memberships (
  user_id uuid primary key references auth.users(id) on delete cascade,
  role text not null check (role in ('super_admin','operations_admin','verification_admin','support_admin','finance_admin','content_admin','analyst')),
  is_active boolean not null default true,
  created_by uuid references auth.users(id) on delete set null,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create table if not exists public.vendor_institutions (
  vendor_id uuid not null references public.vendor_profiles(id) on delete cascade,
  institution_id uuid not null references public.institutions(id) on delete cascade,
  status text not null default 'pending' check (status in ('pending','approved','rejected','suspended')),
  is_primary boolean not null default false,
  reviewed_by uuid references auth.users(id) on delete set null,
  reviewed_at timestamptz,
  review_note text,
  created_at timestamptz not null default now(),
  primary key (vendor_id, institution_id)
);

create table if not exists public.payments (
  id uuid primary key default gen_random_uuid(),
  vendor_id uuid not null references public.vendor_profiles(id) on delete cascade,
  subscription_id uuid references public.subscriptions(id) on delete set null,
  provider text not null default 'paystack' check (provider in ('paystack','flutterwave','manual')),
  reference text not null unique,
  amount_ngn numeric(12,2) not null check (amount_ngn >= 0),
  status text not null default 'pending' check (status in ('pending','successful','failed','refunded','reversed')),
  provider_payload jsonb,
  paid_at timestamptz,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create table if not exists public.notifications (
  id uuid primary key default gen_random_uuid(),
  user_id uuid not null references auth.users(id) on delete cascade,
  title text not null,
  body text not null,
  type text not null default 'info',
  read_at timestamptz,
  created_at timestamptz not null default now()
);

create table if not exists public.audit_logs (
  id bigint generated always as identity primary key,
  actor_id uuid references auth.users(id) on delete set null,
  action text not null,
  entity_type text not null,
  entity_id text,
  metadata jsonb not null default '{}'::jsonb,
  created_at timestamptz not null default now()
);

create or replace function private.has_admin_role(required_roles text[] default null)
returns boolean
language sql
stable
security definer
set search_path = ''
as $$
  select exists (
    select 1
    from public.admin_memberships a
    where a.user_id = (select auth.uid())
      and a.is_active = true
      and (a.role = 'super_admin' or required_roles is null or a.role = any(required_roles))
  );
$$;

revoke all on function private.has_admin_role(text[]) from public;
grant usage on schema private to authenticated;
grant execute on function private.has_admin_role(text[]) to authenticated;

create or replace function public.handle_new_user()
returns trigger
language plpgsql
security definer
set search_path = ''
as $$
declare
  desired_type text;
begin
  desired_type := case when new.raw_user_meta_data ->> 'account_type' = 'vendor' then 'vendor' else 'student' end;
  insert into public.profiles (id, account_type, first_name, last_name)
  values (
    new.id,
    desired_type,
    nullif(trim(new.raw_user_meta_data ->> 'first_name'), ''),
    nullif(trim(new.raw_user_meta_data ->> 'last_name'), '')
  )
  on conflict (id) do nothing;
  return new;
end;
$$;

revoke all on function public.handle_new_user() from public;
revoke execute on function public.handle_new_user() from anon, authenticated;

alter table public.admin_memberships enable row level security;
alter table public.vendor_institutions enable row level security;
alter table public.payments enable row level security;
alter table public.notifications enable row level security;
alter table public.audit_logs enable row level security;

-- Remove permissive legacy policies that allowed sensitive status/role fields to be updated with the row.
drop policy if exists "users can update own profile" on public.profiles;
drop policy if exists "vendors can update own vendor profile" on public.vendor_profiles;
drop policy if exists "vendors manage own documents" on public.vendor_documents;

-- Rebuild narrow ownership policies.
drop policy if exists profiles_own_update on public.profiles;
create policy profiles_own_update on public.profiles
for update to authenticated
using ((select auth.uid()) = id)
with check ((select auth.uid()) = id);

drop policy if exists admin_memberships_admin_read on public.admin_memberships;
create policy admin_memberships_admin_read on public.admin_memberships
for select to authenticated using ((select private.has_admin_role(null)));

drop policy if exists admin_memberships_super_admin_manage on public.admin_memberships;
create policy admin_memberships_super_admin_manage on public.admin_memberships
for all to authenticated
using ((select private.has_admin_role(array['super_admin'])))
with check ((select private.has_admin_role(array['super_admin'])));

drop policy if exists vendor_institutions_public_read on public.vendor_institutions;
create policy vendor_institutions_public_read on public.vendor_institutions
for select to anon, authenticated using (status = 'approved');

drop policy if exists vendor_institutions_owner_read on public.vendor_institutions;
create policy vendor_institutions_owner_read on public.vendor_institutions
for select to authenticated using ((select auth.uid()) = vendor_id);

drop policy if exists vendor_institutions_owner_insert on public.vendor_institutions;
create policy vendor_institutions_owner_insert on public.vendor_institutions
for insert to authenticated
with check ((select auth.uid()) = vendor_id and status = 'pending' and reviewed_by is null and reviewed_at is null);

drop policy if exists vendor_institutions_owner_delete_pending on public.vendor_institutions;
create policy vendor_institutions_owner_delete_pending on public.vendor_institutions
for delete to authenticated using ((select auth.uid()) = vendor_id and status = 'pending');

drop policy if exists vendor_institutions_admin_manage on public.vendor_institutions;
create policy vendor_institutions_admin_manage on public.vendor_institutions
for all to authenticated
using ((select private.has_admin_role(array['operations_admin','verification_admin'])))
with check ((select private.has_admin_role(array['operations_admin','verification_admin'])));

drop policy if exists payments_vendor_read on public.payments;
create policy payments_vendor_read on public.payments
for select to authenticated using ((select auth.uid()) = vendor_id);

drop policy if exists payments_finance_read on public.payments;
create policy payments_finance_read on public.payments
for select to authenticated using ((select private.has_admin_role(array['finance_admin','operations_admin','analyst'])));

drop policy if exists notifications_owner_read on public.notifications;
create policy notifications_owner_read on public.notifications
for select to authenticated using ((select auth.uid()) = user_id);

drop policy if exists notifications_owner_update on public.notifications;
create policy notifications_owner_update on public.notifications
for update to authenticated
using ((select auth.uid()) = user_id)
with check ((select auth.uid()) = user_id);

drop policy if exists audit_logs_admin_read on public.audit_logs;
create policy audit_logs_admin_read on public.audit_logs
for select to authenticated using ((select private.has_admin_role(array['super_admin','operations_admin','analyst'])));

-- Least-privilege grants. RLS controls rows; column grants protect sensitive fields from self-editing.
revoke update on public.profiles from authenticated;
grant update (first_name,last_name,phone,avatar_url,institution_id,updated_at) on public.profiles to authenticated;

revoke update on public.vendor_profiles from authenticated;
grant update (business_name,slug,description,whatsapp_number,business_email,location_text,logo_url,cover_url,updated_at) on public.vendor_profiles to authenticated;

revoke all on public.admin_memberships, public.vendor_institutions, public.payments, public.notifications, public.audit_logs from anon, authenticated;
grant select on public.vendor_institutions to anon, authenticated;
grant select on public.admin_memberships, public.payments, public.notifications, public.audit_logs to authenticated;
grant insert, delete on public.vendor_institutions to authenticated;
grant update (read_at) on public.notifications to authenticated;

create index if not exists vendor_institutions_institution_status_idx on public.vendor_institutions(institution_id,status);
create index if not exists payments_vendor_status_idx on public.payments(vendor_id,status);
create index if not exists notifications_user_unread_idx on public.notifications(user_id,read_at);
create index if not exists audit_logs_actor_idx on public.audit_logs(actor_id);
