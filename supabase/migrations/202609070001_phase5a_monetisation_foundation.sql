-- Campus Link Phase 5A — monetisation, trust-safe entitlements and analytics foundation.
-- This migration extends the existing subscription/payment tables instead of creating parallel billing models.
-- Payments never grant verification or campus approval. Trust and monetisation remain separate.

create extension if not exists "pgcrypto";
create schema if not exists private;

-- ---------------------------------------------------------------------------
-- Subscription plans: one public tier can have multiple billing intervals.
-- ---------------------------------------------------------------------------
alter table public.subscription_plans
  add column if not exists tier text,
  add column if not exists description text,
  add column if not exists currency text not null default 'NGN',
  add column if not exists price_kobo bigint,
  add column if not exists entitlements jsonb not null default '{}'::jsonb,
  add column if not exists paystack_plan_code text,
  add column if not exists is_public boolean not null default true,
  add column if not exists sort_order integer not null default 0,
  add column if not exists updated_at timestamptz not null default now();

update public.subscription_plans
set
  tier = coalesce(tier, case when price_ngn = 0 then 'free' else 'pro' end),
  price_kobo = coalesce(price_kobo, round(price_ngn * 100)::bigint)
where tier is null or price_kobo is null;

alter table public.subscription_plans
  alter column tier set not null,
  alter column price_kobo set not null;

do $$
begin
  if not exists (
    select 1 from pg_constraint
    where conname = 'subscription_plans_tier_check'
      and conrelid = 'public.subscription_plans'::regclass
  ) then
    alter table public.subscription_plans
      add constraint subscription_plans_tier_check check (tier in ('free','pro'));
  end if;

  if not exists (
    select 1 from pg_constraint
    where conname = 'subscription_plans_currency_check'
      and conrelid = 'public.subscription_plans'::regclass
  ) then
    alter table public.subscription_plans
      add constraint subscription_plans_currency_check check (currency = 'NGN');
  end if;

  if not exists (
    select 1 from pg_constraint
    where conname = 'subscription_plans_price_kobo_check'
      and conrelid = 'public.subscription_plans'::regclass
  ) then
    alter table public.subscription_plans
      add constraint subscription_plans_price_kobo_check check (price_kobo >= 0);
  end if;
end
$$;

create unique index if not exists subscription_plans_paystack_code_uidx
  on public.subscription_plans(paystack_plan_code)
  where paystack_plan_code is not null;

create index if not exists subscription_plans_tier_interval_idx
  on public.subscription_plans(tier, billing_interval, is_active, is_public);

-- Seed the launch plan catalogue. Pro monthly/annual are one customer-facing tier.
insert into public.subscription_plans (
  name, slug, tier, description, price_ngn, price_kobo, currency,
  billing_interval, features, entitlements, is_active, is_public, sort_order
)
values
  (
    'Campus Link Free',
    'free',
    'free',
    'A useful vendor profile for trusted campus discovery. Payment never affects verification.',
    0,
    0,
    'NGN',
    'monthly',
    '["Verified vendor profile","1 approved campus","Up to 5 services","Basic portfolio","Reviews and ratings","WhatsApp/contact access","Basic dashboard"]'::jsonb,
    '{"service_limit":5,"portfolio_limit":6,"analytics_days":7,"advanced_analytics":false,"featured_eligible":false,"promotion_tools":false,"profile_insights":false}'::jsonb,
    true,
    true,
    10
  ),
  (
    'Campus Link Pro Monthly',
    'pro-monthly',
    'pro',
    'Growth tools for approved vendors. Pro adds business tools; it never buys trust or verification.',
    2500,
    250000,
    'NGN',
    'monthly',
    '["Everything in Free","Up to 20 services","Larger portfolio","30-day analytics","Profile insights","Featured eligibility","Promotion tools"]'::jsonb,
    '{"service_limit":20,"portfolio_limit":30,"analytics_days":30,"advanced_analytics":true,"featured_eligible":true,"promotion_tools":true,"profile_insights":true}'::jsonb,
    true,
    true,
    20
  ),
  (
    'Campus Link Pro Annual',
    'pro-annual',
    'pro',
    'Annual Pro access with the same trust-safe growth tools and a lower effective monthly cost.',
    24000,
    2400000,
    'NGN',
    'yearly',
    '["Everything in Free","Up to 20 services","Larger portfolio","90-day analytics","Profile insights","Featured eligibility","Promotion tools"]'::jsonb,
    '{"service_limit":20,"portfolio_limit":30,"analytics_days":90,"advanced_analytics":true,"featured_eligible":true,"promotion_tools":true,"profile_insights":true}'::jsonb,
    true,
    true,
    30
  )
on conflict (slug) do update set
  tier = excluded.tier,
  description = excluded.description,
  price_ngn = excluded.price_ngn,
  price_kobo = excluded.price_kobo,
  currency = excluded.currency,
  billing_interval = excluded.billing_interval,
  features = excluded.features,
  entitlements = excluded.entitlements,
  is_active = excluded.is_active,
  is_public = excluded.is_public,
  sort_order = excluded.sort_order,
  updated_at = now();

-- ---------------------------------------------------------------------------
-- Subscription lifecycle. We retain the existing table and add provider fields.
-- ---------------------------------------------------------------------------
alter table public.subscriptions
  add column if not exists provider text not null default 'paystack',
  add column if not exists provider_customer_code text,
  add column if not exists provider_subscription_code text,
  add column if not exists provider_email_token text,
  add column if not exists current_period_start timestamptz,
  add column if not exists current_period_end timestamptz,
  add column if not exists cancel_at_period_end boolean not null default false,
  add column if not exists grace_period_ends_at timestamptz,
  add column if not exists last_payment_at timestamptz,
  add column if not exists cancelled_at timestamptz,
  add column if not exists metadata jsonb not null default '{}'::jsonb,
  add column if not exists updated_at timestamptz not null default now();

-- Expand legacy status vocabulary without relying on a client-side paid boolean.
do $$
declare
  c record;
begin
  for c in
    select conname
    from pg_constraint
    where conrelid = 'public.subscriptions'::regclass
      and contype = 'c'
      and pg_get_constraintdef(oid) ilike '%status%'
  loop
    execute format('alter table public.subscriptions drop constraint %I', c.conname);
  end loop;
end
$$;

alter table public.subscriptions
  add constraint subscriptions_status_check
  check (status in ('inactive','active','attention','non_renewing','past_due','cancelled','expired'));

create unique index if not exists subscriptions_provider_code_uidx
  on public.subscriptions(provider_subscription_code)
  where provider_subscription_code is not null;

create index if not exists subscriptions_vendor_status_idx
  on public.subscriptions(vendor_id, status, current_period_end desc);

-- ---------------------------------------------------------------------------
-- Existing payments table becomes the canonical payment transaction ledger.
-- ---------------------------------------------------------------------------
alter table public.payments
  add column if not exists currency text not null default 'NGN',
  add column if not exists amount_kobo bigint,
  add column if not exists provider_transaction_id text,
  add column if not exists provider_event_id text,
  add column if not exists channel text,
  add column if not exists failure_reason text,
  add column if not exists metadata jsonb not null default '{}'::jsonb;

update public.payments
set amount_kobo = coalesce(amount_kobo, round(amount_ngn * 100)::bigint)
where amount_kobo is null;

alter table public.payments alter column amount_kobo set not null;

create unique index if not exists payments_provider_transaction_uidx
  on public.payments(provider_transaction_id)
  where provider_transaction_id is not null;

create unique index if not exists payments_provider_event_uidx
  on public.payments(provider_event_id)
  where provider_event_id is not null;

create index if not exists payments_vendor_created_idx
  on public.payments(vendor_id, created_at desc);

-- ---------------------------------------------------------------------------
-- Immutable-ish event ledgers. These are server/admin written only.
-- ---------------------------------------------------------------------------
create table if not exists public.subscription_events (
  id bigint generated always as identity primary key,
  subscription_id uuid references public.subscriptions(id) on delete cascade,
  vendor_id uuid not null references public.vendor_profiles(id) on delete cascade,
  event_type text not null,
  source text not null default 'system' check (source in ('system','paystack','admin')),
  provider_event_id text,
  previous_status text,
  new_status text,
  metadata jsonb not null default '{}'::jsonb,
  created_at timestamptz not null default now()
);

create unique index if not exists subscription_events_provider_event_uidx
  on public.subscription_events(provider_event_id)
  where provider_event_id is not null;
create index if not exists subscription_events_vendor_created_idx
  on public.subscription_events(vendor_id, created_at desc);

create table if not exists public.payment_events (
  id bigint generated always as identity primary key,
  payment_id uuid references public.payments(id) on delete set null,
  vendor_id uuid references public.vendor_profiles(id) on delete set null,
  provider text not null default 'paystack',
  provider_event_id text not null,
  event_type text not null,
  payload jsonb not null default '{}'::jsonb,
  processed_at timestamptz,
  processing_error text,
  created_at timestamptz not null default now(),
  unique(provider, provider_event_id)
);

create index if not exists payment_events_type_created_idx
  on public.payment_events(event_type, created_at desc);

-- ---------------------------------------------------------------------------
-- Effective entitlements. This snapshot is derived from the active subscription
-- and never contains verification/campus-approval privileges.
-- ---------------------------------------------------------------------------
create table if not exists public.vendor_entitlements (
  vendor_id uuid primary key references public.vendor_profiles(id) on delete cascade,
  plan_id uuid references public.subscription_plans(id) on delete set null,
  tier text not null default 'free' check (tier in ('free','pro')),
  entitlements jsonb not null default '{}'::jsonb,
  source text not null default 'plan' check (source in ('plan','admin_grant','system')),
  valid_until timestamptz,
  updated_at timestamptz not null default now()
);

-- Entitlement calculation is server-trusted and defaults safely to Free.
create or replace function private.refresh_vendor_entitlements(target_vendor uuid)
returns void
language plpgsql
security definer
set search_path = ''
as $$
declare
  chosen_plan public.subscription_plans%rowtype;
  chosen_subscription public.subscriptions%rowtype;
begin
  select s.* into chosen_subscription
  from public.subscriptions s
  where s.vendor_id = target_vendor
    and s.status in ('active','attention','non_renewing')
    and (s.current_period_end is null or s.current_period_end > now() or (s.grace_period_ends_at is not null and s.grace_period_ends_at > now()))
  order by
    case s.status when 'active' then 1 when 'attention' then 2 else 3 end,
    coalesce(s.current_period_end, s.ends_at, 'infinity'::timestamptz) desc
  limit 1;

  if chosen_subscription.id is not null then
    select p.* into chosen_plan from public.subscription_plans p where p.id = chosen_subscription.plan_id;
  else
    select p.* into chosen_plan
    from public.subscription_plans p
    where p.slug = 'free' and p.is_active = true
    limit 1;
  end if;

  if chosen_plan.id is null then
    raise exception 'Campus Link Free plan is missing';
  end if;

  insert into public.vendor_entitlements(vendor_id, plan_id, tier, entitlements, source, valid_until, updated_at)
  values (
    target_vendor,
    chosen_plan.id,
    chosen_plan.tier,
    chosen_plan.entitlements,
    'plan',
    case when chosen_subscription.id is null then null else coalesce(chosen_subscription.current_period_end, chosen_subscription.ends_at) end,
    now()
  )
  on conflict (vendor_id) do update set
    plan_id = excluded.plan_id,
    tier = excluded.tier,
    entitlements = excluded.entitlements,
    source = excluded.source,
    valid_until = excluded.valid_until,
    updated_at = now();
end;
$$;

revoke all on function private.refresh_vendor_entitlements(uuid) from public, anon, authenticated;

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
stable
security definer
set search_path = ''
as $$
declare
  current_vendor uuid := (select auth.uid());
begin
  if current_vendor is null then
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

-- ---------------------------------------------------------------------------
-- Analytics foundation: raw event stream + daily aggregate.
-- No sensitive student profile data is needed for vendor performance analytics.
-- ---------------------------------------------------------------------------
create table if not exists public.vendor_analytics_events (
  id bigint generated always as identity primary key,
  vendor_id uuid not null references public.vendor_profiles(id) on delete cascade,
  actor_id uuid references auth.users(id) on delete set null,
  institution_id uuid references public.institutions(id) on delete set null,
  event_type text not null check (event_type in (
    'search_impression','search_click','profile_view','service_view','portfolio_view',
    'contact_click','whatsapp_click','save','unsave','review_received'
  )),
  service_id uuid references public.vendor_services(id) on delete set null,
  metadata jsonb not null default '{}'::jsonb,
  occurred_at timestamptz not null default now()
);

create index if not exists vendor_analytics_events_vendor_time_idx
  on public.vendor_analytics_events(vendor_id, occurred_at desc);
create index if not exists vendor_analytics_events_type_time_idx
  on public.vendor_analytics_events(event_type, occurred_at desc);
create index if not exists vendor_analytics_events_institution_idx
  on public.vendor_analytics_events(institution_id, occurred_at desc);

create table if not exists public.vendor_analytics_daily (
  vendor_id uuid not null references public.vendor_profiles(id) on delete cascade,
  day date not null,
  profile_views bigint not null default 0,
  search_impressions bigint not null default 0,
  search_clicks bigint not null default 0,
  service_views bigint not null default 0,
  portfolio_views bigint not null default 0,
  contact_clicks bigint not null default 0,
  whatsapp_clicks bigint not null default 0,
  saves bigint not null default 0,
  unsaves bigint not null default 0,
  reviews_received bigint not null default 0,
  updated_at timestamptz not null default now(),
  primary key (vendor_id, day)
);

-- ---------------------------------------------------------------------------
-- Promotion foundation is intentionally inert until Phase 5F.
-- Only verified + campus-approved vendors will be made eligible later.
-- ---------------------------------------------------------------------------
create table if not exists public.promotion_campaigns (
  id uuid primary key default gen_random_uuid(),
  vendor_id uuid not null references public.vendor_profiles(id) on delete cascade,
  institution_id uuid references public.institutions(id) on delete cascade,
  placement text not null check (placement in ('student_home','search_results','category_spotlight')),
  status text not null default 'draft' check (status in ('draft','pending_payment','scheduled','active','paused','completed','cancelled','rejected')),
  starts_at timestamptz,
  ends_at timestamptz,
  budget_ngn numeric(12,2),
  payment_id uuid references public.payments(id) on delete set null,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create index if not exists promotion_campaigns_vendor_status_idx
  on public.promotion_campaigns(vendor_id, status, starts_at desc);

-- ---------------------------------------------------------------------------
-- RLS: vendors can read their own commercial data; clients cannot forge it.
-- Finance/operations/analyst admins can read financial/analytics state.
-- ---------------------------------------------------------------------------
alter table public.subscription_events enable row level security;
alter table public.payment_events enable row level security;
alter table public.vendor_entitlements enable row level security;
alter table public.vendor_analytics_events enable row level security;
alter table public.vendor_analytics_daily enable row level security;
alter table public.promotion_campaigns enable row level security;

drop policy if exists subscription_plans_public_read on public.subscription_plans;
create policy subscription_plans_public_read on public.subscription_plans
for select to anon, authenticated
using (is_active = true and is_public = true);

drop policy if exists subscriptions_vendor_read on public.subscriptions;
create policy subscriptions_vendor_read on public.subscriptions
for select to authenticated using ((select auth.uid()) = vendor_id);

drop policy if exists subscriptions_finance_read on public.subscriptions;
create policy subscriptions_finance_read on public.subscriptions
for select to authenticated using ((select private.has_admin_role(array['finance_admin','operations_admin','analyst'])));

drop policy if exists subscription_events_vendor_read on public.subscription_events;
create policy subscription_events_vendor_read on public.subscription_events
for select to authenticated using ((select auth.uid()) = vendor_id);

drop policy if exists subscription_events_admin_read on public.subscription_events;
create policy subscription_events_admin_read on public.subscription_events
for select to authenticated using ((select private.has_admin_role(array['finance_admin','operations_admin','analyst'])));

drop policy if exists payment_events_admin_read on public.payment_events;
create policy payment_events_admin_read on public.payment_events
for select to authenticated using ((select private.has_admin_role(array['finance_admin','operations_admin','analyst'])));

drop policy if exists vendor_entitlements_owner_read on public.vendor_entitlements;
create policy vendor_entitlements_owner_read on public.vendor_entitlements
for select to authenticated using ((select auth.uid()) = vendor_id);

drop policy if exists vendor_entitlements_admin_read on public.vendor_entitlements;
create policy vendor_entitlements_admin_read on public.vendor_entitlements
for select to authenticated using ((select private.has_admin_role(array['finance_admin','operations_admin','analyst'])));

drop policy if exists vendor_analytics_events_owner_read on public.vendor_analytics_events;
create policy vendor_analytics_events_owner_read on public.vendor_analytics_events
for select to authenticated using ((select auth.uid()) = vendor_id);

drop policy if exists vendor_analytics_events_admin_read on public.vendor_analytics_events;
create policy vendor_analytics_events_admin_read on public.vendor_analytics_events
for select to authenticated using ((select private.has_admin_role(array['operations_admin','analyst','finance_admin'])));

drop policy if exists vendor_analytics_daily_owner_read on public.vendor_analytics_daily;
create policy vendor_analytics_daily_owner_read on public.vendor_analytics_daily
for select to authenticated using ((select auth.uid()) = vendor_id);

drop policy if exists vendor_analytics_daily_admin_read on public.vendor_analytics_daily;
create policy vendor_analytics_daily_admin_read on public.vendor_analytics_daily
for select to authenticated using ((select private.has_admin_role(array['operations_admin','analyst','finance_admin'])));

drop policy if exists promotion_campaigns_owner_read on public.promotion_campaigns;
create policy promotion_campaigns_owner_read on public.promotion_campaigns
for select to authenticated using ((select auth.uid()) = vendor_id);

drop policy if exists promotion_campaigns_admin_read on public.promotion_campaigns;
create policy promotion_campaigns_admin_read on public.promotion_campaigns
for select to authenticated using ((select private.has_admin_role(array['finance_admin','operations_admin','analyst'])));

-- Explicit table privileges. Server/service role performs writes for billing/events.
grant select on public.subscription_plans to anon, authenticated;
grant select on public.subscriptions, public.subscription_events, public.vendor_entitlements,
  public.vendor_analytics_events, public.vendor_analytics_daily, public.promotion_campaigns to authenticated;
grant select on public.payment_events to authenticated;

-- Initialise entitlement snapshots for existing vendors as Free unless a qualifying subscription already exists.
do $$
declare
  v record;
begin
  for v in select id from public.vendor_profiles loop
    perform private.refresh_vendor_entitlements(v.id);
  end loop;
end
$$;
