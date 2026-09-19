-- Phase 6 least privilege cleanup.

revoke insert, update, delete on public.admin_memberships from authenticated;
revoke select on public.payment_events from anon;
revoke select on public.vendor_entitlements from anon;
revoke delete on public.vendor_profiles from authenticated;
