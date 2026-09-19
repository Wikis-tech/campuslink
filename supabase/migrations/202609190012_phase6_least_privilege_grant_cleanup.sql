-- Phase 6: least-privilege cleanup for analytics, promotion and billing event tables.

revoke all on table public.promotion_campaigns from anon;
revoke insert, update, delete, truncate, references, trigger on table public.promotion_campaigns from authenticated;

revoke all on table public.vendor_analytics_events from anon;
revoke insert, update, delete, truncate, references, trigger on table public.vendor_analytics_events from authenticated;

revoke all on table public.vendor_analytics_daily from anon;
revoke insert, update, delete, truncate, references, trigger on table public.vendor_analytics_daily from authenticated;

revoke all on table public.vendor_listing_analytics_daily from anon;
revoke insert, update, delete, truncate, references, trigger on table public.vendor_listing_analytics_daily from authenticated;

revoke all on table public.subscription_events from anon;

grant select on table public.promotion_campaigns to authenticated;
grant select on table public.vendor_analytics_daily to authenticated;
grant select on table public.vendor_listing_analytics_daily to authenticated;
grant select on table public.subscription_events to authenticated;

comment on table public.promotion_campaigns is
'Campus Link promotion records. Browser writes are prohibited; trusted server/RPC flows only.';
comment on table public.vendor_analytics_events is
'Raw Campus Link analytics events. Writes are restricted to validated RPC/service-role paths.';
