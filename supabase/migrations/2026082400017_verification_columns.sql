-- Campus Link V2 - verification review metadata used by Phase 2.

alter table public.vendor_documents
  add column if not exists institution_id uuid references public.institutions(id) on delete set null,
  add column if not exists rejection_reason text,
  add column if not exists reviewed_by uuid references auth.users(id) on delete set null;

create index if not exists vendor_documents_institution_idx on public.vendor_documents(institution_id);
create index if not exists vendor_documents_reviewed_by_idx on public.vendor_documents(reviewed_by);
