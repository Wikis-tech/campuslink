-- CampusMode: vendor-defined services for the "Other campus services" category.
-- Keep the existing categories taxonomy and vendor_services ownership/RLS intact.
alter table public.vendor_services
  add column if not exists other_service_details text;

do $$
begin
  if not exists (
    select 1 from pg_constraint
    where conname = 'vendor_services_other_service_details_length'
      and conrelid = 'public.vendor_services'::regclass
  ) then
    alter table public.vendor_services
      add constraint vendor_services_other_service_details_length
      check (
        other_service_details is null or
        char_length(btrim(other_service_details)) between 3 and 120
      );
  end if;
end $$;

comment on column public.vendor_services.other_service_details is
  'Vendor submitted description for Other campus services, shown to authorised Admins. Not an automatically approved category.';
