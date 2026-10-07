-- Notification inbox UX: unread/read/dismissed are separate states.
-- Dismissal hides a notification for the owning user without deleting audit history.

alter table public.notifications
  add column if not exists dismissed_at timestamptz;

revoke update on table public.notifications from authenticated;
grant update(read_at, dismissed_at) on table public.notifications to authenticated;

create index if not exists notifications_user_visible_created_idx
  on public.notifications(user_id, dismissed_at, created_at desc);
