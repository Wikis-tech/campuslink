-- Campus Link Phase 5C — Paystack test-mode security and billing customer mapping.
-- Billing never grants verification or campus approval.

create table if not exists public.billing_customers (
  vendor_id uuid primary key references public.vendor_profiles(id) on delete cascade,
  provider text not null default 'paystack' check (provider = 'paystack'),
  provider_customer_code text not null unique,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

alter table public.billing_customers enable row level security;

drop policy if exists billing_customers_owner_read on public.billing_customers;
create policy billing_customers_owner_read on public.billing_customers
for select to authenticated using ((select auth.uid()) = vendor_id);

drop policy if exists billing_customers_admin_read on public.billing_customers;
create policy billing_customers_admin_read on public.billing_customers
for select to authenticated using ((select private.has_admin_role(array['finance_admin','operations_admin','analyst'])));

revoke all on public.billing_customers from anon, authenticated;
grant select on public.billing_customers to authenticated;

alter table public.payment_events
  add column if not exists processing_started_at timestamptz;

create index if not exists payment_events_processing_idx
  on public.payment_events(processed_at, processing_started_at, created_at);

-- Service-only entitlement refresh after a verified billing lifecycle change.
create or replace function public.refresh_vendor_entitlements_after_billing(target_vendor uuid)
returns void
language plpgsql
security definer
set search_path = ''
as $$
begin
  if (select auth.role()) <> 'service_role' then
    raise exception 'service role required';
  end if;
  perform private.refresh_vendor_entitlements(target_vendor);
end;
$$;

revoke all on function public.refresh_vendor_entitlements_after_billing(uuid) from public, anon, authenticated;
grant execute on function public.refresh_vendor_entitlements_after_billing(uuid) to service_role;

-- Prevent browser clients from mutating billing state directly.
revoke insert, update, delete on public.payments from anon, authenticated;
revoke insert, update, delete on public.subscriptions from anon, authenticated;
revoke insert, update, delete on public.payment_events from anon, authenticated;
revoke insert, update, delete on public.subscription_events from anon, authenticated;
revoke insert, update, delete on public.vendor_entitlements from anon, authenticated;
