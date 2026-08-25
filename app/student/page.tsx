import Link from 'next/link'
import { redirect } from 'next/navigation'
import { BadgeCheck, Bookmark, MapPin, Search, ShieldCheck, Star } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'

export default async function StudentDashboard() {
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const { data: profile } = await supabase
    .from('profiles')
    .select('first_name,account_type,institution_id,student_verification_status,onboarding_completed_at')
    .eq('id', userId)
    .single()

  if (!profile || profile.account_type === 'vendor') redirect('/dashboard')
  if (!profile.onboarding_completed_at) redirect('/onboarding/student')

  const [{ data: verification }, institutionResult, savedResult] = await Promise.all([
    supabase.from('student_verifications').select('status,verification_method,submitted_at,review_note').eq('student_id', userId).maybeSingle(),
    profile.institution_id ? supabase.from('institutions').select('name').eq('id', profile.institution_id).maybeSingle() : Promise.resolve({ data: null }),
    supabase.from('saved_vendors').select('vendor_id').eq('student_id', userId),
  ])

  const school = institutionResult.data?.name || null
  const status = verification?.status || profile.student_verification_status || 'pending'
  let featured: any[] = []

  if (profile.institution_id) {
    const { data: campusLinks } = await supabase.from('vendor_institutions').select('vendor_id').eq('institution_id', profile.institution_id).eq('status', 'approved').limit(8)
    const ids = (campusLinks || []).map((row) => row.vendor_id)
    if (ids.length) {
      const { data } = await supabase.from('vendor_profiles').select('id,business_name,slug,logo_url,average_rating,review_count').in('id', ids).eq('verification_status', 'approved').order('average_rating', { ascending: false }).limit(3)
      featured = data || []
    }
  }

  return (
    <main className="student-app">
      <header className="student-nav">
        <Link href="/student" className="student-brand">Campus<span>Link</span></Link>
        <nav className="student-navlinks"><Link href="/student">Dashboard</Link><Link href="/student/discover">Discover</Link><Link href="/student/saved">Saved</Link><form action="/auth/signout" method="post"><button>Sign out</button></form></nav>
      </header>

      <section className="student-shell">
        <div className="student-head">
          <div><h1>Hi{profile.first_name ? `, ${profile.first_name}` : ''}. What do you need today?</h1><p>{school ? `Discover verified people serving ${school}.` : 'Discover verified services around your campus.'}</p></div>
          {school ? <span className="campus-chip"><MapPin size={16}/>{school}</span> : null}
        </div>

        <form className="search-panel" action="/student/discover" method="get" style={{gridTemplateColumns:'1fr auto'}}>
          <label className="search-field"><Search size={18}/><input name="q" placeholder="Search phone repair, tutor, photographer, braids..."/></label>
          <button className="search-button" type="submit">Find a vendor</button>
        </form>

        <section className="portal-grid" style={{marginBottom:28}}>
          <Link href="/student/discover" className="portal-action primary-action" style={{textDecoration:'none'}}><Search size={24}/><div><strong>Discover services</strong><span>Find vendors approved for your school.</span></div></Link>
          <Link href="/student/saved" className="portal-action" style={{textDecoration:'none'}}><Bookmark size={24}/><div><strong>Saved vendors</strong><span>{savedResult.data?.length || 0} saved vendor{savedResult.data?.length === 1 ? '' : 's'}.</span></div></Link>
          <article className="portal-action"><BadgeCheck size={24}/><div><strong>Student verification</strong><span>{status === 'verified' ? 'Verified — you can publish reviews.' : 'Pending — discovery is available, reviews unlock after verification.'}</span></div></article>
        </section>

        <div className="result-meta"><strong style={{color:'#172033'}}>Popular around your campus</strong><Link href="/student/discover">See all vendors</Link></div>
        {featured.length ? <div className="vendor-grid">{featured.map((vendor) => <article className="vendor-card" key={vendor.id}><div className="vendor-cover"></div><div className="vendor-avatar">{vendor.logo_url ? <img src={vendor.logo_url} alt={`${vendor.business_name} logo`}/> : vendor.business_name.slice(0,1)}</div><div className="vendor-card-body"><div className="vendor-card-top"><div><Link className="vendor-name" href={`/student/vendors/${vendor.slug}`}>{vendor.business_name}</Link><div className="verified-line"><ShieldCheck size={14}/> Campus verified</div></div><div className="rating"><Star size={15} fill="currentColor"/> {Number(vendor.average_rating || 0).toFixed(1)}</div></div><div className="vendor-card-actions"><Link className="view-btn" href={`/student/vendors/${vendor.slug}`}>View profile</Link></div></div></article>)}</div> : <div className="empty-state"><ShieldCheck size={34}/><h2>Your campus network is getting ready</h2><p>There are no approved vendors to show yet. As soon as vendors pass verification and campus approval, they will appear here automatically.</p></div>}
      </section>

      <nav className="bottom-nav"><Link href="/student">Home</Link><Link href="/student/discover">Discover</Link><Link href="/student/saved">Saved</Link></nav>
    </main>
  )
}
