import Link from 'next/link'
import { BadgeCheck, MessageSquareText, ShieldCheck, Star } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { requireAdminContext } from '../lib'
import { moderateReview } from '../actions'

export default async function ReviewsPage({ searchParams }: { searchParams: Promise<{ success?: string; error?: string }> }) {
  const params = await searchParams
  await requireAdminContext()
  const supabase = await createClient()
  const { data: reviews } = await supabase.from('reviews').select('id,student_id,vendor_id,rating,comment,status,contact_verified_at,vendor_response,vendor_response_status,created_at,updated_at').order('created_at',{ascending:false}).limit(100)
  const vendorIds = Array.from(new Set((reviews || []).map((r:any)=>r.vendor_id))) as string[]
  const studentIds = Array.from(new Set((reviews || []).map((r:any)=>r.student_id))) as string[]
  const [{data:vendors},{data:students}] = await Promise.all([
    vendorIds.length ? supabase.from('vendor_profiles').select('id,business_name').in('id',vendorIds) : Promise.resolve({data:[] as any[]}),
    studentIds.length ? supabase.from('profiles').select('id,first_name,last_name,student_verification_status').in('id',studentIds) : Promise.resolve({data:[] as any[]}),
  ])
  const vendorMap = new Map((vendors || []).map((v:any)=>[v.id,v.business_name]))
  const studentMap = new Map((students || []).map((s:any)=>[s.id,{name:[s.first_name,s.last_name].filter(Boolean).join(' ') || 'Student',verified:s.student_verification_status==='verified'}]))
  const verifiedContactCount=(reviews || []).filter((r:any)=>r.contact_verified_at).length

  return <>
    <header className="admin-topbar"><div><span className="admin-pill"><MessageSquareText size={15}/> Reputation moderation</span><h1>Reviews</h1><p>Student feedback stays independent. Verified-contact labels are evidence that Campus Link recorded a contact event, not proof of a completed purchase.</p></div><div className="admin-top-actions"><Link className="admin-action" href="/control-center/safety"><ShieldCheck size={14}/> Safety investigations</Link></div></header>
    {params.success ? <div className="admin-success">{params.success}</div> : null}{params.error ? <div className="admin-error">{params.error}</div> : null}
    <section className="admin-grid"><article className="admin-stat"><span>Reviews in view</span><strong>{reviews?.length || 0}</strong><small>Latest 100 visible to your role.</small></article><article className="admin-stat"><span>Verified-contact</span><strong>{verifiedContactCount}</strong><small>Backed by a Campus Link contact event.</small></article><article className="admin-stat"><span>Published</span><strong>{(reviews || []).filter((r:any)=>r.status==='published').length}</strong><small>Visible on Vendor storefronts.</small></article><article className="admin-stat"><span>Rating integrity</span><strong><Star size={28}/></strong><small>Moderation never gives Vendors permission to rewrite Student feedback.</small></article></section>
    <section className="admin-section"><div className="admin-section-head"><div><h2>Review moderation</h2><p>Open either side of the interaction when you need more context.</p></div></div><div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>Review</th><th>Vendor</th><th>Student</th><th>Trust evidence</th><th>Status</th><th>Moderate</th></tr></thead><tbody>{(reviews || []).map((review:any)=>{const student=studentMap.get(review.student_id);return <tr key={review.id}><td><span className="admin-name">{'★'.repeat(review.rating)}{'☆'.repeat(5-review.rating)}</span><span className="admin-sub">{review.comment || 'No written comment'}</span>{review.vendor_response?<span className="admin-sub"><strong>Vendor response:</strong> {review.vendor_response} {review.vendor_response_status==='hidden'?'(hidden)':''}</span>:null}<span className="admin-sub">{new Date(review.created_at).toLocaleString()}</span></td><td><Link className="admin-link" href={`/control-center/vendors/${review.vendor_id}`}>{vendorMap.get(review.vendor_id) || 'Vendor'}</Link></td><td><Link className="admin-link" href={`/control-center/students/${review.student_id}`}>{student?.name || 'Student'}</Link></td><td><div className="phase5g-admin-trust-badges">{student?.verified?<span><BadgeCheck size={13}/> Verified Student</span>:<span>Student</span>}{review.contact_verified_at?<span><MessageSquareText size={13}/> Contacted through Campus Link</span>:null}</div></td><td><span className={`status-badge status-${review.status==='published'?'approved':review.status==='hidden'?'rejected':'reviewing'}`}>{review.status}</span></td><td><form action={moderateReview} className="admin-actions"><input type="hidden" name="review_id" value={review.id}/><select name="status" defaultValue={review.status} className="admin-action"><option value="published">Published</option><option value="reported">Reported</option><option value="hidden">Hidden</option></select><button className="admin-action primary">Save</button></form></td></tr>})}{!reviews?.length?<tr><td colSpan={6}><div className="empty-admin">No reviews are visible in your scope.</div></td></tr>:null}</tbody></table></div></section>
  </>
}
