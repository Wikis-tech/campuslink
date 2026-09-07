import Link from 'next/link'
import { redirect } from 'next/navigation'
import { BadgeCheck, BarChart3, Bookmark, Building2, CircleDollarSign, ImagePlus, LayoutDashboard, ListChecks, MessageCircle, Package, Plus, ShieldAlert, Star, Store } from 'lucide-react'
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

  if (!vendor) redirect('/onboarding/vendor')

  const [{ data: campus }, contactResult, saveResult, serviceResult, productResult, portfolioResult, { data: entitlementRows }] = await Promise.all([
    supabase.from('vendor_institutions').select('status,institution_id').eq('vendor_id', userId).eq('is_primary', true).maybeSingle(),
    supabase.from('contact_events').select('id', { count: 'exact', head: true }).eq('vendor_id', userId),
    supabase.from('saved_vendors').select('vendor_id', { count: 'exact', head: true }).eq('vendor_id', userId),
    supabase.from('vendor_services').select('id', { count: 'exact', head: true }).eq('vendor_id', userId).eq('is_active', true),
    supabase.from('vendor_products').select('id', { count: 'exact', head: true }).eq('vendor_id', userId).eq('is_active', true),
    supabase.from('vendor_portfolio_items').select('id', { count: 'exact', head: true }).eq('vendor_id', userId).eq('is_active', true),
    supabase.rpc('get_my_vendor_entitlements'),
  ])

  let school: string | null = null
  if (campus?.institution_id) {
    const { data: institution } = await supabase.from('institutions').select('name').eq('id', campus.institution_id).maybeSingle()
    school = institution?.name || null
  }

  const setupComplete = Boolean(vendor.onboarding_completed_at)
  const verification = vendor.verification_status || 'pending'
  const marketplace = vendor.marketplace_status || 'active'
  const suspensionExpired = marketplace === 'suspended' && vendor.suspended_until && new Date(vendor.suspended_until) <= new Date()
  const safetyClear = marketplace === 'active' || Boolean(suspensionExpired)
  const discoverable = setupComplete && verification === 'approved' && campus?.status === 'approved' && safetyClear
  const entitlement = Array.isArray(entitlementRows) ? entitlementRows[0] : null
  const currentTier = entitlement?.tier === 'pro' ? 'Pro' : 'Free'
  const serviceLimit = Number(entitlement?.entitlements?.service_limit || 5)
  const productLimit = Number(entitlement?.entitlements?.product_limit || 5)
  const portfolioLimit = Number(entitlement?.entitlements?.portfolio_limit || 6)
  const tasks = [
    !setupComplete ? { icon:<BadgeCheck size={18}/>, title:'Finish business setup', copy:'Complete your profile and verification evidence.', href:'/onboarding/vendor' } : null,
    verification !== 'approved' ? { icon:<BadgeCheck size={18}/>, title:'Identity verification', copy:`Current status: ${verification.replace('_',' ')}.`, href:'/onboarding/vendor' } : null,
    campus?.status !== 'approved' ? { icon:<Building2 size={18}/>, title:'Campus approval', copy:'Your business needs campus clearance before students can discover it.', href:'/onboarding/vendor' } : null,
    marketplace !== 'active' && !suspensionExpired ? { icon:<ShieldAlert size={18}/>, title:'Marketplace safety review', copy:`Your visibility is ${marketplace.replace('_',' ')}${vendor.risk_report_count ? ` after ${vendor.risk_report_count} unresolved report${vendor.risk_report_count === 1 ? '' : 's'}` : ''}.`, href:'/vendor-v2' } : null,
    (productResult.count || 0) === 0 ? { icon:<Package size={18}/>, title:'Add your first product', copy:'Show students an individual item they can ask you about.', href:'/vendor-v2/products' } : null,
  ].filter(Boolean) as {icon:React.ReactNode;title:string;copy:string;href:string}[]

  return (
    <main className="v3-vendor-page">
      <div className="v3-vendor-shell">
        <aside className="v3-vendor-sidebar">
          <Link href="/vendor-v2" className="v3-brand">Campus<span>Link</span></Link>
          <nav><Link className="active" href="/vendor-v2"><LayoutDashboard size={17}/> Overview</Link><Link href="/vendor-v2/products"><Package size={17}/> Products</Link><Link href="/vendor-v2/services"><ListChecks size={17}/> Services</Link><Link href="/vendor-v2/portfolio"><ImagePlus size={17}/> Portfolio</Link><Link href="/vendor-v2/growth"><BarChart3 size={17}/> Growth</Link><Link href="/vendor-v2/growth"><CircleDollarSign size={17}/> Plan & billing</Link></nav>
          <div style={{marginTop:'auto',display:'grid',gap:10}}><ThemeToggle compact/><form action="/auth/signout" method="post"><button className="btn btn-ghost" style={{width:'100%'}}>Sign out</button></form></div>
        </aside>

        <section className="v3-vendor-content">
          <header className="v3-vendor-top"><div><small style={{textTransform:'uppercase',letterSpacing:'.09em',fontWeight:850,color:'var(--v3-muted)'}}>{vendor.business_name}</small><DynamicGreeting firstName={profile.first_name || vendor.business_name} sessionSeed={sessionSeed} role="vendor" /><p>{discoverable ? `You are live for students around ${school || 'your campus'}. Keep listings fresh and watch what gets attention.` : 'Your business workspace is active. The status strip below shows exactly what needs attention before public discovery.'}</p></div><div style={{display:'flex',gap:10,flexWrap:'wrap'}}><Link href="/vendor-v2/products" className="btn btn-primary"><Plus size={16}/> Add product</Link><Link href="/vendor-v2/services" className="btn btn-ghost"><Plus size={16}/> Add service</Link></div></header>

          <section className="v3-status-ribbon" aria-label="Business status">
            <div className={`v3-status-cell ${discoverable ? 'good' : 'warn'}`}><span>Marketplace</span><strong>{discoverable ? 'Live' : marketplace.replace('_',' ')}</strong></div>
            <div className={`v3-status-cell ${verification === 'approved' ? 'good' : 'warn'}`}><span>Identity</span><strong>{verification.replace('_',' ')}</strong></div>
            <div className={`v3-status-cell ${campus?.status === 'approved' ? 'good' : 'warn'}`}><span>Campus</span><strong>{campus?.status || 'not set'}</strong></div>
            <div className="v3-status-cell"><span>Plan</span><strong>{currentTier}</strong></div>
          </section>

          <div className="v3-quick-add"><Link href="/vendor-v2/products"><Package size={19}/> Add a product</Link><Link href="/vendor-v2/services"><ListChecks size={19}/> Add a service</Link><Link href="/vendor-v2/portfolio"><ImagePlus size={19}/> Add portfolio work</Link></div>

          <section className="v3-vendor-grid">
            <div style={{display:'grid',gap:20}}>
              <article className="v3-task-card"><div className="v3-card-head"><h2>{tasks.length ? 'What needs attention' : 'Everything important is in good shape'}</h2><Link href="/onboarding/vendor">Business settings</Link></div>{tasks.length ? tasks.slice(0,5).map((task,index)=><div className="v3-task-row" key={`${task.title}-${index}`}><span>{task.icon}</span><div><strong>{task.title}</strong><small>{task.copy}</small></div><Link href={task.href}>Open</Link></div>) : <div className="v3-task-row"><span><BadgeCheck size={18}/></span><div><strong>Your storefront is ready</strong><small>Keep products, services and portfolio examples current.</small></div><Link href="/vendor-v2/products">Manage</Link></div>}</article>

              <article className="v3-task-card"><div className="v3-card-head"><h2>Your storefront</h2><Link href={`/student/vendors/${vendor.slug}`}>Preview profile</Link></div><div className="v3-task-row"><span><Package size={18}/></span><div><strong>Products</strong><small>{productResult.count || 0} of {productLimit} active. Individual items students can open and ask about.</small></div><Link href="/vendor-v2/products">Manage</Link></div><div className="v3-task-row"><span><ListChecks size={18}/></span><div><strong>Services</strong><small>{serviceResult.count || 0} of {serviceLimit} active. What students can hire you to do.</small></div><Link href="/vendor-v2/services">Manage</Link></div><div className="v3-task-row"><span><ImagePlus size={18}/></span><div><strong>Portfolio</strong><small>{portfolioResult.count || 0} of {portfolioLimit} items. Proof of your work and quality.</small></div><Link href="/vendor-v2/portfolio">Manage</Link></div></article>
            </div>

            <div style={{display:'grid',gap:20,alignContent:'start'}}>
              <article className="v3-task-card"><div className="v3-card-head"><h2>Student activity</h2><Link href="/vendor-v2/growth">View growth</Link></div><div className="v3-metric-grid"><div className="v3-metric"><span>Contacts</span><strong>{contactResult.count || 0}</strong></div><div className="v3-metric"><span>Saves</span><strong>{saveResult.count || 0}</strong></div><div className="v3-metric"><span>Rating</span><strong>{Number(vendor.average_rating || 0).toFixed(1)}</strong></div><div className="v3-metric"><span>Reviews</span><strong>{vendor.review_count || 0}</strong></div></div></article>
              <article className="v3-task-card"><div className="v3-card-head"><h2>Trust & safety</h2></div><div className="v3-task-row"><span><ShieldAlert size={18}/></span><div><strong>{marketplace === 'active' ? 'No active marketplace hold' : `Status: ${marketplace.replace('_',' ')}`}</strong><small>{vendor.risk_report_count ? `${vendor.risk_report_count} unresolved report${vendor.risk_report_count === 1 ? '' : 's'} currently counted in the safety window.` : 'Reports are reviewed separately from your paid plan and verification.'}</small></div></div></article>
            </div>
          </section>
        </section>
      </div>

      <nav className="v3-mobile-nav"><Link className="active" href="/vendor-v2"><LayoutDashboard size={17}/><br/>Home</Link><Link href="/vendor-v2/products"><Package size={17}/><br/>Products</Link><Link href="/vendor-v2/services"><Store size={17}/><br/>Services</Link><Link href="/vendor-v2/growth"><BarChart3 size={17}/><br/>Growth</Link></nav>
    </main>
  )
}
