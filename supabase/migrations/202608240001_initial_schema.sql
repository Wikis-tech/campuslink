create extension if not exists "pgcrypto";

create table if not exists public.institutions (
  id uuid primary key default gen_random_uuid(),
  name text not null,
  slug text not null unique,
  email_domain text,
  city text,
  state text,
  country text not null default 'Nigeria',
  is_active boolean not null default true,
  created_at timestamptz not null default now()
);

create table if not exists public.profiles (
  id uuid primary key references auth.users(id) on delete cascade,
  role text not null default 'student' check (role in ('student','vendor','admin')),
  institution_id uuid references public.institutions(id) on delete set null,
  first_name text,
  last_name text,
  phone text,
  avatar_url text,
  verification_status text not null default 'pending' check (verification_status in ('pending','verified','rejected','suspended')),
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create table if not exists public.categories (
  id uuid primary key default gen_random_uuid(),
  name text not null unique,
  slug text not null unique,
  description text,
  icon text,
  is_active boolean not null default true,
  created_at timestamptz not null default now()
);

create table if not exists public.vendor_profiles (
  id uuid primary key references public.profiles(id) on delete cascade,
  business_name text not null,
  slug text not null unique,
  description text,
  whatsapp_number text,
  location_text text,
  logo_url text,
  cover_url text,
  status text not null default 'pending' check (status in ('pending','approved','rejected','suspended')),
  average_rating numeric(3,2) not null default 0,
  review_count integer not null default 0,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create table if not exists public.vendor_services (
  id uuid primary key default gen_random_uuid(),
  vendor_id uuid not null references public.vendor_profiles(id) on delete cascade,
  category_id uuid references public.categories(id) on delete set null,
  name text not null,
  description text,
  price_from numeric(12,2),
  is_active boolean not null default true,
  created_at timestamptz not null default now()
);

create table if not exists public.reviews (
  id uuid primary key default gen_random_uuid(),
  student_id uuid not null references public.profiles(id) on delete cascade,
  vendor_id uuid not null references public.vendor_profiles(id) on delete cascade,
  rating integer not null check (rating between 1 and 5),
  comment text,
  status text not null default 'published' check (status in ('published','hidden','reported')),
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now(),
  unique(student_id, vendor_id)
);

create table if not exists public.saved_vendors (
  student_id uuid not null references public.profiles(id) on delete cascade,
  vendor_id uuid not null references public.vendor_profiles(id) on delete cascade,
  created_at timestamptz not null default now(),
  primary key (student_id, vendor_id)
);

create table if not exists public.contact_events (
  id uuid primary key default gen_random_uuid(),
  student_id uuid references public.profiles(id) on delete set null,
  vendor_id uuid not null references public.vendor_profiles(id) on delete cascade,
  channel text not null default 'whatsapp' check (channel in ('whatsapp','phone','email')),
  created_at timestamptz not null default now()
);

create table if not exists public.complaints (
  id uuid primary key default gen_random_uuid(),
  reporter_id uuid not null references public.profiles(id) on delete cascade,
  vendor_id uuid references public.vendor_profiles(id) on delete set null,
  title text not null,
  description text not null,
  status text not null default 'open' check (status in ('open','reviewing','resolved','closed')),
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create table if not exists public.vendor_documents (
  id uuid primary key default gen_random_uuid(),
  vendor_id uuid not null references public.vendor_profiles(id) on delete cascade,
  document_type text not null,
  storage_path text not null,
  status text not null default 'pending' check (status in ('pending','approved','rejected')),
  reviewed_at timestamptz,
  created_at timestamptz not null default now()
);

create table if not exists public.subscription_plans (
  id uuid primary key default gen_random_uuid(),
  name text not null unique,
  slug text not null unique,
  price_ngn numeric(12,2) not null default 0,
  billing_interval text not null default 'monthly' check (billing_interval in ('monthly','quarterly','yearly')),
  features jsonb not null default '[]'::jsonb,
  is_active boolean not null default true,
  created_at timestamptz not null default now()
);

create table if not exists public.subscriptions (
  id uuid primary key default gen_random_uuid(),
  vendor_id uuid not null references public.vendor_profiles(id) on delete cascade,
  plan_id uuid not null references public.subscription_plans(id),
  status text not null default 'inactive' check (status in ('inactive','active','past_due','cancelled','expired')),
  starts_at timestamptz,
  ends_at timestamptz,
  created_at timestamptz not null default now()
);

create or replace function public.handle_new_user()
returns trigger
language plpgsql
security definer set search_path = ''
as $$
begin
  insert into public.profiles (id, role, first_name, last_name)
  values (
    new.id,
    case when new.raw_user_meta_data ->> 'role' = 'vendor' then 'vendor' else 'student' end,
    new.raw_user_meta_data ->> 'first_name',
    new.raw_user_meta_data ->> 'last_name'
  );
  return new;
end;
$$;

drop trigger if exists on_auth_user_created on auth.users;
create trigger on_auth_user_created
after insert on auth.users
for each row execute procedure public.handle_new_user();

alter table public.institutions enable row level security;
alter table public.profiles enable row level security;
alter table public.categories enable row level security;
alter table public.vendor_profiles enable row level security;
alter table public.vendor_services enable row level security;
alter table public.reviews enable row level security;
alter table public.saved_vendors enable row level security;
alter table public.contact_events enable row level security;
alter table public.complaints enable row level security;
alter table public.vendor_documents enable row level security;
alter table public.subscription_plans enable row level security;
alter table public.subscriptions enable row level security;

create policy "public can view active institutions" on public.institutions for select using (is_active = true);
create policy "public can view active categories" on public.categories for select using (is_active = true);
create policy "public can view approved vendors" on public.vendor_profiles for select using (status = 'approved');
create policy "public can view active services" on public.vendor_services for select using (is_active = true);
create policy "public can view published reviews" on public.reviews for select using (status = 'published');
create policy "public can view active plans" on public.subscription_plans for select using (is_active = true);

create policy "users can view own profile" on public.profiles for select to authenticated using (auth.uid() = id);
create policy "users can update own profile" on public.profiles for update to authenticated using (auth.uid() = id) with check (auth.uid() = id);

create policy "vendors can view own vendor profile" on public.vendor_profiles for select to authenticated using (auth.uid() = id);
create policy "vendors can create own vendor profile" on public.vendor_profiles for insert to authenticated with check (auth.uid() = id);
create policy "vendors can update own vendor profile" on public.vendor_profiles for update to authenticated using (auth.uid() = id) with check (auth.uid() = id);

create policy "vendors manage own services" on public.vendor_services for all to authenticated using (vendor_id = auth.uid()) with check (vendor_id = auth.uid());

create policy "students create own reviews" on public.reviews for insert to authenticated with check (student_id = auth.uid());
create policy "students update own reviews" on public.reviews for update to authenticated using (student_id = auth.uid()) with check (student_id = auth.uid());
create policy "students delete own reviews" on public.reviews for delete to authenticated using (student_id = auth.uid());

create policy "students manage saved vendors" on public.saved_vendors for all to authenticated using (student_id = auth.uid()) with check (student_id = auth.uid());
create policy "students create contact events" on public.contact_events for insert to authenticated with check (student_id = auth.uid());
create policy "users create own complaints" on public.complaints for insert to authenticated with check (reporter_id = auth.uid());
create policy "users view own complaints" on public.complaints for select to authenticated using (reporter_id = auth.uid());

create policy "vendors manage own documents" on public.vendor_documents for all to authenticated using (vendor_id = auth.uid()) with check (vendor_id = auth.uid());
create policy "vendors view own subscriptions" on public.subscriptions for select to authenticated using (vendor_id = auth.uid());

create index if not exists vendor_profiles_status_idx on public.vendor_profiles(status);
create index if not exists vendor_services_vendor_idx on public.vendor_services(vendor_id);
create index if not exists vendor_services_category_idx on public.vendor_services(category_id);
create index if not exists reviews_vendor_idx on public.reviews(vendor_id);
create index if not exists contact_events_vendor_idx on public.contact_events(vendor_id);
