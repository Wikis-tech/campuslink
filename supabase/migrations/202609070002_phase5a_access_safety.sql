-- Phase 5A follow-up: tighten public plan visibility and make entitlement refresh callable safely.

-- Remove the legacy policy that exposed every active plan regardless of is_public.
drop policy if exists "public can view active plans" on public.subscription_plans;

-- The entitlement RPC refreshes a snapshot, so it must remain VOLATILE (the PostgreSQL default),
-- not STABLE. It is still scoped to auth.uid() and cannot be used to request another vendor.
create or replace function public.get_my_vendor_entitlements()
returns table (
  tier text,
  plan_slug text,
  plan_name text,
  billing_interval text,
  entitlements jsonb,
  valid_until timestamptz
)
language plpgsql
volatile
security definer
set search_path = ''
as $$
declare
  current_vendor uuid := (select auth.uid());
begin
  if current_vendor is null then
    return;
  end if;

  if not exists (
    select 1 from public.profiles p
    where p.id = current_vendor and p.account_type = 'vendor'
  ) then
    return;
  end if;

  perform private.refresh_vendor_entitlements(current_vendor);

  return query
  select e.tier, p.slug, p.name, p.billing_interval, e.entitlements, e.valid_until
  from public.vendor_entitlements e
  join public.subscription_plans p on p.id = e.plan_id
  where e.vendor_id = current_vendor;
end;
$$;

revoke all on function public.get_my_vendor_entitlements() from public, anon;
grant execute on function public.get_my_vendor_entitlements() to authenticated;
