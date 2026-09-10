-- Campus Link — vendor products, marketplace safety holds, and report escalation.
-- Safe to rerun after a partial/failed execution.
-- Products are discovery/contact listings only. Campus Link does not process student-to-vendor purchases.

create extension if not exists "pgcrypto";
create schema if not exists private;

-- ---------------------------------------------------------------------------
-- 1) Marketplace safety state first, because product RLS depends on it.
-- ---------------------------------------------------------------------------
alter table public.vendor_profiles
  add column if not exists marketplace_status text not null default 'active',
  add column if not exists risk_report_count integer not null default 0,
  add column if not exists suspended_until timestamptz,
  add column if not exists suspension_reason text,
  add column if not exists safety_reviewed_at timestamptz,
  add column if not exists safety_reviewed_by uuid references auth.users(id) on delete set null;

do $$
begin
  if not exists (
    select 1 from pg_constraint
    where conname = 'vendor_profiles_marketplace_status_check'
      and conrelid = 'public.vendor_profiles'::regclass
  ) then
    alter table public.vendor_profiles
      add constraint vendor_profiles_marketplace_status_check
      check (marketplace_status in ('active','under_review','suspended'));
  end if;
end
$$;

create index if not exists vendor_profiles_marketplace_status_idx
  on public.vendor_profiles(marketplace_status, suspended_until);

create or replace function private.student_can_access_vendor(target_vendor uuid)
returns boolean
language sql
stable
security definer
set search_path = ''
as $$
  select exists (
    select 1
    from public.profiles p
    join public.vendor_institutions vi
      on vi.institution_id = p.institution_id
     and vi.vendor_id = target_vendor
     and vi.status = 'approved'
    join public.vendor_profiles vp
      on vp.id = target_vendor
     and vp.verification_status = 'approved'
    where p.id = (select auth.uid())
      and p.account_type = 'student'
      and p.onboarding_completed_at is not null
      and (
        vp.marketplace_status = 'active'
        or (
          vp.marketplace_status = 'suspended'
          and vp.suspended_until is not null
          and vp.suspended_until <= now()
        )
      )
  );
$$;

revoke all on function private.student_can_access_vendor(uuid) from public;
grant usage on schema private to authenticated;
grant execute on function private.student_can_access_vendor(uuid) to authenticated;

drop policy if exists "public can view approved vendors" on public.vendor_profiles;
drop policy if exists vendor_profiles_public_read on public.vendor_profiles;
create policy vendor_profiles_public_read on public.vendor_profiles
for select to anon, authenticated
using (
  verification_status = 'approved'
  and (
    marketplace_status = 'active'
    or (
      marketplace_status = 'suspended'
      and suspended_until is not null
      and suspended_until <= now()
    )
  )
);

-- ---------------------------------------------------------------------------
-- 2) Product catalogue, separate from services and portfolio.
-- ---------------------------------------------------------------------------
create table if not exists public.vendor_products (
  id uuid primary key default gen_random_uuid(),
  vendor_id uuid not null references public.vendor_profiles(id) on delete cascade,
  category_id uuid references public.categories(id) on delete set null,
  name text not null,
  description text,
  price_ngn numeric(12,2),
  pricing_type text not null default 'fixed' check (pricing_type in ('fixed','from','contact')),
  cover_image_url text,
  storage_path text,
  is_active boolean not null default true,
  sort_order integer not null default 0,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now(),
  check (char_length(trim(name)) between 2 and 140),
  check (price_ngn is null or price_ngn >= 0)
);

create index if not exists vendor_products_vendor_active_idx
  on public.vendor_products(vendor_id, is_active, sort_order, created_at desc);
create index if not exists vendor_products_category_active_idx
  on public.vendor_products(category_id, is_active, created_at desc);

alter table public.vendor_products enable row level security;

update public.subscription_plans
set entitlements = entitlements || jsonb_build_object(
  'product_limit', case when tier = 'pro' then 30 else 5 end
), updated_at = now()
where tier in ('free','pro');

create or replace function private.effective_product_limit(target_vendor uuid)
returns integer
language plpgsql
security definer
set search_path = ''
as $$
declare
  limit_value integer;
begin
  perform private.refresh_vendor_entitlements(target_vendor);
  select greatest(0, coalesce((e.entitlements ->> 'product_limit')::integer, 5))
    into limit_value
  from public.vendor_entitlements e
  where e.vendor_id = target_vendor;
  return coalesce(limit_value, 5);
end;
$$;

revoke all on function private.effective_product_limit(uuid) from public, anon, authenticated;

create or replace function private.enforce_vendor_product_limit()
returns trigger
language plpgsql
security definer
set search_path = ''
as $$
declare
  allowed_count integer;
  active_count integer;
begin
  if new.is_active is not true then return new; end if;
  if tg_op = 'UPDATE' and old.is_active is true then return new; end if;

  allowed_count := private.effective_product_limit(new.vendor_id);
  select count(*)::integer into active_count
  from public.vendor_products p
  where p.vendor_id = new.vendor_id
    and p.is_active = true
    and (tg_op <> 'UPDATE' or p.id <> new.id);

  if active_count >= allowed_count then
    raise exception 'Your current Campus Link plan allows % active product listings. Pause or remove a product, or upgrade your plan.', allowed_count;
  end if;
  return new;
end;
$$;

revoke all on function private.enforce_vendor_product_limit() from public, anon, authenticated;

drop trigger if exists vendor_products_plan_limit on public.vendor_products;
create trigger vendor_products_plan_limit
before insert or update of is_active on public.vendor_products
for each row execute procedure private.enforce_vendor_product_limit();

drop policy if exists vendor_products_owner_read on public.vendor_products;
create policy vendor_products_owner_read on public.vendor_products
for select to authenticated using ((select auth.uid()) = vendor_id);

drop policy if exists vendor_products_owner_insert on public.vendor_products;
create policy vendor_products_owner_insert on public.vendor_products
for insert to authenticated with check ((select auth.uid()) = vendor_id);

drop policy if exists vendor_products_owner_update on public.vendor_products;
create policy vendor_products_owner_update on public.vendor_products
for update to authenticated
using ((select auth.uid()) = vendor_id)
with check ((select auth.uid()) = vendor_id);

drop policy if exists vendor_products_owner_delete on public.vendor_products;
create policy vendor_products_owner_delete on public.vendor_products
for delete to authenticated using ((select auth.uid()) = vendor_id);

drop policy if exists vendor_products_student_read on public.vendor_products;
create policy vendor_products_student_read on public.vendor_products
for select to authenticated
using (
  is_active = true
  and (select private.student_can_access_vendor(vendor_id))
);

revoke all on public.vendor_products from anon, authenticated;
grant select, insert, update, delete on public.vendor_products to authenticated;

-- ---------------------------------------------------------------------------
-- 3) Report escalation: five DISTINCT unresolved reporters in 30 days -> hold.
-- ---------------------------------------------------------------------------
create unique index if not exists complaints_one_unresolved_per_reporter_vendor_uidx
  on public.complaints(reporter_id, vendor_id)
  where vendor_id is not null and status in ('open','reviewing');

create or replace function private.recalculate_vendor_report_risk(target_vendor uuid)
returns void
language plpgsql
security definer
set search_path = ''
as $$
declare
  report_total integer;
  current_marketplace_status text;
begin
  select count(distinct reporter_id)::integer into report_total
  from public.complaints
  where vendor_id = target_vendor
    and status in ('open','reviewing')
    and created_at >= now() - interval '30 days';

  select marketplace_status into current_marketplace_status
  from public.vendor_profiles
  where id = target_vendor;

  update public.vendor_profiles
  set
    risk_report_count = coalesce(report_total, 0),
    marketplace_status = case
      when coalesce(report_total, 0) >= 5 and current_marketplace_status = 'active' then 'under_review'
      else current_marketplace_status
    end,
    suspension_reason = case
      when coalesce(report_total, 0) >= 5 and current_marketplace_status = 'active'
        then 'Automatically placed under safety review after five distinct unresolved reports in 30 days.'
      else suspension_reason
    end,
    updated_at = now()
  where id = target_vendor;
end;
$$;

revoke all on function private.recalculate_vendor_report_risk(uuid) from public, anon, authenticated;

create or replace function private.handle_complaint_vendor_risk()
returns trigger
language plpgsql
security definer
set search_path = ''
as $$
begin
  if tg_op = 'DELETE' then
    if old.vendor_id is not null then
      perform private.recalculate_vendor_report_risk(old.vendor_id);
    end if;
    return old;
  end if;

  if new.vendor_id is not null then
    perform private.recalculate_vendor_report_risk(new.vendor_id);
  end if;

  if tg_op = 'UPDATE'
     and old.vendor_id is distinct from new.vendor_id
     and old.vendor_id is not null then
    perform private.recalculate_vendor_report_risk(old.vendor_id);
  end if;

  return new;
end;
$$;

revoke all on function private.handle_complaint_vendor_risk() from public, anon, authenticated;

drop trigger if exists complaints_vendor_risk_refresh on public.complaints;
create trigger complaints_vendor_risk_refresh
after insert or update or delete on public.complaints
for each row execute procedure private.handle_complaint_vendor_risk();

-- ---------------------------------------------------------------------------
-- 4) Admin marketplace-safety action, separate from billing and verification.
-- ---------------------------------------------------------------------------
create or replace function public.admin_set_vendor_marketplace_status(
  target_vendor uuid,
  next_status text,
  suspension_days integer default null,
  note text default null
)
returns void
language plpgsql
security definer
set search_path = ''
as $$
declare
  until_time timestamptz;
begin
  if not (select private.has_admin_role(array['super_admin','operations_admin','support_admin','verification_admin'])) then
    raise exception 'Not authorised to manage vendor marketplace safety';
  end if;

  if next_status not in ('active','under_review','suspended') then
    raise exception 'Invalid marketplace status';
  end if;

  if suspension_days is not null and (suspension_days < 1 or suspension_days > 365) then
    raise exception 'Suspension days must be between 1 and 365';
  end if;

  until_time := case
    when next_status = 'suspended' and suspension_days is not null
      then now() + make_interval(days => suspension_days)
    else null
  end;

  update public.vendor_profiles
  set
    marketplace_status = next_status,
    suspended_until = until_time,
    suspension_reason = nullif(trim(coalesce(note,'')),''),
    safety_reviewed_at = now(),
    safety_reviewed_by = (select auth.uid()),
    updated_at = now()
  where id = target_vendor;

  if not found then raise exception 'Vendor not found'; end if;

  insert into public.audit_logs(actor_id,action,entity_type,entity_id,metadata)
  values (
    (select auth.uid()),
    'vendor_marketplace.' || next_status,
    'vendor',
    target_vendor::text,
    jsonb_build_object('suspension_days', suspension_days, 'note', note)
  );
end;
$$;

revoke all on function public.admin_set_vendor_marketplace_status(uuid,text,integer,text) from public, anon;
grant execute on function public.admin_set_vendor_marketplace_status(uuid,text,integer,text) to authenticated;

-- ---------------------------------------------------------------------------
-- 5) Refresh existing vendors so product_limit and report risk are ready now.
-- ---------------------------------------------------------------------------
do $$
declare
  v record;
begin
  for v in select id from public.vendor_profiles loop
    perform private.refresh_vendor_entitlements(v.id);
    perform private.recalculate_vendor_report_risk(v.id);
  end loop;
end
$$;
