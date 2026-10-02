-- Phase 3: new Admin assignments must use dedicated Admin identities, not Student/Vendor marketplace accounts.

create or replace function public.admin_assign_school_admin_by_email(
  target_email text,
  target_institution uuid,
  assignment_role text default 'school_admin'
)
returns void
language plpgsql
security definer
set search_path = ''
as $$
declare
  target_user uuid;
  marketplace_type text;
begin
  if not (select private.has_admin_role(array['super_admin','operations_admin'])) then
    raise exception 'Not authorised to assign school admins';
  end if;
  if assignment_role <> 'school_admin' then
    raise exception 'Invalid school admin role';
  end if;

  select id into target_user from auth.users where lower(email)=lower(trim(target_email));
  if target_user is null then raise exception 'No CampusLink account exists for that email'; end if;

  select account_type into marketplace_type from public.profiles where id=target_user;
  if marketplace_type in ('student','vendor') then
    raise exception 'Use a dedicated Admin account. Student and Vendor marketplace accounts cannot receive new Admin assignments';
  end if;

  insert into public.institution_admin_assignments(user_id,institution_id,role,is_active,created_by)
  values(target_user,target_institution,'school_admin',true,(select auth.uid()))
  on conflict(user_id,institution_id) do update set
    role='school_admin',is_active=true,created_by=excluded.created_by,updated_at=now();

  insert into public.audit_logs(actor_id,action,entity_type,entity_id,metadata)
  values((select auth.uid()),'school_admin.assign','institution',target_institution::text,
    jsonb_build_object('user_id',target_user,'role','school_admin'));
end;
$$;

create or replace function public.admin_assign_global_role_by_email(
  target_email text,
  target_role text
)
returns void
language plpgsql
security definer
set search_path = ''
as $$
declare
  target_user uuid;
  marketplace_type text;
begin
  if not (select private.has_admin_role(array['super_admin'])) then
    raise exception 'Only a super admin can assign global roles';
  end if;

  if target_role not in ('super_admin','operations_admin','verification_admin','support_admin','finance_admin','content_admin','analyst') then
    raise exception 'Invalid global admin role';
  end if;

  select id into target_user from auth.users where lower(email)=lower(trim(target_email));
  if target_user is null then raise exception 'No CampusLink account exists for that email'; end if;

  select account_type into marketplace_type from public.profiles where id=target_user;
  if marketplace_type in ('student','vendor') then
    raise exception 'Use a dedicated Admin account. Student and Vendor marketplace accounts cannot receive new global Admin roles';
  end if;

  insert into public.admin_memberships(user_id,role,is_active,created_by)
  values(target_user,target_role,true,(select auth.uid()))
  on conflict(user_id) do update set role=excluded.role,is_active=true,created_by=excluded.created_by,updated_at=now();

  insert into public.audit_logs(actor_id,action,entity_type,entity_id,metadata)
  values ((select auth.uid()),'global_admin.assign','admin_membership',target_user::text,
    jsonb_build_object('role',target_role));
end;
$$;
