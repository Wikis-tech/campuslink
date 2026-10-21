import Link from 'next/link'
import { redirect } from 'next/navigation'
import {
  BadgeCheck,
  Building2,
  CircleDollarSign,
  Eye,
  ImagePlus,
  LayoutGrid,
  LogOut,
  Package,
  Plus,
  ShieldAlert,
  ShieldCheck,
  Star,
  Store,
  TrendingUp,
  UserRound,
} from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { DynamicGreeting } from '@/components/dynamic-greeting'
import { VendorWorkspaceSidebar } from '@/components/vendor-workspace-sidebar'
import { calculateVendorStorefrontHealth } from '@/lib/vendor-storefront-health'
import { DashboardNotifications } from '@/components/dashboard-notifications'
import { ThemeToggle } from '@/components/theme-toggle'

function formatNaira(value: number | string | null | undefined, prefix = '') {
  if (value === null || value === undefined || value === '') return 'Ask for price'
  return `${prefix}₦${Number(value || 0).toLocaleString('en-NG')}`
}

export default async function VendorDashboard() {
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')
  const sessionSeed = typeof claimsData?.claims?.session_id === 'string' ? claimsData.claims.session_id : userId

  const { data: profile } = await supabase
    .from('profiles')
    .select('first_name,account_type')
    .eq('id', userId)
    .maybeSingle()

  if (!profile || profile.account_type !== 'vendor') redirect('/dashboard')

  const { data: vendor } = await supabase
    .from('vendor_profiles')
    .select('business_name,slug,description,logo_url,cover_url,whatsapp_number,business_email,verification_status,onboarding_completed_at,average_rating,review_count,marketplace_status,risk_report_count,suspended_until')
    .eq('id', userId)
    .maybeSingle()

  if (!vendor) redirect('/onboarding/vendor')

  const sevenDaysAgo = new Date()
  sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 6)

  const [
    { data: campus },
    contactResult,
    saveResult,
    reviewResult,
    serviceResult,
    productResult,
    portfolioResult,
    { data: entitlementRows },
    { data: analyticsRows },
  ] = await Promise.all([
    supabase
      .from('vendor_institutions')
      .select('status,institution_id')
      .eq('vendor_id', userId)
      .eq('is_primary', true)
      .maybeSingle(),
    supabase.from('contact_events').select('student_id').eq('vendor_id', userId),
    supabase.from('saved_vendors').select('vendor_id', { count: 'exact', head: true }).eq('vendor_id', userId),
    supabase.from('reviews').select('rating', { count: 'exact' }).eq('vendor_id', userId).eq('status', 'published'),
    supabase
      .from('vendor_services')
      .select('id,name,price_from,created_at', { count: 'exact' })
      .eq('vendor_id', userId)
      .eq('is_active', true)
      .order('created_at', { ascending: false }),
    supabase
      .from('vendor_products')
      .select('id,name,cover_image_url,price_ngn,pricing_type,created_at', { count: 'exact' })
      .eq('vendor_id', userId)
      .eq('is_active', true)
      .order('created_at', { ascending: false }),
    supabase
      .from('vendor_portfolio_items')
      .select('id,title,image_url,created_at', { count: 'exact' })
      .eq('vendor_id', userId)
      .eq('is_active', true)
      .order('created_at', { ascending: false }),
    supabase.rpc('get_my_vendor_entitlements'),
    supabase
      .from('vendor_analytics_daily')
      .select('search_impressions,profile_views,contact_clicks,whatsapp_clicks,saves')
      .eq('vendor_id', userId)
      .gte('day', sevenDaysAgo.toISOString().slice(0, 10)),
  ])

  let school: string | null = null
  if (campus?.institution_id) {
    const { data: institution } = await supabase
      .from('institutions')
      .select('name')
      .eq('id', campus.institution_id)
      .maybeSingle()
    school = institution?.name || null
  }

  const setupComplete = Boolean(vendor.onboarding_completed_at)
  const verification = vendor.verification_status || 'pending'
  const marketplace = vendor.marketplace_status || 'active'
  const suspensionExpired =
    marketplace === 'suspended'
    && vendor.suspended_until
    && new Date(vendor.suspended_until) <= new Date()

  const safetyClear = marketplace === 'active' || Boolean(suspensionExpired)
  const campusApproved = campus?.status === 'approved'
  const discoverable = setupComplete && verification === 'approved' && campusApproved && safetyClear

  const entitlement = Array.isArray(entitlementRows) ? entitlementRows[0] : null
  const currentTier = entitlement?.tier === 'pro' ? 'Pro' : 'Free'
  const serviceLimit = Number(entitlement?.entitlements?.service_limit || 5)
  const productLimit = Number(entitlement?.entitlements?.product_limit || 5)
  const portfolioLimit = Number(entitlement?.entitlements?.portfolio_limit || 6)

  const products = productResult.data || []
  const services = serviceResult.data || []
  const portfolio = portfolioResult.data || []
  const productCount = productResult.count ?? products.length
  const activeServiceCount = serviceResult.count ?? services.length
  const portfolioCount = portfolioResult.count ?? portfolio.length

  const uniqueContactCount = new Set(
    (contactResult.data || []).map((row: any) => row.student_id).filter(Boolean),
  ).size

  const publishedReviewCount = reviewResult.count ?? reviewResult.data?.length ?? 0
  const lifetimeRating = publishedReviewCount
    ? (reviewResult.data || []).reduce((sum: number, row: any) => sum + Number(row.rating || 0), 0) / publishedReviewCount
    : 0

  const productsWithoutPrice = products.filter(
    (product: any) => product.pricing_type !== 'contact' && !product.price_ngn,
  ).length

  const analytics = (analyticsRows || []).reduce(
    (acc: any, row: any) => ({
      impressions: acc.impressions + Number(row.search_impressions || 0),
      views: acc.views + Number(row.profile_views || 0),
      contacts: acc.contacts + Number(row.contact_clicks || 0) + Number(row.whatsapp_clicks || 0),
      saves: acc.saves + Number(row.saves || 0),
    }),
    { impressions: 0, views: 0, contacts: 0, saves: 0 },
  )

  const storefrontHealth = calculateVendorStorefrontHealth({
    logoUrl: vendor.logo_url,
    coverUrl: vendor.cover_url,
    description: vendor.description,
    whatsappNumber: vendor.whatsapp_number,
    businessEmail: vendor.business_email,
    activeProductCount: productCount,
    activeServiceCount,
    productsWithoutPrice,
    activePortfolioCount: portfolioCount,
  })

  const tasks = [
    !setupComplete
      ? {
          icon: <BadgeCheck size={17}/>,
          title: 'Finish business setup',
          copy: 'Complete your business details and verification evidence.',
          href: '/onboarding/vendor',
        }
      : null,
    verification !== 'approved'
      ? {
          icon: <BadgeCheck size={17}/>,
          title: 'Complete identity verification',
          copy: `Current status: ${verification.replace('_', ' ')}.`,
          href: '/onboarding/vendor',
        }
      : null,
    !campusApproved
      ? {
          icon: <Building2 size={17}/>,
          title: 'Finish campus approval',
          copy: 'Students cannot discover you at this institution until campus approval is complete.',
          href: '/onboarding/vendor',
        }
      : null,
    !vendor.logo_url
      ? {
          icon: <Store size={17}/>,
          title: 'Add a business logo',
          copy: 'A recognizable storefront is easier for Students to identify.',
          href: '/vendor-v2/profile',
        }
      : null,
    !vendor.cover_url
      ? {
          icon: <ImagePlus size={17}/>,
          title: 'Add a cover image',
          copy: 'Give Students a stronger first impression of your business.',
          href: '/vendor-v2/profile',
        }
      : null,
    productCount === 0
      ? {
          icon: <Package size={17}/>,
          title: 'Add your first product',
          copy: 'Show a real item Students can open and ask about.',
          href: '/vendor-v2/products',
        }
      : null,
    portfolioCount === 0
      ? {
          icon: <ImagePlus size={17}/>,
          title: 'Add proof of work',
          copy: 'Portfolio examples help Students judge quality before contacting you.',
          href: '/vendor-v2/portfolio',
        }
      : null,
    productsWithoutPrice > 0
      ? {
          icon: <CircleDollarSign size={17}/>,
          title: `Add pricing to ${productsWithoutPrice} product${productsWithoutPrice === 1 ? '' : 's'}`,
          copy: 'Clear prices help Students decide faster and reduce unnecessary back-and-forth.',
          href: '/vendor-v2/products',
        }
      : null,
    marketplace !== 'active' && !suspensionExpired
      ? {
          icon: <ShieldAlert size={17}/>,
          title: 'Marketplace safety review',
          copy: 'Public visibility is paused while Kampivo reviews this account.',
          href: '/vendor-v2',
        }
      : null,
  ].filter(Boolean) as { icon: React.ReactNode; title: string; copy: string; href: string }[]

  const nextTask = tasks[0] || null

  const recentListings = [
    ...products.slice(0, 3).map((product: any) => ({
      id: `product-${product.id}`,
      type: 'Product',
      title: product.name,
      image: product.cover_image_url ? `/media/product/${product.id}` : null,
      price:
        product.pricing_type === 'contact'
          ? 'Ask for price'
          : formatNaira(product.price_ngn, product.pricing_type === 'from' ? 'From ' : ''),
      href: '/vendor-v2/products',
      createdAt: product.created_at,
    })),
    ...services.slice(0, 3).map((service: any) => ({
      id: `service-${service.id}`,
      type: 'Service',
      title: service.name,
      image: vendor.cover_url || vendor.logo_url || null,
      price: service.price_from ? formatNaira(service.price_from, 'From ') : 'Ask for price',
      href: '/vendor-v2/services',
      createdAt: service.created_at,
    })),
    ...portfolio.slice(0, 3).map((item: any) => ({
      id: `portfolio-${item.id}`,
      type: 'Portfolio',
      title: item.title,
      image: item.image_url || null,
      price: 'Proof of work',
      href: '/vendor-v2/portfolio',
      createdAt: item.created_at,
    })),
  ]
    .sort(
      (a, b) =>
        new Date(b.createdAt || 0).getTime() - new Date(a.createdAt || 0).getTime(),
    )
    .slice(0, 3)

  const firstInitial = (profile.first_name || vendor.business_name || 'V').slice(0, 1).toUpperCase()

  return (
    <main className="v5e-vendor-page kv-vendor-page">
      <VendorWorkspaceSidebar storefrontHref={vendor.slug ? `/student/vendors/${vendor.slug}` : undefined}/>

      <section className="v5e-vendor-main kv-vendor-main">
        <header className="kv-vendor-topbar">
          <div className="kv-vendor-topbar-left">
            <span className="kv-vendor-mobile-brand"><img src="/brand/logo" alt="Kampivo"/></span>
            <span className="kv-vendor-workspace-pill">Vendor workspace</span>
          </div>
          <div className="kv-vendor-topbar-actions">
            <ThemeToggle compact/>
            <DashboardNotifications compact/>
            <Link href="/vendor-v2/profile" className="kv-vendor-avatar" aria-label="Open business profile">
              {vendor.logo_url ? <img src={vendor.logo_url} alt=""/> : firstInitial}
            </Link>
          </div>
        </header>

        <div className="kv-vendor-content">
          <header className="kv-vendor-intro">
            <div>
              <span className="kv-vendor-eyebrow">Your business</span>
              <DynamicGreeting
                firstName={profile.first_name || vendor.business_name}
                sessionSeed={sessionSeed}
                role="vendor"
              />
              <p>
                {discoverable
                  ? `${vendor.business_name} is live at ${school || 'your approved campus'}.`
                  : `${vendor.business_name} is being prepared for campus discovery.`}
              </p>
            </div>

            <div className="kv-vendor-intro-actions">
              {vendor.slug ? (
                <Link href={`/student/vendors/${vendor.slug}`} className="kv-vendor-btn">
                  <Eye size={16}/> View storefront
                </Link>
              ) : null}
              <Link href="/vendor-v2/listings" className="kv-vendor-btn primary">
                <Plus size={16}/> Add listing
              </Link>
            </div>
          </header>

          {marketplace !== 'active' && !suspensionExpired ? (
            <div className="v5e-alert">
              <strong>Marketplace visibility paused.</strong> Safety review is separate from your paid plan. You can still manage your business while the case is reviewed.
            </div>
          ) : null}

          <section className="kv-vendor-status-strip">
            <div className="kv-vendor-live">
              <span className={discoverable ? 'live' : 'pending'}/>
              {discoverable ? 'Live on campus' : 'Setup not complete'}
            </div>
            <div><span>Identity</span><strong className={verification === 'approved' ? 'ok' : ''}>{verification === 'approved' ? '✓ Approved' : verification.replace('_', ' ')}</strong></div>
            <div><span>Campus</span><strong className={campusApproved ? 'ok' : ''}>{campusApproved ? '✓ Approved' : (campus?.status || 'Not set')}</strong></div>
            <div><span>Plan</span><strong>{currentTier}</strong></div>
          </section>

          <section className="kv-vendor-dashboard-grid">
            <div className="kv-vendor-stack">
              <article className="kv-vendor-card">
                <div className="kv-vendor-card-head">
                  <div>
                    <span className="kv-vendor-label">Your next best move</span>
                    <h2>{nextTask ? 'Keep the storefront moving forward' : 'You are in good shape today'}</h2>
                  </div>
                  <Link href="/vendor-v2/profile">Business settings</Link>
                </div>

                <div className="kv-vendor-next">
                  <div className="kv-vendor-next-icon">
                    {nextTask?.icon || <TrendingUp size={18}/>}
                  </div>
                  <div>
                    <strong>{nextTask?.title || 'Nothing urgent right now'}</strong>
                    <p>{nextTask?.copy || 'Keep Products, Services and Portfolio examples current, then watch how Students respond.'}</p>
                  </div>
                  <Link href={nextTask?.href || '/vendor-v2/listings'}>
                    {nextTask ? 'Open' : 'Manage listings'} →
                  </Link>
                </div>
              </article>

              <article className="kv-vendor-card">
                <div className="kv-vendor-card-head">
                  <div>
                    <span className="kv-vendor-label">Performance</span>
                    <h2>Last 7 days</h2>
                  </div>
                  <Link href="/vendor-v2/analytics">Open analytics</Link>
                </div>

                <div className="kv-vendor-kpis">
                  <div><span>Profile views</span><strong>{analytics.views}</strong><small>Students opened your storefront</small></div>
                  <div><span>Student contacts</span><strong>{analytics.contacts}</strong><small>Contact actions in this period</small></div>
                  <div><span>Saves</span><strong>{analytics.saves}</strong><small>Save actions in this period</small></div>
                  <div><span>Reviews</span><strong>{publishedReviewCount}</strong><small>Published lifetime feedback</small></div>
                </div>
              </article>

              <article className="kv-vendor-card">
                <div className="kv-vendor-card-head">
                  <div>
                    <span className="kv-vendor-label">Your storefront</span>
                    <h2>What Students can discover</h2>
                  </div>
                </div>

                <div className="kv-vendor-storefront-list">
                  <Link href="/vendor-v2/products" className="kv-vendor-store-row">
                    <span className="kv-vendor-thumb"><Package size={18}/></span>
                    <span><strong>Products</strong><small>{productCount} of {productLimit} active listings</small></span>
                    <b>Manage →</b>
                  </Link>
                  <Link href="/vendor-v2/services" className="kv-vendor-store-row">
                    <span className="kv-vendor-thumb"><LayoutGrid size={18}/></span>
                    <span><strong>Services</strong><small>{activeServiceCount} of {serviceLimit} active services</small></span>
                    <b>Manage →</b>
                  </Link>
                  <Link href="/vendor-v2/portfolio" className="kv-vendor-store-row">
                    <span className="kv-vendor-thumb"><ImagePlus size={18}/></span>
                    <span><strong>Portfolio</strong><small>{portfolioCount} of {portfolioLimit} proof-of-work items</small></span>
                    <b>Manage →</b>
                  </Link>
                </div>
              </article>
            </div>

            <aside className="kv-vendor-stack">
              <article className="kv-vendor-card">
                <div className="kv-vendor-card-head">
                  <div>
                    <span className="kv-vendor-label">Business health</span>
                    <h2>{storefrontHealth.label}</h2>
                  </div>
                  <span className="kv-vendor-note">Not a trust score</span>
                </div>

                <div className="kv-vendor-health">
                  <div
                    className="kv-vendor-health-ring"
                    style={{ '--score': storefrontHealth.score } as React.CSSProperties}
                  >
                    <strong>{storefrontHealth.score}%</strong>
                  </div>
                  <div>
                    <strong>{storefrontHealth.score >= 80 ? 'Almost complete' : 'Keep improving'}</strong>
                    <p>Storefront completeness, active listings, clear pricing and portfolio coverage.</p>
                  </div>
                </div>

                {storefrontHealth.improvements.length ? (
                  <div className="kv-vendor-improvements">
                    {storefrontHealth.improvements.slice(0, 3).map((improvement) => (
                      <div key={improvement}><span>✓</span><p>{improvement}</p></div>
                    ))}
                  </div>
                ) : null}
              </article>

              <article className="kv-vendor-card">
                <div className="kv-vendor-card-head">
                  <div>
                    <span className="kv-vendor-label">Student response</span>
                    <h2>Lifetime signals</h2>
                  </div>
                </div>

                <div className="kv-vendor-signals">
                  <div><span>Unique contacts</span><strong>{uniqueContactCount}</strong></div>
                  <div><span>Saves</span><strong>{saveResult.count || 0}</strong></div>
                  <div><span>Rating</span><strong>{lifetimeRating.toFixed(1)}</strong></div>
                  <div><span>Reviews</span><strong>{publishedReviewCount}</strong></div>
                </div>
              </article>

              <article className="kv-vendor-card">
                <div className="kv-vendor-card-head">
                  <div>
                    <span className="kv-vendor-label">Trust & eligibility</span>
                    <h2>{discoverable ? 'Good standing' : 'Action required'}</h2>
                  </div>
                </div>

                <div className="kv-vendor-trust-list">
                  <div><span className={verification === 'approved' ? 'ok' : 'pending'}>{verification === 'approved' ? '✓' : '!'}</span><p>Identity {verification === 'approved' ? 'approved' : verification.replace('_', ' ')}</p></div>
                  <div><span className={campusApproved ? 'ok' : 'pending'}>{campusApproved ? '✓' : '!'}</span><p>{school || 'Campus'} {campusApproved ? 'approved' : (campus?.status || 'not set')}</p></div>
                  <div><span className={safetyClear ? 'ok' : 'pending'}>{safetyClear ? '✓' : '!'}</span><p>Marketplace {safetyClear ? 'good standing' : marketplace.replace('_', ' ')}</p></div>
                </div>

                <p className="kv-vendor-trust-note">
                  Paid plans never override verification, campus approval or marketplace safety.
                </p>
              </article>
            </aside>
          </section>

          <section className="kv-vendor-section">
            <div className="kv-vendor-section-head">
              <div>
                <h2>Recent listings</h2>
                <p>A quick visual check of what Students currently see.</p>
              </div>
              <Link href="/vendor-v2/listings">Manage listings</Link>
            </div>

            {recentListings.length ? (
              <div className="kv-vendor-listing-grid">
                {recentListings.map((item) => (
                  <Link href={item.href} className="kv-vendor-listing-card" key={item.id}>
                    <div className="kv-vendor-listing-media">
                      {item.image
                        ? <img src={item.image} alt={item.title}/>
                        : <div><Store size={26}/><span>{item.type} image</span></div>}
                      <span className="kv-vendor-live-pill">Live</span>
                    </div>
                    <div className="kv-vendor-listing-body">
                      <h3>{item.title}</h3>
                      <p>{item.type}</p>
                      <strong>{item.price}</strong>
                    </div>
                  </Link>
                ))}
              </div>
            ) : (
              <div className="kv-vendor-empty">
                <Store size={22}/>
                <strong>No active listings yet.</strong>
                <p>Add a Product, Service or Portfolio item and it will appear here once active.</p>
                <Link href="/vendor-v2/listings">Add your first listing</Link>
              </div>
            )}
          </section>
        </div>
      </section>
    </main>
  )
}
