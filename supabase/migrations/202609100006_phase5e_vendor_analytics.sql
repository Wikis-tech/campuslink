-- Campus Link Phase 5E — privacy-safe vendor analytics, business health support and listing performance.
-- Vendors can see aggregate performance, not student identities. Student activity is recorded through a narrow RPC.

alter table public.vendor_analytics_events
  add column if not exists product_id uuid references public.vendor_products(id) on delete set null;

-- Expand the event vocabulary for product and phone interactions.
do $$
declare c record;
begin
  for c in
    select conname from pg_constraint
    where conrelid = 'public.vendor_analytics_events'::regclass
      and contype = 'c'
      and pg_get_constraintdef(oid) ilike '%event_type%'
  loop
    execute format('alter table public.vendor_analytics_events drop constraint %I', c.conname);
  end loop;
end
$$;

alter table public.vendor_analytics_events
  add constraint vendor_analytics_events_event_type_check
  check (event_type in (
    'search_impression','search_click','profile_view','product_view','service_view','portfolio_view',
    'contact_click','whatsapp_click','phone_click','save','unsave','review_received'
  ));

create index if not exists vendor_analytics_events_product_time_idx
  on public.vendor_analytics_events(product_id, occurred_at desc)
  where product_id is not null;

-- Raw event data contains actor identifiers for abuse controls. Vendors must never read it directly.
drop policy if exists vendor_analytics_events_owner_read on public.vendor_analytics_events;
revoke select on public.vendor_analytics_events from authenticated;

create table if not exists public.vendor_listing_analytics_daily (
  vendor_id uuid not null references public.vendor_profiles(id) on delete cascade,
  listing_type text not null check (listing_type in ('product','service')),
  listing_id uuid not null,
  day date not null,
  impressions bigint not null default 0,
  clicks bigint not null default 0,
  views bigint not null default 0,
  contacts bigint not null default 0,
  saves bigint not null default 0,
  updated_at timestamptz not null default now(),
  primary key (vendor_id, listing_type, listing_id, day)
);

alter table public.vendor_listing_analytics_daily enable row level security;
drop policy if exists vendor_listing_analytics_owner_read on public.vendor_listing_analytics_daily;
create policy vendor_listing_analytics_owner_read on public.vendor_listing_analytics_daily
for select to authenticated using ((select auth.uid()) = vendor_id);
drop policy if exists vendor_listing_analytics_admin_read on public.vendor_listing_analytics_daily;
create policy vendor_listing_analytics_admin_read on public.vendor_listing_analytics_daily
for select to authenticated using ((select private.has_admin_role(array['operations_admin','analyst','finance_admin'])));
grant select on public.vendor_listing_analytics_daily to authenticated;

create or replace function private.aggregate_vendor_analytics_event()
returns trigger
language plpgsql
security definer
set search_path = ''
as $$
declare
  event_day date := (new.occurred_at at time zone 'Africa/Lagos')::date;
  listing_kind text;
  listing_target uuid;
begin
  insert into public.vendor_analytics_daily(
    vendor_id, day, profile_views, search_impressions, search_clicks, service_views,
    portfolio_views, contact_clicks, whatsapp_clicks, saves, unsaves, reviews_received, updated_at
  ) values (
    new.vendor_id,
    event_day,
    case when new.event_type='profile_view' then 1 else 0 end,
    case when new.event_type='search_impression' then 1 else 0 end,
    case when new.event_type='search_click' then 1 else 0 end,
    case when new.event_type in ('service_view','product_view') then 1 else 0 end,
    case when new.event_type='portfolio_view' then 1 else 0 end,
    case when new.event_type in ('contact_click','phone_click') then 1 else 0 end,
    case when new.event_type='whatsapp_click' then 1 else 0 end,
    case when new.event_type='save' then 1 else 0 end,
    case when new.event_type='unsave' then 1 else 0 end,
    case when new.event_type='review_received' then 1 else 0 end,
    now()
  )
  on conflict (vendor_id, day) do update set
    profile_views = public.vendor_analytics_daily.profile_views + excluded.profile_views,
    search_impressions = public.vendor_analytics_daily.search_impressions + excluded.search_impressions,
    search_clicks = public.vendor_analytics_daily.search_clicks + excluded.search_clicks,
    service_views = public.vendor_analytics_daily.service_views + excluded.service_views,
    portfolio_views = public.vendor_analytics_daily.portfolio_views + excluded.portfolio_views,
    contact_clicks = public.vendor_analytics_daily.contact_clicks + excluded.contact_clicks,
    whatsapp_clicks = public.vendor_analytics_daily.whatsapp_clicks + excluded.whatsapp_clicks,
    saves = public.vendor_analytics_daily.saves + excluded.saves,
    unsaves = public.vendor_analytics_daily.unsaves + excluded.unsaves,
    reviews_received = public.vendor_analytics_daily.reviews_received + excluded.reviews_received,
    updated_at = now();

  if new.product_id is not null then listing_kind := 'product'; listing_target := new.product_id;
  elsif new.service_id is not null then listing_kind := 'service'; listing_target := new.service_id;
  else return new;
  end if;

  insert into public.vendor_listing_analytics_daily(vendor_id,listing_type,listing_id,day,impressions,clicks,views,contacts,saves,updated_at)
  values (
    new.vendor_id, listing_kind, listing_target, event_day,
    case when new.event_type='search_impression' then 1 else 0 end,
    case when new.event_type='search_click' then 1 else 0 end,
    case when new.event_type in ('product_view','service_view') then 1 else 0 end,
    case when new.event_type in ('contact_click','whatsapp_click','phone_click') then 1 else 0 end,
    case when new.event_type='save' then 1 else 0 end,
    now()
  )
  on conflict (vendor_id,listing_type,listing_id,day) do update set
    impressions = public.vendor_listing_analytics_daily.impressions + excluded.impressions,
    clicks = public.vendor_listing_analytics_daily.clicks + excluded.clicks,
    views = public.vendor_listing_analytics_daily.views + excluded.views,
    contacts = public.vendor_listing_analytics_daily.contacts + excluded.contacts,
    saves = public.vendor_listing_analytics_daily.saves + excluded.saves,
    updated_at = now();
  return new;
end;
$$;

revoke all on function private.aggregate_vendor_analytics_event() from public, anon, authenticated;
drop trigger if exists vendor_analytics_event_aggregate on public.vendor_analytics_events;
create trigger vendor_analytics_event_aggregate
after insert on public.vendor_analytics_events
for each row execute procedure private.aggregate_vendor_analytics_event();

create or replace function public.record_vendor_analytics_event(
  target_vendor uuid,
  event_name text,
  target_product uuid default null,
  target_service uuid default null
)
returns void
language plpgsql
security definer
set search_path = ''
as $$
declare
  actor uuid := (select auth.uid());
  actor_institution uuid;
  recent_count integer;
begin
  if actor is null then raise exception 'Authentication required'; end if;
  if event_name not in ('profile_view','product_view','service_view','portfolio_view','contact_click','whatsapp_click','phone_click','save','unsave','review_received','search_impression','search_click') then
    raise exception 'Unsupported analytics event';
  end if;

  select p.institution_id into actor_institution
  from public.profiles p
  where p.id = actor and p.account_type = 'student' and p.onboarding_completed_at is not null;
  if actor_institution is null then raise exception 'Student setup required'; end if;
  if not (select private.student_can_access_vendor(target_vendor)) then raise exception 'Vendor unavailable'; end if;

  if target_product is not null and not exists (
    select 1 from public.vendor_products where id = target_product and vendor_id = target_vendor and is_active = true
  ) then raise exception 'Invalid product'; end if;
  if target_service is not null and not exists (
    select 1 from public.vendor_services where id = target_service and vendor_id = target_vendor and is_active = true
  ) then raise exception 'Invalid service'; end if;

  -- Lightweight anti-spam guard. Genuine use remains countable, refresh loops do not explode analytics.
  select count(*)::integer into recent_count
  from public.vendor_analytics_events e
  where e.actor_id = actor and e.vendor_id = target_vendor and e.event_type = event_name
    and e.occurred_at >= now() - interval '2 minutes'
    and coalesce(e.product_id,'00000000-0000-0000-0000-000000000000'::uuid)=coalesce(target_product,'00000000-0000-0000-0000-000000000000'::uuid)
    and coalesce(e.service_id,'00000000-0000-0000-0000-000000000000'::uuid)=coalesce(target_service,'00000000-0000-0000-0000-000000000000'::uuid);
  if recent_count >= 3 then return; end if;

  insert into public.vendor_analytics_events(vendor_id,actor_id,institution_id,event_type,product_id,service_id,metadata)
  values (target_vendor,actor,actor_institution,event_name,target_product,target_service,'{}'::jsonb);
end;
$$;

revoke all on function public.record_vendor_analytics_event(uuid,text,uuid,uuid) from public, anon;
grant execute on function public.record_vendor_analytics_event(uuid,text,uuid,uuid) to authenticated;

create or replace function public.get_my_listing_performance(requested_days integer default 7)
returns table(listing_type text, listing_id uuid, listing_name text, impressions bigint, clicks bigint, views bigint, contacts bigint, saves bigint)
language plpgsql
stable
security definer
set search_path = ''
as $$
declare
  vendor uuid := (select auth.uid());
  allowed_days integer := 7;
begin
  if vendor is null or not exists(select 1 from public.profiles where id=vendor and account_type='vendor') then return; end if;
  select greatest(7,coalesce((e.entitlements->>'analytics_days')::integer,7)) into allowed_days from public.vendor_entitlements e where e.vendor_id=vendor;
  requested_days := least(greatest(requested_days,7),allowed_days);

  return query
  select x.listing_type, x.listing_id,
    case when x.listing_type='product' then coalesce(p.name,'Product') else coalesce(s.name,'Service') end,
    sum(x.impressions)::bigint, sum(x.clicks)::bigint, sum(x.views)::bigint, sum(x.contacts)::bigint, sum(x.saves)::bigint
  from public.vendor_listing_analytics_daily x
  left join public.vendor_products p on x.listing_type='product' and p.id=x.listing_id
  left join public.vendor_services s on x.listing_type='service' and s.id=x.listing_id
  where x.vendor_id=vendor and x.day >= (current_date - (requested_days - 1))
  group by x.listing_type,x.listing_id,p.name,s.name
  order by sum(x.views)+sum(x.contacts)*3 desc, sum(x.impressions) desc
  limit 20;
end;
$$;

revoke all on function public.get_my_listing_performance(integer) from public, anon;
grant execute on function public.get_my_listing_performance(integer) to authenticated;
