import Link from 'next/link'
import { notFound } from 'next/navigation'
import { ArrowUpRight, Building2, CheckCircle2, Globe2, MailCheck, MapPinned, Plus, ShieldCheck, UsersRound } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { createAdminClient } from '@/lib/supabase/admin'
import { calculateSchoolReadiness } from '@/lib/admin-phase2'
import { canManageSchools, requireAdminContext } from '../lib'
import { createInstitution, processSchoolRequest, setInstitutionActive } from '../actions'

export default async function SchoolsAdminPage({
  searchParams,
}: {
  searchParams: Promise<{ success?: string; error?: string; readiness?: string }>
}) {
  const params = await searchParams
  const context = await requireAdminContext()
  const supabase = await createClient()
  const admin = createAdminClient()
  const manageable = canManageSchools(context.globalRole)
  if (!manageable) notFound()

  const [{ data: schools }, { data: requests }] = await Promise.all([
    supabase.from('institutions').select('id,name,slug,city,state,country,email_domain,is_active,verification_mode,allowed_student_email_domains,verification_instructions').order('name'),
    context.isGlobalAdmin
      ? supabase.from('school_requests').select('id,school_name,city,state,website,status,created_at').eq('status','pending').order('created_at',{ascending:false}).limit(30)
      : Promise.resolve({ data: [] as Array<Record<string, unknown>> }),
  ])

  const schoolIds = (schools || []).map((school) => school.id)
  const [{ data: locations }, { data: assignments }] = schoolIds.length ? await Promise.all([
    admin.from('campus_locations').select('institution_id,is_active').in('institution_id',schoolIds),
    admin.from('institution_admin_assignments').select('institution_id,is_active').in('institution_id',schoolIds),
  ]) : [{data:[] as any[]},{data:[] as any[]}]

  const locationCount = new Map<string,number>()
  for (const row of locations || []) if (row.is_active) locationCount.set(row.institution_id,(locationCount.get(row.institution_id)||0)+1)
  const adminCount = new Map<string,number>()
  for (const row of assignments || []) if (row.is_active) adminCount.set(row.institution_id,(adminCount.get(row.institution_id)||0)+1)

  const rows = (schools || []).map((school) => ({
    school,
    readiness: calculateSchoolReadiness({
      name:school.name,
      city:school.city,
      state:school.state,
      country:school.country,
      verificationMode:school.verification_mode,
      emailDomain:school.email_domain,
      allowedEmailDomains:school.allowed_student_email_domains,
      verificationInstructions:school.verification_instructions,
      activeLocationCount:locationCount.get(school.id)||0,
      schoolAdminCount:adminCount.get(school.id)||0,
    }),
  }))
  const filteredRows = params.readiness === 'needs_setup'
    ? rows.filter((row) => row.readiness.status === 'needs_setup')
    : params.readiness === 'ready'
      ? rows.filter((row) => row.readiness.status === 'ready')
      : rows
  const readyCount = rows.filter((row) => row.readiness.status === 'ready').length

  return (
    <>
      <header className="admin-topbar"><div><span className="admin-pill"><Building2 size={15}/> School management</span><h1>Schools</h1><p>Add institutions, understand setup readiness and open School 360 without turning school onboarding into a technical form.</p></div></header>
      {params.success ? <div className="admin-success">{params.success}</div> : null}
      {params.error ? <div className="admin-error">{params.error}</div> : null}

      <section className="admin-grid">
        <article className="admin-stat"><span>Active schools</span><strong>{rows.filter((row) => row.school.is_active).length}</strong><small>Currently available to onboarding.</small></article>
        <article className="admin-stat"><span>Ready</span><strong>{readyCount}</strong><small>Core setup checks completed.</small></article>
        <article className="admin-stat"><span>Needs setup</span><strong>{rows.length-readyCount}</strong><small>Missing one or more operational requirements.</small></article>
        <article className="admin-stat"><span>School requests</span><strong>{requests?.length || 0}</strong><small>“My school is not listed” requests waiting.</small></article>
      </section>

      <section className="admin-section">
        <div className="admin-section-head"><div><span className="admin-eyebrow">Easy Add School</span><h2>Four-step setup</h2><p>Create the institution first, then CampusLink takes you directly to School 360 to finish Campus setup and Administration.</p></div><Plus size={20}/></div>
        <div className="admin-section-body">
          <div className="setup-step-grid">
            <div className="setup-step active"><span>1</span><div><strong>School details</strong><small>Name and location.</small></div></div>
            <div className="setup-step active"><span>2</span><div><strong>Student verification</strong><small>Email/manual method.</small></div></div>
            <div className="setup-step"><span>3</span><div><strong>Campus setup</strong><small>Add Main Gate or another real location.</small></div></div>
            <div className="setup-step"><span>4</span><div><strong>Administration</strong><small>Assign one School Admin.</small></div></div>
          </div>

          <form action={createInstitution} className="admin-form school-create-form">
            <div className="admin-form-section">
              <div className="admin-form-section-title"><Building2 size={17}/><div><strong>Step 1 — School details</strong><span>The basic institution record Students and Vendors will recognize.</span></div></div>
              <div className="admin-form-grid">
                <div className="admin-field"><label>Full school name</label><input name="name" required placeholder="University of Lagos"/></div>
                <div className="admin-field"><label>Slug <span style={{fontWeight:500}}>(optional)</span></label><input name="slug" placeholder="unilag"/></div>
                <div className="admin-field"><label>City</label><input name="city" required placeholder="Lagos"/></div>
                <div className="admin-field"><label>State</label><input name="state" required placeholder="Lagos"/></div>
                <div className="admin-field"><label>Country</label><input name="country" defaultValue="Nigeria" required/></div>
              </div>
            </div>

            <div className="admin-form-section">
              <div className="admin-form-section-title"><ShieldCheck size={17}/><div><strong>Step 2 — Student verification</strong><span>Choose how Students from this institution can prove membership.</span></div></div>
              <div className="admin-form-grid">
                <div className="admin-field"><label>Verification mode</label><select name="verification_mode" defaultValue="hybrid"><option value="hybrid">Hybrid: email or document</option><option value="institution_email">Institution email</option><option value="manual">Manual evidence</option></select></div>
                <div className="admin-field"><label>Primary Student email domain</label><input name="email_domain" placeholder="student.unilag.edu.ng"/></div>
                <div className="admin-field"><label>Allowed email domains</label><input name="allowed_student_email_domains" placeholder="student.school.edu.ng, school.edu.ng"/></div>
              </div>
              <div className="admin-field"><label>Manual verification instructions</label><textarea name="verification_instructions" rows={3} placeholder="Explain what evidence Students can use when email verification is unavailable."/></div>
            </div>

            <div className="admin-note"><CheckCircle2 size={16}/> After creation, CampusLink opens School 360 so you can add Campus locations and assign a School Admin. Archiving later never deletes historical Students, Vendors, reviews or reports.</div>
            <div><button className="admin-action primary" type="submit">Create school & continue setup</button></div>
          </form>
        </div>
      </section>

      <section className="admin-section">
        <div className="admin-section-head"><div><h2>Institution directory</h2><p>Readiness explains exactly what is missing. It is an internal setup state, not a public verification badge.</p></div><Globe2 size={20}/></div>
        <div className="admin-filter-bar">
          <Link className={!params.readiness ? 'active' : ''} href="/control-center/schools">All ({rows.length})</Link>
          <Link className={params.readiness === 'ready' ? 'active' : ''} href="/control-center/schools?readiness=ready">Ready ({readyCount})</Link>
          <Link className={params.readiness === 'needs_setup' ? 'active' : ''} href="/control-center/schools?readiness=needs_setup">Needs setup ({rows.length-readyCount})</Link>
        </div>
        <div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>School</th><th>Readiness</th><th>Verification</th><th>Campus & admin</th><th>Status</th><th>Action</th></tr></thead><tbody>
          {filteredRows.map(({school,readiness}) => <tr key={school.id}>
            <td><Link className="admin-name admin-link" href={'/control-center/schools/' + school.id}>{school.name}</Link><span className="admin-sub">{[school.city,school.state,school.country].filter(Boolean).join(', ') || school.slug}</span></td>
            <td><span className={'readiness-badge ' + (readiness.status === 'ready' ? 'ready' : 'needs')}>{readiness.status === 'ready' ? 'Ready' : 'Needs setup'}</span>{readiness.blockingIssues.length ? <span className="admin-sub">{readiness.blockingIssues[0]}{readiness.blockingIssues.length > 1 ? ' +' + (readiness.blockingIssues.length-1) : ''}</span> : readiness.recommendations.length ? <span className="admin-sub">Recommended: {readiness.recommendations[0]}</span> : <span className="admin-sub">Core setup complete.</span>}</td>
            <td><span className="status-badge status-reviewing">{school.verification_mode?.replaceAll('_',' ') || 'hybrid'}</span>{school.email_domain ? <span className="admin-sub"><MailCheck size={13}/> {school.email_domain}</span> : <span className="admin-sub">No primary email domain</span>}</td>
            <td><span className="admin-sub"><MapPinned size={13}/> {locationCount.get(school.id)||0} active location{(locationCount.get(school.id)||0) === 1 ? '' : 's'}</span><span className="admin-sub"><UsersRound size={13}/> {adminCount.get(school.id)||0} School Admin{(adminCount.get(school.id)||0) === 1 ? '' : 's'}</span></td>
            <td><span className={'status-badge ' + (school.is_active ? 'status-approved' : 'status-rejected')}>{school.is_active ? 'active' : 'archived'}</span></td>
            <td><div className="admin-actions"><Link className="admin-action primary" href={'/control-center/schools/' + school.id}>Open 360 <ArrowUpRight size={13}/></Link><form action={setInstitutionActive}><input type="hidden" name="institution_id" value={school.id}/><input type="hidden" name="active" value={school.is_active ? 'false' : 'true'}/><button className={'admin-action ' + (school.is_active ? 'danger' : 'success')} type="submit">{school.is_active ? 'Archive' : 'Restore'}</button></form></div></td>
          </tr>)}
          {!filteredRows.length ? <tr><td colSpan={6}><div className="empty-admin">No schools match this readiness filter.</div></td></tr> : null}
        </tbody></table></div>
      </section>

      {context.isGlobalAdmin ? <section className="admin-section">
        <div className="admin-section-head"><div><h2>Student-requested schools</h2><p>Requests submitted from “My school is not listed”. Approval creates the institution; School 360 then guides the remaining setup.</p></div></div>
        <div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>School</th><th>Location</th><th>Website</th><th>Requested</th><th>Decision</th></tr></thead><tbody>
          {(requests || []).map((request: any) => <tr key={request.id}><td><span className="admin-name">{request.school_name}</span></td><td>{[request.city,request.state].filter(Boolean).join(', ') || '—'}</td><td>{request.website || '—'}</td><td>{request.created_at ? new Date(request.created_at).toLocaleDateString() : '—'}</td><td><form action={processSchoolRequest} className="admin-actions"><input type="hidden" name="request_id" value={request.id}/><button className="admin-action success" name="decision" value="approve">Approve + add</button><button className="admin-action danger" name="decision" value="reject">Reject</button></form></td></tr>)}
          {!requests?.length ? <tr><td colSpan={5}><div className="empty-admin">No pending school requests.</div></td></tr> : null}
        </tbody></table></div>
      </section> : null}
    </>
  )
}
