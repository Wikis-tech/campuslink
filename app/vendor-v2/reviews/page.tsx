import { redirect } from 'next/navigation'
import { BadgeCheck, MessageSquareReply, ShieldCheck, Star } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { checkPhase5gReadiness } from '@/lib/phase5g-readiness'
import { VendorWorkspaceSidebar } from '@/components/vendor-workspace-sidebar'
import { respondToReview } from './actions'

export default async function VendorReviewsPage({ searchParams }: { searchParams: Promise<{ ok?: string; error?: string }> }) {
  const params = await searchParams
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const [{ data: profile }, { data: vendor }] = await Promise.all([
    supabase.from('profiles').select('account_type').eq('id', userId).maybeSingle(),
    supabase.from('vendor_profiles').select('business_name,average_rating,review_count,marketplace_status').eq('id', userId).maybeSingle(),
  ])
  if (profile?.account_type !== 'vendor') redirect('/dashboard')

  const readiness = await checkPhase5gReadiness(supabase)
  if (!readiness.ready) {
    return <main className="v5e-vendor-page">
      <VendorWorkspaceSidebar/>
      <section className="v5e-vendor-main phase5g-workspace">
        <header className="v5e-vendor-header phase5g-header"><div><small className="v5e-eyebrow">Reputation</small><h1>Reviews & responses</h1><p>Your review tools are temporarily unavailable while Campus Link completes a trust-system database update.</p></div></header>
        <div className="notice error"><strong>Review tools are not ready yet.</strong> No review data has been lost. Please try again after the system update is completed.</div>
      </section>
    </main>
  }

  const { data: reviews } = await supabase
    .from('reviews')
    .select('id,student_id,rating,comment,status,contact_verified_at,vendor_response,vendor_response_status,vendor_responded_at,created_at')
    .eq('vendor_id', userId)
    .eq('status', 'published')
    .order('created_at', { ascending: false })
    .limit(100)

  const verifiedContactCount = (reviews || []).filter((r:any) => r.contact_verified_at).length
  const unanswered = (reviews || []).filter((r:any) => !r.vendor_response).length
  const rating = Number(vendor?.average_rating || 0)

  return <main className="v5e-vendor-page">
    <VendorWorkspaceSidebar/>
    <section className="v5e-vendor-main phase5g-workspace">
      <header className="v5e-vendor-header phase5g-header"><div><small className="v5e-eyebrow">Reputation</small><h1>Reviews & responses</h1><p>Student feedback is independent. You can respond publicly, but you cannot edit ratings, rewrite Student comments, or change moderation state.</p></div></header>
      {params.ok ? <div className="notice success">{params.ok}</div> : null}
      {params.error ? <div className="notice error">{params.error}</div> : null}

      <section className="phase5g-summary-grid">
        <article className="v5e-card"><span className="v5e-section-label">Rating</span><h2>{rating.toFixed(1)} <Star size={18} fill="currentColor"/></h2><p>{vendor?.review_count || 0} published reviews</p></article>
        <article className="v5e-card"><span className="v5e-section-label">Verified-contact reviews</span><h2>{verifiedContactCount}</h2><p>Reviews backed by a Campus Link contact event.</p></article>
        <article className="v5e-card"><span className="v5e-section-label">Needs a response</span><h2>{unanswered}</h2><p>Reply where useful. Avoid arguments or personal information.</p></article>
      </section>

      <section className="v5e-card phase5g-panel">
        <div className="v5e-card-head"><div><span className="v5e-section-label"><ShieldCheck size={14}/> Reputation principles</span><h2>Respond professionally, not defensively</h2></div></div>
        <div className="phase5g-principles"><span><BadgeCheck size={16}/> Reviews stay Student-owned.</span><span><MessageSquareReply size={16}/> Your response appears underneath the review.</span><span><ShieldCheck size={16}/> Campus Link Admin can hide abusive responses without changing the Student review.</span></div>
      </section>

      <section className="phase5g-review-list">
        {(reviews || []).map((review:any) => <article className="v5e-card phase5g-review-card" key={review.id}>
          <div className="phase5g-review-top"><div><strong className="phase5g-stars">{'★'.repeat(review.rating)}{'☆'.repeat(5-review.rating)}</strong><span>{new Date(review.created_at).toLocaleDateString('en-NG',{day:'numeric',month:'short',year:'numeric'})}</span></div>{review.contact_verified_at ? <span className="phase5g-verified"><BadgeCheck size={15}/> Contacted through Campus Link</span> : <span className="phase5g-neutral">Verified Student</span>}</div>
          <p className="phase5g-review-copy">{review.comment || 'Rating only.'}</p>
          {review.vendor_response && review.vendor_response_status === 'published' ? <div className="phase5g-response"><strong>Your public response</strong><p>{review.vendor_response}</p></div> : null}
          <form action={respondToReview} className="phase5g-response-form"><input type="hidden" name="review_id" value={review.id}/><label>{review.vendor_response ? 'Update your response' : 'Respond to this review'}<textarea name="response" maxLength={1000} defaultValue={review.vendor_response || ''} placeholder="Thank the Student, clarify facts if needed, and keep personal details private." required/></label><button className="btn btn-primary" type="submit">{review.vendor_response ? 'Update response' : 'Publish response'}</button></form>
        </article>)}
        {!reviews?.length ? <div className="v5e-card phase5g-empty"><Star size={22}/><strong>No published reviews yet</strong><p>Reviews will appear here once verified Students leave feedback.</p></div> : null}
      </section>
    </section>
  </main>
}
