import Link from 'next/link'
import { redirect } from 'next/navigation'
import { Bookmark, MapPin, Package, Search, ShieldCheck, Star, Wrench } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { StudentMarketplaceHeader } from '@/components/student-marketplace-header'
import { toggleSavedVendor } from '../actions'

function vendorIsSafe(vendor: any) {
  if (vendor.marketplace_status === 'active') return true
  return vendor.marketplace_status === 'suspended' && vendor.suspended_until && new Date(vendor.suspended_until) <= new Date()
}

export default async function DiscoverPage({ searchParams }: { searchParams: Promise<{ q?: string; category?: string; rating?: string; error?: string }> }) {
  const params = await searchParams
  const q = (params.q || '').trim().toLowerCase()
  const category = (params.category || '').trim()
  const minRating = Number(params.rating || 0)

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

  const [{ data: institution }, { data: campusLinks }, { data: categories }, { data: saved }] = await Promise.all([
    supabase.from('institutions').select('name').eq('id', profile.institution_id).maybeSingle(),
    supabase.from('vendor_institutions').select('vendor_id').eq('institution_id', profile.institution_id).eq('status', 'approved'),
    supabase.from('categories').select('id,name,slug').eq('is_active', true).order('name'),
    supabase.from('saved_vendors').select('vendor_id').eq('student_id', userId),
  ])

  const campusVendorIds = (campusLinks || []).map((row) => row.vendor_id)
  let vendors: any[] = []
  let services: any[] = []
  let products: any[] = []

  if (campusVendorIds.length) {
    const { data: vendorResult } = await supabase
      .from('vendor_profiles')
      .select('id,business_name,slug,description,location_text,logo_url,cover_url,average_rating,review_count,verification_status,marketplace_status,suspended_until')
      .in('id', campusVendorIds)
      .eq('verification_status', 'approved')
      .order('average_rating', { ascending: false })

    vendors = (vendorResult || []).filter(vendorIsSafe)
    const safeVendorIds = vendors.map((vendor) => vendor.id)
    if (safeVendorIds.length) {
      const [serviceResult, productResult] = await Promise.all([
        supabase.from('vendor_services').select('id,vendor_id,category_id,name,description,price_from,is_active').in('vendor_id', safeVendorIds).eq('is_active', true),
        supabase.from('vendor_products').select('id,vendor_id,category_id,name,description,price_ngn,pricing_type,cover_image_url,is_active,created_at').in('vendor_id', safeVendorIds).eq('is_active', true).order('created_at', { ascending: false }),
      ])
      services = serviceResult.data || []
      products = productResult.data || []
    }
  }

  const categoryById = new Map((categories || []).map((item) => [item.id, item]))
  const vendorMap = new Map(vendors.map((vendor) => [vendor.id, vendor]))
  const servicesByVendor = new Map<string, any[]>()
  for (const service of services) {
    const list = servicesByVendor.get(service.vendor_id) || []
    list.push(service)
    servicesByVendor.set(service.vendor_id, list)
  }

  const vendorMatches = (vendor: any) => {
    const vendorServices = servicesByVendor.get(vendor.id) || []
    const searchable = [vendor.business_name, vendor.description, vendor.location_text, ...vendorServices.map((service) => service.name)].filter(Boolean).join(' ').toLowerCase()
    const matchesQ = !q || searchable.includes(q)
    const matchesCategory = !category || vendorServices.some((service) => categoryById.get(service.category_id)?.slug === category)
    const matchesRating = !minRating || Number(vendor.average_rating || 0) >= minRating
    return matchesQ && matchesCategory && matchesRating
  }

  const filteredVendors = vendors.filter(vendorMatches)
  const filteredProducts = products.filter((product) => {
    const vendor = vendorMap.get(product.vendor_id)
    if (!vendor || (minRating && Number(vendor.average_rating || 0) < minRating)) return false
    const searchable = [product.name, product.description, vendor.business_name].filter(Boolean).join(' ').toLowerCase()
    const matchesQ = !q || searchable.includes(q)
    const matchesCategory = !category || categoryById.get(product.category_id)?.slug === category
    return matchesQ && matchesCategory
  })
  const filteredServices = services.filter((service) => {
    const vendor = vendorMap.get(service.vendor_id)
    if (!vendor || (minRating && Number(vendor.average_rating || 0) < minRating)) return false
    const searchable = [service.name, service.description, vendor.business_name].filter(Boolean).join(' ').toLowerCase()
    const matchesQ = !q || searchable.includes(q)
    const matchesCategory = !category || categoryById.get(service.category_id)?.slug === category
    return matchesQ && matchesCategory
  })

  const listings = [
    ...filteredProducts.map((product) => ({ type: 'product' as const, item: product, vendor: vendorMap.get(product.vendor_id) })),
    ...filteredServices.map((service) => ({ type: 'service' as const, item: service, vendor: vendorMap.get(service.vendor_id) })),
  ].filter((entry) => entry.vendor).slice(0, 36)

  const savedSet = new Set((saved || []).map((row) => row.vendor_id))

  return (
    <main className="cl-student-page">
      <StudentMarketplaceHeader firstName={profile.first_name} schoolName={institution?.name}/>
      <section className="cl-student-shell">
        <div className="student-head"><div><h1>{q ? `Results for “${params.q}”` : 'Explore your campus marketplace.'}</h1><p>Browse products and services first, then open the vendor storefront when you want more context, portfolio proof or reviews.</p></div><span className="campus-chip"><MapPin size={16}/> {institution?.name || 'Your campus'}</span></div>
        {params.error ? <div className="notice error">{params.error}</div> : null}

        <form className="search-panel" method="get">
          <label className="search-field"><Search size={18}/><input name="q" defaultValue={params.q || ''} placeholder="Search products, services or vendor names..."/></label>
          <select name="category" defaultValue={category}><option value="">All categories</option>{(categories || []).map((item) => <option key={item.id} value={item.slug}>{item.name}</option>)}</select>
          <select name="rating" defaultValue={params.rating || ''}><option value="">Any rating</option><option value="4">4.0+ rating</option><option value="4.5">4.5+ rating</option></select>
          <button className="search-button" type="submit">Search</button>
        </form>

        <div className="result-meta"><span>{listings.length} listing{listings.length === 1 ? '' : 's'} · {filteredVendors.length} vendor{filteredVendors.length === 1 ? '' : 's'}</span><span><ShieldCheck size={13}/> Identity, campus and safety eligibility enforced</span></div>

        {listings.length ? <section className="cl-student-section" style={{marginTop:20}}>
          <div className="cl-student-section-head"><div><h2>Products & services</h2><p>A Fiverr-style listing view adapted for campus commerce — open the item first, contact the vendor when ready.</p></div></div>
          <div className="cl-gig-grid">{listings.map(({ type, item, vendor }) => {
            const href = type === 'product' ? `/student/products/${item.id}` : `/student/services/${item.id}`
            const image = type === 'product' ? item.cover_image_url : vendor.cover_url || vendor.logo_url
            const price = type === 'product'
              ? item.pricing_type === 'contact' ? 'Ask for price' : `${item.pricing_type === 'from' ? 'From ' : ''}₦${Number(item.price_ngn || 0).toLocaleString()}`
              : item.price_from ? `From ₦${Number(item.price_from).toLocaleString()}` : 'Ask for price'
            return <Link href={href} className="cl-gig-card" key={`${type}-${item.id}`}>
              <div className="cl-gig-image">{image ? <img src={image} alt={type === 'product' ? item.name : ''}/> : <div className="cl-gig-placeholder">{type === 'product' ? <Package size={38}/> : <Wrench size={38}/>}</div>}<span className="cl-gig-badge">{type === 'product' ? 'Product' : 'Service'}</span></div>
              <div className="cl-gig-copy"><div className="cl-gig-seller"><span className="cl-gig-seller-avatar">{vendor.logo_url ? <img src={vendor.logo_url} alt=""/> : vendor.business_name.slice(0,1)}</span><span>{vendor.business_name}</span><ShieldCheck size={12}/></div><div className="cl-gig-title">{item.name}</div><div className="cl-gig-rating"><Star size={13} fill="currentColor"/> {Number(vendor.average_rating || 0).toFixed(1)} <span style={{color:'var(--v3-muted)'}}>({vendor.review_count || 0})</span></div><div className="cl-gig-price">Starting at <strong>{price}</strong></div></div>
            </Link>
          })}</div>
        </section> : <div className="empty-state"><Search size={34}/><h2>No matching listings yet</h2><p>Try another category or keyword. Campus Link only includes listings from vendors that remain verified, approved for your institution and eligible for discovery.</p></div>}

        {filteredVendors.length ? <section className="cl-student-section"><div className="cl-student-section-head"><div><h2>Vendor storefronts</h2><p>Open a business to see all products, services, portfolio proof and reviews together.</p></div></div><div className="cl-vendor-grid-fiverr">{filteredVendors.slice(0,12).map((vendor) => {
          const vendorServices = servicesByVendor.get(vendor.id) || []
          const savedVendor = savedSet.has(vendor.id)
          return <article className="cl-vendor-tile" key={vendor.id}><Link href={`/student/vendors/${vendor.slug}`} style={{color:'inherit',textDecoration:'none'}}><div className="cl-vendor-tile-cover">{vendor.cover_url ? <img src={vendor.cover_url} alt=""/> : null}</div><div className="cl-vendor-tile-body"><div className="cl-vendor-tile-top"><span className="cl-vendor-tile-logo">{vendor.logo_url ? <img src={vendor.logo_url} alt=""/> : vendor.business_name.slice(0,1)}</span><div><strong>{vendor.business_name}</strong><div className="cl-safety-note"><ShieldCheck size={13}/> Campus approved</div></div></div><p>{vendor.description || 'Verified Campus Link vendor.'}</p><div className="service-tags">{vendorServices.slice(0,3).map((service) => <span className="service-tag" key={service.id}>{service.name}</span>)}</div><div className="cl-vendor-meta"><span><Star size={13} fill="currentColor"/> {Number(vendor.average_rating || 0).toFixed(1)} ({vendor.review_count || 0})</span><span className="verified">View storefront</span></div></div></Link><div className="vendor-card-actions" style={{padding:'0 15px 15px'}}><form action={toggleSavedVendor}><input type="hidden" name="vendor_id" value={vendor.id}/><input type="hidden" name="return_to" value="/student/discover"/><button className={`save-btn${savedVendor ? ' saved' : ''}`}><Bookmark size={16} fill={savedVendor ? 'currentColor' : 'none'}/> {savedVendor ? 'Saved' : 'Save'}</button></form></div></article>
        })}</div></section> : null}
      </section>
    </main>
  )
}
