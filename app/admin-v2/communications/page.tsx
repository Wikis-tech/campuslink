import { BellRing, Building2, Mail, Send, ShieldCheck, UsersRound } from 'lucide-react'
import { notFound } from 'next/navigation'
import { createAdminClient } from '@/lib/supabase/admin'
import { requireAdminContext } from '../lib'
import { sendAnnouncement, sendOperationalReminder } from './actions'

const GLOBAL_ANNOUNCERS = new Set(['super_admin','operations_admin','content_admin'])
const GLOBAL_REMINDERS = new Set(['super_admin','operations_admin'])

export default async function CommunicationsPage({
  searchParams,
}: {
  searchParams: Promise<{ success?: string; error?: string }>
}) {
  const params = await searchParams
  const context = await requireAdminContext()
  const role = context.globalRole || 'school_admin'
  const isSchoolAdmin = !context.isGlobalAdmin
  const canAnnounce = isSchoolAdmin || GLOBAL_ANNOUNCERS.has(role)
  const canStudentVerificationReminder = isSchoolAdmin || ['super_admin','operations_admin','verification_admin'].includes(role)
  const canOnboardingReminders = isSchoolAdmin || GLOBAL_REMINDERS.has(role)

  if (!canAnnounce && !canStudentVerificationReminder && !canOnboardingReminders) notFound()

  const admin = createAdminClient()
  const assignedIds = context.schoolAssignments.filter((item) => item.is_active).map((item) => item.institution_id)

  const schoolQuery = admin.from('institutions').select('id,name,is_active').eq('is_active',true).order('name')
  const { data: allSchools } = await schoolQuery
  const schools = context.isGlobalAdmin
    ? (allSchools || [])
    : (allSchools || []).filter((school) => assignedIds.includes(school.id))

  const { data: studentProfiles } = await admin.from('profiles')
    .select('id,institution_id,onboarding_completed_at,student_verification_status')
    .eq('account_type','student')

  const visibleStudents = (studentProfiles || []).filter((row) => context.isGlobalAdmin || (row.institution_id && assignedIds.includes(row.institution_id)))
  const incompleteStudents = visibleStudents.filter((row) => !row.onboarding_completed_at)

  const verificationCandidateIds = visibleStudents
    .filter((row) => row.onboarding_completed_at && row.student_verification_status !== 'verified')
    .map((row) => row.id)
  const { data: verificationRows } = verificationCandidateIds.length
    ? await admin.from('student_verifications').select('student_id,status').in('student_id', verificationCandidateIds)
    : { data: [] as any[] }
  const verificationMap = new Map((verificationRows || []).map((row) => [row.student_id,row.status]))
  const incompleteVerification = verificationCandidateIds.filter((id) => {
    const status = verificationMap.get(id)
    return !status || status === 'rejected'
  })

  let incompleteVendors = 0
  if (GLOBAL_REMINDERS.has(role)) {
    const [{ data: vendorAccounts }, { data: vendorProfiles }] = await Promise.all([
      admin.from('profiles').select('id').eq('account_type','vendor'),
      admin.from('vendor_profiles').select('id'),
    ])
    const profileIds = new Set((vendorProfiles || []).map((row) => row.id))
    incompleteVendors = (vendorAccounts || []).filter((row) => !profileIds.has(row.id)).length
  }

  let recentQuery = admin.from('admin_communications')
    .select('id,created_by,kind,audience_type,institution_id,subject,recipient_count,dashboard_sent_count,email_sent_count,email_failed_count,created_at')
    .order('created_at',{ascending:false})
    .limit(20)

  if (!context.isGlobalAdmin && assignedIds.length === 1) recentQuery = recentQuery.eq('institution_id',assignedIds[0])
  else if (!context.isGlobalAdmin && assignedIds.length > 1) recentQuery = recentQuery.in('institution_id',assignedIds)
  else if (context.globalRole === 'verification_admin') recentQuery = recentQuery.eq('created_by',context.userId)

  const { data: recent } = await recentQuery
  const emailConfigured = Boolean(process.env.RESEND_API_KEY)

  return (
    <>
      <header className="admin-topbar">
        <div>
          <span className="admin-pill"><Mail size={15}/> Communications</span>
          <h1>Alerts & dashboard messages</h1>
          <p>Send useful Kampivo reminders and announcements through official email and in-app notifications without exposing user email addresses unnecessarily.</p>
        </div>
      </header>

      {params.success ? <div className="admin-success">{params.success}</div> : null}
      {params.error ? <div className="admin-error">{params.error}</div> : null}

      <section className="admin-grid">
        <article className="admin-stat"><span>Official sender</span><strong style={{fontSize:16}}>notifications@campuslink.name.ng</strong><small>Kampivo Alerts transactional sender.</small></article>
        <article className="admin-stat"><span>Email channel</span><strong>{emailConfigured ? 'Ready' : 'Needs key'}</strong><small>{emailConfigured ? 'RESEND_API_KEY is available to the server.' : 'Dashboard notifications work, but Vercel still needs RESEND_API_KEY for email delivery.'}</small></article>
        <article className="admin-stat"><span>Dashboard delivery</span><strong>Active</strong><small>Students and Vendors see recent Admin messages when they sign in.</small></article>
        <article className="admin-stat"><span>Anti-spam</span><strong>24h</strong><small>The same operational reminder cannot be sent to the same account more than once per day.</small></article>
      </section>

      <section className="admin-section">
        <div className="admin-section-head"><div><h2>Operational reminders</h2><p>These messages are generated from current account state, so Admins do not have to write reminder copy manually.</p></div><BellRing size={20}/></div>
        <div className="admin-section-body queue-grid">
          {canOnboardingReminders ? <article className="queue-card">
            <h3>Incomplete Student setup</h3>
            <p><strong>{incompleteStudents.length}</strong> Student account{incompleteStudents.length === 1 ? '' : 's'} in your scope have not completed onboarding.</p>
            <form action={sendOperationalReminder}><input type="hidden" name="reminder_type" value="student_onboarding_incomplete"/><input type="hidden" name="return_to" value="/control-center/communications"/><button className="admin-action primary" disabled={!incompleteStudents.length}>Remind all eligible</button></form>
          </article> : null}

          {canStudentVerificationReminder ? <article className="queue-card">
            <h3>Student verification incomplete</h3>
            <p><strong>{incompleteVerification.length}</strong> onboarded Student{incompleteVerification.length === 1 ? '' : 's'} still need to submit or resubmit verification. Pending Admin reviews are not reminded.</p>
            <form action={sendOperationalReminder}><input type="hidden" name="reminder_type" value="student_verification_incomplete"/><input type="hidden" name="return_to" value="/control-center/communications"/><button className="admin-action primary" disabled={!incompleteVerification.length}>Remind all eligible</button></form>
          </article> : null}

          {GLOBAL_REMINDERS.has(role) ? <article className="queue-card">
            <h3>Incomplete Vendor registration</h3>
            <p><strong>{incompleteVendors}</strong> Vendor account{incompleteVendors === 1 ? '' : 's'} exist without a completed business profile.</p>
            <form action={sendOperationalReminder}><input type="hidden" name="reminder_type" value="vendor_onboarding_incomplete"/><input type="hidden" name="return_to" value="/control-center/communications"/><button className="admin-action primary" disabled={!incompleteVendors}>Remind all eligible</button></form>
          </article> : null}
        </div>
        <div className="admin-section-body"><div className="admin-note"><ShieldCheck size={16}/> Reminders create the dashboard notification first, then attempt email delivery. Users who are already waiting on Admin review are not repeatedly told to “complete” something they have already submitted.</div></div>
      </section>

      {canAnnounce ? <section className="admin-section">
        <div className="admin-section-head"><div><h2>Send an announcement</h2><p>Use this for genuine Kampivo updates. Every send is written to the communication log and Admin audit trail.</p></div><Send size={20}/></div>
        <div className="admin-section-body">
          <form action={sendAnnouncement} className="admin-form">
            <div className="admin-form-grid">
              <div className="admin-field"><label>Audience</label><select name="audience" defaultValue={isSchoolAdmin ? 'school_all' : 'all_students'}>
                {!isSchoolAdmin ? <><option value="all_students">All Students</option><option value="all_vendors">All Vendors</option><option value="all_users">All Students + Vendors</option></> : null}
                <option value="school_students">Students in one school</option>
                <option value="school_vendors">Vendors in one school</option>
                <option value="school_all">Students + Vendors in one school</option>
              </select></div>
              <div className="admin-field"><label>School</label><select name="institution_id" defaultValue={isSchoolAdmin && schools.length === 1 ? schools[0].id : ''}><option value="">Choose for school audience</option>{schools.map((school)=><option key={school.id} value={school.id}>{school.name}</option>)}</select></div>
              <div className="admin-field"><label>Email subject</label><input name="subject" maxLength={160} required placeholder="Kampivo update"/></div>
              <div className="admin-field"><label>Dashboard title</label><input name="title" maxLength={160} required placeholder="What users should notice"/></div>
            </div>
            <div className="admin-field"><label>Message</label><textarea name="body" rows={5} maxLength={1800} required placeholder="Write a clear, useful update. Avoid sensitive personal information."/></div>
            <div className="admin-form-grid">
              <div className="admin-field"><label>Button label <span style={{fontWeight:500}}>(optional)</span></label><input name="cta_label" maxLength={60} placeholder="Open Kampivo"/></div>
              <div className="admin-field"><label>Button link <span style={{fontWeight:500}}>(optional)</span></label><input name="cta_url" maxLength={300} placeholder="/student or https://..."/></div>
            </div>
            <div className="admin-note"><Mail size={16}/> Delivery is email + dashboard notification. School Admins can only target their assigned school. Platform-wide sends are limited to Super Admin, Operations Admin and Content Admin.</div>
            <button className="admin-action primary" type="submit">Send email + dashboard update</button>
          </form>
        </div>
      </section> : null}

      <section className="admin-section">
        <div className="admin-section-head"><div><h2>Recent communications</h2><p>Delivery counts make failed email attempts visible instead of silently pretending everything was sent.</p></div><UsersRound size={20}/></div>
        <div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>Message</th><th>Audience</th><th>Recipients</th><th>Dashboard</th><th>Email</th><th>When</th></tr></thead><tbody>
          {(recent || []).map((item)=><tr key={item.id}><td><span className="admin-name">{item.subject}</span><span className="admin-sub">{item.kind}</span></td><td>{item.audience_type.replaceAll('_',' ')}</td><td>{item.recipient_count}</td><td>{item.dashboard_sent_count}</td><td><span className={item.email_failed_count ? 'status-badge status-reviewing' : 'status-badge status-approved'}>{item.email_sent_count} sent{item.email_failed_count ? ` · ${item.email_failed_count} issue${item.email_failed_count === 1 ? '' : 's'}` : ''}</span></td><td>{new Date(item.created_at).toLocaleString()}</td></tr>)}
          {!recent?.length ? <tr><td colSpan={6}><div className="empty-admin">No Admin communications have been sent in your visible scope yet.</div></td></tr> : null}
        </tbody></table></div>
      </section>

      <div className="admin-success"><Building2 size={16}/> School-scoped messages stay inside the assigned institution boundary. Global audiences remain a platform-level privilege.</div>
    </>
  )
}
