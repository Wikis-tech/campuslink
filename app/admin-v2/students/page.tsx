import Link from 'next/link'
import { FileCheck2, GraduationCap, ShieldCheck } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { requireAdminContext } from '../lib'
import { reviewStudent } from '../actions'
import { sendOperationalReminder } from '../communications/actions'

export default async function StudentsAdminPage({ searchParams }: { searchParams: Promise<{ success?: string; error?: string }> }) {
  const params = await searchParams
  await requireAdminContext()
  const supabase = await createClient()

  const [{ data: profiles }, { data: verifications }] = await Promise.all([
    supabase.from('profiles')
      .select('id,first_name,last_name,phone,institution_id,course_of_study,study_level,student_verification_status,onboarding_completed_at,created_at')
      .eq('account_type', 'student')
      .order('created_at', { ascending: false })
      .limit(200),
    supabase.from('student_verifications')
      .select('student_id,status,verification_method,matric_number,school_email,submitted_at,review_note')
      .order('submitted_at', { ascending: false })
      .limit(200),
  ])

  const verificationIds = (verifications || []).map((row) => row.student_id)
  const { data: documents } = verificationIds.length
    ? await supabase.from('student_documents').select('id,student_id,document_type,storage_path,status,created_at').in('student_id', verificationIds).order('created_at',{ascending:false})
    : { data: [] as any[] }

  const institutionIds = Array.from(new Set((profiles || []).map((p) => p.institution_id).filter(Boolean))) as string[]
  const { data: institutions } = institutionIds.length
    ? await supabase.from('institutions').select('id,name').in('id', institutionIds)
    : { data: [] as any[] }

  const profileMap = new Map((profiles || []).map((profile) => [profile.id, profile]))
  const verificationMap = new Map((verifications || []).map((verification) => [verification.student_id, verification]))
  const schoolMap = new Map((institutions || []).map((school) => [school.id, school.name]))
  const docMap = new Map<string, any>()
  for (const doc of documents || []) if (!docMap.has(doc.student_id)) docMap.set(doc.student_id, doc)

  const signedUrls = new Map<string, string>()
  for (const doc of documents || []) {
    if (!doc.storage_path || signedUrls.has(doc.student_id)) continue
    const { data } = await supabase.storage.from('verification-documents').createSignedUrl(doc.storage_path, 300)
    if (data?.signedUrl) signedUrls.set(doc.student_id, data.signedUrl)
  }

  const pending = (verifications || []).filter((row) => ['pending','under_review'].includes(row.status))
  const reviewed = (verifications || []).filter((row) => !['pending','under_review'].includes(row.status))
  const registeredStudents = profiles?.length || 0
  const onboardedStudents = (profiles || []).filter((profile) => Boolean(profile.onboarding_completed_at)).length
  const verifiedStudents = (profiles || []).filter((profile) => profile.student_verification_status === 'verified').length
  const incompleteStudents = (profiles || []).filter((profile) => !profile.onboarding_completed_at || !profile.institution_id)

  const renderRows = (rows: typeof verifications) => (rows || []).map((row) => {
    const profile = profileMap.get(row.student_id)
    const school = profile?.institution_id ? schoolMap.get(profile.institution_id) : null
    const doc = docMap.get(row.student_id)
    return <tr key={row.student_id}>
      <td><span className="admin-name">{[profile?.first_name,profile?.last_name].filter(Boolean).join(' ') || 'Student'}</span><span className="admin-sub">{profile?.course_of_study || 'Course not set'} · {profile?.study_level || 'Level not set'}</span><span className="admin-sub">{profile?.phone || row.school_email || 'No contact shown'}</span></td>
      <td><span className="admin-name">{school || 'School not resolved'}</span><span className="admin-sub">{row.matric_number || 'No matric number provided'}</span></td>
      <td><span className="status-badge status-reviewing">{row.verification_method?.replaceAll('_',' ')}</span>{doc ? <span className="admin-sub"><FileCheck2 size={13}/> {doc.document_type.replaceAll('_',' ')}</span> : null}{signedUrls.get(row.student_id) ? <a className="admin-link admin-sub" href={signedUrls.get(row.student_id)} target="_blank" rel="noreferrer">Open private evidence</a> : null}</td>
      <td><span className={`status-badge status-${row.status}`}>{row.status.replaceAll('_',' ')}</span><span className="admin-sub">{row.submitted_at ? new Date(row.submitted_at).toLocaleString() : 'Not submitted'}</span>{row.review_note ? <span className="admin-sub">Note: {row.review_note}</span> : null}</td>
      <td>{['pending','under_review','rejected'].includes(row.status) ? <div className="admin-form" style={{minWidth:230}}><form action={reviewStudent} className="admin-form"><input type="hidden" name="student_id" value={row.student_id}/><div className="admin-field"><input name="note" placeholder="Optional review note" maxLength={800}/></div><div className="admin-actions"><button className="admin-action success" name="decision" value="approve">Approve</button><button className="admin-action danger" name="decision" value="reject">Reject</button></div></form>{row.status === 'rejected' ? <form action={sendOperationalReminder}><input type="hidden" name="reminder_type" value="student_verification_incomplete"/><input type="hidden" name="target_user_id" value={row.student_id}/><input type="hidden" name="return_to" value="/control-center/students"/><button className="admin-action">Remind to resubmit</button></form> : null}</div> : <span className="admin-sub">Review complete</span>}</td>
    </tr>
  })

  return <>
    <header className="admin-topbar"><div><span className="admin-pill"><GraduationCap size={15}/> Student trust</span><h1>Student verification</h1><p>Students can use their accounts before verification, but only verified students receive trust-sensitive privileges.</p></div></header>
    {params.success ? <div className="admin-success">{params.success}</div> : null}{params.error ? <div className="admin-error">{params.error}</div> : null}
    <section className="admin-grid">
      <article className="admin-stat"><span>Registered Students</span><strong>{registeredStudents}</strong><small>Student accounts visible in your Admin scope.</small></article>
      <article className="admin-stat"><span>Onboarded</span><strong>{onboardedStudents}</strong><small>Students who completed Campus Link onboarding.</small></article>
      <article className="admin-stat"><span>Verified</span><strong>{verifiedStudents}</strong><small>Students with completed verification.</small></article>
      <article className="admin-stat"><span>Incomplete onboarding</span><strong>{incompleteStudents.length}</strong><small>Registered Students who still need to complete setup.</small></article>
    </section>
    <section className="admin-section"><div className="admin-section-head"><div><h2>Incomplete onboarding</h2><p>Registered Student accounts that have not completed campus setup yet. They are not verification failures.</p></div>{incompleteStudents.length ? <form action={sendOperationalReminder}><input type="hidden" name="reminder_type" value="student_onboarding_incomplete"/><input type="hidden" name="return_to" value="/control-center/students"/><button className="admin-action primary">Remind all</button></form> : <ShieldCheck size={20}/>}</div><div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>Student</th><th>School</th><th>Account state</th><th>Open</th><th>Reminder</th></tr></thead><tbody>{incompleteStudents.map((profile) => { const verification = verificationMap.get(profile.id); return <tr key={profile.id}><td><span className="admin-name">{[profile.first_name,profile.last_name].filter(Boolean).join(' ') || 'Student'}</span><span className="admin-sub">{profile.phone || 'No contact shown'}</span></td><td><span className="admin-name">{profile.institution_id ? schoolMap.get(profile.institution_id) || 'School not resolved' : 'School not selected'}</span></td><td><span className="status-badge status-reviewing">incomplete</span><span className="admin-sub">{verification ? `Verification: ${verification.status.replaceAll('_',' ')}` : 'No verification submission yet'}</span></td><td><Link className="admin-link" href={`/control-center/students/${profile.id}`}>Open Student 360</Link></td><td><form action={sendOperationalReminder}><input type="hidden" name="reminder_type" value="student_onboarding_incomplete"/><input type="hidden" name="target_user_id" value={profile.id}/><input type="hidden" name="return_to" value="/control-center/students"/><button className="admin-action">Send reminder</button></form></td></tr> })}{!incompleteStudents.length ? <tr><td colSpan={5}><div className="empty-admin">No incomplete Student registrations in your scope.</div></td></tr> : null}</tbody></table></div></section>
    <section className="admin-section"><div className="admin-section-head"><div><h2>Awaiting review</h2><p>{pending.length} submission{pending.length === 1 ? '' : 's'} waiting. Check school, course and private evidence before approving.</p></div></div><div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>Student</th><th>School</th><th>Evidence</th><th>Status</th><th>Decision</th></tr></thead><tbody>{renderRows(pending)}{!pending.length ? <tr><td colSpan={5}><div className="empty-admin">No student verification is waiting for you.</div></td></tr> : null}</tbody></table></div></section>
    <section className="admin-section"><div className="admin-section-head"><div><h2>Recent decisions</h2><p>Previous verification outcomes in your scope.</p></div></div><div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>Student</th><th>School</th><th>Evidence</th><th>Status</th><th>Decision</th></tr></thead><tbody>{renderRows(reviewed.slice(0,30))}{!reviewed.length ? <tr><td colSpan={5}><div className="empty-admin">No completed reviews yet.</div></td></tr> : null}</tbody></table></div></section>
  </>
}
