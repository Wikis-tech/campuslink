-- Phase 7A: Super Admin branding controls + PWA identity foundation
create table if not exists public.platform_branding (
  id smallint primary key default 1 check (id = 1),
  website_logo_url text,
  favicon_url text,
  app_icon_url text,
  revision bigint not null default 1,
  updated_by uuid references auth.users(id) on delete set null,
  updated_at timestamptz not null default now()
);

alter table public.platform_branding enable row level security;

drop policy if exists "Public can read platform branding" on public.platform_branding;
create policy "Public can read platform branding"
on public.platform_branding
for select
to anon, authenticated
using (true);

revoke insert, update, delete on public.platform_branding from anon, authenticated;
grant select on public.platform_branding to anon, authenticated;

insert into public.platform_branding (id)
values (1)
on conflict (id) do nothing;

insert into storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
values (
  'branding-assets',
  'branding-assets',
  true,
  5242880,
  array['image/png','image/jpeg','image/webp']::text[]
)
on conflict (id) do update
set public = excluded.public,
    file_size_limit = excluded.file_size_limit,
    allowed_mime_types = excluded.allowed_mime_types;
