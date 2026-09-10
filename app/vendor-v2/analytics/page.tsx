import Link from 'next/link'
import { redirect } from 'next/navigation'
import { BarChart3, Eye, MessageCircle, MousePointerClick, Search, Sparkles, TrendingUp } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { VendorAnalyticsChart } from '@/components/vendor-analytics-chart'
import { VendorWorkspaceSidebar } from '@/components/vendor-workspace-sidebar'

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
    supabase.from('vendor_profiles').select('business_name,average_rating,review_count,slug').eq('id', userId).maybeSingle(),
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
    acc.listingViews += Number(row.service_views || 0)
    acc.portfolioViews += Number(row.portfolio_views || 0)
    return acc
  }, { impressions:0, profileViews:0, contacts:0, saves:0, listingViews:0, portfolioViews:0 })

  const profileRate = totals.impressions > 0 ? (totals.profileViews / totals.impressions) * 100 : 0
  const contactRate = totals.profileViews > 0 ? (totals.contacts / totals.profileViews) * 100 : 0
  const saveRate = totals.profileViews > 0 ? (totals.saves / totals.profileViews) * 100 : 0
  const chartData = rows.map((row: any) => ({ day: formatDay(row.day), impressions:Number(row.search_impressions||0), profileViews:Number(row.profile_views||0), contacts:Number(row.contact_clicks||0)+Number(row.whatsapp_clicks||0) }))
  const sortedListings = [...(listings || [])].sort((a:any,b:any)=>Number(b.contacts||0)-Number(a.contacts||0) || Number(b.views||0)-Number(a.views||0))
  const topListing = sortedListings[0]

  let insight = 'Your analytics will become more useful as students discover and contact your business.'
  if (totals.impressions > 0 && profileRate < 10) insight = 'Students are seeing you, but relatively few are opening your profile. Stronger product images, titles and pricing may help.'
  else if (totals.profileViews > 0 && contactRate < 8) insight = 'Students are opening your profile, but contact intent is still low. Improve trust signals, pricing clarity and portfolio proof.'
  else if (totals.contacts > 0) insight = 'Students are moving from discovery to contact. Keep your strongest listings current and respond quickly when enquiries arrive.'

  return <main className="v5e-page">
    <VendorWorkspaceSidebar storefrontHref={vendor.slug ? `/student/vendors/${vendor.slug}` : undefined}/>
    <section className="v5e-content vendor-analytics-content">
      <header className="v5e-topbar vendor-section-header">
        <div><span className="v5e-section-label">Analytics</span><h1>Understand what students respond to.</h1><p>See how your storefront moves from campus visibility to real contact intent, without exposing student identities.</p></div>
        <div className="v5e-range">{[7,30,90].map((value)=><Link key={value} className={days===value?'active':''} aria-disabled={value>maxDays} href={value<=maxDays?`/vendor-v2/analytics?range=${value}`:'#'}>{value}D{value>maxDays?<span> Pro</span>:null}</Link>)}</div>
      </header>

      <section className="analytics-insight-strip"><Sparkles size={18}/><div><span>Campus Link insight</span><strong>{insight}</strong></div></section>

      <section className="analytics-metric-strip">
        <article><div><Search size={17}/><span>Impressions</span></div><strong>{totals.impressions.toLocaleString()}</strong><small>Appeared in student discovery</small></article>
        <article><div><Eye size={17}/><span>Profile views</span></div><strong>{totals.profileViews.toLocaleString()}</strong><small>{profileRate.toFixed(1)}% of impressions</small></article>
        <article><div><MessageCircle size={17}/><span>Contacts</span></div><strong>{totals.contacts.toLocaleString()}</strong><small>{contactRate.toFixed(1)}% of profile views</small></article>
        <article><div><MousePointerClick size={17}/><span>Saves</span></div><strong>{totals.saves.toLocaleString()}</strong><small>{saveRate.toFixed(1)}% of profile views</small></article>
      </section>

      <section className="analytics-layout">
        <article className="v5e-panel analytics-chart-card">
          <div className="v5e-panel-head"><div><small>Visibility trend</small><h2>Discovery → profile → contact</h2></div><span>{tier} · {days} days</span></div>
          {chartData.length ? <VendorAnalyticsChart data={chartData}/> : <div className="v5e-empty"><BarChart3 size={30}/><strong>No activity in this period yet.</strong><p>When students discover your products, services or profile, aggregate activity will appear here.</p></div>}
        </article>

        <aside className="v5e-panel analytics-funnel-card">
          <div className="v5e-panel-head"><div><small>Discovery funnel</small><h2>Where attention becomes intent</h2></div></div>
          <div className="analytics-funnel-line"><span>Search appearances</span><strong>{totals.impressions.toLocaleString()}</strong></div>
          <div className="analytics-funnel-bar"><span style={{width:'100%'}}/></div>
          <div className="analytics-funnel-line"><span>Profile views</span><strong>{totals.profileViews.toLocaleString()}</strong></div>
          <div className="analytics-funnel-bar"><span style={{width:`${Math.min(profileRate,100)}%`}}/></div>
          <div className="analytics-funnel-line"><span>Contacts</span><strong>{totals.contacts.toLocaleString()}</strong></div>
          <div className="analytics-funnel-bar"><span style={{width:`${Math.min(contactRate,100)}%`}}/></div>
          <p>These are aggregate business signals. Vendors never see which individual student viewed them.</p>
        </aside>
      </section>

      <section className="analytics-bottom-grid">
        <article className="v5e-panel">
          <div className="v5e-panel-head"><div><small>Listing performance</small><h2>What students are responding to</h2></div><span>{(listings||[]).length} tracked</span></div>
          {sortedListings.length ? <div className="analytics-listings">{sortedListings.slice(0,8).map((item:any,index:number)=><div className="analytics-listing-row" key={`${item.listing_type}-${item.listing_id}`}><span className="analytics-rank">{index+1}</span><div><strong>{item.listing_name}</strong><small>{item.listing_type}</small></div><div><strong>{Number(item.views||0).toLocaleString()}</strong><small>views</small></div><div><strong>{Number(item.contacts||0).toLocaleString()}</strong><small>contacts</small></div></div>)}</div> : <div className="v5e-empty compact"><Sparkles size={24}/><strong>No listing activity yet.</strong><p>Add products and services, then share your storefront and let Campus Link discovery begin working.</p></div>}
        </article>

        <aside className="v5e-panel analytics-highlight-card">
          <div className="v5e-panel-head"><div><small>Best performer</small><h2>{topListing ? topListing.listing_name : 'No leader yet'}</h2></div><TrendingUp size={20}/></div>
          {topListing ? <><strong className="analytics-big-number">{Number(topListing.contacts||0).toLocaleString()}</strong><span>contact actions</span><div className="analytics-mini-stats"><div><strong>{Number(topListing.views||0).toLocaleString()}</strong><small>views</small></div><div><strong>{Number(topListing.impressions||0).toLocaleString()}</strong><small>impressions</small></div></div></> : <p>Your strongest product or service will appear here once there is enough activity.</p>}
        </aside>
      </section>

      {tier === 'Free' ? <section className="v5e-upgrade"><div><small>Free analytics</small><h2>Your 7-day view stays useful.</h2><p>Pro unlocks longer history and deeper growth tools. Payment never changes verification or trust status.</p></div><Link href="/vendor-v2/growth">Compare Pro</Link></section> : null}
    </section>
  </main>
}
