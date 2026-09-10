import Link from 'next/link'
import { notFound, redirect } from 'next/navigation'
import { Bookmark, Flag, MapPin, MessageCircle, Package, ShieldCheck, Star } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { VendorAnalyticsBeacon } from '@/components/vendor-analytics-beacon'
import { StudentMarketplaceHeader } from '@/components/student-marketplace-header'
import { reportVendor, submitReview, toggleSavedVendor } from '../../actions'

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

  const { data: vendor } = await supabase.from('vendor_profiles').select('id,business_name,slug,description,location_text,logo_url,cover_url,average_rating,review_count,verification_status,marketplace_status,suspended_until').eq('slug', slug).eq('verification_status', 'approved').maybeSingle()
  if (!vendor) notFound()
  const safetyAllowed = vendor.marketplace_status === 'active' || (vendor.marketplace_status === 'suspended' && vendor.suspended_until && new Date(vendor.suspended_until) <= new Date())
  if (!safetyAllowed) notFound()

  const { data: campusApproval } = await supabase.from('vendor_institutions').select('vendor_id').eq('vendor_id', vendor.id).eq('institution_id', profile.institution_id).eq('status', 'approved').maybeSingle()
  if (!campusApproval) notFound()

  const [{ data: products }, { data: services }, { data: reviews }, { data: saved }, { data: institution }, { data: portfolio }] = await Promise.all([
    supabase.from('vendor_products').select('id,name,description,price_ngn,pricing_type,cover_image_url').eq('vendor_id', vendor.id).eq('is_active', true).order('sort_order').order('created_at', { ascending: false }),
    supabase.from('vendor_services').select('id,name,description,price_from').eq('vendor_id', vendor.id).eq('is_active', true).order('name'),
    supabase.from('reviews').select('id,student_id,rating,comment,created_at').eq('vendor_id', vendor.id).eq('status', 'published').order('created_at', { ascending: false }).limit(20),
    supabase.from('saved_vendors').select('vendor_id').eq('student_id', userId).eq('vendor_id', vendor.id).maybeSingle(),
    supabase.from('institutions').select('name').eq('id', profile.institution_id).maybeSingle(),
    supabase.from('vendor_portfolio_items').select('id,title,description,image_url,sort_order').eq('vendor_id', vendor.id).eq('is_active', true).order('sort_order').order('created_at', { ascending: false }),
  ])

  const reviewerIds = Array.from(new Set((reviews || []).map((review) => review.student_id).filter(Boolean))) as string[]
  const [{ data: reviewerProfiles }, { data: contactedRows }] = reviewerIds.length ? await Promise.all([
    supabase.from('profiles').select('id,student_verification_status').in('id', reviewerIds),
    supabase.from('contact_events').select('student_id').eq('vendor_id', vendor.id).in('student_id', reviewerIds),
  ]) : [{ data: [] as any[] }, { data: [] as any[] }]

  const verifiedReviewers = new Set((reviewerProfiles || []).filter((row) => row.student_verification_status === 'verified').map((row) => row.id))
  const contactedReviewers = new Set((contactedRows || []).map((row) => row.student_id))
  const initial = vendor.business_name?.slice(0,1)?.toUpperCase() || 'V'
  const returnTo = `/student/vendors/${vendor.slug}`

  return <main className="cl-student-page">
    <VendorAnalyticsBeacon vendorId={vendor.id} event="profile_view"/>
    <StudentMarketplaceHeader firstName={profile.first_name} schoolName={institution?.name}/>
    <section className="cl-student-shell">
      {notices.error ? <div className="notice error">{notices.error}</div> : null}
      {notices.review === 'saved' ? <div className="notice success">Your review has been saved.</div> : null}
      {notices.reported === '1' ? <div className="notice success">Your report has been submitted privately to Campus Link for review.</div> : null}

      <section className="v3-surface" style={{padding:0,overflow:'hidden',borderRadius:16}}>
        <div style={{height:260,background:'var(--v3-soft-blue)',overflow:'hidden'}}>{vendor.cover_url ? <img src={vendor.cover_url} alt="" style={{width:'100%',height:'100%',objectFit:'cover'}}/> : null}</div>
        <div style={{padding:'0 clamp(20px,4vw,44px) 36px'}}>
          <div style={{width:82,height:82,borderRadius:'50%',marginTop:-42,border:'5px solid var(--v3-surface)',background:'var(--v3-navy)',color:'#fff',overflow:'hidden',display:'grid',placeItems:'center',fontWeight:900,fontSize:28,position:'relative'}}>{vendor.logo_url ? <img src={vendor.logo_url} alt="" style={{width:'100%',height:'100%',objectFit:'cover'}}/> : initial}</div>
          <div style={{display:'flex',justifyContent:'space-between',gap:20,alignItems:'flex-start',flexWrap:'wrap',marginTop:16}}>
            <div style={{maxWidth:720}}>
              <div className="v3-trust-line" style={{borderTop:0,paddingTop:0}}><ShieldCheck size={16}/> Identity verified · Approved for {institution?.name || 'your campus'}</div>
              <h1 style={{fontSize:'clamp(34px,5vw,58px)',letterSpacing:'-.05em',margin:'10px 0'}}>{vendor.business_name}</h1>
              <div style={{display:'flex',gap:14,flexWrap:'wrap',color:'var(--v3-muted)',fontSize:14}}><span><Star size={14} fill="currentColor"/> {Number(vendor.average_rating || 0).toFixed(1)} ({vendor.review_count || 0})</span>{vendor.location_text ? <span><MapPin size={14}/> {vendor.location_text}</span> : null}</div>
              <p style={{color:'var(--v3-muted)',lineHeight:1.7}}>{vendor.description || 'Verified Campus Link vendor.'}</p>
            </div>
            <div style={{display:'grid',gap:9,minWidth:220}}>
              <Link className="btn btn-primary" href={`/student/vendors/${vendor.slug}/contact`}><MessageCircle size={17}/> WhatsApp vendor</Link>
              <form action={toggleSavedVendor}><input type="hidden" name="vendor_id" value={vendor.id}/><input type="hidden" name="return_to" value={returnTo}/><button className="btn btn-ghost" style={{width:'100%'}}><Bookmark size={17} fill={saved ? 'currentColor' : 'none'}/> {saved ? 'Saved' : 'Save vendor'}</button></form>
            </div>
          </div>
        </div>
      </section>

      {(products || []).length ? <section className="cl-student-section"><div className="cl-student-section-head"><div><h2>Products</h2><p>Items this vendor currently sells.</p></div></div><div className="cl-gig-grid">{(products || []).map((product) => <Link href={`/student/products/${product.id}`} className="cl-gig-card" key={product.id}><div className="cl-gig-image">{product.cover_image_url ? <img src={product.cover_image_url} alt={product.name}/> : <div className="cl-gig-placeholder"><Package size={32}/></div>}</div><div className="cl-gig-copy"><div className="cl-gig-title">{product.name}</div><div className="cl-gig-price">{product.pricing_type === 'contact' ? <strong>Ask for price</strong> : <>Starting at <strong>{product.pricing_type === 'from' ? 'From ' : ''}₦{Number(product.price_ngn || 0).toLocaleString()}</strong></>}</div></div></Link>)}</div></section> : null}

      <section className="cl-student-section"><div className="cl-student-section-head"><div><h2>Services</h2><p>What this vendor can do for students.</p></div></div><div className="v3-surface">{(services || []).length ? (services || []).map((service) => <div className="v3-action-row" key={service.id}><span><ShieldCheck size={17}/></span><div><strong>{service.name}</strong><small>{service.description || 'Contact vendor for details.'}</small></div><div style={{display:'grid',justifyItems:'end',gap:6}}><strong>{service.price_from ? `From ₦${Number(service.price_from).toLocaleString()}` : 'Ask vendor'}</strong><Link href={`/student/vendors/${vendor.slug}/contact?service=${service.id}&item=${encodeURIComponent(service.name)}`} style={{fontSize:12,fontWeight:850,color:'var(--v3-blue)',textDecoration:'none'}}>Ask about service</Link></div></div>) : <p>No detailed services added yet.</p>}</div></section>

      {(portfolio || []).length ? <section className="cl-student-section"><div className="cl-student-section-head"><div><h2>Portfolio</h2><p>Proof of previous work, kept separate from products and services.</p></div></div><div style={{columns:'3 240px',columnGap:14}}>{(portfolio || []).map((item) => <figure key={item.id} className="v3-surface" style={{breakInside:'avoid',padding:0,overflow:'hidden',margin:'0 0 14px'}}><img src={item.image_url} alt={item.title} style={{width:'100%',height:'auto',display:'block'}}/><figcaption style={{padding:14}}><strong>{item.title}</strong>{item.description ? <p style={{color:'var(--v3-muted)',fontSize:13}}>{item.description}</p> : null}</figcaption></figure>)}</div></section> : null}

      <section className="v3-split cl-student-section">
        <div className="v3-surface"><div className="cl-student-section-head"><div><h2>Student reviews</h2><p>Trust labels are based on the reviewer's actual account and Campus Link interaction history.</p></div></div>
          {(reviews || []).map((review) => <article className="v3-action-row" key={review.id}><span><Star size={17}/></span><div><strong>{review.rating}/5 · {verifiedReviewers.has(review.student_id) ? 'Verified student' : 'Student'}</strong><small>{review.comment || 'Rating only'}</small><div style={{display:'flex',gap:8,flexWrap:'wrap',marginTop:5,fontSize:11,color:'var(--v3-muted)'}}>{verifiedReviewers.has(review.student_id) ? <span>Student verification ✓</span> : null}{contactedReviewers.has(review.student_id) ? <span>Contacted through Campus Link ✓</span> : null}</div></div></article>)}
          {!(reviews || []).length ? <p>No published reviews yet.</p> : null}
          {profile.student_verification_status === 'verified' ? <form className="review-form" action={submitReview} style={{marginTop:18}}><input type="hidden" name="vendor_id" value={vendor.id}/><input type="hidden" name="slug" value={vendor.slug}/><strong>Share your experience</strong><select name="rating" required defaultValue=""><option value="" disabled>Choose rating</option><option value="5">5 — Excellent</option><option value="4">4 — Good</option><option value="3">3 — Okay</option><option value="2">2 — Poor</option><option value="1">1 — Very poor</option></select><textarea name="comment" maxLength={1000} placeholder="Keep your review factual and helpful."/><button className="btn btn-primary">Submit review</button></form> : <p className="cl-safety-note"><ShieldCheck size={15}/> Complete Student verification before posting public reviews.</p>}
        </div>
        <aside className="v3-surface"><div className="cl-student-section-head"><div><h2>Safety & reporting</h2><p>Reports go to Campus Link Admin; vendors cannot delete reports filed against them.</p></div></div><p style={{color:'var(--v3-muted)',lineHeight:1.6}}>Five distinct unresolved Student reports in the safety window can automatically place a vendor under review and remove them from discovery while Admin investigates.</p><form className="report-form" action={reportVendor}><input type="hidden" name="vendor_id" value={vendor.id}/><input type="hidden" name="slug" value={vendor.slug}/><input name="title" minLength={4} maxLength={120} required placeholder="What happened?"/><textarea name="description" minLength={10} maxLength={1500} required placeholder="Give enough factual detail for review."/><button className="danger-cta"><Flag size={17}/> Submit private report</button></form></aside>
      </section>
    </section>
  </main>
}
