-- Campus Link V2 - align Data API grants with RLS for onboarding.

-- Public discovery/reference data.
grant select on public.institutions, public.categories, public.vendor_profiles, public.vendor_services, public.reviews, public.subscription_plans to anon, authenticated;
grant select on public.profiles, public.vendor_documents, public.subscriptions, public.complaints, public.saved_vendors, public.contact_events to authenticated;

-- Vendor profile: ownership is still enforced by RLS; status/rating fields are excluded from update grants.
grant insert on public.vendor_profiles to authenticated;
grant update (business_name,slug,description,whatsapp_number,business_email,location_text,logo_url,cover_url,updated_at) on public.vendor_profiles to authenticated;

drop policy if exists vendor_profiles_owner_update on public.vendor_profiles;
create policy vendor_profiles_owner_update on public.vendor_profiles
for update to authenticated
using ((select auth.uid()) = id)
with check ((select auth.uid()) = id);

-- Vendor services.
grant insert, update, delete on public.vendor_services to authenticated;

-- Private vendor verification records: vendors can submit/read their own evidence but cannot approve it.
drop policy if exists "vendors manage own documents" on public.vendor_documents;
drop policy if exists vendor_documents_owner_read on public.vendor_documents;
create policy vendor_documents_owner_read on public.vendor_documents
for select to authenticated using ((select auth.uid()) = vendor_id);

drop policy if exists vendor_documents_owner_insert on public.vendor_documents;
create policy vendor_documents_owner_insert on public.vendor_documents
for insert to authenticated
with check ((select auth.uid()) = vendor_id and status = 'pending' and reviewed_at is null);

drop policy if exists vendor_documents_owner_delete_pending on public.vendor_documents;
create policy vendor_documents_owner_delete_pending on public.vendor_documents
for delete to authenticated
using ((select auth.uid()) = vendor_id and status = 'pending');

grant insert, delete on public.vendor_documents to authenticated;

-- Vendor-to-campus requests are request-only for vendors; approval remains an admin action.
grant insert, delete on public.vendor_institutions to authenticated;

-- Student self-service tables used by later discovery/reporting flows.
grant insert, update, delete on public.reviews to authenticated;
grant insert, delete on public.saved_vendors to authenticated;
grant insert on public.contact_events, public.complaints to authenticated;
