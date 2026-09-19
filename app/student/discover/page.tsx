import Link from 'next/link'
import { redirect } from 'next/navigation'
import { Bookmark, Clock3, MapPin, Package, Search, ShieldCheck, SlidersHorizontal, Star, Wrench } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { StudentMarketplaceHeader } from '@/components/student-marketplace-header'
import { getVendorAvailability } from '@/lib/phase5f'
import { toggleSavedVendor } from '../actions'

function vendorIsSafe(vendor: any) {
  if (vendor.marketplace_status === 'active') return true
  return vendor.marketplace_status === 'suspended' && vendor.suspended_until && new Date(vendor.suspended_until) <= new Date()
}

type SmartRow = {
  entity_type: 'vendor' | 'product' | 'service'
  entity_id: string
  vendor_id: string
  relevance_score: number | string
  match_reason: string
}

export default async function DiscoverPage({ searchParams }: { searchParams: Promise<{ q?: string; category?: string; rating?: string; area?: string; open?: string; type?: string; sort?: string; error?: string }> }) {
  const params = await searchParams
  const q = (params.q || '').trim().toLowerCase()
  const category = (params.category || '').trim()
  const minRating = Number(params.rating || 0)
  const area = (params.area || '').trim()
  const openOnly = params.open === '1'
  const resultType = ['all','product','service','vendor'].includes(params.type || '') ? (params.type || 'all') : 'all'
  const sort = ['relevance','rating','newest'].includes(params.sort || '') ? (params.sort || 'relevance') : 'relevance'

  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const { data: profile } = await supabase
    .from('profiles')
    .select('first_name,account_type,institution_id,onboarding_completed_at')
    .eq('id', userId)
    .single()

  if (!profile || profile.account_type !== 'student') redirect('/dashboard')
  if (!profile.onboarding_completed_at || !profile.institution_id) redirect('/onboarding/student')

  const [{ data: institution }, { data: campusLinks }, { data: categories }, { data: saved }, { data: campusLocations }, smartResult] = await Promise.all([
    supabase.from('institutions').select('name').eq('id', profile.institution_id).maybeSingle(),
    supabase.from('vendor_institutions').select('vendor_id').eq('institution_id', profile.institution_id).eq('status', 'approved'),
    supabase.from('categories').select('id,name,slug').eq('is_active', true).order('name'),
    supabase.from('saved_vendors').select('vendor_id').eq('student_id', userId),
    supabase.from('campus_locations').select('id,name,location_type').eq('institution_id', profile.institution_id).eq('is_active', true).order('sort_order').order('name'),
    supabase.rpc('student_smart_search', {
      target_institution: profile.institution_id,
      search_query: q,
      category_slug: category || null,
      result_limit: 120,
    }),
  ])

  const smartRows = ((smartResult.data || []) as SmartRow[])
  const smartReady = !smartResult.error
  const entityRank = new Map<string, { score: number; reason: string }>()
  const vendorRank = new Map<string, number>()
  for (const row of smartRows) {
    const score = Number(row.relevance_score || 0)
    entityRank.set(`${row.entity_type}:${row.entity_id}`, { score, reason: row.match_reason })
    vendorRank.set(row.vendor_id, Math.max(vendorRank.get(row.vendor_id) || 0, score))
  }

  const campusVendorIds = (campusLinks || []).map((row) => row.vendor_id)
  let vendors: any[] = []
  let services: any[] = []
  let products: any[] = []
  let serviceAreas: any[] = []
  let availabilityRows: any[] = []
  let businessHours: any[] = []

  if (campusVendorIds.length) {
    const { data: vendorResult } = await supabase
      .from('vendor_profiles')
      .select('id,business_name,slug,description,location_text,logo_url,cover_url,average_rating,review_count,verification_status,marketplace_status,suspended_until,created_at')
      .in('id', campusVendorIds)
      .eq('verification_status', 'approved')
      .order('average_rating', { ascending: false })

    vendors = (vendorResult || []).filter(vendorIsSafe)
    const safeVendorIds = vendors.map((vendor) => vendor.id)
    if (safeVendorIds.length) {
      const [serviceResult, productResult, areaResult, availabilityResult, hoursResult] = await Promise.all([
        supabase.from('vendor_services').select('id,vendor_id,category_id,name,description,price_from,is_active,created_at').in('vendor_id', safeVendorIds).eq('is_active', true),
        supabase.from('vendor_products').select('id,vendor_id,category_id,name,description,price_ngn,pricing_type,cover_image_url,is_active,created_at').in('vendor_id', safeVendorIds).eq('is_active', true).order('created_at', { ascending: false }),
        supabase.from('vendor_service_areas').select('vendor_id,campus_location_id').in('vendor_id', safeVendorIds),
        supabase.from('vendor_availability').select('vendor_id,manual_status,status_message,back_at,exam_mode_until').in('vendor_id', safeVendorIds),
        supabase.from('vendor_business_hours').select('vendor_id,day_of_week,is_closed,opens_at,closes_at').in('vendor_id', safeVendorIds),
      ])
      services = serviceResult.data || []
      products = productResult.data || []
      serviceAreas = areaResult.data || []
      availabilityRows = availabilityResult.data || []
      businessHours = hoursResult.data || []
    }
  }

  const categoryById = new Map((categories || []).map((item) => [item.id, item]))
  const vendorMap = new Map(vendors.map((vendor) => [vendor.id, vendor]))
  const servicesByVendor = new Map<string, any[]>()
  const productsByVendor = new Map<string, any[]>()
  for (const service of services) {
    const list = servicesByVendor.get(service.vendor_id) || []
    list.push(service)
    servicesByVendor.set(service.vendor_id, list)
  }
  for (const product of products) {
    const list = productsByVendor.get(product.vendor_id) || []
    list.push(product)
    productsByVendor.set(product.vendor_id, list)
  }

  const areaIdsByVendor = new Map<string, Set<string>>()
  for (const row of serviceAreas) {
    const set = areaIdsByVendor.get(row.vendor_id) || new Set<string>()
    set.add(row.campus_location_id)
    areaIdsByVendor.set(row.vendor_id, set)
  }
  const availabilityByVendor = new Map(availabilityRows.map((row) => [row.vendor_id, row]))
  const hoursByVendor = new Map<string, any[]>()
  for (const row of businessHours) {
    const list = hoursByVendor.get(row.vendor_id) || []
    list.push(row)
    hoursByVendor.set(row.vendor_id, list)
  }
  const liveStateByVendor = new Map(vendors.map((vendor) => [vendor.id, getVendorAvailability(availabilityByVendor.get(vendor.id), hoursByVendor.get(vendor.id) || [])]))

  const availabilityBoost = (vendorId: string) => {
    const live = liveStateByVendor.get(vendorId)
    if (live?.isOpen) return 12
    if (live?.code === 'busy') return 3
    return 0
  }

  const vendorMatches = (vendor: any) => {
    const vendorServices = servicesByVendor.get(vendor.id) || []
    const vendorProducts = productsByVendor.get(vendor.id) || []
    const searchable = [vendor.business_name, vendor.description, vendor.location_text, ...vendorServices.map((item) => item.name), ...vendorProducts.map((item) => item.name)].filter(Boolean).join(' ').toLowerCase()
    const matchesSmart = smartReady
      ? (!q || vendorRank.has(vendor.id))
      : (!q || searchable.includes(q))
    const matchesCategory = !category || [...vendorServices, ...vendorProducts].some((item) => categoryById.get(item.category_id)?.slug === category)
    const matchesRating = !minRating || Number(vendor.average_rating || 0) >= minRating
    const vendorAreaIds = areaIdsByVendor.get(vendor.id)
    const matchesArea = !area || !vendorAreaIds?.size || vendorAreaIds.has(area)
    const matchesOpen = !openOnly || liveStateByVendor.get(vendor.id)?.isOpen
    return matchesSmart && matchesCategory && matchesRating && matchesArea && matchesOpen
  }

  let filteredVendors = vendors.filter(vendorMatches)
  if (sort === 'rating') {
    filteredVendors.sort((a,b) => Number(b.average_rating || 0) - Number(a.average_rating || 0) || Number(b.review_count || 0) - Number(a.review_count || 0))
  } else if (sort === 'newest') {
    filteredVendors.sort((a,b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
  } else {
    filteredVendors.sort((a,b) => ((vendorRank.get(b.id) || 0) + availabilityBoost(b.id)) - ((vendorRank.get(a.id) || 0) + availabilityBoost(a.id)))
  }

  const eligibleVendorIds = new Set(filteredVendors.map((vendor) => vendor.id))
  const itemMatches = (type: 'product' | 'service', item: any) => {
    if (!eligibleVendorIds.has(item.vendor_id)) return false
    if (resultType !== 'all' && resultType !== type) return false
    if (category && categoryById.get(item.category_id)?.slug !== category) return false
    if (smartReady && q) return entityRank.has(`${type}:${item.id}`)
    if (!smartReady && q) {
      const vendor = vendorMap.get(item.vendor_id)
      return [item.name, item.description, vendor?.business_name].filter(Boolean).join(' ').toLowerCase().includes(q)
    }
    return true
  }

  const rankedProducts = products.filter((item) => itemMatches('product', item))
  const rankedServices = services.filter((item) => itemMatches('service', item))
  let listings = [
    ...rankedProducts.map((item) => ({ type: 'product' as const, item, vendor: vendorMap.get(item.vendor_id) })),
    ...rankedServices.map((item) => ({ type: 'service' as const, item, vendor: vendorMap.get(item.vendor_id) })),
  ].filter((entry) => entry.vendor)

  listings.sort((a,b) => {
    if (sort === 'rating') return Number(b.vendor?.average_rating || 0) - Number(a.vendor?.average_rating || 0) || Number(b.vendor?.review_count || 0) - Number(a.vendor?.review_count || 0)
    if (sort === 'newest') return new Date(b.item.created_at).getTime() - new Date(a.item.created_at).getTime()
    const aScore = (entityRank.get(`${a.type}:${a.item.id}`)?.score || 0) + availabilityBoost(a.vendor.id)
    const bScore = (entityRank.get(`${b.type}:${b.item.id}`)?.score || 0) + availabilityBoost(b.vendor.id)
    return bScore - aScore
  })
  listings = listings.slice(0, 36)

  const savedSet = new Set((saved || []).map((row) => row.vendor_id))
  const selectedAreaName = (campusLocations || []).find((item:any) => item.id === area)?.name
  const showListings = resultType !== 'vendor'
  const showVendors = resultType === 'all' || resultType === 'vendor'
  const suggestedCategories = (categories || []).slice(0, 6)

  return (
    <main className="cl-student-page">
      <StudentMarketplaceHeader firstName={profile.first_name} schoolName={institution?.name}/>
      <section className="cl-student-shell">
        <div className="student-head">
          <div>
            <div className="cl-safety-note"><Search size={14}/> Phase 5H smart discovery</div>
            <h1>{q ? `Results for “${params.q}”` : 'Explore your campus marketplace.'}</h1>
            <p>Search across Vendor names, Products, Services, categories and descriptions. Results stay restricted to approved businesses for your campus.</p>
          </div>
          <span className="campus-chip"><MapPin size={16}/> {institution?.name || 'Your campus'}</span>
        </div>
        {params.error ? <div className="notice error">{params.error}</div> : null}
        {!smartReady ? <div className="notice error">Smart ranking is temporarily unavailable, so Campus Link is using basic marketplace matching.</div> : null}

        <form className="search-panel phase5f-search-panel" method="get">
          <label className="search-field"><Search size={18}/><input name="q" defaultValue={params.q || ''} placeholder="Try “barbr”, “phone repair”, “braids” or a Vendor name..."/></label>
          <select name="category" defaultValue={category}><option value="">All categories</option>{(categories || []).map((item) => <option key={item.id} value={item.slug}>{item.name}</option>)}</select>
          <select name="area" defaultValue={area}><option value="">Anywhere on campus</option>{(campusLocations || []).map((item:any) => <option key={item.id} value={item.id}>{item.name}</option>)}</select>
          <select name="type" defaultValue={resultType}><option value="all">Everything</option><option value="product">Products</option><option value="service">Services</option><option value="vendor">Vendors</option></select>
          <select name="rating" defaultValue={params.rating || ''}><option value="">Any rating</option><option value="4">4.0+ rating</option><option value="4.5">4.5+ rating</option></select>
          <select name="sort" defaultValue={sort}><option value="relevance">Best match</option><option value="rating">Top rated</option><option value="newest">Newest</option></select>
          <label className="phase5f-open-filter"><input type="checkbox" name="open" value="1" defaultChecked={openOnly}/><Clock3 size={15}/> Available now</label>
          <button className="search-button" type="submit">Search</button>
        </form>

        <div className="result-meta">
          <span>{showListings ? listings.length : 0} listing{listings.length === 1 ? '' : 's'} · {showVendors ? filteredVendors.length : 0} vendor{filteredVendors.length === 1 ? '' : 's'}{selectedAreaName ? ` · ${selectedAreaName}` : ''}</span>
          <span><ShieldCheck size={13}/> Campus approval + safety status enforced · Pro does not buy ranking</span>
        </div>

        {!q && !category ? <div className="service-tags" style={{marginTop:16}}>
          {suggestedCategories.map((item:any) => <Link className="service-tag" key={item.id} href={`/student/discover?category=${encodeURIComponent(item.slug)}`}>{item.name}</Link>)}
        </div> : null}

        {showListings && listings.length ? <section className="cl-student-section" style={{marginTop:20}}>
          <div className="cl-student-section-head"><div><h2>{q ? 'Best matching products & services' : 'Recommended products & services'}</h2><p>Exact matches, close spellings and relevant keywords rank ahead of weaker matches. Available Vendors receive a small relevance boost.</p></div><span className="cl-safety-note"><SlidersHorizontal size={14}/> {sort === 'relevance' ? 'Smart relevance' : sort === 'rating' ? 'Top rated' : 'Newest first'}</span></div>
          <div className="cl-gig-grid">{listings.map(({ type, item, vendor }) => {
            const href = type === 'product' ? `/student/products/${item.id}` : `/student/services/${item.id}`
            const image = type === 'product' ? item.cover_image_url : vendor.cover_url || vendor.logo_url
            const price = type === 'product'
              ? item.pricing_type === 'contact' ? 'Ask for price' : `${item.pricing_type === 'from' ? 'From ' : ''}₦${Number(item.price_ngn || 0).toLocaleString()}`
              : item.price_from ? `From ₦${Number(item.price_from).toLocaleString()}` : 'Ask for price'
            const live = liveStateByVendor.get(vendor.id)
            const reason = entityRank.get(`${type}:${item.id}`)?.reason
            return <Link href={href} className="cl-gig-card" key={`${type}-${item.id}`}>
              <div className="cl-gig-image">{image ? <img src={image} alt={type === 'product' ? item.name : ''}/> : <div className="cl-gig-placeholder">{type === 'product' ? <Package size={38}/> : <Wrench size={38}/>}</div>}<span className="cl-gig-badge">{type === 'product' ? 'Product' : 'Service'}</span>{live ? <span className={`phase5f-availability-badge ${live.code}`}>{live.label}</span> : null}</div>
              <div className="cl-gig-copy">
                <div className="cl-gig-seller"><span className="cl-gig-seller-avatar">{vendor.logo_url ? <img src={vendor.logo_url} alt=""/> : vendor.business_name.slice(0,1)}</span><span>{vendor.business_name}</span><ShieldCheck size={12}/></div>
                <div className="cl-gig-title">{item.name}</div>
                {q && reason ? <div className="cl-safety-note"><Search size={12}/> {reason}</div> : null}
                <div className="cl-gig-rating"><Star size={13} fill="currentColor"/> {Number(vendor.average_rating || 0).toFixed(1)} <span style={{color:'var(--v3-muted)'}}>({vendor.review_count || 0})</span></div>
                <div className="cl-gig-price">Starting at <strong>{price}</strong></div>
              </div>
            </Link>
          })}</div>
        </section> : showListings ? <div className="empty-state"><Search size={34}/><h2>No matching listings yet</h2><p>Try a shorter phrase, another category or turn off “Available now”. Smart search already checks close spellings, Vendor names and related listing text.</p><div className="service-tags" style={{justifyContent:'center'}}>{suggestedCategories.map((item:any) => <Link className="service-tag" key={item.id} href={`/student/discover?category=${encodeURIComponent(item.slug)}`}>{item.name}</Link>)}</div></div> : null}

        {showVendors && filteredVendors.length ? <section className="cl-student-section"><div className="cl-student-section-head"><div><h2>{q ? 'Relevant Vendor storefronts' : 'Trusted Vendor storefronts'}</h2><p>Vendor ranking uses search relevance, reputation and current availability. Subscription tier is deliberately excluded.</p></div></div><div className="cl-vendor-grid-fiverr">{filteredVendors.slice(0,12).map((vendor) => {
          const vendorServices = servicesByVendor.get(vendor.id) || []
          const savedVendor = savedSet.has(vendor.id)
          const live = liveStateByVendor.get(vendor.id)
          const vendorAreas = (campusLocations || []).filter((item:any) => areaIdsByVendor.get(vendor.id)?.has(item.id)).slice(0,3)
          return <article className="cl-vendor-tile" key={vendor.id}><Link href={`/student/vendors/${vendor.slug}`} style={{color:'inherit',textDecoration:'none'}}><div className="cl-vendor-tile-cover">{vendor.cover_url ? <img src={vendor.cover_url} alt=""/> : null}{live ? <span className={`phase5f-availability-badge ${live.code}`}>{live.label}</span> : null}</div><div className="cl-vendor-tile-body"><div className="cl-vendor-tile-top"><span className="cl-vendor-tile-logo">{vendor.logo_url ? <img src={vendor.logo_url} alt=""/> : vendor.business_name.slice(0,1)}</span><div><strong>{vendor.business_name}</strong><div className="cl-safety-note"><ShieldCheck size={13}/> Campus approved</div></div></div><p>{vendor.description || 'Verified Campus Link vendor.'}</p>{vendorAreas.length ? <div className="phase5f-area-tags">{vendorAreas.map((item:any) => <span key={item.id}><MapPin size={11}/>{item.name}</span>)}</div> : null}<div className="service-tags">{vendorServices.slice(0,3).map((service) => <span className="service-tag" key={service.id}>{service.name}</span>)}</div><div className="cl-vendor-meta"><span><Star size={13} fill="currentColor"/> {Number(vendor.average_rating || 0).toFixed(1)} ({vendor.review_count || 0})</span><span className="verified">View storefront</span></div></div></Link><div className="vendor-card-actions" style={{padding:'0 15px 15px'}}><form action={toggleSavedVendor}><input type="hidden" name="vendor_id" value={vendor.id}/><input type="hidden" name="return_to" value="/student/discover"/><button className={`save-btn${savedVendor ? ' saved' : ''}`}><Bookmark size={16} fill={savedVendor ? 'currentColor' : 'none'}/> {savedVendor ? 'Saved' : 'Save'}</button></form></div></article>
        })}</div></section> : null}
      </section>
    </main>
  )
}
