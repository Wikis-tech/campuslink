import { MessageSquareText, Star } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { requireAdminContext } from '../lib'
import { moderateReview } from '../actions'

export default async function ReviewsPage({ searchParams }: { searchParams: Promise<{ success?: string; error?: string }> }) {
  const params = await searchParams
  await requireAdminContext()
  const supabase = await createClient()
  const { data: reviews } = await supabase.from('reviews').select('id,student_id,vendor_id,rating,comment,status,created_at,updated_at').order('created_at',{ascending:false}).limit(100)
  const vendorIds = Array.from(new Set((reviews || []).map((r)=>r.vendor_id))) as string[]
  const studentIds = Array.from(new Set((reviews || []).map((r)=>r.student_id))) as string[]
  const [{data:vendors},{data:students}] = await Promise.all([
    vendorIds.length ? supabase.from('vendor_profiles').select('id,business_name').in('id',vendorIds) : Promise.resolve({data:[] as any[]}),
    studentIds.length ? supabase.from('profiles').select('id,first_name,last_name').in('id',studentIds) : Promise.resolve({data:[] as any[]}),
  ])
  const vendorMap = new Map((vendors || []).map((v)=>[v.id,v.business_name]))
  const studentMap = new Map((students || []).map((s)=>[s.id,[s.first_name,s.last_name].filter(Boolean).join(' ') || 'Student']))

  return <>
    <header className="admin-topbar"><div><span className="admin-pill"><MessageSquareText size={15}/> Reputation moderation</span><h1>Reviews</h1><p>Moderate abusive or reported reviews without letting vendors rewrite student feedback.</p></div></header>
    {params.success ? <div className="admin-success">{params.success}</div> : null}{params.error ? <div className="admin-error">{params.error}</div> : null}
    <section className="admin-grid"><article className="admin-stat"><span>Reviews in view</span><strong>{reviews?.length || 0}</strong><small>Latest 100 visible to your role.</small></article><article className="admin-stat"><span>Published</span><strong>{(reviews || []).filter(r=>r.status==='published').length}</strong><small>Visible on vendor profiles.</small></article><article className="admin-stat"><span>Flagged / hidden</span><strong>{(reviews || []).filter(r=>r.status!=='published').length}</strong><small>Under moderation or removed from public display.</small></article><article className="admin-stat"><span>Rating integrity</span><strong><Star size={28}/></strong><small>Aggregate ratings are recalculated from published reviews.</small></article></section>
    <section className="admin-section"><div className="admin-section-head"><div><h2>Review moderation</h2><p>School support/admin roles only see reviews tied to vendors serving their assigned school.</p></div></div><div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>Review</th><th>Vendor</th><th>Student</th><th>Status</th><th>Moderate</th></tr></thead><tbody>{(reviews || []).map((review)=><tr key={review.id}><td><span className="admin-name">{'★'.repeat(review.rating)}{'☆'.repeat(5-review.rating)}</span><span className="admin-sub">{review.comment || 'No written comment'}</span><span className="admin-sub">{new Date(review.created_at).toLocaleString()}</span></td><td>{vendorMap.get(review.vendor_id) || 'Vendor'}</td><td>{studentMap.get(review.student_id) || 'Student'}</td><td><span className={`status-badge status-${review.status==='published'?'approved':review.status==='hidden'?'rejected':'reviewing'}`}>{review.status}</span></td><td><form action={moderateReview} className="admin-actions"><input type="hidden" name="review_id" value={review.id}/><select name="status" defaultValue={review.status} className="admin-action"><option value="published">Published</option><option value="reported">Reported</option><option value="hidden">Hidden</option></select><button className="admin-action primary">Save</button></form></td></tr>)}{!reviews?.length?<tr><td colSpan={5}><div className="empty-admin">No reviews are visible in your scope.</div></td></tr>:null}</tbody></table></div></section>
  </>
}
