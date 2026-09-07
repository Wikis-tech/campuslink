-- Campus Link Phase 5B — enforce plan limits at the database boundary.
-- Trust remains independent from billing. These checks only govern growth-tool limits.

create schema if not exists private;

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

revoke all on function private.vendor_entitlement_limit(uuid,text,integer) from public, anon, authenticated;

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

  -- Updates that keep an already-active row active do not consume another slot.
  if tg_op = 'UPDATE' and old.is_active is true then
    return new;
  end if;

  allowed_count := private.vendor_entitlement_limit(new.vendor_id, 'service_limit', 5);

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

revoke all on function private.enforce_vendor_service_limit() from public, anon, authenticated;

drop trigger if exists vendor_services_plan_limit on public.vendor_services;
create trigger vendor_services_plan_limit
before insert or update of is_active on public.vendor_services
for each row execute procedure private.enforce_vendor_service_limit();

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
  allowed_count := private.vendor_entitlement_limit(new.vendor_id, 'portfolio_limit', 6);

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

revoke all on function private.enforce_vendor_portfolio_limit() from public, anon, authenticated;

drop trigger if exists vendor_portfolio_plan_limit on public.vendor_portfolio_items;
create trigger vendor_portfolio_plan_limit
before insert on public.vendor_portfolio_items
for each row execute procedure private.enforce_vendor_portfolio_limit();

-- Current vendors get a refreshed snapshot immediately. Future vendors are refreshed lazily
-- by the entitlement RPC and the enforcement triggers above.
do $$
declare
  v record;
begin
  for v in select id from public.vendor_profiles loop
    perform private.refresh_vendor_entitlements(v.id);
  end loop;
end
$$;
