-- Keep Admin-created notification content authoritative.
-- Signed-in users may read their own rows and only update read_at.

revoke update on table public.notifications from authenticated;
grant update(read_at) on table public.notifications to authenticated;
grant select on table public.notifications to authenticated;
