import Link from 'next/link'
import { redirect } from 'next/navigation'
import { ArrowUpRight, BadgeCheck, Bookmark, MapPin, Search, ShieldCheck, Star, UsersRound } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { StudentActivityChart } from '@/components/dashboard-charts'

export default async function StudentDashboard() {
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const { data: profile } = await supabase
    .from('profiles')
    .select('first_name,account_type,institution_id,student_verification_status,onboarding_completed_at')
    .eq('id', userId)
    .maybeSingle()

  if (!profile || profile.account_type === 'vendor') redirect('/dashboard')

  const [{ data: verification }, institutionResult, savedResult, reviewResult, contactResult, reportResult] = await Promise.all([
    supabase.from('student_verifications').select('status,verification_method,submitted_at,review_note').eq('student_id', userId).maybeSingle(),
    profile.institution_id ? supabase.from('institutions').select('name').eq('id', profile.institution_id).maybeSingle() : Promise.resolve({ data: null }),
    supabase.from('saved_vendors').select('vendor_id', { count: 'exact' }).eq('student_id', userId),
    supabase.from('reviews').select('id', { count: 'exact', head: true }).eq('student_id', userId),
    supabase.from('contact_events').select('id', { count: 'exact', head: true }).eq('student_id', userId),
    supabase.from('complaints').select('id', { count: 'exact', head: true }).eq('reporter_id', userId),
  ])

  const school = institutionResult.data?.name || null
  const status = verification?.status || profile.student_verification_status || 'pending'
  const setupComplete = Boolean(profile.onboarding_completed_at)
  const savedCount = savedResult.count ?? savedResult.data?.length ?? 0
  const reviewCount = reviewResult.count || 0
  const contactCount = contactResult.count || 0
  const reportCount = reportResult.count || 0
  let featured: any[] = []
  let campusVendorCount = 0

  if (profile.institution_id) {
    const { data: campusLinks } = await supabase.from('vendor_institutions').select('vendor_id').eq('institution_id', profile.institution_id).eq('status', 'approved').limit(50)
    const ids = (campusLinks || []).map((row) => row.vendor_id)
    campusVendorCount = ids.length
    if (ids.length) {
      const { data } = await supabase.from('vendor_profiles').select('id,business_name,slug,logo_url,average_rating,review_count').in('id', ids).eq('verification_status', 'approved').order('average_rating', { ascending: false }).limit(3)
      featured = data || []
    }
  }

  return (
    <main className="student-app">
      <header className="student-nav">
        <Link href="/student" className="student-brand">Campus<span>Link</span></Link>
        <nav className="student-navlinks">
          <Link href="/student">Dashboard</Link>
          <Link href="/student/discover">Discover</Link>
          <Link href="/student/saved">Saved</Link>
          <Link href="/onboarding/student">Verification</Link>
          <form action="/auth/signout" method="post"><button>Sign out</button></form>
        </nav>
      </header>

      <section className="student-shell">
        <section className="student-hero-card reveal-panel">
          <div className="student-hero-copy">
            <div className="student-hero-kicker"><ShieldCheck size={16}/> Your campus network</div>
            <h1>Welcome back{profile.first_name ? `, ${profile.first_name}` : ''}.</h1>
            <p>{school ? `Find trusted people already approved to serve ${school}.` : 'Explore Campus Link now and connect your account to your school whenever you are ready.'}</p>
            <form className="hero-search" action="/student/discover" method="get">
              <Search size={20}/>
              <input name="q" placeholder="What do you need today? Try phone repair, braids, tutor..."/>
              <button type="submit">Search <ArrowUpRight size={16}/></button>
            </form>
          </div>
          <div className="student-hero-side">
            {school ? <div className="hero-campus"><MapPin size={18}/><span>Your campus</span><strong>{school}</strong></div> : <div className="hero-campus"><UsersRound size={18}/><span>Account</span><strong>Student</strong></div>}
            <div className="hero-network-number"><span>Approved vendors around you</span><strong>{campusVendorCount}</strong><small>Updates automatically as your campus network grows.</small></div>
          </div>
        </section>

        <section className="student-stat-grid stagger-grid">
          <article className="student-stat-card blue"><span>Saved vendors</span><strong>{savedCount}</strong><small>Your shortlist for quick return visits.</small></article>
          <article className="student-stat-card green"><span>Vendor contacts</span><strong>{contactCount}</strong><small>Connections started through Campus Link.</small></article>
          <article className="student-stat-card soft"><span>Your reviews</span><strong>{reviewCount}</strong><small>Feedback shared with your campus community.</small></article>
          <article className="student-stat-card dark"><span>Verification</span><strong className="status-word">{status.replace('_', ' ')}</strong><small>{status === 'verified' ? 'Your student identity is verified.' : 'You can keep browsing while verification is pending.'}</small></article>
        </section>

        {!setupComplete ? (
          <section className="verification-banner reveal-panel">
            <div><span className="verification-icon"><BadgeCheck size={22}/></span><div><strong>Complete your student verification</strong><p>Add your school and student details to unlock campus-specific discovery and review privileges.</p></div></div>
            <Link href="/onboarding/student" className="verification-link">Complete verification <ArrowUpRight size={15}/></Link>
          </section>
        ) : status !== 'verified' ? (
          <section className="verification-banner soft reveal-panel">
            <div><span className="verification-icon"><ShieldCheck size={22}/></span><div><strong>Verification {status.replace('_', ' ')}</strong><p>Your account remains usable while the verification team reviews your submission.</p></div></div>
          </section>
        ) : null}

        <section className="student-insight-grid">
          <StudentActivityChart saved={savedCount} reviews={reviewCount} contacts={contactCount} reports={reportCount}/>
          <div className="student-quick-panel">
            <div className="panel-heading"><span>Quick access</span><strong>Move around Campus Link</strong></div>
            <div className="quick-link-grid">
              <Link href="/student/discover" className="quick-link blue"><Search size={21}/><div><strong>Discover</strong><span>Browse approved campus vendors</span></div><ArrowUpRight/></Link>
              <Link href="/student/saved" className="quick-link green"><Bookmark size={21}/><div><strong>Saved</strong><span>Return to vendors you liked</span></div><ArrowUpRight/></Link>
              <Link href="/onboarding/student" className="quick-link light"><BadgeCheck size={21}/><div><strong>Verification</strong><span>Manage your trust status</span></div><ArrowUpRight/></Link>
            </div>
          </div>
        </section>

        <div className="result-meta section-title-row"><div><span>Campus picks</span><strong>Popular around your campus</strong></div><Link href="/student/discover">See all vendors <ArrowUpRight size={15}/></Link></div>
        {featured.length ? (
          <div className="vendor-grid">
            {featured.map((vendor) => (
              <article className="vendor-card" key={vendor.id}>
                <div className="vendor-cover"></div>
                <div className="vendor-avatar">{vendor.logo_url ? <img src={vendor.logo_url} alt={`${vendor.business_name} logo`}/> : vendor.business_name.slice(0,1)}</div>
                <div className="vendor-card-body">
                  <div className="vendor-card-top"><div><Link className="vendor-name" href={`/student/vendors/${vendor.slug}`}>{vendor.business_name}</Link><div className="verified-line"><ShieldCheck size={14}/> Campus verified</div></div><div className="rating"><Star size={15} fill="currentColor"/> {Number(vendor.average_rating || 0).toFixed(1)}</div></div>
                  <div className="vendor-card-actions"><Link className="view-btn" href={`/student/vendors/${vendor.slug}`}>View profile <ArrowUpRight size={14}/></Link></div>
                </div>
              </article>
            ))}
          </div>
        ) : (
          <div className="empty-state branded-empty"><ShieldCheck size={34}/><h2>{school ? 'Your campus network is getting ready' : 'Connect your school to personalise Campus Link'}</h2><p>{school ? 'Approved vendors will appear here automatically as your school network grows.' : 'You can use your account already. Add your school whenever convenient so discovery becomes campus-specific.'}</p>{!school ? <Link href="/onboarding/student" className="search-button inline-button">Add my school</Link> : null}</div>
        )}
      </section>

      <nav className="bottom-nav"><Link href="/student">Home</Link><Link href="/student/discover">Discover</Link><Link href="/student/saved">Saved</Link><Link href="/onboarding/student">Verify</Link></nav>
    </main>
  )
}
