import Link from 'next/link'
import { redirect } from 'next/navigation'
import { ArrowLeft, BarChart3, Eye, MessageCircle, MousePointerClick, Package, Search, Sparkles, Store, TrendingUp } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { ThemeToggle } from '@/components/theme-toggle'
import { VendorAnalyticsChart } from '@/components/vendor-analytics-chart'

function formatDay(value: string) {
  return new Intl.DateTimeFormat('en-NG', { month: 'short', day: 'numeric' }).format(new Date(`${value}T12:00:00`))
}

export default async function VendorAnalyticsPage({ searchParams }: { searchParams: Promise<{ range?: string }> }) {
  const params = await searchParams
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const [{ data: profile }, { data: vendor }, { data: entitlementRows }] = await Promise.all([
    supabase.from('profiles').select('account_type').eq('id', userId).maybeSingle(),
    supabase.from('vendor_profiles').select('business_name,average_rating,review_count').eq('id', userId).maybeSingle(),
    supabase.rpc('get_my_vendor_entitlements'),
  ])
  if (!profile || profile.account_type !== 'vendor') redirect('/dashboard')
  if (!vendor) redirect('/onboarding/vendor')

  const entitlement = Array.isArray(entitlementRows) ? entitlementRows[0] : null
  const tier = entitlement?.tier === 'pro' ? 'Pro' : 'Free'
  const maxDays = Math.max(7, Number(entitlement?.entitlements?.analytics_days || 7))
  const requested = Number(params.range || 7)
  const days = [7, 30, 90].includes(requested) ? Math.min(requested, maxDays) : 7
  const start = new Date(); start.setDate(start.getDate() - (days - 1)); const startDay = start.toISOString().slice(0,10)

  const [{ data: daily }, { data: listings }] = await Promise.all([
    supabase.from('vendor_analytics_daily').select('*').eq('vendor_id', userId).gte('day', startDay).order('day'),
    supabase.rpc('get_my_listing_performance', { requested_days: days }),
  ])

  const rows = daily || []
  const totals = rows.reduce((acc: any, row: any) => {
    acc.impressions += Number(row.search_impressions || 0)
    acc.profileViews += Number(row.profile_views || 0)
    acc.contacts += Number(row.contact_clicks || 0) + Number(row.whatsapp_clicks || 0)
    acc.saves += Number(row.saves || 0)
    acc.productServiceViews += Number(row.service_views || 0)
    acc.portfolioViews += Number(row.portfolio_views || 0)
    return acc
  }, { impressions:0, profileViews:0, contacts:0, saves:0, productServiceViews:0, portfolioViews:0 })

  const ctr = totals.impressions > 0 ? (totals.profileViews / totals.impressions) * 100 : 0
  const contactRate = totals.profileViews > 0 ? (totals.contacts / totals.profileViews) * 100 : 0
  const chartData = rows.map((row: any) => ({ day: formatDay(row.day), impressions:Number(row.search_impressions||0), profileViews:Number(row.profile_views||0), contacts:Number(row.contact_clicks||0)+Number(row.whatsapp_clicks||0) }))

  return (
    <main className="v5e-page">
      <aside className="v5e-sidebar">
        <Link href="/vendor-v2" className="v3-brand">Campus<span>Link</span></Link>
        <nav>
          <Link href="/vendor-v2"><Store size={17}/> Overview</Link>
          <Link className="active" href="/vendor-v2/analytics"><BarChart3 size={17}/> Analytics</Link>
          <Link href="/vendor-v2/products"><Package size={17}/> Products</Link>
          <Link href="/vendor-v2/growth"><TrendingUp size={17}/> Growth</Link>
        </nav>
        <div className="v5e-side-footer"><ThemeToggle compact/><Link href="/vendor-v2/billing">Plan & billing</Link></div>
      </aside>

      <section className="v5e-content">
        <header className="v5e-topbar">
          <div><Link href="/vendor-v2" className="v5e-back"><ArrowLeft size={15}/> Overview</Link><h1>Performance</h1><p>See what students notice, open and contact — without exposing student identities.</p></div>
          <div className="v5e-range">{[7,30,90].map((value)=><Link key={value} className={days===value?'active':''} aria-disabled={value>maxDays} href={value<=maxDays?`/vendor-v2/analytics?range=${value}`:'#'}>{value}D{value>maxDays?<span> Pro</span>:null}</Link>)}</div>
        </header>

        <section className="v5e-summary">
          <article><span><Search size={17}/> Search impressions</span><strong>{totals.impressions.toLocaleString()}</strong><small>Times your listings appeared</small></article>
          <article><span><Eye size={17}/> Profile views</span><strong>{totals.profileViews.toLocaleString()}</strong><small>{ctr.toFixed(1)}% from impressions</small></article>
          <article><span><MessageCircle size={17}/> Contact intent</span><strong>{totals.contacts.toLocaleString()}</strong><small>{contactRate.toFixed(1)}% of profile views</small></article>
          <article><span><MousePointerClick size={17}/> Saves</span><strong>{totals.saves.toLocaleString()}</strong><small>Students who bookmarked you</small></article>
        </section>

        <section className="v5e-main-grid">
          <article className="v5e-panel v5e-chart-panel">
            <div className="v5e-panel-head"><div><small>Visibility over time</small><h2>How students move toward contact</h2></div><span>{tier} plan · {days} days</span></div>
            {chartData.length ? <VendorAnalyticsChart data={chartData}/> : <div className="v5e-empty"><BarChart3 size={30}/><strong>Your analytics will build as students discover you.</strong><p>Profile views, search appearances and contact intent will appear here automatically.</p></div>}
          </article>

          <aside className="v5e-panel v5e-funnel">
            <div className="v5e-panel-head"><div><small>Discovery funnel</small><h2>From visibility to enquiry</h2></div></div>
            <div className="v5e-funnel-step"><span>1</span><div><strong>{totals.impressions.toLocaleString()}</strong><small>Search appearances</small></div></div>
            <div className="v5e-funnel-step"><span>2</span><div><strong>{totals.profileViews.toLocaleString()}</strong><small>Profile views</small></div></div>
            <div className="v5e-funnel-step"><span>3</span><div><strong>{totals.contacts.toLocaleString()}</strong><small>Contact actions</small></div></div>
          </aside>
        </section>

        <section className="v5e-panel">
          <div className="v5e-panel-head"><div><small>Listing performance</small><h2>What is getting attention</h2></div><span>{(listings||[]).length} tracked listings</span></div>
          {(listings||[]).length ? <div className="v5e-table"><div className="v5e-table-row head"><span>Listing</span><span>Views</span><span>Impressions</span><span>Contacts</span></div>{(listings||[]).map((item:any)=><div className="v5e-table-row" key={`${item.listing_type}-${item.listing_id}`}><span><strong>{item.listing_name}</strong><small>{item.listing_type}</small></span><span>{Number(item.views||0).toLocaleString()}</span><span>{Number(item.impressions||0).toLocaleString()}</span><span>{Number(item.contacts||0).toLocaleString()}</span></div>)}</div> : <div className="v5e-empty compact"><Sparkles size={24}/><strong>No listing activity yet.</strong><p>As students open products and services, their aggregate performance will appear here.</p></div>}
        </section>

        {tier === 'Free' ? <section className="v5e-upgrade"><div><small>Free analytics</small><h2>You have the essential 7-day view.</h2><p>Pro unlocks longer history and deeper growth insights. Verification and trust remain completely separate from payment.</p></div><Link href="/vendor-v2/growth">Compare Pro</Link></section> : null}
      </section>
    </main>
  )
}
