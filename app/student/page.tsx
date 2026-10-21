import Link from 'next/link'
import { redirect } from 'next/navigation'
import {
  BadgeCheck,
  BookOpen,
  Bookmark,
  Camera,
  ChevronRight,
  Laptop,
  MapPin,
  MessageCircle,
  Package,
  Search,
  Scissors,
  Shapes,
  ShieldCheck,
  Shirt,
  Sparkles,
  Star,
  UtensilsCrossed,
  Wrench,
} from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { DynamicGreeting } from '@/components/dynamic-greeting'
import { StudentMarketplaceHeader } from '@/components/student-marketplace-header'
import { DashboardNotifications } from '@/components/dashboard-notifications'

function CategoryIcon({ name }: { name: string }) {
  const value = name.toLowerCase()
  if (value.includes('hair') || value.includes('beauty') || value.includes('barb')) return <Scissors size={17}/>
  if (value.includes('food') || value.includes('cater') || value.includes('meal')) return <UtensilsCrossed size={17}/>
  if (value.includes('repair') || value.includes('fix')) return <Wrench size={17}/>
  if (value.includes('tech') || value.includes('computer') || value.includes('phone')) return <Laptop size={17}/>
  if (value.includes('tutor') || value.includes('academic') || value.includes('education')) return <BookOpen size={17}/>
  if (value.includes('photo') || value.includes('video')) return <Camera size={17}/>
  if (value.includes('fashion') || value.includes('laundry') || value.includes('cloth')) return <Shirt size={17}/>
  if (value.includes('event') || value.includes('creative')) return <Sparkles size={17}/>
  return <Shapes size={17}/>
}

function vendorIsSafe(vendor: any) {
  if (vendor.marketplace_status === 'active') return true
  return vendor.marketplace_status === 'suspended' && vendor.suspended_until && new Date(vendor.suspended_until) <= new Date()
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

  if (!profile || profile.account_type !== 'student') redirect('/dashboard')

  const [institutionResult, savedResult, categoryResult, verificationResult, smartResult] = await Promise.all([
    profile.institution_id
      ? supabase.from('institutions').select('name,city,state').eq('id', profile.institution_id).maybeSingle()
      : Promise.resolve({ data: null }),
    supabase.from('saved_vendors').select('vendor_id', { count: 'exact' }).eq('student_id', userId),
    supabase.from('categories').select('id,name,slug').eq('is_active', true).order('name').limit(12),
    supabase.from('student_verifications').select('status,submitted_at,review_note').eq('student_id', userId).maybeSingle(),
    profile.institution_id
      ? supabase.rpc('student_smart_search', {
          target_institution: profile.institution_id,
          search_query: '',
          category_slug: null,
          result_limit: 80,
        })
      : Promise.resolve({ data: [] as any[] }),
  ])

  const institution = institutionResult.data
  const school = institution?.name || null
  const location = [institution?.city, institution?.state].filter(Boolean).join(', ')
  const status = profile.student_verification_status || 'pending'
  const verification = verificationResult.data
  const hasVerificationSubmission = Boolean(verification)
  const verificationPending = ['pending', 'under_review'].includes(verification?.status || '')
  const verificationRejected = verification?.status === 'rejected'
  const savedCount = savedResult.count ?? savedResult.data?.length ?? 0
  const categories = categoryResult.data || []

  const smartRows = (smartResult.data || []) as any[]
  const entityRank = new Map<string, number>()
  const vendorRank = new Map<string, number>()
  for (const row of smartRows) {
    const score = Number(row.relevance_score || 0)
    entityRank.set(`${row.entity_type}:${row.entity_id}`, score)
    vendorRank.set(row.vendor_id, Math.max(vendorRank.get(row.vendor_id) || 0, score))
  }

  let vendors: any[] = []
  let products: any[] = []
  let services: any[] = []

  if (profile.institution_id) {
    const { data: campusLinks } = await supabase
      .from('vendor_institutions')
      .select('vendor_id')
      .eq('institution_id', profile.institution_id)
      .eq('status', 'approved')
      .limit(100)

    const campusVendorIds = (campusLinks || []).map((row) => row.vendor_id)

    if (campusVendorIds.length) {
      const { data: candidateVendors } = await supabase
        .from('vendor_profiles')
        .select('id,business_name,slug,description,logo_url,cover_url,average_rating,review_count,marketplace_status,suspended_until')
        .in('id', campusVendorIds)
        .eq('verification_status', 'approved')
        .order('average_rating', { ascending: false })

      vendors = (candidateVendors || []).filter(vendorIsSafe)
      const safeVendorIds = vendors.map((vendor) => vendor.id)

      if (safeVendorIds.length) {
        const [productResult, serviceResult] = await Promise.all([
          supabase
            .from('vendor_products')
            .select('id,vendor_id,name,description,price_ngn,pricing_type,cover_image_url,created_at')
            .in('vendor_id', safeVendorIds)
            .eq('is_active', true)
            .order('created_at', { ascending: false })
            .limit(16),
          supabase
            .from('vendor_services')
            .select('id,vendor_id,name,price_from,description,created_at')
            .in('vendor_id', safeVendorIds)
            .eq('is_active', true)
            .order('created_at', { ascending: false })
            .limit(16),
        ])
        products = productResult.data || []
        services = serviceResult.data || []
      }
    }
  }

  vendors.sort((a, b) => (vendorRank.get(b.id) || 0) - (vendorRank.get(a.id) || 0))
  products.sort((a, b) => (entityRank.get(`product:${b.id}`) || 0) - (entityRank.get(`product:${a.id}`) || 0))
  services.sort((a, b) => (entityRank.get(`service:${b.id}`) || 0) - (entityRank.get(`service:${a.id}`) || 0))

  const vendorMap = new Map(vendors.map((vendor) => [vendor.id, vendor]))
  const popularSearches = ['Phone repair', 'Food', 'Hair & beauty', 'Graphic design']

  const mixedListings = [
    ...products.map((item) => ({
      type: 'product' as const,
      id: item.id,
      vendorId: item.vendor_id,
      name: item.name,
      image: item.cover_image_url,
      href: `/student/products/${item.id}`,
      price: item.pricing_type === 'contact'
        ? 'Ask for price'
        : `${item.pricing_type === 'from' ? 'From ' : ''}₦${Number(item.price_ngn || 0).toLocaleString()}`,
      createdAt: item.created_at,
      score: entityRank.get(`product:${item.id}`) || 0,
    })),
    ...services.map((item) => {
      const vendor = vendorMap.get(item.vendor_id)
      return {
        type: 'service' as const,
        id: item.id,
        vendorId: item.vendor_id,
        name: item.name,
        image: vendor?.cover_url || vendor?.logo_url || null,
        href: `/student/services/${item.id}`,
        price: item.price_from ? `From ₦${Number(item.price_from).toLocaleString()}` : 'Ask vendor',
        createdAt: item.created_at,
        score: entityRank.get(`service:${item.id}`) || 0,
      }
    }),
  ]
    .sort((a, b) => b.score - a.score || new Date(b.createdAt || 0).getTime() - new Date(a.createdAt || 0).getTime())
    .slice(0, 8)

  return (
    <main className="cl-fv-page kv-student-home">
      <StudentMarketplaceHeader
        firstName={profile.first_name}
        schoolName={school}
        verificationStatus={profile.student_verification_status}
      />

      <div className="cl-fv-shell kv-student-shell">
        <div className="kv-student-intro">
          <div>
            <span className="kv-student-eyebrow">Student marketplace</span>
            <DynamicGreeting firstName={profile.first_name} sessionSeed={sessionSeed} role="student" />
            <p>Find what you need around your campus without the clutter.</p>
          </div>
          <span className="kv-campus-pill">
            <MapPin size={14}/>
            {school ? `${school}${location ? ` · ${location}` : ''}` : 'Set your campus'}
          </span>
        </div>

        <DashboardNotifications />

        {status !== 'verified' ? (
          <section className="kv-verification-bar" aria-live="polite">
            <div>
              <BadgeCheck size={17}/>
              <div>
                <strong>
                  {verificationPending
                    ? 'Your Student verification is under review'
                    : verificationRejected
                      ? 'Your Student verification needs attention'
                      : 'Complete your Student verification'}
                </strong>
                <p>
                  {verificationPending
                    ? 'You can keep browsing while Kampivo reviews your submission.'
                    : verificationRejected
                      ? (verification?.review_note || 'Review the feedback and submit updated evidence when ready.')
                      : 'Browsing stays open. Verification unlocks reviews and stronger trust features.'}
                </p>
              </div>
            </div>
            {!hasVerificationSubmission || verificationRejected ? (
              <Link href="/onboarding/student">
                {verificationRejected ? 'Review & resubmit' : 'Verify now'} <ChevronRight size={14}/>
              </Link>
            ) : null}
          </section>
        ) : null}

        <section className="kv-student-hero">
          <div className="kv-student-hero-main">
            <h2>What do you need on campus today?</h2>
            <p>Search approved vendors, services and products around your school. Contact vendors directly when you find the right fit.</p>

            <form className="kv-student-hero-search" action="/student/discover" method="get">
              <Search size={18}/>
              <input
                name="q"
                placeholder="Try “phone repair”, “laundry”, “food”..."
                aria-label="Search Kampivo marketplace"
              />
              <button type="submit">Search Kampivo</button>
            </form>

            <div className="kv-search-chips">
              {popularSearches.map((term) => (
                <Link key={term} href={`/student/discover?q=${encodeURIComponent(term)}`}>{term}</Link>
              ))}
            </div>
          </div>

          <aside className="kv-trust-card">
            <div className="kv-trust-card-head">
              <div>
                <h3>Why this feels safer</h3>
                <p>Trust information stays close to the marketplace, not hidden in a separate dashboard.</p>
              </div>
              <span><ShieldCheck size={18}/></span>
            </div>
            <div className="kv-trust-list">
              <div><BadgeCheck size={16}/><span><strong>Identity checked</strong><small>Visible vendors pass Kampivo identity review.</small></span></div>
              <div><ShieldCheck size={16}/><span><strong>Campus approved</strong><small>Approval is specific to your institution.</small></span></div>
              <div><Star size={16}/><span><strong>Trust is not for sale</strong><small>Paid plans do not buy verification.</small></span></div>
            </div>
          </aside>
        </section>

        {categories.length ? (
          <section className="kv-student-section">
            <div className="kv-section-head">
              <div>
                <h2>Browse by category</h2>
                <p>Start with what you need.</p>
              </div>
              <Link href="/student/discover">See all categories</Link>
            </div>
            <div className="kv-category-grid">
              {categories.slice(0, 6).map((category) => (
                <Link className="kv-category-card" href={`/student/discover?category=${encodeURIComponent(category.slug)}`} key={category.id}>
                  <span><CategoryIcon name={category.name}/></span>
                  <strong>{category.name}</strong>
                </Link>
              ))}
            </div>
          </section>
        ) : null}

        <section className="kv-student-section">
          <div className="kv-section-head">
            <div>
              <h2>Picked for your campus</h2>
              <p>A single useful feed for products and services.</p>
            </div>
            <Link href="/student/discover">Explore all</Link>
          </div>

          {mixedListings.length ? (
            <div className="kv-listing-grid">
              {mixedListings.map((listing) => {
                const vendor = vendorMap.get(listing.vendorId)
                return (
                  <Link href={listing.href} className="kv-listing-card" key={`${listing.type}-${listing.id}`}>
                    <div className="kv-listing-media">
                      {listing.image
                        ? <img src={listing.image} alt={listing.name}/>
                        : <div className="kv-listing-placeholder">{listing.type === 'product' ? <Package size={34}/> : <MessageCircle size={34}/>}</div>}
                      <span className="kv-listing-type">{listing.type === 'product' ? 'Product' : 'Service'}</span>
                      <span className="kv-listing-save" aria-hidden="true"><Bookmark size={14}/></span>
                    </div>
                    <div className="kv-listing-body">
                      <div className="kv-listing-vendor">
                        <span className="kv-listing-avatar">
                          {vendor?.logo_url ? <img src={vendor.logo_url} alt=""/> : (vendor?.business_name || 'V').slice(0, 1)}
                        </span>
                        <span>{vendor?.business_name || 'Campus vendor'}</span>
                        <ShieldCheck size={12}/>
                      </div>
                      <h3>{listing.name}</h3>
                      <div className="kv-listing-meta">
                        <span><Star size={12} fill="currentColor"/> {Number(vendor?.average_rating || 0).toFixed(1)} ({vendor?.review_count || 0})</span>
                        <strong>{listing.price}</strong>
                      </div>
                    </div>
                  </Link>
                )
              })}
            </div>
          ) : (
            <div className="kv-campus-empty">
              <span><Package size={20}/></span>
              <div>
                <strong>No approved listings for {school || 'your campus'} yet.</strong>
                <p>
                  “Picked for your campus” only shows active products and services from identity-approved Vendors
                  that are also approved for your institution. Kampivo does not mix another school’s Vendors into this section.
                </p>
              </div>
              <div className="kv-campus-empty-actions">
                <Link href="/student/discover">Explore your campus <ChevronRight size={13}/></Link>
                <Link href="/student/explore-campuses">Explore other campuses</Link>
              </div>
            </div>
          )}
        </section>

        <section className="kv-student-utility-grid">
          {savedCount > 0 ? (
            <div className="kv-utility-card">
              <div>
                <Bookmark size={18}/>
                <h3>Saved for later</h3>
              </div>
              <p>You have {savedCount} saved vendor{savedCount === 1 ? '' : 's'} ready when you need them.</p>
              <Link href="/student/saved">View saved <ChevronRight size={14}/></Link>
            </div>
          ) : null}

          <div className="kv-utility-card">
            <div>
              <ShieldCheck size={18}/>
              <h3>Need help or feel unsafe?</h3>
            </div>
            <p>Reporting and Safety Centre stay easy to reach without taking over your marketplace home.</p>
            <Link href="/student/safety">Open Safety Centre <ChevronRight size={14}/></Link>
          </div>
        </section>
      </div>
    </main>
  )
}
