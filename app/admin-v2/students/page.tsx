import { FileCheck2, GraduationCap, ShieldCheck } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { requireAdminContext } from '../lib'
import { reviewStudent } from '../actions'

export default async function StudentsAdminPage({ searchParams }: { searchParams: Promise<{ success?: string; error?: string }> }) {
  const params = await searchParams
  await requireAdminContext()
  const supabase = await createClient()

  const { data: verifications } = await supabase
    .from('student_verifications')
    .select('student_id,status,verification_method,matric_number,school_email,submitted_at,review_note')
    .order('submitted_at', { ascending: false })
    .limit(100)

  const ids = (verifications || []).map((row) => row.student_id)
  const [{ data: profiles }, { data: documents }] = ids.length ? await Promise.all([
    supabase.from('profiles').select('id,first_name,last_name,phone,institution_id,course_of_study,study_level,student_verification_status').in('id', ids),
    supabase.from('student_documents').select('id,student_id,document_type,storage_path,status,created_at').in('student_id', ids).order('created_at',{ascending:false}),
  ]) : [{ data: [] as any[] }, { data: [] as any[] }]

  const institutionIds = Array.from(new Set((profiles || []).map((p) => p.institution_id).filter(Boolean))) as string[]
  const { data: institutions } = institutionIds.length
    ? await supabase.from('institutions').select('id,name').in('id', institutionIds)
    : { data: [] as any[] }

  const profileMap = new Map((profiles || []).map((profile) => [profile.id, profile]))
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

  const renderRows = (rows: typeof verifications) => (rows || []).map((row) => {
    const profile = profileMap.get(row.student_id)
    const school = profile?.institution_id ? schoolMap.get(profile.institution_id) : null
    const doc = docMap.get(row.student_id)
    return <tr key={row.student_id}>
      <td><span className="admin-name">{[profile?.first_name,profile?.last_name].filter(Boolean).join(' ') || 'Student'}</span><span className="admin-sub">{profile?.course_of_study || 'Course not set'} · {profile?.study_level || 'Level not set'}</span><span className="admin-sub">{profile?.phone || row.school_email || 'No contact shown'}</span></td>
      <td><span className="admin-name">{school || 'School not resolved'}</span><span className="admin-sub">{row.matric_number || 'No matric number provided'}</span></td>
      <td><span className="status-badge status-reviewing">{row.verification_method?.replaceAll('_',' ')}</span>{doc ? <span className="admin-sub"><FileCheck2 size={13}/> {doc.document_type.replaceAll('_',' ')}</span> : null}{signedUrls.get(row.student_id) ? <a className="admin-link admin-sub" href={signedUrls.get(row.student_id)} target="_blank" rel="noreferrer">Open private evidence</a> : null}</td>
      <td><span className={`status-badge status-${row.status}`}>{row.status.replaceAll('_',' ')}</span><span className="admin-sub">{row.submitted_at ? new Date(row.submitted_at).toLocaleString() : 'Not submitted'}</span>{row.review_note ? <span className="admin-sub">Note: {row.review_note}</span> : null}</td>
      <td>{['pending','under_review','rejected'].includes(row.status) ? <form action={reviewStudent} className="admin-form" style={{minWidth:230}}><input type="hidden" name="student_id" value={row.student_id}/><div className="admin-field"><input name="note" placeholder="Optional review note" maxLength={800}/></div><div className="admin-actions"><button className="admin-action success" name="decision" value="approve">Approve</button><button className="admin-action danger" name="decision" value="reject">Reject</button></div></form> : <span className="admin-sub">Review complete</span>}</td>
    </tr>
  })

  return <>
    <header className="admin-topbar"><div><span className="admin-pill"><GraduationCap size={15}/> Student trust</span><h1>Student verification</h1><p>Students can use their accounts before verification, but only verified students receive trust-sensitive privileges.</p></div></header>
    {params.success ? <div className="admin-success">{params.success}</div> : null}{params.error ? <div className="admin-error">{params.error}</div> : null}
    <section className="admin-grid">
      <article className="admin-stat"><span>Awaiting review</span><strong>{pending.length}</strong><small>Pending or under-review submissions in your scope.</small></article>
      <article className="admin-stat"><span>Reviewed in view</span><strong>{reviewed.length}</strong><small>Verified/rejected records in the current queue.</small></article>
      <article className="admin-stat"><span>Total in view</span><strong>{verifications?.length || 0}</strong><small>Latest 100 verification records.</small></article>
      <article className="admin-stat"><span>Security rule</span><strong><ShieldCheck size={28}/></strong><small>School sub-admins only see assigned institutions.</small></article>
    </section>
    <section className="admin-section"><div className="admin-section-head"><div><h2>Awaiting review</h2><p>Check school, course and private evidence before approving.</p></div></div><div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>Student</th><th>School</th><th>Evidence</th><th>Status</th><th>Decision</th></tr></thead><tbody>{renderRows(pending)}{!pending.length ? <tr><td colSpan={5}><div className="empty-admin">No student verification is waiting for you.</div></td></tr> : null}</tbody></table></div></section>
    <section className="admin-section"><div className="admin-section-head"><div><h2>Recent decisions</h2><p>Previous verification outcomes in your scope.</p></div></div><div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>Student</th><th>School</th><th>Evidence</th><th>Status</th><th>Decision</th></tr></thead><tbody>{renderRows(reviewed.slice(0,30))}{!reviewed.length ? <tr><td colSpan={5}><div className="empty-admin">No completed reviews yet.</div></td></tr> : null}</tbody></table></div></section>
  </>
}
