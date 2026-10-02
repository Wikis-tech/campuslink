-- CampusLink operational communications foundation.
-- Adds actionable in-app notifications and server-only communication audit/delivery tables.

alter table public.notifications
  add column if not exists action_url text,
  add column if not exists source text not null default 'system',
  add column if not exists created_by uuid references auth.users(id) on delete set null,
  add column if not exists metadata jsonb not null default '{}'::jsonb;

create table if not exists public.admin_communications (
  id uuid primary key default gen_random_uuid(),
  created_by uuid not null references auth.users(id) on delete restrict,
  kind text not null check (kind in ('reminder','announcement')),
  audience_type text not null,
  institution_id uuid references public.institutions(id) on delete set null,
  subject text not null,
  title text not null,
  body text not null,
  cta_label text,
  cta_url text,
  recipient_count integer not null default 0 check (recipient_count >= 0),
  dashboard_sent_count integer not null default 0 check (dashboard_sent_count >= 0),
  email_sent_count integer not null default 0 check (email_sent_count >= 0),
  email_failed_count integer not null default 0 check (email_failed_count >= 0),
  created_at timestamptz not null default now()
);

create table if not exists public.admin_communication_deliveries (
  id uuid primary key default gen_random_uuid(),
  communication_id uuid not null references public.admin_communications(id) on delete cascade,
  user_id uuid not null references auth.users(id) on delete cascade,
  email text,
  reminder_key text,
  notification_id uuid references public.notifications(id) on delete set null,
  email_status text not null default 'pending'
    check (email_status in ('pending','sent','failed','not_configured','skipped_cooldown')),
  email_provider_id text,
  error_message text,
  sent_at timestamptz not null default now(),
  unique (communication_id, user_id)
);

create index if not exists admin_communication_deliveries_user_reminder_idx
  on public.admin_communication_deliveries(user_id, reminder_key, sent_at desc);

create index if not exists admin_communications_created_at_idx
  on public.admin_communications(created_at desc);

alter table public.admin_communications enable row level security;
alter table public.admin_communication_deliveries enable row level security;

-- These tables are intentionally server-only. Admin pages use the server-side
-- Supabase secret client only after requireAdminContext() has validated MFA and role.
revoke all on table public.admin_communications from anon, authenticated;
revoke all on table public.admin_communication_deliveries from anon, authenticated;

-- Existing notification owner policies continue to control user reads/updates.
grant select, update on table public.notifications to authenticated;
