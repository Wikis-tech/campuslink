import Link from 'next/link'
import { FileCheck2, GraduationCap, Search, ShieldCheck } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { requireAdminContext } from '../lib'
import { reviewStudent } from '../actions'
import { sendOperationalReminder } from '../communications/actions'

export default async function StudentsAdminPage({
  searchParams,
}: {
  searchParams: Promise<{ success?: string; error?: string; status?: string; school?: string; q?: string }>
}) {
  const params = await searchParams
  await requireAdminContext()
  const supabase = await createClient()

  const [{ data: profiles }, { data: verifications }] = await Promise.all([
    supabase.from('profiles')
      .select('id,first_name,last_name,phone,institution_id,course_of_study,study_level,student_verification_status,onboarding_completed_at,created_at')
      .eq('account_type','student')
      .order('created_at',{ascending:false})
      .limit(500),
    supabase.from('student_verifications')
      .select('student_id,status,verification_method,matric_number,school_email,submitted_at,review_note')
      .order('submitted_at',{ascending:false})
      .limit(500),
  ])

  const verificationIds = (verifications || []).map((row) => row.student_id)
  const { data: documents } = verificationIds.length
    ? await supabase.from('student_documents').select('id,student_id,document_type,storage_path,status,created_at').in('student_id',verificationIds).order('created_at',{ascending:false})
    : { data: [] as any[] }

  const institutionIds = Array.from(new Set((profiles || []).map((profile) => profile.institution_id).filter(Boolean))) as string[]
  const { data: institutions } = institutionIds.length
    ? await supabase.from('institutions').select('id,name').in('id',institutionIds).order('name')
    : { data: [] as any[] }

  const verificationMap = new Map((verifications || []).map((verification) => [verification.student_id,verification]))
  const schoolMap = new Map((institutions || []).map((school) => [school.id,school.name]))
  const docMap = new Map<string,any>()
  for (const doc of documents || []) if (!docMap.has(doc.student_id)) docMap.set(doc.student_id,doc)

  const signedUrls = new Map<string,string>()
  for (const doc of documents || []) {
    if (!doc.storage_path || signedUrls.has(doc.student_id)) continue
    const { data } = await supabase.storage.from('verification-documents').createSignedUrl(doc.storage_path,300)
    if (data?.signedUrl) signedUrls.set(doc.student_id,data.signedUrl)
  }

  const studentState = (profile: any) => {
    if (!profile.onboarding_completed_at || !profile.institution_id) return 'incomplete'
    const verification = verificationMap.get(profile.id)
    if (verification?.status === 'verified' || profile.student_verification_status === 'verified') return 'verified'
    if (verification?.status === 'rejected') return 'rejected'
    if (verification && ['pending','under_review'].includes(verification.status)) return 'awaiting'
    return 'needs_verification'
  }

  const allProfiles = profiles || []
  const registeredStudents = allProfiles.length
  const onboardedStudents = allProfiles.filter((profile) => Boolean(profile.onboarding_completed_at)).length
  const verifiedStudents = allProfiles.filter((profile) => studentState(profile) === 'verified').length
  const incompleteStudents = allProfiles.filter((profile) => studentState(profile) === 'incomplete')
  const awaitingStudents = allProfiles.filter((profile) => studentState(profile) === 'awaiting')
  const rejectedStudents = allProfiles.filter((profile) => studentState(profile) === 'rejected')

  const q = String(params.q || '').trim().toLowerCase()
  const selectedStatus = params.status || 'all'
  const selectedSchool = params.school || ''

  const filteredProfiles = allProfiles.filter((profile) => {
    const state = studentState(profile)
    if (selectedStatus !== 'all' && state !== selectedStatus) return false
    if (selectedSchool && profile.institution_id !== selectedSchool) return false
    if (q) {
      const haystack = [
        profile.first_name,
        profile.last_name,
        profile.phone,
        profile.course_of_study,
        profile.study_level,
        profile.institution_id ? schoolMap.get(profile.institution_id) : '',
      ].filter(Boolean).join(' ').toLowerCase()
      if (!haystack.includes(q)) return false
    }
    return true
  })

  const pending = (verifications || []).filter((row) => ['pending','under_review'].includes(row.status))
  const profileMap = new Map(allProfiles.map((profile) => [profile.id,profile]))

  const filterHref = (status: string) => {
    const query = new URLSearchParams()
    if (status !== 'all') query.set('status',status)
    if (selectedSchool) query.set('school',selectedSchool)
    if (params.q) query.set('q',params.q)
    const suffix = query.toString()
    return '/control-center/students' + (suffix ? '?' + suffix : '')
  }

  return <>
    <header className="admin-topbar"><div><span className="admin-pill"><GraduationCap size={15}/> Student management</span><h1>Students</h1><p>Understand registrations, onboarding, verification and school membership without treating every Student as a verification ticket.</p></div></header>
    {params.success ? <div className="admin-success">{params.success}</div> : null}
    {params.error ? <div className="admin-error">{params.error}</div> : null}

    <section className="admin-grid">
      <article className="admin-stat"><span>Registered Students</span><strong>{registeredStudents}</strong><small>Student accounts visible in your Admin scope.</small></article>
      <article className="admin-stat"><span>Onboarded</span><strong>{onboardedStudents}</strong><small>Completed Kampivo onboarding.</small></article>
      <article className="admin-stat"><span>Verified</span><strong>{verifiedStudents}</strong><small>Completed Student verification.</small></article>
      <article className="admin-stat"><span>Needs action</span><strong>{incompleteStudents.length + awaitingStudents.length + rejectedStudents.length}</strong><small>{incompleteStudents.length} incomplete · {awaitingStudents.length} awaiting · {rejectedStudents.length} rejected.</small></article>
    </section>

    <section className="admin-section">
      <div className="admin-section-head"><div><h2>Student directory</h2><p>Use one set of filters for account state, school and quick search.</p></div><Search size={19}/></div>
      <div className="admin-section-body">
        <div className="admin-filter-bar">
          <Link className={selectedStatus === 'all' ? 'active' : ''} href={filterHref('all')}>All ({registeredStudents})</Link>
          <Link className={selectedStatus === 'incomplete' ? 'active' : ''} href={filterHref('incomplete')}>Incomplete setup ({incompleteStudents.length})</Link>
          <Link className={selectedStatus === 'awaiting' ? 'active' : ''} href={filterHref('awaiting')}>Awaiting verification ({awaitingStudents.length})</Link>
          <Link className={selectedStatus === 'verified' ? 'active' : ''} href={filterHref('verified')}>Verified ({verifiedStudents})</Link>
          <Link className={selectedStatus === 'rejected' ? 'active' : ''} href={filterHref('rejected')}>Rejected ({rejectedStudents.length})</Link>
        </div>
        <form method="get" className="admin-filter-form">
          {selectedStatus !== 'all' ? <input type="hidden" name="status" value={selectedStatus}/> : null}
          <div className="admin-field"><label>School</label><select name="school" defaultValue={selectedSchool}><option value="">All visible schools</option>{(institutions || []).map((school) => <option key={school.id} value={school.id}>{school.name}</option>)}</select></div>
          <div className="admin-field"><label>Search</label><input name="q" defaultValue={params.q || ''} placeholder="Name, phone, course or level"/></div>
          <button className="admin-action primary" type="submit">Apply filters</button>
          {(selectedSchool || params.q) ? <Link className="admin-action" href={filterHref(selectedStatus)}>Clear search</Link> : null}
        </form>
      </div>
      <div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>Student</th><th>School</th><th>Account state</th><th>Verification</th><th>Action</th></tr></thead><tbody>
        {filteredProfiles.map((profile) => {
          const verification = verificationMap.get(profile.id)
          const state = studentState(profile)
          const canRemind = state === 'incomplete' || state === 'rejected' || state === 'needs_verification'
          return <tr key={profile.id}>
            <td><span className="admin-name">{[profile.first_name,profile.last_name].filter(Boolean).join(' ') || 'Student'}</span><span className="admin-sub">{profile.course_of_study || 'Course not set'} · {profile.study_level || 'Level not set'}</span><span className="admin-sub">{profile.phone || 'No phone shown'}</span></td>
            <td><span className="admin-name">{profile.institution_id ? schoolMap.get(profile.institution_id) || 'School not resolved' : 'School not selected'}</span></td>
            <td><span className={'status-badge status-' + (state === 'verified' ? 'approved' : state === 'rejected' ? 'rejected' : 'reviewing')}>{state.replaceAll('_',' ')}</span><span className="admin-sub">{profile.onboarding_completed_at ? 'Onboarding complete' : 'Onboarding incomplete'}</span></td>
            <td>{verification ? <><span className={'status-badge status-' + verification.status}>{verification.status.replaceAll('_',' ')}</span><span className="admin-sub">{verification.verification_method?.replaceAll('_',' ')}</span></> : <span className="admin-sub">No verification submission yet</span>}</td>
            <td><div className="admin-actions"><Link className="admin-action" href={'/control-center/students/' + profile.id}>Open Student 360</Link>{canRemind ? <form action={sendOperationalReminder}><input type="hidden" name="reminder_type" value={state === 'incomplete' ? 'student_onboarding_incomplete' : 'student_verification_incomplete'}/><input type="hidden" name="target_user_id" value={profile.id}/><input type="hidden" name="return_to" value="/control-center/students"/><button className="admin-action primary" type="submit">Send reminder</button></form> : null}</div></td>
          </tr>
        })}
        {!filteredProfiles.length ? <tr><td colSpan={5}><div className="empty-admin">No Students match the current filters.</div></td></tr> : null}
      </tbody></table></div>
    </section>

    <section className="admin-section">
      <div className="admin-section-head"><div><h2>Awaiting review</h2><p>{pending.length} submitted verification{pending.length === 1 ? '' : 's'} waiting. Only submitted evidence appears here.</p></div><ShieldCheck size={20}/></div>
      <div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>Student</th><th>School</th><th>Evidence</th><th>Status</th><th>Decision</th></tr></thead><tbody>
        {pending.map((row) => {
          const profile = profileMap.get(row.student_id)
          const school = profile?.institution_id ? schoolMap.get(profile.institution_id) : null
          const doc = docMap.get(row.student_id)
          return <tr key={row.student_id}>
            <td><span className="admin-name">{[profile?.first_name,profile?.last_name].filter(Boolean).join(' ') || 'Student'}</span><span className="admin-sub">{profile?.course_of_study || 'Course not set'} · {profile?.study_level || 'Level not set'}</span></td>
            <td><span className="admin-name">{school || 'School not resolved'}</span><span className="admin-sub">{row.matric_number || 'No matric number provided'}</span></td>
            <td><span className="status-badge status-reviewing">{row.verification_method?.replaceAll('_',' ')}</span>{doc ? <span className="admin-sub"><FileCheck2 size={13}/> {doc.document_type.replaceAll('_',' ')}</span> : null}{signedUrls.get(row.student_id) ? <a className="admin-link admin-sub" href={signedUrls.get(row.student_id)} target="_blank" rel="noreferrer">Open private evidence</a> : null}</td>
            <td><span className={'status-badge status-' + row.status}>{row.status.replaceAll('_',' ')}</span><span className="admin-sub">{row.submitted_at ? new Date(row.submitted_at).toLocaleString() : 'Not submitted'}</span></td>
            <td><form action={reviewStudent} className="admin-form" style={{minWidth:230}}><input type="hidden" name="student_id" value={row.student_id}/><div className="admin-field"><input name="note" placeholder="Optional review note" maxLength={800}/></div><div className="admin-actions"><button className="admin-action success" name="decision" value="approve">Approve</button><button className="admin-action danger" name="decision" value="reject">Reject</button></div></form></td>
          </tr>
        })}
        {!pending.length ? <tr><td colSpan={5}><div className="empty-admin">No Student verification is waiting for review.</div></td></tr> : null}
      </tbody></table></div>
    </section>
  </>
}
