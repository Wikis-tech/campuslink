-- Campus Link Phase 5G — Trust Profiles, Reviews & Safety
-- Explainable trust, verified-contact reviews, vendor responses, report categories,
-- safety investigation notes and admin-only risk signals.

create schema if not exists private;

-- ---------------------------------------------------------------------------
-- 1) Review trust metadata and vendor responses.
-- ---------------------------------------------------------------------------
alter table public.reviews
  add column if not exists contact_verified_at timestamptz,
  add column if not exists vendor_response text,
  add column if not exists vendor_response_status text not null default 'published',
  add column if not exists vendor_responded_at timestamptz,
  add column if not exists vendor_response_updated_at timestamptz,
  add column if not exists student_edit_count integer not null default 0,
  add column if not exists last_student_edit_at timestamptz;

do $$
begin
  if not exists (
    select 1 from pg_constraint
    where conname = 'reviews_vendor_response_status_check'
      and conrelid = 'public.reviews'::regclass
  ) then
    alter table public.reviews
      add constraint reviews_vendor_response_status_check
      check (vendor_response_status in ('published','hidden'));
  end if;
end
$$;

create index if not exists reviews_vendor_status_created_idx
  on public.reviews(vendor_id, status, created_at desc);
create index if not exists reviews_contact_verified_idx
  on public.reviews(vendor_id, contact_verified_at)
  where contact_verified_at is not null;

-- Backfill genuine Campus Link contact evidence for existing reviews.
update public.reviews r
set contact_verified_at = evidence.first_contact
from (
  select student_id, vendor_id, min(created_at) as first_contact
  from public.contact_events
  group by student_id, vendor_id
) evidence
where evidence.student_id = r.student_id
  and evidence.vendor_id = r.vendor_id
  and evidence.first_contact <= r.created_at
  and r.contact_verified_at is null;

create or replace function private.review_contact_evidence(target_student uuid, target_vendor uuid, review_time timestamptz default now())
returns timestamptz
language sql
stable
security invoker
set search_path = public, pg_temp
as $$
  select min(created_at)
  from public.contact_events
  where student_id = target_student
    and vendor_id = target_vendor
    and created_at <= review_time;
$$;

revoke all on function private.review_contact_evidence(uuid,uuid,timestamptz) from public, anon;
grant execute on function private.review_contact_evidence(uuid,uuid,timestamptz) to authenticated;

-- Student review submission is centralized so anti-spam and trust evidence cannot
-- be bypassed by changing browser payloads.
create or replace function public.student_submit_vendor_review(
  target_vendor uuid,
  review_rating integer,
  review_comment text default null
)
returns table(review_id uuid, is_new boolean, contact_verified boolean)
language plpgsql
security definer
set search_path = ''
as $$
declare
  actor uuid := (select auth.uid());
  existing public.reviews%rowtype;
  saved public.reviews%rowtype;
  contact_time timestamptz;
begin
  if actor is null then raise exception 'Authentication required'; end if;
  if review_rating < 1 or review_rating > 5 then raise exception 'Rating must be between 1 and 5'; end if;
  if char_length(trim(coalesce(review_comment,''))) > 1000 then raise exception 'Review is too long'; end if;

  if not exists (
    select 1 from public.profiles p
    where p.id = actor
      and p.account_type = 'student'
      and p.student_verification_status = 'verified'
  ) then raise exception 'Student verification is required before reviewing vendors'; end if;

  if not (select private.student_can_access_vendor(target_vendor)) then
    raise exception 'This vendor is not available to your campus';
  end if;

  select * into existing
  from public.reviews
  where student_id = actor and vendor_id = target_vendor
  for update;

  contact_time := private.review_contact_evidence(actor, target_vendor, now());

  if existing.id is null then
    insert into public.reviews(
      student_id,vendor_id,rating,comment,status,contact_verified_at,
      student_edit_count,last_student_edit_at,created_at,updated_at
    ) values (
      actor,target_vendor,review_rating,nullif(trim(coalesce(review_comment,'')),''),'published',contact_time,
      0,null,now(),now()
    ) returning * into saved;
    return query select saved.id, true, saved.contact_verified_at is not null;
    return;
  end if;

  -- Prevent scripts/repeated clicks from rewriting reputation continuously.
  if existing.last_student_edit_at is not null
     and existing.last_student_edit_at > now() - interval '60 seconds' then
    raise exception 'Please wait a minute before editing this review again';
  end if;

  update public.reviews
  set rating = review_rating,
      comment = nullif(trim(coalesce(review_comment,'')),''),
      status = 'published',
      contact_verified_at = coalesce(existing.contact_verified_at, contact_time),
      student_edit_count = existing.student_edit_count + 1,
      last_student_edit_at = now(),
      updated_at = now()
  where id = existing.id
  returning * into saved;

  return query select saved.id, false, saved.contact_verified_at is not null;
end;
$$;

revoke all on function public.student_submit_vendor_review(uuid,integer,text) from public, anon;
grant execute on function public.student_submit_vendor_review(uuid,integer,text) to authenticated;

-- Vendors can only answer reviews that belong to them. They cannot edit rating,
-- Student feedback or moderation state.
create or replace function public.vendor_respond_to_review(target_review uuid, response_text text)
returns void
language plpgsql
security definer
set search_path = ''
as $$
declare
  actor uuid := (select auth.uid());
  normalized text := trim(coalesce(response_text,''));
begin
  if actor is null then raise exception 'Authentication required'; end if;
  if char_length(normalized) < 2 or char_length(normalized) > 1000 then
    raise exception 'Response must be between 2 and 1000 characters';
  end if;

  update public.reviews
  set vendor_response = normalized,
      vendor_response_status = 'published',
      vendor_responded_at = coalesce(vendor_responded_at, now()),
      vendor_response_updated_at = now(),
      updated_at = now()
  where id = target_review
    and vendor_id = actor
    and status = 'published';

  if not found then raise exception 'Review not found or not available for response'; end if;
end;
$$;

revoke all on function public.vendor_respond_to_review(uuid,text) from public, anon;
grant execute on function public.vendor_respond_to_review(uuid,text) to authenticated;

-- ---------------------------------------------------------------------------
-- 2) Better safety-report taxonomy and investigation metadata.
-- ---------------------------------------------------------------------------
alter table public.complaints
  add column if not exists category text not null default 'other',
  add column if not exists severity text not null default 'medium',
  add column if not exists admin_notes text,
  add column if not exists assigned_to uuid references auth.users(id) on delete set null,
  add column if not exists resolution_code text,
  add column if not exists resolved_at timestamptz;

do $$
begin
  if not exists (
    select 1 from pg_constraint
    where conname = 'complaints_category_check'
      and conrelid = 'public.complaints'::regclass
  ) then
    alter table public.complaints add constraint complaints_category_check
      check (category in ('fraud_scam','harassment','fake_product','misrepresentation','unsafe_behavior','spam','prohibited_item','other'));
  end if;
  if not exists (
    select 1 from pg_constraint
    where conname = 'complaints_severity_check'
      and conrelid = 'public.complaints'::regclass
  ) then
    alter table public.complaints add constraint complaints_severity_check
      check (severity in ('low','medium','high','critical'));
  end if;
end
$$;

create index if not exists complaints_vendor_status_category_idx
  on public.complaints(vendor_id,status,category,created_at desc);
create index if not exists complaints_vendor_severity_idx
  on public.complaints(vendor_id,severity,created_at desc);

-- Private investigation notes are deliberately separated from Student-visible reports.
create table if not exists public.safety_case_notes (
  id uuid primary key default gen_random_uuid(),
  complaint_id uuid not null references public.complaints(id) on delete cascade,
  author_id uuid not null references auth.users(id) on delete restrict,
  note text not null check (char_length(trim(note)) between 2 and 3000),
  created_at timestamptz not null default now()
);

create index if not exists safety_case_notes_complaint_idx
  on public.safety_case_notes(complaint_id,created_at desc);

alter table public.safety_case_notes enable row level security;
revoke all on public.safety_case_notes from anon, authenticated;
grant select, insert on public.safety_case_notes to authenticated;

-- Admin access helper that respects global roles and institution scope.
create or replace function private.admin_can_access_vendor_safety(target_vendor uuid)
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
      and am.role in ('super_admin','operations_admin','support_admin','verification_admin')
  ) or exists (
    select 1
    from public.institution_admin_assignments ia
    join public.vendor_institutions vi on vi.institution_id = ia.institution_id
    where ia.user_id = (select auth.uid())
      and ia.is_active = true
      and ia.role in ('school_admin','school_support','school_verifier')
      and vi.vendor_id = target_vendor
  );
$$;

revoke all on function private.admin_can_access_vendor_safety(uuid) from public, anon;
grant execute on function private.admin_can_access_vendor_safety(uuid) to authenticated;

drop policy if exists safety_case_notes_admin_read on public.safety_case_notes;
create policy safety_case_notes_admin_read on public.safety_case_notes
for select to authenticated
using (
  exists (
    select 1 from public.complaints c
    where c.id = complaint_id
      and c.vendor_id is not null
      and (select private.admin_can_access_vendor_safety(c.vendor_id))
  )
);

drop policy if exists safety_case_notes_admin_insert on public.safety_case_notes;
create policy safety_case_notes_admin_insert on public.safety_case_notes
for insert to authenticated
with check (
  author_id = (select auth.uid())
  and exists (
    select 1 from public.complaints c
    where c.id = complaint_id
      and c.vendor_id is not null
      and (select private.admin_can_access_vendor_safety(c.vendor_id))
  )
);

-- Student report creation is centralized so severity is server-derived.
create or replace function public.student_report_vendor(
  target_vendor uuid,
  report_category text,
  report_title text,
  report_description text
)
returns uuid
language plpgsql
security definer
set search_path = ''
as $$
declare
  actor uuid := (select auth.uid());
  new_id uuid;
  derived_severity text;
begin
  if actor is null then raise exception 'Authentication required'; end if;
  if report_category not in ('fraud_scam','harassment','fake_product','misrepresentation','unsafe_behavior','spam','prohibited_item','other') then
    raise exception 'Invalid report category';
  end if;
  if char_length(trim(coalesce(report_title,''))) < 4 or char_length(trim(coalesce(report_title,''))) > 120 then
    raise exception 'Give this report a clear title';
  end if;
  if char_length(trim(coalesce(report_description,''))) < 10 or char_length(trim(coalesce(report_description,''))) > 1500 then
    raise exception 'Give enough factual detail for review';
  end if;
  if not exists (
    select 1 from public.profiles p
    where p.id = actor and p.account_type = 'student' and p.onboarding_completed_at is not null
  ) then raise exception 'Student account required'; end if;
  if not (select private.student_can_access_vendor(target_vendor)) then
    raise exception 'This vendor is not available to your campus';
  end if;

  derived_severity := case report_category
    when 'harassment' then 'high'
    when 'unsafe_behavior' then 'high'
    when 'fraud_scam' then 'high'
    when 'prohibited_item' then 'high'
    when 'spam' then 'low'
    else 'medium'
  end;

  insert into public.complaints(reporter_id,vendor_id,title,description,category,severity,status,created_at,updated_at)
  values (actor,target_vendor,trim(report_title),trim(report_description),report_category,derived_severity,'open',now(),now())
  returning id into new_id;

  return new_id;
end;
$$;

revoke all on function public.student_report_vendor(uuid,text,text,text) from public, anon;
grant execute on function public.student_report_vendor(uuid,text,text,text) to authenticated;

-- Admin investigation update with internal notes, assignment and resolution metadata.
create or replace function public.admin_update_safety_case(
  target_complaint uuid,
  next_status text,
  internal_note text default null,
  resolution text default null
)
returns void
language plpgsql
security definer
set search_path = ''
as $$
declare
  actor uuid := (select auth.uid());
  case_vendor uuid;
begin
  if actor is null then raise exception 'Authentication required'; end if;
  select vendor_id into case_vendor from public.complaints where id = target_complaint;
  if case_vendor is null or not (select private.admin_can_access_vendor_safety(case_vendor)) then
    raise exception 'Not authorised to manage this safety case';
  end if;
  if next_status not in ('open','reviewing','resolved','closed') then raise exception 'Invalid case status'; end if;

  update public.complaints
  set status = next_status,
      assigned_to = case when next_status = 'reviewing' then coalesce(assigned_to,actor) else assigned_to end,
      admin_notes = case when nullif(trim(coalesce(internal_note,'')),'') is not null then trim(internal_note) else admin_notes end,
      resolution_code = case when next_status in ('resolved','closed') then nullif(trim(coalesce(resolution,'')),'') else resolution_code end,
      resolved_at = case when next_status in ('resolved','closed') then now() else null end,
      updated_at = now()
  where id = target_complaint;

  if nullif(trim(coalesce(internal_note,'')),'') is not null then
    insert into public.safety_case_notes(complaint_id,author_id,note)
    values(target_complaint,actor,trim(internal_note));
  end if;

  insert into public.audit_logs(actor_id,action,entity_type,entity_id,metadata)
  values(actor,'safety_case.' || next_status,'complaint',target_complaint::text,
    jsonb_build_object('resolution',resolution));
end;
$$;

revoke all on function public.admin_update_safety_case(uuid,text,text,text) from public, anon;
grant execute on function public.admin_update_safety_case(uuid,text,text,text) to authenticated;

-- Admin-only risk signals. These are investigation aids, never public trust scores
-- and do not independently suspend a vendor.
create or replace function public.admin_vendor_risk_signals(target_vendor uuid)
returns table(
  unresolved_reports_30d integer,
  high_severity_reports_30d integer,
  verified_contact_reviews integer,
  review_burst_24h integer,
  safety_band text,
  last_report_at timestamptz
)
language plpgsql
security definer
set search_path = ''
as $$
declare
  reports integer;
  severe integer;
  verified_reviews integer;
  burst integer;
  last_report timestamptz;
begin
  if not (select private.admin_can_access_vendor_safety(target_vendor)) then
    raise exception 'Not authorised to inspect this vendor';
  end if;

  select count(distinct reporter_id)::integer,
         count(*) filter (where severity in ('high','critical'))::integer,
         max(created_at)
  into reports,severe,last_report
  from public.complaints
  where vendor_id = target_vendor
    and status in ('open','reviewing')
    and created_at >= now() - interval '30 days';

  select count(*)::integer into verified_reviews
  from public.reviews
  where vendor_id = target_vendor
    and status = 'published'
    and contact_verified_at is not null;

  select count(*)::integer into burst
  from public.reviews
  where vendor_id = target_vendor
    and status = 'published'
    and created_at >= now() - interval '24 hours';

  return query select
    coalesce(reports,0),
    coalesce(severe,0),
    coalesce(verified_reviews,0),
    coalesce(burst,0),
    case
      when coalesce(severe,0) >= 3 or coalesce(reports,0) >= 5 then 'critical'
      when coalesce(severe,0) >= 1 or coalesce(reports,0) >= 3 then 'high'
      when coalesce(reports,0) >= 1 or coalesce(burst,0) >= 8 then 'medium'
      else 'low'
    end,
    last_report;
end;
$$;

revoke all on function public.admin_vendor_risk_signals(uuid) from public, anon;
grant execute on function public.admin_vendor_risk_signals(uuid) to authenticated;

-- Admin moderation can hide a vendor response without rewriting Student feedback.
create or replace function public.admin_moderate_vendor_response(target_review uuid, next_status text)
returns void
language plpgsql
security definer
set search_path = ''
as $$
declare
  review_vendor uuid;
begin
  select vendor_id into review_vendor from public.reviews where id = target_review;
  if review_vendor is null or not (select private.admin_can_access_vendor_safety(review_vendor)) then
    raise exception 'Not authorised to moderate this response';
  end if;
  if next_status not in ('published','hidden') then raise exception 'Invalid response status'; end if;

  update public.reviews
  set vendor_response_status = next_status,
      updated_at = now()
  where id = target_review;

  insert into public.audit_logs(actor_id,action,entity_type,entity_id,metadata)
  values((select auth.uid()),'review_response.' || next_status,'review',target_review::text,'{}'::jsonb);
end;
$$;

revoke all on function public.admin_moderate_vendor_response(uuid,text) from public, anon;
grant execute on function public.admin_moderate_vendor_response(uuid,text) to authenticated;

-- Explicit Data API grants: Supabase no longer guarantees automatic exposure for new tables.
grant select, insert on public.safety_case_notes to authenticated;
