import Link from 'next/link'
import { notFound } from 'next/navigation'
import { AlertTriangle, ArrowUpRight, BadgeCheck, Building2, CalendarDays, CheckCircle2, GraduationCap, MailCheck, MapPinned, ShieldCheck, Store, UsersRound, Wrench } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { createAdminClient } from '@/lib/supabase/admin'
import { calculateSchoolReadiness } from '@/lib/admin-phase2'
import { assignSchoolAdmin, updateInstitutionSetup } from '../../actions'
import { createCampusLocation } from '../../campus-intelligence/actions'
import { canManageAdmins, canManageSchools, requireAdminContext } from '../../lib'

export default async function School360Page({
  params,
  searchParams,
}: {
  params: Promise<{ id: string }>
  searchParams: Promise<{ setup?: string; success?: string; error?: string }>
}) {
  const { id } = await params
  const query = await searchParams
  const context = await requireAdminContext()
  const assigned = context.schoolAssignments.some((item) => item.is_active && item.institution_id === id)
  const globalSchoolAccess = canManageSchools(context.globalRole)
  if (!globalSchoolAccess && !assigned) notFound()

  const supabase = await createClient()
  const admin = createAdminClient()
  const { data: school } = await supabase.from('institutions')
    .select('id,name,slug,city,state,country,email_domain,is_active,verification_mode,allowed_student_email_domains,verification_instructions,created_at')
    .eq('id',id)
    .maybeSingle()
  if (!school) notFound()

  const [
    studentResult,
    verificationResult,
    campusVendorResult,
    locationsResult,
    eventsResult,
    assignmentsResult,
  ] = await Promise.all([
    supabase.from('profiles').select('id,onboarding_completed_at,student_verification_status,created_at').eq('account_type','student').eq('institution_id',id),
    supabase.from('student_verifications').select('student_id,status,submitted_at'),
    supabase.from('vendor_institutions').select('vendor_id,status,is_primary,created_at').eq('institution_id',id),
    supabase.from('campus_locations').select('id,name,location_type,is_active,description').eq('institution_id',id).order('sort_order').order('name'),
    supabase.from('campus_calendar_events').select('id,title,event_type,starts_on,ends_on,is_published').eq('institution_id',id).eq('is_published',true).gte('starts_on',new Date().toISOString().slice(0,10)).order('starts_on').limit(8),
    admin.from('institution_admin_assignments').select('user_id,role,is_active,created_at').eq('institution_id',id).eq('is_active',true),
  ])

  const students = studentResult.data || []
  const studentIds = new Set(students.map((item) => item.id))
  const verifications = (verificationResult.data || []).filter((item) => studentIds.has(item.student_id))
  const vendorLinks = campusVendorResult.data || []
  const vendorIds = Array.from(new Set(vendorLinks.map((item) => item.vendor_id)))

  const [{ data: vendors }, { data: products }, { data: services }, { data: contacts }, { data: reports }] = vendorIds.length ? await Promise.all([
    supabase.from('vendor_profiles').select('id,business_name,verification_status,marketplace_status,suspended_until,onboarding_completed_at').in('id',vendorIds),
    supabase.from('vendor_products').select('id,vendor_id,is_active').in('vendor_id',vendorIds).eq('is_active',true),
    supabase.from('vendor_services').select('id,vendor_id,is_active').in('vendor_id',vendorIds).eq('is_active',true),
    supabase.from('contact_events').select('id,vendor_id,created_at').eq('institution_id',id).gte('created_at',new Date(Date.now()-7*86400000).toISOString()),
    supabase.from('complaints').select('id,reporter_id,vendor_id,status,severity,created_at').in('vendor_id',vendorIds).in('status',['open','reviewing']),
  ]) : [{data:[] as any[]},{data:[] as any[]},{data:[] as any[]},{data:[] as any[]},{data:[] as any[]}]

  const vendorMap = new Map((vendors || []).map((vendor) => [vendor.id,vendor]))
  const approvedLinks = vendorLinks.filter((link) => link.status === 'approved')
  const pendingLinks = vendorLinks.filter((link) => link.status === 'pending')
  const studentVisible = approvedLinks.filter((link) => {
    const vendor = vendorMap.get(link.vendor_id)
    if (!vendor || !vendor.onboarding_completed_at || vendor.verification_status !== 'approved') return false
    if (vendor.marketplace_status === 'active') return true
    return vendor.marketplace_status === 'suspended' && vendor.suspended_until && new Date(vendor.suspended_until) <= new Date()
  })

  const activeLocations = (locationsResult.data || []).filter((item) => item.is_active)
  const schoolAdmins = assignmentsResult.data || []
  const readiness = calculateSchoolReadiness({
    name: school.name,
    city: school.city,
    state: school.state,
    country: school.country,
    verificationMode: school.verification_mode,
    emailDomain: school.email_domain,
    allowedEmailDomains: school.allowed_student_email_domains,
    verificationInstructions: school.verification_instructions,
    activeLocationCount: activeLocations.length,
    schoolAdminCount: schoolAdmins.length,
  })

  const pendingStudentReviews = verifications.filter((item) => ['pending','under_review'].includes(item.status)).length
  const openReports = (reports || []).filter((item) => studentIds.has(item.reporter_id))
  const criticalReports = openReports.filter((item) => ['high','critical'].includes(item.severity || '')).length
  const visibleVendorIds = new Set(studentVisible.map((link) => link.vendor_id))
  const visibleProductCount = (products || []).filter((item) => visibleVendorIds.has(item.vendor_id)).length
  const visibleServiceCount = (services || []).filter((item) => visibleVendorIds.has(item.vendor_id)).length
  const onboardedStudents = students.filter((item) => Boolean(item.onboarding_completed_at)).length
  const verifiedStudents = students.filter((item) => item.student_verification_status === 'verified').length
  const incompleteStudents = students.filter((item) => !item.onboarding_completed_at).length

  const canEditSchool = canManageSchools(context.globalRole)
  const canAssignAdmin = canManageAdmins(context.globalRole)
  const canResolveAdminEmails = ['super_admin','operations_admin'].includes(context.globalRole || '')
  const adminEmailMap = new Map<string,string>()
  if (canResolveAdminEmails && schoolAdmins.length) {
    const { data: authUsers } = await admin.auth.admin.listUsers({ page:1, perPage:1000 })
    for (const user of authUsers.users || []) if (user.email) adminEmailMap.set(user.id,user.email)
  }

  return (
    <>
      <header className="admin-topbar">
        <div>
          <span className="admin-pill"><Building2 size={15}/> School 360</span>
          <h1>{school.name}</h1>
          <p>{[school.city,school.state,school.country].filter(Boolean).join(', ') || 'Location setup incomplete'} · One operational view of Students, Vendors, verification, marketplace health and Campus Intelligence.</p>
        </div>
        <div className="admin-actions">
          {context.isGlobalAdmin ? <Link className="admin-action" href="/control-center/schools">Back to schools</Link> : <Link className="admin-action" href="/control-center">Back to overview</Link>}
          <Link className="admin-action primary" href={'/control-center/campus-intelligence?campus=' + school.id}>Campus Intelligence</Link>
        </div>
      </header>

      {query.setup === 'created' ? <div className="admin-success"><CheckCircle2 size={16}/> School created. Finish Campus setup and Administration below so the institution becomes operationally ready.</div> : null}
      {query.success ? <div className="admin-success">{query.success}</div> : null}
      {query.error ? <div className="admin-error">{query.error}</div> : null}

      <section className="admin-section school-readiness-card">
        <div className="admin-section-head">
          <div><span className="admin-eyebrow">School readiness</span><h2>{readiness.status === 'ready' ? 'Ready for CampusLink' : 'Needs setup — ' + readiness.blockingIssues.length + ' item' + (readiness.blockingIssues.length === 1 ? '' : 's')}</h2><p>Readiness checks only operational prerequisites. It is not a public “verified school” badge.</p></div>
          <span className={'readiness-badge ' + (readiness.status === 'ready' ? 'ready' : 'needs')}>{readiness.status === 'ready' ? 'Ready' : 'Needs setup'}</span>
        </div>
        <div className="admin-section-body readiness-checklist">
          <div className={school.city && school.state && school.country ? 'done' : 'todo'}><BadgeCheck size={17}/><div><strong>School details</strong><span>{school.city && school.state && school.country ? 'Location details complete.' : 'City, state and country need attention.'}</span></div></div>
          <div className={readiness.blockingIssues.some((issue) => issue.toLowerCase().includes('verification') || issue.toLowerCase().includes('email domain')) ? 'todo' : 'done'}><MailCheck size={17}/><div><strong>Student verification</strong><span>{school.verification_mode.replaceAll('_',' ')} · {school.email_domain || 'manual/document configuration'}</span></div></div>
          <div className={activeLocations.length ? 'done' : 'todo'}><MapPinned size={17}/><div><strong>Campus setup</strong><span>{activeLocations.length} active location{activeLocations.length === 1 ? '' : 's'}.</span></div></div>
          <div className={schoolAdmins.length ? 'done' : 'recommended'}><UsersRound size={17}/><div><strong>Administration</strong><span>{schoolAdmins.length ? schoolAdmins.length + ' School Admin assignment' + (schoolAdmins.length === 1 ? '' : 's') + '.' : 'Recommended: assign one School Admin.'}</span></div></div>
        </div>
      </section>

      <section className="admin-grid">
        <article className="admin-stat"><span>Students</span><strong>{students.length}</strong><small>{onboardedStudents} onboarded · {verifiedStudents} verified · {incompleteStudents} incomplete.</small></article>
        <article className="admin-stat"><span>Vendors</span><strong>{vendorIds.length}</strong><small>{approvedLinks.length} Campus approved · {pendingLinks.length} waiting.</small></article>
        <article className="admin-stat"><span>Student-visible Vendors</span><strong>{studentVisible.length}</strong><small>Passes setup, identity, Campus and safety gates.</small></article>
        <article className="admin-stat"><span>Trust queue</span><strong>{pendingStudentReviews + pendingLinks.length + openReports.length}</strong><small>{pendingStudentReviews} Student · {pendingLinks.length} Vendor Campus · {openReports.length} report.</small></article>
      </section>

      <section className="school360-grid">
        <section className="admin-section">
          <div className="admin-section-head"><div><h2>Verification</h2><p>How Students from this institution prove their campus identity.</p></div><ShieldCheck size={19}/></div>
          <div className="admin-section-body school360-detail-list">
            <div><span>Mode</span><strong>{school.verification_mode.replaceAll('_',' ')}</strong></div>
            <div><span>Primary email domain</span><strong>{school.email_domain || 'Not configured'}</strong></div>
            <div><span>Allowed domains</span><strong>{school.allowed_student_email_domains?.length ? school.allowed_student_email_domains.join(', ') : 'None added'}</strong></div>
            <div><span>Manual instructions</span><strong>{school.verification_instructions || 'Not configured'}</strong></div>
          </div>
        </section>

        <section className="admin-section">
          <div className="admin-section-head"><div><h2>Marketplace</h2><p>Current discoverable inventory and Student response.</p></div><Store size={19}/></div>
          <div className="admin-section-body school360-detail-list">
            <div><span>Active Vendor links</span><strong>{approvedLinks.length}</strong></div>
            <div><span>Active products</span><strong>{visibleProductCount}</strong></div>
            <div><span>Active services</span><strong>{visibleServiceCount}</strong></div>
            <div><span>Contacts in last 7 days</span><strong>{contacts?.length || 0}</strong></div>
          </div>
        </section>

        <section className="admin-section">
          <div className="admin-section-head"><div><h2>Trust & operations</h2><p>Queues that may require action at this school.</p></div><AlertTriangle size={19}/></div>
          <div className="admin-section-body school360-detail-list">
            <div><span>Student verification waiting</span><strong>{pendingStudentReviews}</strong></div>
            <div><span>Vendor Campus approvals</span><strong>{pendingLinks.length}</strong></div>
            <div><span>Open reports</span><strong>{openReports.length}</strong></div>
            <div><span>High / critical reports</span><strong>{criticalReports}</strong></div>
          </div>
        </section>

        <section className="admin-section">
          <div className="admin-section-head"><div><h2>Campus Intelligence</h2><p>Places and dates that shape local marketplace context.</p></div><CalendarDays size={19}/></div>
          <div className="admin-section-body school360-detail-list">
            <div><span>Active locations</span><strong>{activeLocations.length}</strong></div>
            <div><span>Upcoming dates</span><strong>{eventsResult.data?.length || 0}</strong></div>
            {(eventsResult.data || []).slice(0,3).map((event) => <div key={event.id}><span>{event.starts_on}</span><strong>{event.title}</strong></div>)}
          </div>
        </section>
      </section>

      {canEditSchool ? <section className="admin-section">
        <div className="admin-section-head"><div><span className="admin-eyebrow">Steps 1–2</span><h2>School details & Student verification</h2><p>Update the configuration in one place. Changes immediately feed readiness checks.</p></div><Wrench size={19}/></div>
        <div className="admin-section-body">
          <form action={updateInstitutionSetup} className="admin-form">
            <input type="hidden" name="institution_id" value={school.id}/>
            <div className="admin-form-grid">
              <div className="admin-field"><label>Full school name</label><input name="name" defaultValue={school.name} required/></div>
              <div className="admin-field"><label>City</label><input name="city" defaultValue={school.city || ''}/></div>
              <div className="admin-field"><label>State</label><input name="state" defaultValue={school.state || ''}/></div>
              <div className="admin-field"><label>Country</label><input name="country" defaultValue={school.country || 'Nigeria'}/></div>
              <div className="admin-field"><label>Verification mode</label><select name="verification_mode" defaultValue={school.verification_mode}><option value="hybrid">Hybrid: email or document</option><option value="institution_email">Institution email</option><option value="manual">Manual evidence</option></select></div>
              <div className="admin-field"><label>Primary Student email domain</label><small className="admin-sub">The school's main/canonical Student email domain. It is always accepted.</small><input name="email_domain" defaultValue={school.email_domain || ''}/></div>
              <div className="admin-field"><label>Allowed email domains</label><small className="admin-sub">Additional approved Student email domains or aliases. Separate multiple domains with commas.</small><input name="allowed_student_email_domains" defaultValue={(school.allowed_student_email_domains || []).join(', ')}/></div>
            </div>
            <div className="admin-field"><label>Verification instructions</label><textarea name="verification_instructions" rows={3} defaultValue={school.verification_instructions || ''}/></div>
            <button className="admin-action primary" type="submit">Save school setup</button>
          </form>
        </div>
      </section> : null}

      <section className="school360-grid">
        <section className="admin-section">
          <div className="admin-section-head"><div><span className="admin-eyebrow">Step 3</span><h2>Campus setup</h2><p>Add at least one location. More detail can be managed in Campus Intelligence.</p></div><MapPinned size={19}/></div>
          <div className="admin-section-body">
            <form action={createCampusLocation} className="admin-form">
              <input type="hidden" name="institution_id" value={school.id}/>
              <input type="hidden" name="return_to" value={'/control-center/schools/' + school.id}/>
              <div className="admin-form-grid">
                <div className="admin-field"><label>Location name</label><input name="name" required minLength={2} placeholder="Main Gate"/></div>
                <div className="admin-field"><label>Type</label><select name="location_type" defaultValue="gate"><option value="gate">Gate</option><option value="hostel">Hostel</option><option value="faculty">Faculty</option><option value="student_centre">Student centre</option><option value="library">Library</option><option value="landmark">Landmark</option><option value="off_campus">Off-campus area</option><option value="other">Other</option></select></div>
              </div>
              <div className="admin-field"><label>Description</label><input name="description" maxLength={240} placeholder="Optional context"/></div>
              <button className="admin-action primary" type="submit">Add campus location</button>
            </form>
            <div className="school360-mini-list">{activeLocations.slice(0,5).map((location) => <div key={location.id}><strong>{location.name}</strong><span>{location.location_type.replaceAll('_',' ')}</span></div>)}{!activeLocations.length ? <div className="admin-note">No active Campus location yet.</div> : null}</div>
          </div>
        </section>

        <section className="admin-section">
          <div className="admin-section-head"><div><span className="admin-eyebrow">Step 4</span><h2>Administration</h2><p>One School Admin role manages institution-level operations without platform-wide power.</p></div><UsersRound size={19}/></div>
          <div className="admin-section-body">
            <div className="school360-mini-list">{schoolAdmins.map((assignment) => <div key={assignment.user_id}><strong>{canResolveAdminEmails ? adminEmailMap.get(assignment.user_id) || 'School Admin' : assignment.user_id === context.userId ? 'You' : 'School Admin'}</strong><span>{assignment.role.replaceAll('_',' ')}</span></div>)}{!schoolAdmins.length ? <div className="admin-note">No School Admin is assigned yet. This is recommended but does not expose the school publicly as “verified”.</div> : null}</div>
            {canAssignAdmin ? <form action={assignSchoolAdmin} className="admin-form" style={{marginTop:16}}>
              <input type="hidden" name="institution_id" value={school.id}/>
              <input type="hidden" name="return_to" value={'/control-center/schools/' + school.id}/>
              <input type="hidden" name="role" value="school_admin"/>
              <div className="admin-field"><label>CampusLink account email</label><input type="email" name="email" required placeholder="schooladmin@example.edu.ng"/></div>
              <button className="admin-action primary" type="submit">Assign School Admin</button>
            </form> : null}
          </div>
        </section>
      </section>

      <section className="admin-section">
        <div className="admin-section-head"><div><h2>Go deeper</h2><p>Use the specialist pages only when you need record-level action.</p></div></div>
        <div className="admin-section-body queue-grid">
          <Link className="queue-card" href={'/control-center/students?school=' + school.id}><GraduationCap/><h3>Students</h3><p>Filter and review Student accounts for this institution.</p><span className="queue-link">Open Students <ArrowUpRight size={14}/></span></Link>
          <Link className="queue-card" href={'/control-center/vendors?school=' + school.id}><Store/><h3>Vendors</h3><p>Review Vendor identity, Campus access and marketplace state.</p><span className="queue-link">Open Vendors <ArrowUpRight size={14}/></span></Link>
        </div>
      </section>
    </>
  )
}
