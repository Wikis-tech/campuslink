import Link from 'next/link'
import { redirect } from 'next/navigation'
import {
  BadgeCheck, BarChart3, Building2, CircleDollarSign, Eye, ImagePlus, LayoutDashboard,
  ListChecks, MessageCircle, Package, Plus, ShieldAlert, Sparkles, Star, Store, TrendingUp,
} from 'lucide-react'
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

  const { data: vendor } = await supabase.from('vendor_profiles')
    .select('business_name,slug,description,logo_url,cover_url,whatsapp_number,business_email,verification_status,onboarding_completed_at,average_rating,review_count,marketplace_status,risk_report_count,suspended_until')
    .eq('id', userId).maybeSingle()
  if (!vendor) redirect('/onboarding/vendor')

  const sevenDaysAgo = new Date(); sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 6)
  const [{ data: campus }, contactResult, saveResult, serviceResult, productResult, portfolioResult, { data: entitlementRows }, { data: analyticsRows }] = await Promise.all([
    supabase.from('vendor_institutions').select('status,institution_id').eq('vendor_id', userId).eq('is_primary', true).maybeSingle(),
    supabase.from('contact_events').select('id', { count: 'exact', head: true }).eq('vendor_id', userId),
    supabase.from('saved_vendors').select('vendor_id', { count: 'exact', head: true }).eq('vendor_id', userId),
    supabase.from('vendor_services').select('id', { count: 'exact', head: true }).eq('vendor_id', userId).eq('is_active', true),
    supabase.from('vendor_products').select('id,price_ngn,pricing_type', { count: 'exact' }).eq('vendor_id', userId).eq('is_active', true),
    supabase.from('vendor_portfolio_items').select('id', { count: 'exact', head: true }).eq('vendor_id', userId).eq('is_active', true),
    supabase.rpc('get_my_vendor_entitlements'),
    supabase.from('vendor_analytics_daily').select('search_impressions,profile_views,contact_clicks,whatsapp_clicks,saves').eq('vendor_id', userId).gte('day', sevenDaysAgo.toISOString().slice(0,10)),
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
  const productCount = productResult.count ?? productResult.data?.length ?? 0
  const productsWithoutPrice = (productResult.data || []).filter((p:any) => p.pricing_type !== 'contact' && !p.price_ngn).length
  const analytics = (analyticsRows || []).reduce((acc:any,row:any)=>({
    impressions:acc.impressions+Number(row.search_impressions||0),
    views:acc.views+Number(row.profile_views||0),
    contacts:acc.contacts+Number(row.contact_clicks||0)+Number(row.whatsapp_clicks||0),
    saves:acc.saves+Number(row.saves||0),
  }),{impressions:0,views:0,contacts:0,saves:0})

  const healthChecks = [
    setupComplete,
    verification === 'approved',
    campus?.status === 'approved',
    Boolean(vendor.whatsapp_number),
    Boolean(vendor.logo_url),
    Boolean(vendor.cover_url),
    Boolean(vendor.description && vendor.description.trim().length >= 40),
    (productCount + (serviceResult.count || 0)) > 0,
    (portfolioResult.count || 0) > 0,
  ]
  const healthScore = Math.round((healthChecks.filter(Boolean).length / healthChecks.length) * 100)

  const tasks = [
    !setupComplete ? { icon:<BadgeCheck size={17}/>, title:'Finish business setup', copy:'Complete your business details and verification evidence.', href:'/onboarding/vendor' } : null,
    verification !== 'approved' ? { icon:<BadgeCheck size={17}/>, title:'Complete identity verification', copy:`Current status: ${verification.replace('_',' ')}.`, href:'/onboarding/vendor' } : null,
    campus?.status !== 'approved' ? { icon:<Building2 size={17}/>, title:'Finish campus approval', copy:'Students cannot discover you until your campus is approved.', href:'/onboarding/vendor' } : null,
    !vendor.logo_url ? { icon:<Store size={17}/>, title:'Add a business logo', copy:'A recognizable storefront is easier for students to trust.', href:'/onboarding/vendor' } : null,
    !vendor.cover_url ? { icon:<ImagePlus size={17}/>, title:'Add a cover image', copy:'Show students what your business looks or feels like.', href:'/onboarding/vendor' } : null,
    productCount === 0 ? { icon:<Package size={17}/>, title:'Add your first product', copy:'Show a real item students can open and ask about.', href:'/vendor-v2/products' } : null,
    (portfolioResult.count || 0) === 0 ? { icon:<ImagePlus size={17}/>, title:'Add proof of work', copy:'Portfolio examples help students judge quality before contacting you.', href:'/vendor-v2/portfolio' } : null,
    productsWithoutPrice > 0 ? { icon:<CircleDollarSign size={17}/>, title:`Add pricing to ${productsWithoutPrice} product${productsWithoutPrice===1?'':'s'}`, copy:'Clear prices reduce unnecessary back-and-forth.', href:'/vendor-v2/products' } : null,
    marketplace !== 'active' && !suspensionExpired ? { icon:<ShieldAlert size={17}/>, title:'Marketplace safety review', copy:'Your public visibility is paused while Campus Link reviews the account.', href:'/vendor-v2' } : null,
  ].filter(Boolean) as {icon:React.ReactNode;title:string;copy:string;href:string}[]

  return (
    <main className="v5e-vendor-page">
      <aside className="v5e-sidebar">
        <Link href="/vendor-v2" className="v3-brand">Campus<span>Link</span></Link>
        <nav>
          <Link className="active" href="/vendor-v2"><LayoutDashboard size={17}/> Overview</Link>
          <Link href="/vendor-v2/products"><Package size={17}/> Products</Link>
          <Link href="/vendor-v2/services"><ListChecks size={17}/> Services</Link>
          <Link href="/vendor-v2/portfolio"><ImagePlus size={17}/> Portfolio</Link>
          <Link href="/vendor-v2/analytics"><BarChart3 size={17}/> Analytics</Link>
          <Link href="/vendor-v2/growth"><TrendingUp size={17}/> Growth</Link>
          <Link href="/vendor-v2/billing"><CircleDollarSign size={17}/> Billing</Link>
        </nav>
        <div className="v5e-side-footer"><ThemeToggle compact/><form action="/auth/signout" method="post"><button className="btn btn-ghost" style={{width:'100%'}}>Sign out</button></form></div>
      </aside>

      <section className="v5e-vendor-main">
        <header className="v5e-vendor-header">
          <div><small style={{fontWeight:850,color:'var(--v3-muted)',textTransform:'uppercase',letterSpacing:'.08em'}}>{vendor.business_name}</small><DynamicGreeting firstName={profile.first_name || vendor.business_name} sessionSeed={sessionSeed} role="vendor" /><p>{discoverable ? `Your storefront is live around ${school || 'your campus'}. Here is what needs attention and how students are responding.` : 'Your business workspace is ready. Complete the important items below before public discovery.'}</p></div>
          <div className="v5e-vendor-actions"><Link href="/vendor-v2/products" className="btn btn-primary"><Plus size={16}/> Add product</Link><Link href={`/student/vendors/${vendor.slug}`} className="btn btn-ghost">Preview storefront</Link></div>
        </header>

        {marketplace !== 'active' && !suspensionExpired ? <div className="v5e-alert"><strong>Marketplace visibility paused.</strong> Safety review is separate from your paid plan. You can still manage your business while the case is reviewed.</div> : null}

        <section className="v5e-statusbar">
          <div><span>Marketplace</span><strong>{discoverable ? 'Live' : marketplace.replace('_',' ')}</strong></div>
          <div><span>Identity</span><strong>{verification.replace('_',' ')}</strong></div>
          <div><span>Campus</span><strong>{campus?.status || 'not set'}</strong></div>
          <div><span>Plan</span><strong>{currentTier}</strong></div>
        </section>

        <section className="v5e-dashboard-grid">
          <div>
            <article className="v5e-card">
              <div className="v5e-card-head"><h2>{tasks.length ? `Today's priorities · ${tasks.length}` : 'You are in good shape today'}</h2><Link href="/onboarding/vendor">Business settings</Link></div>
              {tasks.length ? tasks.slice(0,5).map((task,index)=><div className="v5e-todo" key={`${task.title}-${index}`}><span>{task.icon}</span><div><strong>{task.title}</strong><small>{task.copy}</small></div><Link href={task.href}>Open</Link></div>) : <div className="v5e-todo"><span><Sparkles size={17}/></span><div><strong>No urgent setup tasks</strong><small>Keep products, services and portfolio examples current.</small></div><Link href="/vendor-v2/products">Manage</Link></div>}
            </article>

            <article className="v5e-card">
              <div className="v5e-card-head"><h2>Performance · last 7 days</h2><Link href="/vendor-v2/analytics">Open analytics</Link></div>
              <div className="v5e-kpis"><div><span>Impressions</span><strong>{analytics.impressions}</strong></div><div><span>Profile views</span><strong>{analytics.views}</strong></div><div><span>Contacts</span><strong>{analytics.contacts}</strong></div><div><span>Saves</span><strong>{analytics.saves}</strong></div></div>
            </article>

            <article className="v5e-card">
              <div className="v5e-card-head"><h2>Your storefront</h2><Link href={`/student/vendors/${vendor.slug}`}>Preview</Link></div>
              <div className="v5e-storefront-row"><div><strong>Products</strong><small>{productCount} of {productLimit} active listings</small></div><Link href="/vendor-v2/products">Manage</Link></div>
              <div className="v5e-storefront-row"><div><strong>Services</strong><small>{serviceResult.count || 0} of {serviceLimit} active services</small></div><Link href="/vendor-v2/services">Manage</Link></div>
              <div className="v5e-storefront-row"><div><strong>Portfolio</strong><small>{portfolioResult.count || 0} of {portfolioLimit} proof-of-work items</small></div><Link href="/vendor-v2/portfolio">Manage</Link></div>
            </article>
          </div>

          <aside>
            <article className="v5e-card">
              <div className="v5e-card-head"><h2>Business health</h2><span style={{fontSize:12,color:'var(--v3-muted)',fontWeight:800}}>Actionable, not a trust score</span></div>
              <div className="v5e-health-score"><div className="v5e-health-ring" style={{'--score':healthScore} as React.CSSProperties}><strong>{healthScore}%</strong></div><div className="v5e-health-copy"><strong>{healthScore>=85?'Strong setup':healthScore>=60?'Good foundation':'Needs attention'}</strong><small>Based on profile completeness, listings and verification readiness.</small></div></div>
              <div className="v5e-mini-links"><Link href="/onboarding/vendor">Improve profile <span>→</span></Link><Link href="/vendor-v2/analytics">View performance <span>→</span></Link></div>
            </article>

            <article className="v5e-card">
              <div className="v5e-card-head"><h2>Student response</h2></div>
              <div className="v5e-kpis" style={{gridTemplateColumns:'1fr 1fr'}}><div><span>All contacts</span><strong>{contactResult.count || 0}</strong></div><div><span>All saves</span><strong>{saveResult.count || 0}</strong></div><div><span>Rating</span><strong>{Number(vendor.average_rating || 0).toFixed(1)}</strong></div><div><span>Reviews</span><strong>{vendor.review_count || 0}</strong></div></div>
            </article>

            <article className="v5e-card">
              <div className="v5e-card-head"><h2>Trust & safety</h2></div>
              <div className="v5e-storefront-row"><div><strong>{marketplace === 'active' ? 'No active marketplace hold' : `Status: ${marketplace.replace('_',' ')}`}</strong><small>{vendor.risk_report_count ? `${vendor.risk_report_count} unresolved report${vendor.risk_report_count===1?'':'s'} in the current safety window.` : 'Paid plans never override verification or safety.'}</small></div><ShieldAlert size={18}/></div>
            </article>
          </aside>
        </section>
      </section>
    </main>
  )
}
