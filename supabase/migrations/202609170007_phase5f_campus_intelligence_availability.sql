-- Campus Link Phase 5F — Campus Intelligence & Availability
-- Campus locations, vendor service areas/business hours/availability and campus calendar.

create table if not exists public.campus_locations (
  id uuid primary key default gen_random_uuid(),
  institution_id uuid not null references public.institutions(id) on delete cascade,
  name text not null,
  slug text not null,
  location_type text not null default 'landmark' check (location_type in ('gate','hostel','faculty','student_centre','library','landmark','off_campus','other')),
  description text,
  is_active boolean not null default true,
  sort_order integer not null default 100,
  created_by uuid references auth.users(id) on delete set null,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now(),
  unique (institution_id, slug)
);

create table if not exists public.vendor_service_areas (
  vendor_id uuid not null references public.vendor_profiles(id) on delete cascade,
  campus_location_id uuid not null references public.campus_locations(id) on delete cascade,
  created_at timestamptz not null default now(),
  primary key (vendor_id, campus_location_id)
);

create table if not exists public.vendor_business_hours (
  vendor_id uuid not null references public.vendor_profiles(id) on delete cascade,
  day_of_week smallint not null check (day_of_week between 0 and 6),
  is_closed boolean not null default false,
  opens_at time,
  closes_at time,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now(),
  primary key (vendor_id, day_of_week),
  check (is_closed or (opens_at is not null and closes_at is not null))
);

create table if not exists public.vendor_availability (
  vendor_id uuid primary key references public.vendor_profiles(id) on delete cascade,
  manual_status text not null default 'schedule' check (manual_status in ('schedule','open','closed','busy','back_later','exam_mode')),
  status_message text,
  back_at timestamptz,
  exam_mode_until date,
  updated_at timestamptz not null default now()
);

create table if not exists public.campus_calendar_events (
  id uuid primary key default gen_random_uuid(),
  institution_id uuid not null references public.institutions(id) on delete cascade,
  event_type text not null check (event_type in ('resumption','exam','matriculation','convocation','semester_break','event','other')),
  title text not null,
  description text,
  starts_on date not null,
  ends_on date,
  is_published boolean not null default true,
  created_by uuid references auth.users(id) on delete set null,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now(),
  check (ends_on is null or ends_on >= starts_on)
);

create index if not exists campus_locations_institution_active_idx on public.campus_locations(institution_id, is_active, sort_order);
create index if not exists vendor_service_areas_location_idx on public.vendor_service_areas(campus_location_id, vendor_id);
create index if not exists vendor_business_hours_vendor_idx on public.vendor_business_hours(vendor_id, day_of_week);
create index if not exists campus_calendar_events_institution_dates_idx on public.campus_calendar_events(institution_id, starts_on, ends_on);

alter table public.campus_locations enable row level security;
alter table public.vendor_service_areas enable row level security;
alter table public.vendor_business_hours enable row level security;
alter table public.vendor_availability enable row level security;
alter table public.campus_calendar_events enable row level security;

revoke all on public.campus_locations from anon, authenticated;
revoke all on public.vendor_service_areas from anon, authenticated;
revoke all on public.vendor_business_hours from anon, authenticated;
revoke all on public.vendor_availability from anon, authenticated;
revoke all on public.campus_calendar_events from anon, authenticated;

grant select on public.campus_locations to authenticated;
grant select, insert, delete on public.vendor_service_areas to authenticated;
grant select, insert, update, delete on public.vendor_business_hours to authenticated;
grant select, insert, update on public.vendor_availability to authenticated;
grant select, insert, update, delete on public.campus_calendar_events to authenticated;
grant insert, update, delete on public.campus_locations to authenticated;

-- Shared authorization predicate for global or institution-scoped admins.
create or replace function private.can_manage_campus_intelligence(target_institution uuid)
returns boolean
language sql
stable
security invoker
set search_path = public, pg_temp
as $$
  select exists (
    select 1 from public.admin_memberships am
    where am.user_id = (select auth.uid())
      and am.is_active = true
      and am.role in ('super_admin','operations_admin','content_admin')
  ) or exists (
    select 1 from public.institution_admin_assignments ia
    where ia.user_id = (select auth.uid())
      and ia.institution_id = target_institution
      and ia.is_active = true
      and ia.role in ('school_admin','school_support')
  );
$$;

revoke all on function private.can_manage_campus_intelligence(uuid) from public, anon;
grant execute on function private.can_manage_campus_intelligence(uuid) to authenticated;

-- Campus locations: active locations are available to signed-in members; admins can inspect inactive records.
drop policy if exists campus_locations_read on public.campus_locations;
create policy campus_locations_read on public.campus_locations
for select to authenticated
using (is_active or (select private.can_manage_campus_intelligence(institution_id)));

drop policy if exists campus_locations_manage on public.campus_locations;
create policy campus_locations_manage on public.campus_locations
for all to authenticated
using ((select private.can_manage_campus_intelligence(institution_id)))
with check ((select private.can_manage_campus_intelligence(institution_id)));

-- Vendor service areas: vendor owns mutations; authenticated users can read for marketplace filtering.
drop policy if exists vendor_service_areas_read on public.vendor_service_areas;
create policy vendor_service_areas_read on public.vendor_service_areas
for select to authenticated using (true);

drop policy if exists vendor_service_areas_insert_own on public.vendor_service_areas;
create policy vendor_service_areas_insert_own on public.vendor_service_areas
for insert to authenticated
with check ((select auth.uid()) = vendor_id);

drop policy if exists vendor_service_areas_delete_own on public.vendor_service_areas;
create policy vendor_service_areas_delete_own on public.vendor_service_areas
for delete to authenticated
using ((select auth.uid()) = vendor_id);

-- Business hours: public-to-members read, vendor-owned writes.
drop policy if exists vendor_business_hours_read on public.vendor_business_hours;
create policy vendor_business_hours_read on public.vendor_business_hours
for select to authenticated using (true);

drop policy if exists vendor_business_hours_insert_own on public.vendor_business_hours;
create policy vendor_business_hours_insert_own on public.vendor_business_hours
for insert to authenticated
with check ((select auth.uid()) = vendor_id);

drop policy if exists vendor_business_hours_update_own on public.vendor_business_hours;
create policy vendor_business_hours_update_own on public.vendor_business_hours
for update to authenticated
using ((select auth.uid()) = vendor_id)
with check ((select auth.uid()) = vendor_id);

drop policy if exists vendor_business_hours_delete_own on public.vendor_business_hours;
create policy vendor_business_hours_delete_own on public.vendor_business_hours
for delete to authenticated
using ((select auth.uid()) = vendor_id);

-- Availability overrides: public-to-members read, vendor-owned writes.
drop policy if exists vendor_availability_read on public.vendor_availability;
create policy vendor_availability_read on public.vendor_availability
for select to authenticated using (true);

drop policy if exists vendor_availability_insert_own on public.vendor_availability;
create policy vendor_availability_insert_own on public.vendor_availability
for insert to authenticated
with check ((select auth.uid()) = vendor_id);

drop policy if exists vendor_availability_update_own on public.vendor_availability;
create policy vendor_availability_update_own on public.vendor_availability
for update to authenticated
using ((select auth.uid()) = vendor_id)
with check ((select auth.uid()) = vendor_id);

-- Campus calendar: students/vendors see published dates for their UX; admins manage scoped institutions.
drop policy if exists campus_calendar_read on public.campus_calendar_events;
create policy campus_calendar_read on public.campus_calendar_events
for select to authenticated
using (is_published or (select private.can_manage_campus_intelligence(institution_id)));

drop policy if exists campus_calendar_manage on public.campus_calendar_events;
create policy campus_calendar_manage on public.campus_calendar_events
for all to authenticated
using ((select private.can_manage_campus_intelligence(institution_id)))
with check ((select private.can_manage_campus_intelligence(institution_id)));
