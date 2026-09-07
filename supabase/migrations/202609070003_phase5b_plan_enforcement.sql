-- Campus Link Phase 5B — enforce plan limits at the database boundary.
-- Trust remains independent from billing. These checks only govern growth-tool limits.
-- This migration is intentionally idempotent and repairs the Phase 3 portfolio prerequisite
-- if that migration was not applied to the live database.

create extension if not exists "pgcrypto";
create schema if not exists private;

-- ---------------------------------------------------------------------------
-- Prerequisite repair: some live databases were created before the Phase 3
-- vendor portfolio migration was applied. Create the canonical table/policies
-- here when missing so Phase 5B can safely attach its entitlement trigger.
-- ---------------------------------------------------------------------------
create table if not exists public.vendor_portfolio_items (
  id uuid primary key default gen_random_uuid(),
  vendor_id uuid not null references public.vendor_profiles(id) on delete cascade,
  title text not null,
  description text,
  image_url text not null,
  storage_path text,
  sort_order integer not null default 0,
  is_active boolean not null default true,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

alter table public.vendor_portfolio_items add column if not exists storage_path text;
alter table public.vendor_portfolio_items enable row level security;

drop policy if exists vendor_portfolio_public_read on public.vendor_portfolio_items;
create policy vendor_portfolio_public_read on public.vendor_portfolio_items
for select to anon, authenticated
using (
  is_active = true
  and exists (
    select 1
    from public.vendor_profiles vp
    where vp.id = vendor_id
      and vp.verification_status = 'approved'
  )
  and exists (
    select 1
    from public.vendor_institutions vi
    where vi.vendor_id = vendor_id
      and vi.status = 'approved'
  )
);

drop policy if exists vendor_portfolio_owner_read on public.vendor_portfolio_items;
create policy vendor_portfolio_owner_read on public.vendor_portfolio_items
for select to authenticated
using ((select auth.uid()) = vendor_id);

drop policy if exists vendor_portfolio_owner_insert on public.vendor_portfolio_items;
create policy vendor_portfolio_owner_insert on public.vendor_portfolio_items
for insert to authenticated
with check ((select auth.uid()) = vendor_id);

drop policy if exists vendor_portfolio_owner_update on public.vendor_portfolio_items;
create policy vendor_portfolio_owner_update on public.vendor_portfolio_items
for update to authenticated
using ((select auth.uid()) = vendor_id)
with check ((select auth.uid()) = vendor_id);

drop policy if exists vendor_portfolio_owner_delete on public.vendor_portfolio_items;
create policy vendor_portfolio_owner_delete on public.vendor_portfolio_items
for delete to authenticated
using ((select auth.uid()) = vendor_id);

revoke all on public.vendor_portfolio_items from anon, authenticated;
grant select on public.vendor_portfolio_items to anon, authenticated;
grant insert, update, delete on public.vendor_portfolio_items to authenticated;

create index if not exists vendor_portfolio_vendor_active_idx
  on public.vendor_portfolio_items(vendor_id, is_active, sort_order);

-- Keep the portfolio storage bucket aligned with the application upload rules.
insert into storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
values (
  'vendor-media',
  'vendor-media',
  true,
  5242880,
  array['image/jpeg','image/png','image/webp']
)
on conflict (id) do update set
  public = excluded.public,
  file_size_limit = excluded.file_size_limit,
  allowed_mime_types = excluded.allowed_mime_types;

drop policy if exists vendor_media_owner_insert on storage.objects;
create policy vendor_media_owner_insert on storage.objects
for insert to authenticated
with check (
  bucket_id = 'vendor-media'
  and (storage.foldername(name))[1] = (select auth.uid())::text
);

drop policy if exists vendor_media_owner_update on storage.objects;
create policy vendor_media_owner_update on storage.objects
for update to authenticated
using (
  bucket_id = 'vendor-media'
  and (storage.foldername(name))[1] = (select auth.uid())::text
)
with check (
  bucket_id = 'vendor-media'
  and (storage.foldername(name))[1] = (select auth.uid())::text
);

drop policy if exists vendor_media_owner_delete on storage.objects;
create policy vendor_media_owner_delete on storage.objects
for delete to authenticated
using (
  bucket_id = 'vendor-media'
  and (storage.foldername(name))[1] = (select auth.uid())::text
);

-- ---------------------------------------------------------------------------
-- Effective entitlement lookup. Always refreshes from the canonical plan /
-- subscription state and falls back safely to the Free-plan limit.
-- ---------------------------------------------------------------------------
create or replace function private.vendor_entitlement_limit(
  target_vendor uuid,
  entitlement_key text,
  fallback_limit integer
)
returns integer
language plpgsql
security definer
set search_path = ''
as $$
declare
  result_limit integer;
begin
  perform private.refresh_vendor_entitlements(target_vendor);

  select nullif(e.entitlements ->> entitlement_key, '')::integer
  into result_limit
  from public.vendor_entitlements e
  where e.vendor_id = target_vendor;

  return coalesce(result_limit, fallback_limit);
end;
$$;

revoke all on function private.vendor_entitlement_limit(uuid,text,integer)
from public, anon, authenticated;

-- ---------------------------------------------------------------------------
-- Service limit enforcement.
-- ---------------------------------------------------------------------------
create or replace function private.enforce_vendor_service_limit()
returns trigger
language plpgsql
security definer
set search_path = ''
as $$
declare
  allowed_count integer;
  current_count integer;
begin
  if new.is_active is not true then
    return new;
  end if;

  -- An update that leaves an already-active service active does not consume
  -- another entitlement slot.
  if tg_op = 'UPDATE' and old.is_active is true then
    return new;
  end if;

  allowed_count := private.vendor_entitlement_limit(
    new.vendor_id,
    'service_limit',
    5
  );

  select count(*)::integer
  into current_count
  from public.vendor_services s
  where s.vendor_id = new.vendor_id
    and s.is_active = true
    and (tg_op <> 'UPDATE' or s.id <> new.id);

  if current_count >= allowed_count then
    raise exception using
      errcode = 'P0001',
      message = format('PLAN_LIMIT_SERVICE:%s', allowed_count);
  end if;

  return new;
end;
$$;

revoke all on function private.enforce_vendor_service_limit()
from public, anon, authenticated;

drop trigger if exists vendor_services_plan_limit on public.vendor_services;
create trigger vendor_services_plan_limit
before insert or update of is_active on public.vendor_services
for each row execute procedure private.enforce_vendor_service_limit();

-- ---------------------------------------------------------------------------
-- Portfolio limit enforcement.
-- ---------------------------------------------------------------------------
create or replace function private.enforce_vendor_portfolio_limit()
returns trigger
language plpgsql
security definer
set search_path = ''
as $$
declare
  allowed_count integer;
  current_count integer;
begin
  allowed_count := private.vendor_entitlement_limit(
    new.vendor_id,
    'portfolio_limit',
    6
  );

  select count(*)::integer
  into current_count
  from public.vendor_portfolio_items p
  where p.vendor_id = new.vendor_id;

  if current_count >= allowed_count then
    raise exception using
      errcode = 'P0001',
      message = format('PLAN_LIMIT_PORTFOLIO:%s', allowed_count);
  end if;

  return new;
end;
$$;

revoke all on function private.enforce_vendor_portfolio_limit()
from public, anon, authenticated;

drop trigger if exists vendor_portfolio_plan_limit on public.vendor_portfolio_items;
create trigger vendor_portfolio_plan_limit
before insert on public.vendor_portfolio_items
for each row execute procedure private.enforce_vendor_portfolio_limit();

-- Current vendors get a refreshed snapshot immediately. Future vendors are
-- refreshed lazily by the entitlement RPC and enforcement triggers above.
do $$
declare
  v record;
begin
  for v in select id from public.vendor_profiles loop
    perform private.refresh_vendor_entitlements(v.id);
  end loop;
end
$$;
