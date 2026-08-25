-- Campus Link V2 - Phase 3 vendor portfolio support.

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
    select 1 from public.vendor_profiles vp
    where vp.id = vendor_id and vp.verification_status = 'approved'
  )
  and exists (
    select 1 from public.vendor_institutions vi
    where vi.vendor_id = vendor_id and vi.status = 'approved'
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

create index if not exists vendor_portfolio_vendor_active_idx on public.vendor_portfolio_items(vendor_id, is_active, sort_order);

insert into storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
values ('vendor-media', 'vendor-media', true, 5242880, array['image/jpeg','image/png','image/webp'])
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
