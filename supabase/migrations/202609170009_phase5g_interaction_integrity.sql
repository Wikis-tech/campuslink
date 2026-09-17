-- Campus Link Phase 5G security follow-up.
-- Reputation/safety evidence must only be created through validated RPCs.

-- Contact events power the "Contacted through Campus Link" review label. Students
-- must not be able to manufacture those rows directly through the Data API.
create or replace function public.student_record_contact_event(
  target_vendor uuid,
  contact_channel text
)
returns void
language plpgsql
security definer
set search_path = ''
as $$
declare
  actor uuid := (select auth.uid());
  recent_count integer;
begin
  if actor is null then raise exception 'Authentication required'; end if;
  if contact_channel not in ('whatsapp','phone','email') then raise exception 'Invalid contact channel'; end if;
  if not exists (
    select 1 from public.profiles p
    where p.id = actor and p.account_type = 'student' and p.onboarding_completed_at is not null
  ) then raise exception 'Student setup required'; end if;
  if not (select private.student_can_access_vendor(target_vendor)) then raise exception 'Vendor unavailable'; end if;

  select count(*)::integer into recent_count
  from public.contact_events
  where student_id = actor
    and vendor_id = target_vendor
    and channel = contact_channel
    and created_at >= now() - interval '2 minutes';
  if recent_count > 0 then return; end if;

  insert into public.contact_events(student_id,vendor_id,channel)
  values(actor,target_vendor,contact_channel);
end;
$$;

revoke all on function public.student_record_contact_event(uuid,text) from public, anon;
grant execute on function public.student_record_contact_event(uuid,text) to authenticated;

-- All Student review writes now go through student_submit_vendor_review(); Vendor
-- responses and Admin moderation also use dedicated RPCs. This prevents a Student
-- from modifying contact_verified_at or vendor_response columns on their own row.
revoke insert, update, delete on public.reviews from authenticated;
grant select on public.reviews to authenticated;

-- Safety reports now go through student_report_vendor(), where campus access,
-- category and server-derived severity are checked. This closes cross-campus and
-- severity-tampering paths that a direct insert could otherwise create.
revoke insert, update, delete on public.complaints from authenticated;
grant select on public.complaints to authenticated;

-- Contact evidence is server-recorded only.
revoke insert, update, delete on public.contact_events from authenticated;
grant select on public.contact_events to authenticated;
