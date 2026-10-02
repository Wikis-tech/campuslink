-- Phase 3 security gate: remove broad direct-write permissions that exceeded the documented Admin role model.

drop policy if exists institutions_admin_all on public.institutions;

drop policy if exists vendor_institutions_admin_manage on public.vendor_institutions;
drop policy if exists vendor_institutions_global_admin_read on public.vendor_institutions;
create policy vendor_institutions_global_admin_read
on public.vendor_institutions
for select
to authenticated
using ((select private.has_admin_role(array['super_admin','operations_admin','analyst'])));

drop policy if exists vendor_profiles_admin_manage on public.vendor_profiles;

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
          and am.role in ('super_admin','operations_admin','support_admin')
      )
      or exists (
        select 1
        from public.institution_admin_assignments ia
        join public.vendor_institutions vi on vi.institution_id = ia.institution_id
        where ia.user_id = (select auth.uid())
          and ia.is_active = true
          and ia.role = 'school_admin'
          and vi.vendor_id = target_vendor
      )
    );
$$;

create or replace function public.admin_review_vendor_campus(
  target_vendor uuid,
  target_institution uuid,
  decision text,
  note text default null
)
returns void
language plpgsql
security definer
set search_path = ''
as $$
declare
  next_status text;
begin
  if not (
    (select private.has_admin_role(array['super_admin','operations_admin']))
    or (select private.has_institution_admin_role(target_institution,array['school_admin']))
  ) then raise exception 'Not authorised for this campus'; end if;

  next_status := case decision
    when 'approve' then 'approved'
    when 'reject' then 'rejected'
    when 'suspend' then 'suspended'
    else null
  end;
  if next_status is null then raise exception 'Invalid campus decision'; end if;

  update public.vendor_institutions
  set status = next_status,
      reviewed_by = (select auth.uid()),
      reviewed_at = now(),
      review_note = nullif(trim(note),'')
  where vendor_id = target_vendor and institution_id = target_institution;

  if not found then raise exception 'Vendor campus request not found'; end if;

  insert into public.audit_logs(actor_id,action,entity_type,entity_id,metadata)
  values((select auth.uid()),'vendor_campus.'||next_status,'vendor',target_vendor::text,
    jsonb_build_object('institution_id',target_institution,'note',note));
end;
$$;

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
  if not (select private.has_admin_role(array['super_admin','operations_admin','support_admin'])) then
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
  set marketplace_status = next_status,
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
