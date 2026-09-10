import { Building2, Globe2, MailCheck, Plus, ShieldCheck } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { canManageSchools, requireAdminContext } from '../lib'
import { createInstitution, processSchoolRequest, setInstitutionActive } from '../actions'

export default async function SchoolsAdminPage({ searchParams }: { searchParams: Promise<{ success?: string; error?: string }> }) {
  const params = await searchParams
  const context = await requireAdminContext()
  const supabase = await createClient()
  const manageable = canManageSchools(context.globalRole)

  const [{ data: schools }, { data: requests }] = await Promise.all([
    supabase.from('institutions').select('id,name,slug,city,state,country,email_domain,is_active,verification_mode,allowed_student_email_domains,verification_instructions').order('name'),
    context.isGlobalAdmin
      ? supabase.from('school_requests').select('id,school_name,city,state,website,status,created_at').eq('status','pending').order('created_at',{ascending:false}).limit(30)
      : Promise.resolve({ data: [] as Array<Record<string, unknown>> }),
  ])

  return (
    <>
      <header className="admin-topbar"><div><span className="admin-pill"><Building2 size={15}/> School directory</span><h1>Schools</h1><p>Control which institutions appear during student and vendor onboarding.</p></div></header>
      {params.success ? <div className="admin-success">{params.success}</div> : null}
      {params.error ? <div className="admin-error">{params.error}</div> : null}

      {manageable ? <section className="admin-section">
        <div className="admin-section-head"><div><h2>Add a school</h2><p>New active schools become available to the onboarding dropdown immediately.</p></div><Plus size={20}/></div>
        <div className="admin-section-body">
          <form action={createInstitution} className="admin-form">
            <div className="admin-form-grid">
              <div className="admin-field"><label>Full school name</label><input name="name" required placeholder="University of Lagos"/></div>
              <div className="admin-field"><label>Slug <span style={{fontWeight:500}}>(optional)</span></label><input name="slug" placeholder="unilag"/></div>
              <div className="admin-field"><label>City</label><input name="city" placeholder="Lagos"/></div>
              <div className="admin-field"><label>State</label><input name="state" placeholder="Lagos"/></div>
              <div className="admin-field"><label>Country</label><input name="country" defaultValue="Nigeria"/></div>
              <div className="admin-field"><label>Primary student email domain</label><input name="email_domain" placeholder="student.unilag.edu.ng"/></div>
              <div className="admin-field"><label>Verification mode</label><select name="verification_mode" defaultValue="hybrid"><option value="hybrid">Hybrid: email or document</option><option value="institution_email">Institution email</option><option value="manual">Manual evidence</option></select></div>
              <div className="admin-field"><label>Allowed email domains</label><input name="allowed_student_email_domains" placeholder="student.school.edu.ng, school.edu.ng"/></div>
            </div>
            <div className="admin-field"><label>Verification instructions</label><textarea name="verification_instructions" rows={3} placeholder="Explain what evidence students from this school can use."/></div>
            <div className="admin-note"><ShieldCheck size={16}/> Archiving a school removes it from new onboarding but does not delete students, vendors, reviews or historical records.</div>
            <div><button className="admin-action primary" type="submit">Add school</button></div>
          </form>
        </div>
      </section> : null}

      <section className="admin-section">
        <div className="admin-section-head"><div><h2>Institution directory</h2><p>{schools?.length || 0} school{schools?.length === 1 ? '' : 's'} visible to your admin role.</p></div><Globe2 size={20}/></div>
        <div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>School</th><th>Verification</th><th>Email setup</th><th>Status</th><th>Action</th></tr></thead><tbody>
          {(schools || []).map((school) => <tr key={school.id}>
            <td><span className="admin-name">{school.name}</span><span className="admin-sub">{[school.city,school.state,school.country].filter(Boolean).join(', ') || school.slug}</span></td>
            <td><span className="status-badge status-reviewing">{school.verification_mode?.replaceAll('_',' ') || 'hybrid'}</span>{school.verification_instructions ? <span className="admin-sub">{school.verification_instructions.slice(0,90)}{school.verification_instructions.length > 90 ? '…' : ''}</span> : null}</td>
            <td>{school.email_domain ? <><MailCheck size={15}/><span className="admin-sub">{school.email_domain}</span></> : <span className="admin-sub">Document/manual verification supported</span>}</td>
            <td><span className={`status-badge ${school.is_active ? 'status-approved' : 'status-rejected'}`}>{school.is_active ? 'active' : 'archived'}</span></td>
            <td>{manageable ? <form action={setInstitutionActive}><input type="hidden" name="institution_id" value={school.id}/><input type="hidden" name="active" value={school.is_active ? 'false' : 'true'}/><button className={`admin-action ${school.is_active ? 'danger' : 'success'}`} type="submit">{school.is_active ? 'Archive' : 'Restore'}</button></form> : <span className="admin-sub">Scoped access</span>}</td>
          </tr>)}
          {!schools?.length ? <tr><td colSpan={5}><div className="empty-admin">No schools are configured yet. {manageable ? 'Add your first school above.' : 'Ask a global admin to assign a school to you.'}</div></td></tr> : null}
        </tbody></table></div>
      </section>

      {context.isGlobalAdmin ? <section className="admin-section">
        <div className="admin-section-head"><div><h2>Student-requested schools</h2><p>Requests submitted from “My school is not listed”. Approving creates an active school with hybrid verification; you can then refine its email configuration.</p></div></div>
        <div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>School</th><th>Location</th><th>Website</th><th>Requested</th><th>Decision</th></tr></thead><tbody>
          {(requests || []).map((request: any) => <tr key={request.id}><td><span className="admin-name">{request.school_name}</span></td><td>{[request.city,request.state].filter(Boolean).join(', ') || '—'}</td><td>{request.website || '—'}</td><td>{request.created_at ? new Date(request.created_at).toLocaleDateString() : '—'}</td><td><form action={processSchoolRequest} className="admin-actions"><input type="hidden" name="request_id" value={request.id}/><button className="admin-action success" name="decision" value="approve">Approve + add</button><button className="admin-action danger" name="decision" value="reject">Reject</button></form></td></tr>)}
          {!requests?.length ? <tr><td colSpan={5}><div className="empty-admin">No pending school requests.</div></td></tr> : null}
        </tbody></table></div>
      </section> : null}
    </>
  )
}
