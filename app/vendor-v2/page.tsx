import Link from 'next/link'
import { redirect } from 'next/navigation'
import { BadgeCheck, BarChart3, Bookmark, Building2, CircleDollarSign, ImagePlus, ListChecks, MessageCircle, Package, Star } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { ThemeToggle } from '@/components/theme-toggle'
import { DynamicGreeting } from '@/components/dynamic-greeting'

export default async function VendorDashboard() {
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')
  const sessionSeed = typeof claimsData?.claims?.session_id === 'string' ? claimsData.claims.session_id : userId

  const { data: profile } = await supabase.from('profiles').select('first_name,account_type').eq('id', userId).maybeSingle()
  if (!profile || profile.account_type !== 'vendor') redirect('/dashboard')

  const { data: vendor } = await supabase
    .from('vendor_profiles')
    .select('business_name,slug,verification_status,onboarding_completed_at,average_rating,review_count,marketplace_status,risk_report_count,suspended_until')
    .eq('id', userId)
    .maybeSingle()

  if (!vendor) {
    return (
      <main className="portal-shell">
        <header className="portal-topbar"><Link href="/" className="brand">Campus<span>Link</span></Link><div style={{display:'flex',alignItems:'center',gap:10}}><ThemeToggle compact/><form action="/auth/signout" method="post"><button className="btn btn-ghost">Sign out</button></form></div></header>
        <section className="portal-hero phase45-vendor-hero phase46-vendor-hero"><div><DynamicGreeting firstName={profile.first_name} sessionSeed={sessionSeed} role="vendor" /><p>Your workspace is ready. Set up your business when you’re ready and we’ll keep your student and vendor activity completely separate.</p></div><div className="status-card status-pending"><BadgeCheck size={22}/><div><span>Vendor setup</span><strong>not completed</strong></div></div></section>
        <section className="portal-grid"><Link href="/onboarding/vendor" className="portal-action primary-action" style={{textDecoration:'none'}}><Building2 size={24}/><div><strong>Set up your business</strong><span>Add business details, services, campus and vendor verification evidence.</span></div></Link><article className="portal-action"><ImagePlus size={24}/><div><strong>Portfolio</strong><span>Portfolio tools unlock after your business profile has been created.</span></div></article><article className="portal-action"><BarChart3 size={24}/><div><strong>Discovery</strong><span>Your business will only become public after vendor verification and campus approval.</span></div></article></section>
      </main>
    )
  }

  const [{ data: campus }, contactResult, saveResult, serviceResult, productResult, { data: entitlementRows }] = await Promise.all([
    supabase.from('vendor_institutions').select('status,institution_id').eq('vendor_id', userId).eq('is_primary', true).maybeSingle(),
    supabase.from('contact_events').select('id', { count: 'exact', head: true }).eq('vendor_id', userId),
    supabase.from('saved_vendors').select('vendor_id', { count: 'exact', head: true }).eq('vendor_id', userId),
    supabase.from('vendor_services').select('id', { count: 'exact', head: true }).eq('vendor_id', userId).eq('is_active', true),
    supabase.from('vendor_products').select('id', { count: 'exact', head: true }).eq('vendor_id', userId).eq('is_active', true),
    supabase.rpc('get_my_vendor_entitlements'),
  ])

  let school: string | null = null
  if (campus?.institution_id) {
    const { data: institution } = await supabase.from('institutions').select('name').eq('id', campus.institution_id).maybeSingle()
    school = institution?.name || null
  }

  const status = vendor.verification_status || 'pending'
  const setupComplete = Boolean(vendor.onboarding_completed_at)
  const safetyState = vendor.marketplace_status || 'active'
  const discoverable = setupComplete && status === 'approved' && campus?.status === 'approved' && (safetyState === 'active' || (safetyState === 'suspended' && vendor.suspended_until && new Date(vendor.suspended_until) <= new Date()))
  const entitlement = Array.isArray(entitlementRows) ? entitlementRows[0] : null
  const currentTier = entitlement?.tier === 'pro' ? 'Pro' : 'Free'
  const serviceLimit = Number(entitlement?.entitlements?.service_limit || 5)
  const productLimit = Number(entitlement?.entitlements?.product_limit || 5)

  return (
    <main className="portal-shell">
      <header className="portal-topbar">
        <Link href="/" className="brand">Campus<span>Link</span></Link>
        <nav style={{display:'flex',alignItems:'center',gap:10,flexWrap:'wrap'}}><Link href="/vendor-v2/products" className="btn btn-ghost">Products</Link><Link href="/vendor-v2/services" className="btn btn-ghost">Services</Link><Link href="/vendor-v2/growth" className="btn btn-ghost">Plans & growth</Link><Link href="/onboarding/vendor" className="btn btn-ghost">Verification</Link><ThemeToggle compact/><form action="/auth/signout" method="post"><button className="btn btn-ghost">Sign out</button></form></nav>
      </header>

      <section className="portal-hero phase45-vendor-hero phase46-vendor-hero">
        <div><DynamicGreeting firstName={profile.first_name || vendor.business_name} sessionSeed={sessionSeed} role="vendor" /><p>{discoverable ? `${vendor.business_name} is live around ${school || 'your approved campus'}. Check your activity and keep your products, services and profile fresh.` : `${school ? `${school} is your current campus. ` : ''}Your workspace is active; public discovery unlocks after business verification, campus approval and safety clearance.`}</p></div>
        <div className={`status-card status-${safetyState === 'active' ? status : 'pending'}`}><BadgeCheck size={22}/><div><span>{safetyState === 'active' ? 'Vendor verification' : 'Marketplace safety'}</span><strong>{safetyState === 'active' ? (setupComplete ? status.replace('_',' ') : 'not completed') : safetyState.replace('_',' ')}</strong></div></div>
      </section>

      {safetyState !== 'active' ? <section className="notice error" style={{marginBottom:24}}><strong>Your marketplace visibility is {safetyState.replace('_',' ')}.</strong> {vendor.risk_report_count >= 5 ? `${vendor.risk_report_count} unresolved reports triggered a safety review. ` : ''}You can still manage your business while Campus Link reviews the case. Payment does not override this hold.</section> : null}

      {!setupComplete ? <section className="portal-action primary-action" style={{marginBottom:24}}><BadgeCheck size={24}/><div><strong>Finish vendor verification</strong><span>Complete business details, campus selection and verification evidence. You can return to this dashboard at any time.</span></div><Link href="/onboarding/vendor" className="btn btn-primary">Continue setup</Link></section> : null}

      <section className="cl-data-rail vendor-data-rail" aria-label="Vendor performance summary">
        <div className="brand"><span>Student contacts</span><strong>{contactResult.count || 0}</strong><small>Connections started</small></div>
        <div className="accent"><span>Student saves</span><strong>{saveResult.count || 0}</strong><small>Students who bookmarked you</small></div>
        <div><span>Average rating</span><strong>{Number(vendor.average_rating || 0).toFixed(1)}</strong><small>Across published reviews</small></div>
        <div><span>Reviews</span><strong>{vendor.review_count || 0}</strong><small>Verified student feedback</small></div>
      </section>

      <section className="portal-grid">
        <article className="portal-action primary-action"><Building2 size={24}/><div><strong>Campus visibility</strong><span>{discoverable ? 'Live for students at your approved campus.' : safetyState !== 'active' ? `Hidden while marketplace status is ${safetyState.replace('_',' ')}.` : campus?.status === 'approved' ? 'Campus approved; identity or setup still needs attention.' : setupComplete ? 'Your selected campus is awaiting review.' : 'Select your campus during vendor setup.'}</span></div></article>
        <Link href="/vendor-v2/products" className="portal-action" style={{textDecoration:'none'}}><Package size={24}/><div><strong>Products</strong><span>{productResult.count || 0} of {productLimit} active on your {currentTier} plan. Show individual items students can ask you about.</span></div></Link>
        <Link href="/vendor-v2/services" className="portal-action" style={{textDecoration:'none'}}><ListChecks size={24}/><div><strong>Services</strong><span>{serviceResult.count || 0} of {serviceLimit} active on your {currentTier} plan. Add, pause or organise what students can hire you for.</span></div></Link>
        <Link href={setupComplete ? '/vendor-v2/portfolio' : '/onboarding/vendor'} className="portal-action" style={{textDecoration:'none'}}><ImagePlus size={24}/><div><strong>Portfolio</strong><span>{setupComplete ? 'Add real examples of your work for students to see.' : 'Complete business setup before adding portfolio work.'}</span></div></Link>
        <Link href="/vendor-v2/growth" className="portal-action" style={{textDecoration:'none'}}><CircleDollarSign size={24}/><div><strong>{currentTier} plan · Plans & growth</strong><span>See your limits, compare Pro, and understand why payment never changes verification or safety status.</span></div></Link>
        <article className="portal-action"><MessageCircle size={24}/><div><strong>Student enquiries</strong><span>{contactResult.count ? `${contactResult.count} student contact${contactResult.count === 1 ? '' : 's'} recorded.` : 'No student contacts recorded yet.'}</span></div></article>
        <article className="portal-action"><Bookmark size={24}/><div><strong>Saved by students</strong><span>{saveResult.count ? `${saveResult.count} student${saveResult.count === 1 ? '' : 's'} saved your profile.` : 'Your first save will appear here.'}</span></div></article>
        <article className="portal-action"><Star size={24}/><div><strong>Reputation</strong><span>{vendor.review_count ? `${vendor.review_count} review${vendor.review_count === 1 ? '' : 's'} averaging ${Number(vendor.average_rating || 0).toFixed(1)}/5.` : 'Reviews from verified students will appear here.'}</span></div></article>
      </section>
    </main>
  )
}
