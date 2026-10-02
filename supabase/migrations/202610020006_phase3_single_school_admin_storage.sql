-- Phase 3: remove the final legacy school_verifier reference.
drop policy if exists verification_documents_school_admin_select on storage.objects;
create policy verification_documents_school_admin_select
on storage.objects
for select
to authenticated
using (
  bucket_id = 'verification-documents'
  and exists (
    select 1
    from public.profiles p
    where p.id::text = (storage.foldername(objects.name))[1]
      and p.account_type = 'student'
      and p.institution_id is not null
      and (select private.has_institution_admin_role(p.institution_id,array['school_admin']))
  )
);
