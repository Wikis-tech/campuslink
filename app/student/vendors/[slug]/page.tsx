import Link from 'next/link'
import { notFound, redirect } from 'next/navigation'
import { BadgeCheck, Bookmark, CalendarDays, CheckCircle2, Flag, MapPin, MessageCircle, Package, ShieldCheck, Star } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { VendorAnalyticsBeacon } from '@/components/vendor-analytics-beacon'
import { StudentMarketplaceHeader } from '@/components/student-marketplace-header'
import { reportVendor, submitReview, toggleSavedVendor } from '../../actions'

const reportCategories = [
  ['fraud_scam','Fraud / scam'],['harassment','Harassment'],['fake_product','Fake product'],['misrepresentation','Misrepresentation'],['unsafe_behavior','Unsafe behaviour'],['spam','Spam'],['prohibited_item','Prohibited item'],['other','Other'],
]

export default async function VendorProfilePage({ params, searchParams }: { params: Promise<{ slug: string }>; searchParams: Promise<{ error?: string; review?: string; reported?: string }> }) {
  const { slug } = await params
  const notices = await searchParams
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const { data: profile } = await supabase.from('profiles').select('first_name,account_type,institution_id,student_verification_status,onboarding_completed_at').eq('id', userId).single()
  if (!profile || profile.account_type !== 'student') redirect('/dashboard')
  if (!profile.onboarding_completed_at || !profile.institution_id) redirect('/onboarding/student')

  const { data: vendor } = await supabase.from('vendor_profiles').select('id,business_name,slug,description,location_text,logo_url,cover_url,average_rating,review_count,verification_status,marketplace_status,suspended_until,created_at').eq('slug', slug).eq('verification_status', 'approved').maybeSingle()
  if (!vendor) notFound()
  const safetyAllowed = vendor.marketplace_status === 'active' || (vendor.marketplace_status === 'suspended' && vendor.suspended_until && new Date(vendor.suspended_until) <= new Date())
  if (!safetyAllowed) notFound()

  const { data: campusApproval } = await supabase.from('vendor_institutions').select('vendor_id').eq('vendor_id', vendor.id).eq('institution_id', profile.institution_id).eq('status', 'approved').maybeSingle()
  if (!campusApproval) notFound()

  const [{ data: products }, { data: services }, { data: reviews }, { data: saved }, { data: institution }, { data: portfolio }] = await Promise.all([
    supabase.from('vendor_products').select('id,name,description,price_ngn,pricing_type,cover_image_url').eq('vendor_id', vendor.id).eq('is_active', true).order('sort_order').order('created_at', { ascending: false }),
    supabase.from('vendor_services').select('id,name,description,price_from').eq('vendor_id', vendor.id).eq('is_active', true).order('name'),
    supabase.from('reviews').select('id,student_id,rating,comment,created_at,contact_verified_at,vendor_response,vendor_response_status').eq('vendor_id', vendor.id).eq('status', 'published').order('created_at', { ascending: false }).limit(20),
    supabase.from('saved_vendors').select('vendor_id').eq('student_id', userId).eq('vendor_id', vendor.id).maybeSingle(),
    supabase.from('institutions').select('name').eq('id', profile.institution_id).maybeSingle(),
    supabase.from('vendor_portfolio_items').select('id,title,description,image_url,sort_order').eq('vendor_id', vendor.id).eq('is_active', true).order('sort_order').order('created_at', { ascending: false }),
  ])

  const reviewerIds = Array.from(new Set((reviews || []).map((review:any) => review.student_id).filter(Boolean))) as string[]
  const { data: reviewerProfiles } = reviewerIds.length
    ? await supabase.from('profiles').select('id,student_verification_status').in('id', reviewerIds)
    : { data: [] as any[] }

  const verifiedReviewers = new Set((reviewerProfiles || []).filter((row:any) => row.student_verification_status === 'verified').map((row:any) => row.id))
  const verifiedContactReviews = (reviews || []).filter((review:any) => review.contact_verified_at).length
  const initial = vendor.business_name?.slice(0,1)?.toUpperCase() || 'V'
  const returnTo = `/student/vendors/${vendor.slug}`
  const memberSince = vendor.created_at ? new Date(vendor.created_at).toLocaleDateString('en-NG',{month:'short',year:'numeric'}) : 'Campus Link member'

  return <main className="cl-student-page">
    <VendorAnalyticsBeacon vendorId={vendor.id} event="profile_view"/>
    <StudentMarketplaceHeader firstName={profile.first_name} schoolName={institution?.name}/>
    <section className="cl-student-shell phase5g-storefront">
      {notices.error ? <div className="notice error">{notices.error}</div> : null}
      {notices.review === 'saved' ? <div className="notice success">Your review has been saved.</div> : null}
      {notices.reported === '1' ? <div className="notice success">Your report has been submitted privately to Campus Link for review.</div> : null}

      <section className="v3-surface phase5g-storefront-hero">
        <div className="phase5g-cover">{vendor.cover_url ? <img src={vendor.cover_url} alt=""/> : null}</div>
        <div className="phase5g-profile-body">
          <div className="phase5g-logo">{vendor.logo_url ? <img src={vendor.logo_url} alt=""/> : initial}</div>
          <div className="phase5g-profile-grid">
            <div>
              <div className="v3-trust-line phase5g-inline-trust"><ShieldCheck size={16}/> Identity verified · Approved for {institution?.name || 'your campus'}</div>
              <h1>{vendor.business_name}</h1>
              <div className="phase5g-meta"><span><Star size={14} fill="currentColor"/> {Number(vendor.average_rating || 0).toFixed(1)} ({vendor.review_count || 0})</span>{vendor.location_text ? <span><MapPin size={14}/> {vendor.location_text}</span> : null}</div>
              <p>{vendor.description || 'Verified Campus Link vendor.'}</p>
            </div>
            <div className="phase5g-profile-actions"><a className="btn btn-primary" href={`/student/vendors/${vendor.slug}/contact`}><MessageCircle size={17}/> WhatsApp vendor</a><form action={toggleSavedVendor}><input type="hidden" name="vendor_id" value={vendor.id}/><input type="hidden" name="return_to" value={returnTo}/><button className="btn btn-ghost" type="submit"><Bookmark size={17} fill={saved ? 'currentColor' : 'none'}/> {saved ? 'Saved' : 'Save vendor'}</button></form></div>
          </div>
        </div>
      </section>

      <section className="v3-surface phase5g-trust-profile" id="trust">
        <div className="phase5g-trust-heading"><div><span><ShieldCheck size={15}/> Campus Trust</span><h2>Why this business is trusted here</h2><p>Campus Trust is explainable evidence, not a score and not something a Vendor can buy.</p></div><span className="phase5g-standing"><CheckCircle2 size={16}/> Good standing</span></div>
        <div className="phase5g-trust-grid">
          <div><BadgeCheck/><span><strong>Identity verified</strong><small>Campus Link approved the Vendor identity.</small></span></div>
          <div><BadgeCheck/><span><strong>Campus approved</strong><small>Approved to appear at {institution?.name || 'this campus'}.</small></span></div>
          <div><ShieldCheck/><span><strong>Account in good standing</strong><small>No active marketplace safety hold.</small></span></div>
          <div><MessageCircle/><span><strong>{verifiedContactReviews} verified-contact review{verifiedContactReviews === 1 ? '' : 's'}</strong><small>Backed by a Campus Link contact event.</small></span></div>
          <div><CalendarDays/><span><strong>Member since {memberSince}</strong><small>Account history is part of the trust context.</small></span></div>
        </div>
      </section>

      {(products || []).length ? <section className="cl-student-section"><div className="cl-student-section-head"><div><h2>Products</h2><p>Items this vendor currently sells.</p></div></div><div className="cl-gig-grid">{(products || []).map((product:any) => <Link href={`/student/products/${product.id}`} className="cl-gig-card" key={product.id}><div className="cl-gig-image">{product.cover_image_url ? <img src={product.cover_image_url} alt={product.name}/> : <div className="cl-gig-placeholder"><Package size={32}/></div>}</div><div className="cl-gig-copy"><div className="cl-gig-title">{product.name}</div><div className="cl-gig-price">{product.pricing_type === 'contact' ? <strong>Ask for price</strong> : <>Starting at <strong>{product.pricing_type === 'from' ? 'From ' : ''}₦{Number(product.price_ngn || 0).toLocaleString()}</strong></>}</div></div></Link>)}</div></section> : null}

      <section className="cl-student-section"><div className="cl-student-section-head"><div><h2>Services</h2><p>What this vendor can do for students.</p></div></div><div className="v3-surface">{(services || []).length ? (services || []).map((service:any) => <div className="v3-action-row" key={service.id}><span><ShieldCheck size={17}/></span><div><strong>{service.name}</strong><small>{service.description || 'Contact vendor for details.'}</small></div><div className="phase5g-service-price"><strong>{service.price_from ? `From ₦${Number(service.price_from).toLocaleString()}` : 'Ask vendor'}</strong><a href={`/student/vendors/${vendor.slug}/contact?service=${service.id}&item=${encodeURIComponent(service.name)}`}>Ask about service</a></div></div>) : <p>No detailed services added yet.</p>}</div></section>

      {(portfolio || []).length ? <section className="cl-student-section"><div className="cl-student-section-head"><div><h2>Portfolio</h2><p>Proof of previous work, kept separate from products and services.</p></div></div><div className="phase5g-portfolio">{(portfolio || []).map((item:any) => <figure key={item.id} className="v3-surface"><img src={item.image_url} alt={item.title}/><figcaption><strong>{item.title}</strong>{item.description ? <p>{item.description}</p> : null}</figcaption></figure>)}</div></section> : null}

      <section className="v3-split cl-student-section">
        <div className="v3-surface phase5g-reviews-public"><div className="cl-student-section-head"><div><h2>Student reviews</h2><p>Verified-contact means the Student used Campus Link to contact this Vendor before reviewing. It does not mean Campus Link witnessed or processed a purchase.</p></div></div>
          {(reviews || []).map((review:any) => <article className="phase5g-public-review" key={review.id}><div className="phase5g-review-top"><strong>{'★'.repeat(review.rating)}{'☆'.repeat(5-review.rating)}</strong><span>{new Date(review.created_at).toLocaleDateString('en-NG')}</span></div><p>{review.comment || 'Rating only'}</p><div className="phase5g-review-badges">{verifiedReviewers.has(review.student_id) ? <span><BadgeCheck size={14}/> Verified Student</span> : <span>Student</span>}{review.contact_verified_at ? <span><MessageCircle size={14}/> Contacted through Campus Link</span> : null}</div>{review.vendor_response && review.vendor_response_status === 'published' ? <div className="phase5g-vendor-response"><strong>Vendor response</strong><p>{review.vendor_response}</p></div> : null}</article>)}
          {!(reviews || []).length ? <p>No published reviews yet.</p> : null}
          {profile.student_verification_status === 'verified' ? <form className="review-form phase5g-review-form" action={submitReview}><input type="hidden" name="vendor_id" value={vendor.id}/><input type="hidden" name="slug" value={vendor.slug}/><strong>Share your experience</strong><small>One review per Vendor. You can edit it later, but repeated rapid edits are rate-limited.</small><select name="rating" required defaultValue=""><option value="" disabled>Choose rating</option><option value="5">5 — Excellent</option><option value="4">4 — Good</option><option value="3">3 — Okay</option><option value="2">2 — Poor</option><option value="1">1 — Very poor</option></select><textarea name="comment" maxLength={1000} placeholder="Keep your review factual and helpful."/><button className="btn btn-primary">Submit review</button></form> : <p className="cl-safety-note"><ShieldCheck size={15}/> Complete Student verification before posting public reviews.</p>}
        </div>
        <aside className="v3-surface phase5g-report-panel"><div className="cl-student-section-head"><div><h2>Safety & reporting</h2><p>Reports are private and categorized so the right Admin team can investigate faster.</p></div></div><p>Five distinct unresolved Student reports in the safety window can automatically place a Vendor under review. A report alone is not a public accusation.</p><Link href="/student/safety" className="phase5g-safety-link"><ShieldCheck size={15}/> Read the Safety Centre</Link><form className="report-form" action={reportVendor}><input type="hidden" name="vendor_id" value={vendor.id}/><input type="hidden" name="slug" value={vendor.slug}/><label>Concern category<select name="category" required defaultValue=""><option value="" disabled>Choose a category</option>{reportCategories.map(([value,label])=><option value={value} key={value}>{label}</option>)}</select></label><label>What happened?<input name="title" minLength={4} maxLength={120} required placeholder="Short factual summary"/></label><label>Details<textarea name="description" minLength={10} maxLength={1500} required placeholder="Give enough factual detail for Admin review. Do not include passwords or unnecessary sensitive information."/></label><button className="danger-cta"><Flag size={17}/> Submit private report</button></form></aside>
      </section>
    </section>
  </main>
}
