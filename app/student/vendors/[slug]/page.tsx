import Link from 'next/link'
import { notFound, redirect } from 'next/navigation'
import { Bookmark, Flag, MapPin, MessageCircle, ShieldCheck, Star } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { reportVendor, submitReview, toggleSavedVendor } from '../../actions'

export default async function VendorProfilePage({ params, searchParams }: { params: Promise<{ slug: string }>; searchParams: Promise<{ error?: string; review?: string; reported?: string }> }) {
  const { slug } = await params
  const notices = await searchParams
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const { data: profile } = await supabase
    .from('profiles')
    .select('account_type,institution_id,student_verification_status,onboarding_completed_at')
    .eq('id', userId)
    .single()

  if (!profile || profile.account_type !== 'student') redirect('/dashboard')
  if (!profile.onboarding_completed_at) redirect('/onboarding/student')
  if (!profile.institution_id) redirect('/onboarding/student')

  const { data: vendor } = await supabase
    .from('vendor_profiles')
    .select('id,business_name,slug,description,location_text,logo_url,cover_url,average_rating,review_count,verification_status')
    .eq('slug', slug)
    .eq('verification_status', 'approved')
    .maybeSingle()

  if (!vendor) notFound()

  const { data: campusApproval } = await supabase
    .from('vendor_institutions')
    .select('vendor_id')
    .eq('vendor_id', vendor.id)
    .eq('institution_id', profile.institution_id)
    .eq('status', 'approved')
    .maybeSingle()

  if (!campusApproval) notFound()

  const [{ data: services }, { data: reviews }, { data: saved }, { data: institution }] = await Promise.all([
    supabase.from('vendor_services').select('id,name,description,price_from,category_id').eq('vendor_id', vendor.id).eq('is_active', true).order('name'),
    supabase.from('reviews').select('id,rating,comment,created_at').eq('vendor_id', vendor.id).eq('status', 'published').order('created_at', { ascending: false }).limit(20),
    supabase.from('saved_vendors').select('vendor_id').eq('student_id', userId).eq('vendor_id', vendor.id).maybeSingle(),
    supabase.from('institutions').select('name').eq('id', profile.institution_id).maybeSingle(),
  ])

  const initial = vendor.business_name?.slice(0,1)?.toUpperCase() || 'V'
  const returnTo = `/student/vendors/${vendor.slug}`

  return (
    <main className="student-app">
      <header className="student-nav">
        <Link href="/student" className="student-brand">Campus<span>Link</span></Link>
        <nav className="student-navlinks"><Link href="/student">Dashboard</Link><Link href="/student/discover">Discover</Link><Link href="/student/saved">Saved</Link><form action="/auth/signout" method="post"><button>Sign out</button></form></nav>
      </header>

      <section className="student-shell">
        {notices.error ? <div className="notice error">{notices.error}</div> : null}
        {notices.review === 'saved' ? <div className="notice success">Your review has been saved.</div> : null}
        {notices.reported === '1' ? <div className="notice success">Your report has been submitted for review.</div> : null}

        <article className="vendor-profile">
          <div className="profile-cover">{vendor.cover_url ? <img src={vendor.cover_url} alt="" /> : null}</div>
          <div className="profile-main">
            <div>
              <div className="profile-avatar">{vendor.logo_url ? <img src={vendor.logo_url} alt={`${vendor.business_name} logo`} /> : initial}</div>
              <div className="profile-title">
                <div className="verified-line"><ShieldCheck size={16}/> Verified for {institution?.name || 'your campus'}</div>
                <h1>{vendor.business_name}</h1>
                <div style={{display:'flex',gap:14,flexWrap:'wrap',marginTop:10}}><span className="rating"><Star size={16} fill="currentColor"/> {Number(vendor.average_rating || 0).toFixed(1)} ({vendor.review_count || 0} reviews)</span>{vendor.location_text ? <span style={{display:'inline-flex',alignItems:'center',gap:6,color:'#667085'}}><MapPin size={15}/>{vendor.location_text}</span> : null}</div>
                <p>{vendor.description || 'Verified Campus Link service provider.'}</p>
              </div>

              <section className="profile-section">
                <h2>Services</h2>
                <div className="service-list">{(services || []).length ? (services || []).map((service) => <div className="service-row" key={service.id}><div><strong>{service.name}</strong>{service.description ? <span>{service.description}</span> : null}</div><strong>{service.price_from ? `From ₦${Number(service.price_from).toLocaleString()}` : 'Ask vendor'}</strong></div>) : <div className="service-row"><span>This vendor has not added detailed services yet.</span></div>}</div>
              </section>

              <section className="profile-section">
                <h2>Student reviews</h2>
                <div className="review-list">{(reviews || []).length ? (reviews || []).map((review) => <article className="review-row" key={review.id}><div className="review-head"><strong>Verified student</strong><span className="rating"><Star size={14} fill="currentColor"/> {review.rating}/5</span></div>{review.comment ? <p>{review.comment}</p> : null}</article>) : <div className="review-row"><p>No published reviews yet.</p></div>}</div>

                {profile.student_verification_status === 'verified' ? <form className="review-form" action={submitReview}><input type="hidden" name="vendor_id" value={vendor.id}/><input type="hidden" name="slug" value={vendor.slug}/><strong>Share your experience</strong><select name="rating" required defaultValue=""><option value="" disabled>Choose rating</option><option value="5">5 — Excellent</option><option value="4">4 — Good</option><option value="3">3 — Okay</option><option value="2">2 — Poor</option><option value="1">1 — Very poor</option></select><textarea name="comment" maxLength={1000} placeholder="Keep your review factual and helpful."/><button className="search-button" style={{padding:'12px 16px'}} type="submit">Submit review</button></form> : <div className="notice error" style={{marginTop:14}}>Your student account must be verified before you can publish a review.</div>}
              </section>
            </div>

            <aside className="profile-side">
              <div className="profile-cta">
                <Link className="primary-cta" href={`/student/vendors/${vendor.slug}/contact`}><MessageCircle size={18}/> Contact vendor</Link>
                <form action={toggleSavedVendor}><input type="hidden" name="vendor_id" value={vendor.id}/><input type="hidden" name="return_to" value={returnTo}/><button className="secondary-cta" style={{width:'100%'}}><Bookmark size={18} fill={saved ? 'currentColor' : 'none'}/> {saved ? 'Saved' : 'Save vendor'}</button></form>
              </div>

              <section className="profile-section"><h2>Report a concern</h2><p style={{color:'#667085',fontSize:14}}>If something feels unsafe, misleading or suspicious, tell Campus Link. Reports are reviewed privately.</p><form className="report-form" action={reportVendor}><input type="hidden" name="vendor_id" value={vendor.id}/><input type="hidden" name="slug" value={vendor.slug}/><input name="title" minLength={4} maxLength={120} required placeholder="What happened?"/><textarea name="description" minLength={10} maxLength={1500} required placeholder="Give us enough detail to review the concern."/><button className="danger-cta" type="submit"><Flag size={17}/> Submit report</button></form></section>
            </aside>
          </div>
        </article>
      </section>

      <nav className="bottom-nav"><Link href="/student">Home</Link><Link href="/student/discover">Discover</Link><Link href="/student/saved">Saved</Link></nav>
    </main>
  )
}
