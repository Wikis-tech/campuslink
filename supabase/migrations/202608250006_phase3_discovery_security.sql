-- Campus Link V2 - Phase 3 discovery, favourites, contact tracking and review hardening.

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
  );
$$;

revoke all on function private.student_can_access_vendor(uuid) from public;
grant execute on function private.student_can_access_vendor(uuid) to authenticated;

-- Saved vendors: a student may save only discoverable vendors from their own institution.
drop policy if exists "students manage saved vendors" on public.saved_vendors;
drop policy if exists saved_vendors_owner_select on public.saved_vendors;
drop policy if exists saved_vendors_owner_insert on public.saved_vendors;
drop policy if exists saved_vendors_owner_delete on public.saved_vendors;

create policy saved_vendors_owner_select on public.saved_vendors
for select to authenticated
using ((select auth.uid()) = student_id);

create policy saved_vendors_owner_insert on public.saved_vendors
for insert to authenticated
with check (
  (select auth.uid()) = student_id
  and (select private.student_can_access_vendor(vendor_id))
);

create policy saved_vendors_owner_delete on public.saved_vendors
for delete to authenticated
using ((select auth.uid()) = student_id);

-- Contact events: only authenticated students can create contact events for a vendor discoverable on their campus.
drop policy if exists "students create contact events" on public.contact_events;
drop policy if exists contact_events_student_insert on public.contact_events;
create policy contact_events_student_insert on public.contact_events
for insert to authenticated
with check (
  (select auth.uid()) = student_id
  and (select private.student_can_access_vendor(vendor_id))
);

-- Reviews: verified students can create or modify their own review only for a discoverable vendor on their campus.
drop policy if exists reviews_student_insert on public.reviews;
drop policy if exists "students update own reviews" on public.reviews;
drop policy if exists "students delete own reviews" on public.reviews;

create policy reviews_student_insert on public.reviews
for insert to authenticated
with check (
  (select auth.uid()) = student_id
  and exists (
    select 1 from public.profiles p
    where p.id = (select auth.uid())
      and p.account_type = 'student'
      and p.student_verification_status = 'verified'
  )
  and (select private.student_can_access_vendor(vendor_id))
  and status = 'published'
);

create policy reviews_student_update on public.reviews
for update to authenticated
using ((select auth.uid()) = student_id)
with check (
  (select auth.uid()) = student_id
  and exists (
    select 1 from public.profiles p
    where p.id = (select auth.uid())
      and p.account_type = 'student'
      and p.student_verification_status = 'verified'
  )
  and (select private.student_can_access_vendor(vendor_id))
  and status = 'published'
);

create policy reviews_student_delete on public.reviews
for delete to authenticated
using ((select auth.uid()) = student_id);

-- Complaints against vendors are limited to vendors discoverable on the reporter's campus.
drop policy if exists "users create own complaints" on public.complaints;
drop policy if exists complaints_owner_insert on public.complaints;
create policy complaints_owner_insert on public.complaints
for insert to authenticated
with check (
  (select auth.uid()) = reporter_id
  and (
    vendor_id is null
    or (select private.student_can_access_vendor(vendor_id))
    or exists (
      select 1 from public.vendor_profiles vp
      where vp.id = vendor_id and vp.id = (select auth.uid())
    )
  )
);

-- Maintain vendor rating aggregates in the database instead of trusting clients.
create or replace function private.recalculate_vendor_rating(target_vendor uuid)
returns void
language plpgsql
security definer
set search_path = ''
as $$
begin
  update public.vendor_profiles vp
  set
    average_rating = coalesce((
      select round(avg(r.rating)::numeric, 2)
      from public.reviews r
      where r.vendor_id = target_vendor and r.status = 'published'
    ), 0),
    review_count = (
      select count(*)::integer
      from public.reviews r
      where r.vendor_id = target_vendor and r.status = 'published'
    ),
    updated_at = now()
  where vp.id = target_vendor;
end;
$$;

revoke all on function private.recalculate_vendor_rating(uuid) from public;

create or replace function private.handle_review_rating_change()
returns trigger
language plpgsql
security definer
set search_path = ''
as $$
begin
  if tg_op = 'DELETE' then
    perform private.recalculate_vendor_rating(old.vendor_id);
    return old;
  end if;

  perform private.recalculate_vendor_rating(new.vendor_id);
  if tg_op = 'UPDATE' and old.vendor_id is distinct from new.vendor_id then
    perform private.recalculate_vendor_rating(old.vendor_id);
  end if;
  return new;
end;
$$;

revoke all on function private.handle_review_rating_change() from public;

drop trigger if exists reviews_recalculate_vendor_rating on public.reviews;
create trigger reviews_recalculate_vendor_rating
after insert or update or delete on public.reviews
for each row execute procedure private.handle_review_rating_change();

create index if not exists saved_vendors_student_created_idx on public.saved_vendors(student_id, created_at desc);
create index if not exists reviews_vendor_published_created_idx on public.reviews(vendor_id, created_at desc) where status = 'published';
create index if not exists vendor_services_active_vendor_idx on public.vendor_services(vendor_id) where is_active = true;
create index if not exists contact_events_student_vendor_idx on public.contact_events(student_id, vendor_id, created_at desc);
