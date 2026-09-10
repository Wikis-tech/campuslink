import Link from 'next/link'
import { notFound } from 'next/navigation'
import { AlertTriangle, Bookmark, GraduationCap, MessageCircle, MessageSquareText, ShieldCheck } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { requireAdminContext } from '../../lib'

export default async function AdminStudentDetailPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  await requireAdminContext()
  const supabase = await createClient()

  const { data: student } = await supabase.from('profiles').select('id,first_name,last_name,phone,institution_id,course_of_study,study_level,student_verification_status,onboarding_completed_at,created_at').eq('id', id).eq('account_type', 'student').maybeSingle()
  if (!student) notFound()

  const [{ data: institution }, { data: verification }, { data: reviews }, { data: reports }, { data: contacts }, { data: saved }] = await Promise.all([
    student.institution_id ? supabase.from('institutions').select('id,name,city,state').eq('id', student.institution_id).maybeSingle() : Promise.resolve({ data: null }),
    supabase.from('student_verifications').select('status,verification_method,matric_number,school_email,submitted_at,review_note').eq('student_id', id).maybeSingle(),
    supabase.from('reviews').select('id,vendor_id,rating,comment,status,created_at').eq('student_id', id).order('created_at', { ascending: false }).limit(30),
    supabase.from('complaints').select('id,vendor_id,title,status,created_at').eq('reporter_id', id).order('created_at', { ascending: false }).limit(30),
    supabase.from('contact_events').select('id,vendor_id,channel,created_at').eq('student_id', id).order('created_at', { ascending: false }).limit(100),
    supabase.from('saved_vendors').select('vendor_id,created_at').eq('student_id', id).order('created_at', { ascending: false }).limit(100),
  ])

  const vendorIds = Array.from(new Set([...(reviews || []).map((row) => row.vendor_id), ...(reports || []).map((row) => row.vendor_id), ...(contacts || []).map((row) => row.vendor_id), ...(saved || []).map((row) => row.vendor_id)].filter(Boolean))) as string[]
  const { data: vendors } = vendorIds.length ? await supabase.from('vendor_profiles').select('id,business_name').in('id', vendorIds) : { data: [] as any[] }
  const vendorMap = new Map((vendors || []).map((vendor) => [vendor.id, vendor.business_name]))
  const fullName = [student.first_name, student.last_name].filter(Boolean).join(' ') || 'Student'

  return <>
    <header className="admin-topbar"><div><span className="admin-pill"><GraduationCap size={15}/> Student 360</span><h1>{fullName}</h1><p>Verification, marketplace interactions and safety activity connected to one Student identity.</p></div><div className="admin-actions"><Link className="admin-action" href="/admin-v2/students">Back to students</Link><Link className="admin-action primary" href="/admin-v2/reports">Open reports</Link></div></header>

    <section className="admin-grid">
      <article className="admin-stat"><span>Verification</span><strong>{student.student_verification_status?.replaceAll('_',' ') || 'pending'}</strong><small>{verification?.verification_method?.replaceAll('_',' ') || 'No verification method yet'}.</small></article>
      <article className="admin-stat"><span>Contacts started</span><strong>{contacts?.length || 0}</strong><small>Latest marketplace contact events in view.</small></article>
      <article className="admin-stat"><span>Saved vendors</span><strong>{saved?.length || 0}</strong><small>Current shortlist records visible to Admin scope.</small></article>
      <article className="admin-stat"><span>Reviews / reports</span><strong>{reviews?.length || 0} / {reports?.length || 0}</strong><small>Marketplace reputation and safety contributions.</small></article>
    </section>

    <section className="admin-section"><div className="admin-section-head"><div><h2>Identity & campus</h2><p>Admin evidence access remains controlled by existing role and RLS policies.</p></div></div><div className="admin-table-wrap"><table className="admin-table"><tbody>
      <tr><th>Campus</th><td><span className="admin-name">{institution?.name || 'School not resolved'}</span><span className="admin-sub">{[institution?.city,institution?.state].filter(Boolean).join(', ')}</span></td></tr>
      <tr><th>Study</th><td><span className="admin-name">{student.course_of_study || 'Course not set'}</span><span className="admin-sub">{student.study_level || 'Level not set'}</span></td></tr>
      <tr><th>Verification</th><td><span className={`status-badge status-${verification?.status || 'pending'}`}>{verification?.status?.replaceAll('_',' ') || 'pending'}</span><span className="admin-sub">{verification?.matric_number || verification?.school_email || 'No identifier displayed'}</span>{verification?.review_note ? <span className="admin-sub">Note: {verification.review_note}</span> : null}</td></tr>
      <tr><th>Account</th><td><span className="admin-sub">{student.onboarding_completed_at ? 'Onboarding complete' : 'Onboarding incomplete'} · Joined {new Date(student.created_at).toLocaleDateString()}</span></td></tr>
    </tbody></table></div></section>

    <section className="admin-section"><div className="admin-section-head"><div><h2>Marketplace activity</h2><p>This view connects Student behavior to Vendor records for moderation without exposing it to Vendors.</p></div></div><div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>Type</th><th>Vendor</th><th>Details</th><th>When</th></tr></thead><tbody>
      {(reports || []).slice(0,10).map((report) => <tr key={`report-${report.id}`}><td><span className="admin-name"><AlertTriangle size={13}/> Report</span></td><td>{report.vendor_id ? <Link className="admin-link" href={`/admin-v2/vendors/${report.vendor_id}`}>{vendorMap.get(report.vendor_id) || 'Vendor'}</Link> : 'General report'}</td><td><span className="admin-name">{report.title}</span><span className={`status-badge status-${report.status}`}>{report.status}</span></td><td>{new Date(report.created_at).toLocaleString()}</td></tr>)}
      {(reviews || []).slice(0,10).map((review) => <tr key={`review-${review.id}`}><td><span className="admin-name"><MessageSquareText size={13}/> Review</span></td><td><Link className="admin-link" href={`/admin-v2/vendors/${review.vendor_id}`}>{vendorMap.get(review.vendor_id) || 'Vendor'}</Link></td><td><span className="admin-name">{review.rating}/5</span><span className="admin-sub">{review.comment || 'Rating only'} · {review.status}</span></td><td>{new Date(review.created_at).toLocaleString()}</td></tr>)}
      {(contacts || []).slice(0,10).map((contact) => <tr key={`contact-${contact.id}`}><td><span className="admin-name"><MessageCircle size={13}/> Contact</span></td><td><Link className="admin-link" href={`/admin-v2/vendors/${contact.vendor_id}`}>{vendorMap.get(contact.vendor_id) || 'Vendor'}</Link></td><td>{contact.channel || 'contact'} intent</td><td>{new Date(contact.created_at).toLocaleString()}</td></tr>)}
      {!reports?.length && !reviews?.length && !contacts?.length ? <tr><td colSpan={4}><div className="empty-admin">No recent marketplace activity is visible for this Student.</div></td></tr> : null}
    </tbody></table></div></section>

    <div className="admin-success"><ShieldCheck size={16}/> Student browsing/contact history shown here is an Admin moderation context. Vendors continue to receive aggregate analytics rather than Student identities.</div>
  </>
}
