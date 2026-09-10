-- Campus Link V2 - Phase 4 content management and school request moderation.

-- Global content/operations admins can fully manage category records.
drop policy if exists categories_admin_manage on public.categories;
create policy categories_admin_manage on public.categories
for all to authenticated
using ((select private.has_admin_role(array['super_admin','operations_admin','content_admin'])))
with check ((select private.has_admin_role(array['super_admin','operations_admin','content_admin'])));

grant insert, update, delete on public.categories to authenticated;

create or replace function public.admin_upsert_category(
  category_id uuid,
  category_name text,
  category_slug text,
  category_description text default null,
  category_icon text default null,
  category_active boolean default true
)
returns uuid
language plpgsql
security definer
set search_path = ''
as $$
declare
  result_id uuid;
begin
  if not (select private.has_admin_role(array['super_admin','operations_admin','content_admin'])) then
    raise exception 'Not authorised to manage categories';
  end if;

  if trim(coalesce(category_name,'')) = '' or trim(coalesce(category_slug,'')) = '' then
    raise exception 'Category name and slug are required';
  end if;

  if category_id is null then
    insert into public.categories(name,slug,description,icon,is_active)
    values(trim(category_name),lower(trim(category_slug)),nullif(trim(category_description),''),nullif(trim(category_icon),''),category_active)
    returning id into result_id;
  else
    update public.categories
    set name=trim(category_name),slug=lower(trim(category_slug)),description=nullif(trim(category_description),''),icon=nullif(trim(category_icon),''),is_active=category_active
    where id=category_id
    returning id into result_id;
    if result_id is null then raise exception 'Category not found'; end if;
  end if;

  insert into public.audit_logs(actor_id,action,entity_type,entity_id,metadata)
  values((select auth.uid()),case when category_id is null then 'category.create' else 'category.update' end,'category',result_id::text,jsonb_build_object('name',category_name));
  return result_id;
end;
$$;

revoke all on function public.admin_upsert_category(uuid,text,text,text,text,boolean) from public, anon;
grant execute on function public.admin_upsert_category(uuid,text,text,text,text,boolean) to authenticated;

create or replace function public.admin_moderate_review(
  review_id uuid,
  next_status text
)
returns void
language plpgsql
security definer
set search_path = ''
as $$
declare
  target_vendor uuid;
begin
  if next_status not in ('published','hidden','reported') then raise exception 'Invalid review status'; end if;
  select vendor_id into target_vendor from public.reviews where id=review_id;
  if target_vendor is null then raise exception 'Review not found'; end if;

  if not (
    (select private.has_admin_role(array['super_admin','operations_admin','support_admin','content_admin']))
    or exists (
      select 1 from public.vendor_institutions vi
      where vi.vendor_id = target_vendor
        and (select private.has_institution_admin_role(vi.institution_id,array['school_admin','school_support']))
    )
  ) then raise exception 'Not authorised to moderate this review'; end if;

  update public.reviews set status=next_status,updated_at=now() where id=review_id;
  insert into public.audit_logs(actor_id,action,entity_type,entity_id,metadata)
  values((select auth.uid()),'review.'||next_status,'review',review_id::text,jsonb_build_object('vendor_id',target_vendor));
end;
$$;

revoke all on function public.admin_moderate_review(uuid,text) from public, anon;
grant execute on function public.admin_moderate_review(uuid,text) to authenticated;

create or replace function public.admin_process_school_request(
  request_id uuid,
  decision text
)
returns uuid
language plpgsql
security definer
set search_path = ''
as $$
declare
  req public.school_requests%rowtype;
  base_slug text;
  final_slug text;
  created_school uuid;
begin
  if not (select private.has_admin_role(array['super_admin','operations_admin','content_admin'])) then
    raise exception 'Not authorised to process school requests';
  end if;
  if decision not in ('approve','reject') then raise exception 'Decision must be approve or reject'; end if;

  select * into req from public.school_requests where id=request_id for update;
  if req.id is null then raise exception 'School request not found'; end if;
  if req.status <> 'pending' then raise exception 'School request was already processed'; end if;

  if decision='reject' then
    update public.school_requests set status='rejected',reviewed_by=(select auth.uid()),reviewed_at=now() where id=request_id;
    insert into public.audit_logs(actor_id,action,entity_type,entity_id,metadata)
    values((select auth.uid()),'school_request.rejected','school_request',request_id::text,jsonb_build_object('school_name',req.school_name));
    return null;
  end if;

  base_slug := trim(both '-' from regexp_replace(lower(req.school_name),'[^a-z0-9]+','-','g'));
  final_slug := base_slug;
  if exists(select 1 from public.institutions where slug=final_slug) then
    final_slug := base_slug || '-' || substr(request_id::text,1,8);
  end if;

  insert into public.institutions(name,slug,city,state,country,is_active,verification_mode)
  values(req.school_name,final_slug,req.city,req.state,'Nigeria',true,'hybrid')
  returning id into created_school;

  update public.school_requests set status='approved',reviewed_by=(select auth.uid()),reviewed_at=now() where id=request_id;
  insert into public.audit_logs(actor_id,action,entity_type,entity_id,metadata)
  values((select auth.uid()),'school_request.approved','school_request',request_id::text,jsonb_build_object('institution_id',created_school,'school_name',req.school_name));

  return created_school;
end;
$$;

revoke all on function public.admin_process_school_request(uuid,text) from public, anon;
grant execute on function public.admin_process_school_request(uuid,text) to authenticated;
