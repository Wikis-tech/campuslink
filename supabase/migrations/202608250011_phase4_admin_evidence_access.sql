-- Campus Link V2 - Phase 4 evidence access for school verification staff.

-- School verification staff may open ONLY student verification files belonging to
-- students in their assigned institution. Vendor identity evidence remains global.
drop policy if exists verification_documents_school_admin_select on storage.objects;
create policy verification_documents_school_admin_select on storage.objects
for select to authenticated
using (
  bucket_id = 'verification-documents'
  and exists (
    select 1
    from public.profiles p
    where p.id::text = (storage.foldername(name))[1]
      and p.account_type = 'student'
      and p.institution_id is not null
      and (select private.has_institution_admin_role(p.institution_id,array['school_admin','school_verifier']))
  )
);

-- School admins need row visibility for student document metadata as well.
drop policy if exists student_documents_school_admin_read on public.student_documents;
create policy student_documents_school_admin_read on public.student_documents
for select to authenticated
using (
  exists (
    select 1
    from public.profiles p
    where p.id = student_documents.student_id
      and p.institution_id is not null
      and (select private.has_institution_admin_role(p.institution_id,array['school_admin','school_verifier']))
  )
);

grant select on public.student_documents to authenticated;
