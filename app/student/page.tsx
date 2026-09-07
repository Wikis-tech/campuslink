import Link from 'next/link'
import { redirect } from 'next/navigation'
import { ArrowUpRight, BadgeCheck, BookOpen, Bookmark, Camera, Laptop, Search, Scissors, Shapes, ShieldCheck, Shirt, Sparkles, Star, UtensilsCrossed, Wrench } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { StudentActivityChart } from '@/components/dashboard-charts'
import { ThemeToggle } from '@/components/theme-toggle'
import { DynamicCampusContext, DynamicGreeting } from '@/components/dynamic-greeting'

function CategoryIcon({ name }: { name: string }) {
  const value = name.toLowerCase()
  if (value.includes('hair') || value.includes('beauty') || value.includes('barb')) return <Scissors size={18}/>
  if (value.includes('food') || value.includes('cater') || value.includes('meal')) return <UtensilsCrossed size={18}/>
  if (value.includes('repair') || value.includes('fix')) return <Wrench size={18}/>
  if (value.includes('tech') || value.includes('computer') || value.includes('phone')) return <Laptop size={18}/>
  if (value.includes('tutor') || value.includes('academic') || value.includes('education')) return <BookOpen size={18}/>
  if (value.includes('photo') || value.includes('video')) return <Camera size={18}/>
  if (value.includes('fashion') || value.includes('laundry') || value.includes('cloth')) return <Shirt size={18}/>
  if (value.includes('event') || value.includes('creative')) return <Sparkles size={18}/>
  return <Shapes size={18}/>
}

export default async function StudentDashboard() {
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')
  const sessionSeed = typeof claimsData?.claims?.session_id === 'string' ? claimsData.claims.session_id : userId

  const { data: profile } = await supabase
    .from('profiles')
    .select('first_name,account_type,institution_id,student_verification_status,onboarding_completed_at')
    .eq('id', userId)
    .maybeSingle()

  if (!profile || profile.account_type === 'vendor') redirect('/dashboard')

  const [{ data: verification }, institutionResult, savedResult, reviewResult, contactResult, reportResult, categoryResult] = await Promise.all([
    supabase.from('student_verifications').select('status,verification_method,submitted_at,review_note').eq('student_id', userId).maybeSingle(),
    profile.institution_id ? supabase.from('institutions').select('name').eq('id', profile.institution_id).maybeSingle() : Promise.resolve({ data: null }),
    supabase.from('saved_vendors').select('vendor_id', { count: 'exact' }).eq('student_id', userId),
    supabase.from('reviews').select('id', { count: 'exact', head: true }).eq('student_id', userId),
    supabase.from('contact_events').select('id', { count: 'exact', head: true }).eq('student_id', userId),
    supabase.from('complaints').select('id', { count: 'exact', head: true }).eq('reporter_id', userId),
    supabase.from('categories').select('id,name,slug').eq('is_active', true).order('name').limit(8),
  ])

  const school = institutionResult.data?.name || null
  const status = verification?.status || profile.student_verification_status || 'pending'
  const setupComplete = Boolean(profile.onboarding_completed_at)
  const savedCount = savedResult.count ?? savedResult.data?.length ?? 0
  const reviewCount = reviewResult.count || 0
  const contactCount = contactResult.count || 0
  const reportCount = reportResult.count || 0
  const hasActivity = savedCount + reviewCount + contactCount + reportCount > 0
  const categories = categoryResult.data || []
  let featured: any[] = []
  let campusVendorCount = 0

  if (profile.institution_id) {
    const { data: campusLinks } = await supabase.from('vendor_institutions').select('vendor_id').eq('institution_id', profile.institution_id).eq('status', 'approved').limit(50)
    const ids = (campusLinks || []).map((row) => row.vendor_id)
    campusVendorCount = ids.length
    if (ids.length) {
      const { data } = await supabase.from('vendor_profiles').select('id,business_name,slug,description,logo_url,cover_url,average_rating,review_count').in('id', ids).eq('verification_status', 'approved').order('average_rating', { ascending: false }).limit(6)
      featured = data || []
    }
  }

  return (
    <main className="student-app">
      <header className="student-nav">
        <Link href="/student" className="student-brand">Campus<span>Link</span></Link>
        <div className="student-navtools">
          <nav className="student-navlinks">
            <Link href="/student">Home</Link>
            <Link href="/student/discover">Discover</Link>
            <Link href="/student/saved">Saved</Link>
            <Link href="/onboarding/student">Verification</Link>
            <form action="/auth/signout" method="post"><button>Sign out</button></form>
          </nav>
          <ThemeToggle compact />
        </div>
      </header>

      <section className="student-shell">
        <section className="student-hero-card phase45-hero phase46-hero reveal-panel">
          <div className="student-hero-copy">
            <DynamicGreeting firstName={profile.first_name} sessionSeed={sessionSeed} role="student" />
            <p>{school ? 'What do you need today? Search trusted services already cleared for your school.' : 'Find useful people and services, then connect your account to your school when you are ready.'}</p>
            <form className="hero-search" action="/student/discover" method="get">
              <Search size={20}/>
              <input name="q" placeholder="Search phone repair, braids, food, tutor..."/>
              <button type="submit">Find it <ArrowUpRight size={16}/></button>
            </form>
          </div>

          <div className="student-hero-side">
            <DynamicCampusContext school={school} vendorCount={campusVendorCount} sessionSeed={sessionSeed} />
          </div>
          <div className="cl-link-arc phase46-arc" aria-hidden="true"><span className="arc-line"/></div>
          <div className="phase46-orbit phase46-orbit-one" aria-hidden="true" />
          <div className="phase46-orbit phase46-orbit-two" aria-hidden="true" />
        </section>

        {categories.length ? (
          <section className="marketplace-section" aria-labelledby="browse-category-heading">
            <div className="marketplace-section-head">
              <div><span>Browse by need</span><strong id="browse-category-heading">Popular services on Campus Link</strong></div>
              <Link href="/student/discover">See everything <ArrowUpRight size={15}/></Link>
            </div>
            <div className="marketplace-category-rail">
              {categories.slice(0,6).map((category) => (
                <Link className="marketplace-category" href={`/student/discover?category=${encodeURIComponent(category.slug)}`} key={category.id}>
                  <span><CategoryIcon name={category.name}/></span>
                  <strong>{category.name}</strong>
                </Link>
              ))}
            </div>
          </section>
        ) : null}

        <section className="marketplace-section" aria-labelledby="campus-picks-heading">
          <div className="marketplace-section-head">
            <div><span>{school ? 'Around your school' : 'Campus picks'}</span><strong id="campus-picks-heading">Trusted vendors worth checking out</strong></div>
            <Link href="/student/discover">Browse all <ArrowUpRight size={15}/></Link>
          </div>

          {featured.length ? (
            <div className="vendor-grid">
              {featured.map((vendor) => (
                <article className="vendor-card" key={vendor.id}>
                  <div className="vendor-cover">{vendor.cover_url ? <img src={vendor.cover_url} alt=""/> : null}</div>
                  <div className="vendor-avatar">{vendor.logo_url ? <img src={vendor.logo_url} alt={`${vendor.business_name} logo`}/> : vendor.business_name.slice(0,1)}</div>
                  <div className="vendor-card-body">
                    <div className="vendor-card-top"><div><Link className="vendor-name" href={`/student/vendors/${vendor.slug}`}>{vendor.business_name}</Link><div className="verified-line"><ShieldCheck size={14}/> Campus verified</div></div><div className="rating"><Star size={15} fill="currentColor"/> {Number(vendor.average_rating || 0).toFixed(1)} <span>({vendor.review_count || 0})</span></div></div>
                    <p className="vendor-desc">{vendor.description || 'Trusted service provider available around your campus.'}</p>
                    <div className="vendor-card-actions"><Link className="view-btn" href={`/student/vendors/${vendor.slug}`}>View profile <ArrowUpRight size={14}/></Link></div>
                  </div>
                </article>
              ))}
            </div>
          ) : (
            <div className="empty-state branded-empty"><ShieldCheck size={34}/><h2>{school ? 'Your local vendor list is still growing' : 'Connect your school to personalise Campus Link'}</h2><p>{school ? 'Approved vendors will show up here as more businesses are cleared for your school.' : 'Add your school so Campus Link can show the vendors and services that are actually relevant to you.'}</p>{!school ? <Link href="/onboarding/student" className="search-button inline-button">Add my school</Link> : null}</div>
          )}
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

        <section className="marketplace-section" aria-labelledby="your-space-heading">
          <div className="marketplace-section-head"><div><span>Your space</span><strong id="your-space-heading">A quick look at your Campus Link activity</strong></div></div>
          <section className="cl-data-rail student-data-rail" aria-label="Your Campus Link activity summary">
            <div className="brand"><span>Saved vendors</span><strong>{savedCount}</strong><small>Your shortlist</small></div>
            <div className="accent"><span>Vendor contacts</span><strong>{contactCount}</strong><small>Connections started</small></div>
            <div><span>Your reviews</span><strong>{reviewCount}</strong><small>Feedback shared</small></div>
            <div><span>Verification</span><strong style={{fontSize:18,textTransform:'capitalize'}}>{status.replace('_', ' ')}</strong><small>{status === 'verified' ? 'Identity confirmed' : 'Account remains usable'}</small></div>
          </section>

          <section className="student-insight-grid">
            {hasActivity ? (
              <StudentActivityChart saved={savedCount} reviews={reviewCount} contacts={contactCount} reports={reportCount}/>
            ) : (
              <section className="cl-empty-activity"><div><strong>Your activity will grow with you.</strong><p>Save a vendor, contact someone or leave a review and your Campus Link activity will start appearing here.</p></div></section>
            )}
            <div className="student-quick-panel cl-editorial-surface">
              <div className="panel-heading"><span>Shortcuts</span><strong>Get where you need to go</strong></div>
              <div className="quick-link-grid">
                <Link href="/student/discover" className="quick-link blue"><Search size={21}/><div><strong>Discover</strong><span>Browse approved campus vendors</span></div><ArrowUpRight/></Link>
                <Link href="/student/saved" className="quick-link green"><Bookmark size={21}/><div><strong>Saved</strong><span>Return to vendors you liked</span></div><ArrowUpRight/></Link>
                <Link href="/onboarding/student" className="quick-link light"><BadgeCheck size={21}/><div><strong>Verification</strong><span>Manage your trust status</span></div><ArrowUpRight/></Link>
              </div>
            </div>
          </section>
        </section>
      </section>

      <nav className="bottom-nav"><Link href="/student">Home</Link><Link href="/student/discover">Discover</Link><Link href="/student/saved">Saved</Link><Link href="/onboarding/student">Verify</Link></nav>
    </main>
  )
}
