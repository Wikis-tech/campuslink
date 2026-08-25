import Link from 'next/link'
import { redirect } from 'next/navigation'
import { BadgeCheck, BarChart3, Bookmark, Building2, ImagePlus, MessageCircle, Star } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { ThemeToggle } from '@/components/theme-toggle'

export default async function VendorDashboard() {
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const { data: profile } = await supabase.from('profiles').select('first_name,account_type').eq('id', userId).maybeSingle()
  if (!profile || profile.account_type !== 'vendor') redirect('/dashboard')

  const { data: vendor } = await supabase
    .from('vendor_profiles')
    .select('business_name,slug,verification_status,onboarding_completed_at,average_rating,review_count')
    .eq('id', userId)
    .maybeSingle()

  if (!vendor) {
    return (
      <main className="portal-shell">
        <header className="portal-topbar">
          <Link href="/" className="brand">Campus<span>Link</span></Link>
          <div style={{display:'flex',alignItems:'center',gap:10}}><ThemeToggle compact/><form action="/auth/signout" method="post"><button className="btn btn-ghost">Sign out</button></form></div>
        </header>
        <section className="portal-hero phase45-vendor-hero">
          <div><p className="eyebrow">Vendor workspace</p><h1>Welcome{profile.first_name ? `, ${profile.first_name}` : ''}.</h1><p>Your vendor account is active. Complete your business profile and verification when you are ready; student and vendor data remain completely separate.</p></div>
          <div className="status-card status-pending"><BadgeCheck size={22}/><div><span>Vendor setup</span><strong>not completed</strong></div></div>
        </section>
        <section className="portal-grid">
          <Link href="/onboarding/vendor" className="portal-action primary-action" style={{textDecoration:'none'}}><Building2 size={24}/><div><strong>Set up your business</strong><span>Add business details, services, campus and vendor verification evidence.</span></div></Link>
          <article className="portal-action"><ImagePlus size={24}/><div><strong>Portfolio</strong><span>Portfolio tools unlock after your business profile has been created.</span></div></article>
          <article className="portal-action"><BarChart3 size={24}/><div><strong>Discovery</strong><span>Your business will only become public after vendor verification and campus approval.</span></div></article>
        </section>
      </main>
    )
  }

  const [{ data: campus }, contactResult, saveResult] = await Promise.all([
    supabase.from('vendor_institutions').select('status,institution_id').eq('vendor_id', userId).eq('is_primary', true).maybeSingle(),
    supabase.from('contact_events').select('id', { count: 'exact', head: true }).eq('vendor_id', userId),
    supabase.from('saved_vendors').select('vendor_id', { count: 'exact', head: true }).eq('vendor_id', userId),
  ])

  let school: string | null = null
  if (campus?.institution_id) {
    const { data: institution } = await supabase.from('institutions').select('name').eq('id', campus.institution_id).maybeSingle()
    school = institution?.name || null
  }

  const status = vendor.verification_status || 'pending'
  const setupComplete = Boolean(vendor.onboarding_completed_at)
  const discoverable = setupComplete && status === 'approved' && campus?.status === 'approved'

  return (
    <main className="portal-shell">
      <header className="portal-topbar">
        <Link href="/" className="brand">Campus<span>Link</span></Link>
        <nav style={{display:'flex',alignItems:'center',gap:10}}><Link href="/onboarding/vendor" className="btn btn-ghost">Verification</Link><ThemeToggle compact/><form action="/auth/signout" method="post"><button className="btn btn-ghost">Sign out</button></form></nav>
      </header>

      <section className="portal-hero phase45-vendor-hero">
        <div>
          <p className="eyebrow">Vendor dashboard</p>
          <h1>{vendor.business_name}</h1>
          <p>{discoverable ? `Your business is live for verified discovery around ${school || 'your approved campus'}.` : `${school ? `Primary campus: ${school}. ` : ''}You can use your vendor workspace now; public discovery unlocks only after business verification and campus approval.`}</p>
        </div>
        <div className={`status-card status-${status}`}><BadgeCheck size={22}/><div><span>Vendor verification</span><strong>{setupComplete ? status.replace('_',' ') : 'not completed'}</strong></div></div>
      </section>

      {!setupComplete ? <section className="portal-action primary-action" style={{marginBottom:24}}><BadgeCheck size={24}/><div><strong>Finish vendor verification</strong><span>Complete business details, campus selection and verification evidence. You can return to this dashboard at any time.</span></div><Link href="/onboarding/vendor" className="btn btn-primary">Continue setup</Link></section> : null}

      <section className="cl-data-rail vendor-data-rail" aria-label="Vendor performance summary">
        <div className="brand"><span>Student contacts</span><strong>{contactResult.count || 0}</strong><small>Connections started</small></div>
        <div className="accent"><span>Student saves</span><strong>{saveResult.count || 0}</strong><small>Students who bookmarked you</small></div>
        <div><span>Average rating</span><strong>{Number(vendor.average_rating || 0).toFixed(1)}</strong><small>Across published reviews</small></div>
        <div><span>Reviews</span><strong>{vendor.review_count || 0}</strong><small>Verified student feedback</small></div>
      </section>

      <section className="portal-grid">
        <article className="portal-action primary-action"><Building2 size={24}/><div><strong>Campus visibility</strong><span>{campus?.status === 'approved' ? 'Approved for your selected campus.' : setupComplete ? 'Your selected campus is awaiting review.' : 'Select your campus during vendor setup.'}</span></div></article>
        <Link href={setupComplete ? '/vendor-v2/portfolio' : '/onboarding/vendor'} className="portal-action" style={{textDecoration:'none'}}><ImagePlus size={24}/><div><strong>Portfolio</strong><span>{setupComplete ? 'Add real examples of your work for students to see.' : 'Complete business setup before adding portfolio work.'}</span></div></Link>
        <article className="portal-action"><MessageCircle size={24}/><div><strong>Student enquiries</strong><span>{contactResult.count ? `${contactResult.count} student contact${contactResult.count === 1 ? '' : 's'} recorded.` : 'No student contacts recorded yet.'}</span></div></article>
        <article className="portal-action"><Bookmark size={24}/><div><strong>Saved by students</strong><span>{saveResult.count ? `${saveResult.count} student${saveResult.count === 1 ? '' : 's'} saved your profile.` : 'Your first save will appear here.'}</span></div></article>
        <article className="portal-action"><Star size={24}/><div><strong>Reputation</strong><span>{vendor.review_count ? `${vendor.review_count} review${vendor.review_count === 1 ? '' : 's'} averaging ${Number(vendor.average_rating || 0).toFixed(1)}/5.` : 'Reviews from verified students will appear here.'}</span></div></article>
        <article className="portal-action"><BarChart3 size={24}/><div><strong>Discovery status</strong><span>{discoverable ? 'Live — students at your approved campus can find you.' : 'Not public yet — verification controls are protecting student discovery.'}</span></div></article>
      </section>
    </main>
  )
}
