import Link from 'next/link'
import { Activity, AlertTriangle, ArrowUpRight, BellRing, Building2, CircleAlert, GraduationCap, Info, LifeBuoy, ShieldCheck, Store, UsersRound } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { createAdminClient } from '@/lib/supabase/admin'
import { AdminOverviewChart } from '@/components/dashboard-charts'
import { calculateSchoolReadiness, dayGreeting, waitingAge } from '@/lib/admin-phase2'
import { requireAdminContext } from './lib'

export default async function AdminDashboard() {
  const context = await requireAdminContext()
  const supabase = await createClient()
  const admin = createAdminClient()
  const { data: userData } = await supabase.auth.getUser()
  const displayName = String(userData.user?.user_metadata?.first_name || userData.user?.email?.split('@')[0] || 'Admin')
    .replace(/[._-]+/g,' ')
    .replace(/\b\w/g,(letter) => letter.toUpperCase())
  const lagosHour = Number(new Intl.DateTimeFormat('en-GB',{hour:'2-digit',hour12:false,timeZone:'Africa/Lagos'}).format(new Date()).slice(0,2))
  const greeting = dayGreeting(lagosHour)

  const schoolIds = context.isGlobalAdmin
    ? null
    : context.schoolAssignments.filter((item) => item.is_active).map((item) => item.institution_id)

  let schoolQuery = supabase.from('institutions')
    .select('id,name,city,state,country,email_domain,verification_mode,allowed_student_email_domains,verification_instructions,is_active')
    .eq('is_active',true)
    .order('name')
  if (schoolIds) schoolQuery = schoolQuery.in('id', schoolIds)

  const [
    students,
    onboardedStudents,
    verifiedStudents,
    vendorAccounts,
    vendorProfiles,
    completedVendorProfiles,
    pendingStudents,
    pendingVendors,
    pendingCampusVendors,
    reports,
    underReviewVendors,
    schoolsResult,
  ] = await Promise.all([
    supabase.from('profiles').select('id', { count: 'exact', head: true }).eq('account_type','student'),
    supabase.from('profiles').select('id', { count: 'exact', head: true }).eq('account_type','student').not('onboarding_completed_at','is',null),
    supabase.from('profiles').select('id', { count: 'exact', head: true }).eq('account_type','student').eq('student_verification_status','verified'),
    supabase.from('profiles').select('id', { count: 'exact', head: true }).eq('account_type','vendor'),
    supabase.from('vendor_profiles').select('id', { count: 'exact', head: true }),
    supabase.from('vendor_profiles').select('id', { count: 'exact', head: true }).not('onboarding_completed_at','is',null),
    supabase.from('student_verifications').select('student_id,submitted_at,status').in('status',['pending','under_review']).order('submitted_at',{ascending:true}),
    supabase.from('vendor_profiles').select('id,verification_submitted_at,created_at,verification_status').in('verification_status',['pending','under_review']).order('created_at',{ascending:true}),
    supabase.from('vendor_institutions').select('vendor_id,institution_id,created_at,status').eq('status','pending').order('created_at',{ascending:true}),
    supabase.from('complaints').select('id,severity,status,created_at,vendor_id').in('status',['open','reviewing']).order('created_at',{ascending:true}),
    supabase.from('vendor_profiles').select('id,marketplace_status').eq('marketplace_status','under_review'),
    schoolQuery,
  ])

  const schools = schoolsResult.data || []
  const visibleSchoolIds = schools.map((school) => school.id)

  const [{ data: locationRows }, { data: assignmentRows }] = visibleSchoolIds.length ? await Promise.all([
    admin.from('campus_locations').select('institution_id,is_active').in('institution_id', visibleSchoolIds),
    admin.from('institution_admin_assignments').select('institution_id,is_active').in('institution_id', visibleSchoolIds),
  ]) : [{ data: [] as any[] }, { data: [] as any[] }]

  const locationCount = new Map<string,number>()
  for (const row of locationRows || []) if (row.is_active) locationCount.set(row.institution_id,(locationCount.get(row.institution_id)||0)+1)
  const adminCount = new Map<string,number>()
  for (const row of assignmentRows || []) if (row.is_active) adminCount.set(row.institution_id,(adminCount.get(row.institution_id)||0)+1)

  const schoolReadiness = schools.map((school) => ({
    school,
    readiness: calculateSchoolReadiness({
      name: school.name,
      city: school.city,
      state: school.state,
      country: school.country,
      verificationMode: school.verification_mode,
      emailDomain: school.email_domain,
      allowedEmailDomains: school.allowed_student_email_domains,
      verificationInstructions: school.verification_instructions,
      activeLocationCount: locationCount.get(school.id) || 0,
      schoolAdminCount: adminCount.get(school.id) || 0,
    }),
  }))
  const schoolsNeedingSetup = schoolReadiness.filter((item) => item.readiness.status === 'needs_setup')

  const role = context.globalRole || context.schoolAssignments[0]?.role || 'admin'
  const scoped = !context.isGlobalAdmin
  const studentCount = students.count || 0
  const studentOnboardedCount = onboardedStudents.count || 0
  const studentVerifiedCount = verifiedStudents.count || 0
  const studentIncompleteCount = Math.max(0,studentCount-studentOnboardedCount)
  const vendorAccountCount = vendorAccounts.count || 0
  const vendorProfileCount = vendorProfiles.count || 0
  const vendorCompletedCount = completedVendorProfiles.count || 0
  const vendorIncompleteCount = context.isGlobalAdmin ? Math.max(0,vendorAccountCount-vendorProfileCount) : 0
  const vendorCount = context.isGlobalAdmin ? vendorAccountCount : vendorProfileCount
  const schoolCount = context.isGlobalAdmin ? schools.length : context.schoolAssignments.length
  const openReports = reports.data || []
  const criticalReports = openReports.filter((report) => ['critical','high'].includes(report.severity || ''))
  const reportCount = openReports.length
  const studentQueue = pendingStudents.data || []
  const vendorIdentityQueue = pendingVendors.data || []
  const campusQueue = pendingCampusVendors.data || []
  const underReviewCount = underReviewVendors.data?.length || 0

  const canReviewVendorIdentity = ['super_admin','operations_admin','verification_admin'].includes(context.globalRole || '')
  const canSeeGlobalIncompleteVendors = ['super_admin','operations_admin'].includes(context.globalRole || '')
  let vendorRemindableCount = 0
  if (canSeeGlobalIncompleteVendors && vendorIncompleteCount) {
    const [{ data: vendorProfileIds }, { data: recentReminderRows }, { data: vendorProfileAccounts }] = await Promise.all([
      admin.from('vendor_profiles').select('id'),
      admin.from('admin_communication_deliveries').select('user_id').eq('reminder_key','vendor_onboarding_incomplete').gte('sent_at',new Date(Date.now()-24*60*60*1000).toISOString()),
      admin.from('profiles').select('id').eq('account_type','vendor'),
    ])
    const profiles = new Set((vendorProfileIds || []).map((row) => row.id))
    const reminded = new Set((recentReminderRows || []).map((row) => row.user_id))
    vendorRemindableCount = (vendorProfileAccounts || []).filter((row) => !profiles.has(row.id) && !reminded.has(row.id)).length
  }

  const urgentCount = criticalReports.length + underReviewCount
  const attentionCount =
    urgentCount +
    studentQueue.length +
    (canReviewVendorIdentity ? vendorIdentityQueue.length : 0) +
    campusQueue.length +
    schoolsNeedingSetup.length

  const oldestStudent = studentQueue.find((item) => item.submitted_at)?.submitted_at || null
  const oldestVendor = vendorIdentityQueue.map((item) => item.verification_submitted_at || item.created_at).filter(Boolean).sort()[0] || null
  const oldestCampus = campusQueue.find((item) => item.created_at)?.created_at || null
  const campusSchools = new Set(campusQueue.map((item) => item.institution_id)).size

  return (
    <>
      <header className="admin-hero phase45-admin-hero reveal-admin">
        <div className="admin-hero-copy">
          <div className="admin-hero-label"><ShieldCheck size={16}/> CampusLink Intelligence Centre</div>
          <h1>{greeting}, {displayName}.</h1>
          <p>{attentionCount ? attentionCount + ' operational item' + (attentionCount === 1 ? '' : 's') + ' need attention in your current scope.' : 'Nothing urgent is waiting in your current scope. CampusLink is operationally clear.'}</p>
        </div>
        <div className="admin-hero-side">
          <span>Signed in as</span>
          <strong>{role.replaceAll('_',' ')}</strong>
          <small>{context.isGlobalAdmin ? 'Platform-wide operational view' : context.schoolAssignments.length + ' assigned school' + (context.schoolAssignments.length === 1 ? '' : 's') + ' only'}</small>
        </div>
      </header>

      <section className="cl-data-rail admin-data-rail" aria-label="CampusLink operational summary">
        <div className="brand"><span>Students in scope</span><strong>{studentCount}</strong><small>{studentVerifiedCount} verified · {studentIncompleteCount} incomplete</small></div>
        <div className="accent"><span>Vendors in scope</span><strong>{vendorCount}</strong><small>{vendorCompletedCount} completed business setup</small></div>
        <div><span>{context.isGlobalAdmin ? 'Active schools' : 'Assigned school'}</span><strong>{schoolCount}</strong><small>{schoolsNeedingSetup.length ? schoolsNeedingSetup.length + ' need setup attention' : 'Core school setup looks ready'}</small></div>
        <div><span>Open reports</span><strong>{reportCount}</strong><small>{criticalReports.length ? criticalReports.length + ' high / critical' : 'No high-severity cases'}</small></div>
      </section>

      <section className="admin-section intelligence-command">
        <div className="admin-section-head"><div><span className="admin-eyebrow">Live operational intelligence</span><h2>What needs attention today?</h2><p>Generated from current CampusLink records. No invented AI scoring and no hidden priority model.</p></div><Activity size={20}/></div>
        <div className="admin-section-body intelligence-groups">
          <div className="intelligence-group">
            <div className="intelligence-group-head urgent"><AlertTriangle size={17}/><div><strong>Urgent</strong><small>Safety and visibility risks first.</small></div><b>{urgentCount}</b></div>
            {criticalReports.length ? <Link href="/control-center/safety" className="intelligence-task"><div><strong>{criticalReports.length} high / critical safety case{criticalReports.length === 1 ? '' : 's'}</strong><span>Oldest open case: {waitingAge(criticalReports[0]?.created_at) || 'unknown'}</span></div><ArrowUpRight/></Link> : <div className="intelligence-clear"><ShieldCheck size={16}/> No high-severity safety case is open.</div>}
            {underReviewCount ? <Link href="/control-center/safety" className="intelligence-task"><div><strong>{underReviewCount} Vendor{underReviewCount === 1 ? '' : 's'} under safety review</strong><span>Marketplace visibility is paused until review is completed.</span></div><ArrowUpRight/></Link> : null}
          </div>

          <div className="intelligence-group">
            <div className="intelligence-group-head attention"><CircleAlert size={17}/><div><strong>Needs attention</strong><small>Work queues that block trust or launch readiness.</small></div><b>{attentionCount-urgentCount}</b></div>
            <Link href="/control-center/students?status=awaiting" className="intelligence-task"><div><strong>{studentQueue.length} Student verification{studentQueue.length === 1 ? '' : 's'} waiting</strong><span>{oldestStudent ? 'Oldest waiting: ' + waitingAge(oldestStudent) : 'No Student verification is waiting.'}</span></div><ArrowUpRight/></Link>
            {canReviewVendorIdentity ? <Link href="/control-center/vendors?status=awaiting_identity" className="intelligence-task"><div><strong>{vendorIdentityQueue.length} Vendor identity request{vendorIdentityQueue.length === 1 ? '' : 's'}</strong><span>{oldestVendor ? 'Oldest waiting: ' + waitingAge(oldestVendor) : 'No identity request is waiting.'}</span></div><ArrowUpRight/></Link> : null}
            <Link href="/control-center/vendors?status=awaiting_campus" className="intelligence-task"><div><strong>{campusQueue.length} Campus Vendor approval{campusQueue.length === 1 ? '' : 's'}</strong><span>{campusQueue.length ? 'Across ' + campusSchools + ' school' + (campusSchools === 1 ? '' : 's') + (oldestCampus ? ' · oldest ' + waitingAge(oldestCampus) : '') : 'No Campus Vendor approval is waiting.'}</span></div><ArrowUpRight/></Link>
            {schoolsNeedingSetup.length ? <Link href={context.isGlobalAdmin ? '/control-center/schools?readiness=needs_setup' : '/control-center/schools/' + schoolsNeedingSetup[0].school.id} className="intelligence-task"><div><strong>{schoolsNeedingSetup.length} school setup{schoolsNeedingSetup.length === 1 ? '' : 's'} incomplete</strong><span>{schoolsNeedingSetup[0].school.name}: {schoolsNeedingSetup[0].readiness.blockingIssues[0]}</span></div><ArrowUpRight/></Link> : <div className="intelligence-clear"><Building2 size={16}/> Core setup is ready for every school in your scope.</div>}
          </div>

          <div className="intelligence-group">
            <div className="intelligence-group-head info"><Info size={17}/><div><strong>Informational</strong><small>Useful signals that do not block current operations.</small></div></div>
            <Link href="/control-center/students?status=incomplete" className="intelligence-task"><div><strong>{studentIncompleteCount} incomplete Student registration{studentIncompleteCount === 1 ? '' : 's'}</strong><span>Accounts that still need onboarding, not failed verification.</span></div><ArrowUpRight/></Link>
            {canSeeGlobalIncompleteVendors ? <Link href="/control-center/vendors?status=incomplete" className="intelligence-task"><div><strong>{vendorIncompleteCount} incomplete Vendor registration{vendorIncompleteCount === 1 ? '' : 's'}</strong><span>{vendorRemindableCount} currently eligible for another reminder.</span></div><ArrowUpRight/></Link> : null}
            <Link href="/control-center/communications" className="intelligence-task"><div><strong>Communications centre</strong><span>Send targeted email + dashboard updates when action is genuinely needed.</span></div><BellRing size={16}/></Link>
          </div>
        </div>
      </section>

      <section className="admin-overview-grid">
        <AdminOverviewChart students={studentCount} vendors={vendorCount} schools={schoolCount} reports={reportCount}/>
        <section className="admin-priority-card cl-editorial-surface">
          <div className="admin-priority-head"><div><span>Registration health</span><strong>Where users are in the journey</strong></div><UsersRound size={20}/></div>
          <Link href="/control-center/students" className="priority-row"><div className="priority-icon blue"><GraduationCap/></div><div><strong>Students</strong><span>{studentOnboardedCount} onboarded · {studentVerifiedCount} verified · {studentIncompleteCount} incomplete.</span></div><b>{studentOnboardedCount}/{studentCount}</b><ArrowUpRight/></Link>
          <Link href="/control-center/vendors" className="priority-row"><div className="priority-icon green"><Store/></div><div><strong>Vendors</strong><span>{context.isGlobalAdmin ? vendorProfileCount + ' profiles from ' + vendorAccountCount + ' accounts.' : vendorCompletedCount + ' completed profiles in your scope.'}</span></div><b>{vendorCompletedCount}/{context.isGlobalAdmin ? vendorAccountCount : vendorProfileCount}</b><ArrowUpRight/></Link>
          <Link href={context.isGlobalAdmin ? '/control-center/schools' : schools[0] ? '/control-center/schools/' + schools[0].id : '/control-center'} className="priority-row"><div className="priority-icon amber"><Building2/></div><div><strong>School readiness</strong><span>{schoolsNeedingSetup.length ? schoolsNeedingSetup.length + ' need setup work.' : 'All core school setup requirements are complete.'}</span></div><b>{schoolCount-schoolsNeedingSetup.length}/{schoolCount}</b><ArrowUpRight/></Link>
        </section>
      </section>

      <section className="admin-section modern-admin-section">
        <div className="admin-section-head"><div><h2>Operations shortcuts</h2><p>Go directly to the work area instead of hunting through the control centre.</p></div><UsersRound size={20}/></div>
        <div className="admin-section-body queue-grid modern-queue-grid">
          {context.isGlobalAdmin ? <Link href="/control-center/schools" className="queue-card"><Building2/><h3>School management</h3><p>Review readiness, create schools and open School 360.</p><span className="queue-link">Manage schools <ArrowUpRight size={14}/></span></Link> : schools[0] ? <Link href={'/control-center/schools/' + schools[0].id} className="queue-card"><Building2/><h3>Your School 360</h3><p>See Students, Vendors, readiness, Campus Intelligence and trust state for your assigned institution.</p><span className="queue-link">Open School 360 <ArrowUpRight size={14}/></span></Link> : <Link href="/control-center/campus-intelligence" className="queue-card"><Building2/><h3>Your school operations</h3><p>Manage operational campus context for your assigned scope.</p><span className="queue-link">Open campus intelligence <ArrowUpRight size={14}/></span></Link>}
          <Link href="/control-center/students" className="queue-card"><GraduationCap/><h3>Students</h3><p>Filter registrations, onboarding state and verification work from one page.</p><span className="queue-link">Manage students <ArrowUpRight size={14}/></span></Link>
          <Link href="/control-center/vendors" className="queue-card"><Store/><h3>Vendors</h3><p>Separate incomplete setup, identity, Campus approval and marketplace visibility.</p><span className="queue-link">Manage vendors <ArrowUpRight size={14}/></span></Link>
          <Link href="/control-center/safety" className="queue-card"><LifeBuoy/><h3>Trust & safety</h3><p>Put serious cases first while keeping evidence and decisions connected.</p><span className="queue-link">Open safety centre <ArrowUpRight size={14}/></span></Link>
        </div>
      </section>
    </>
  )
}
