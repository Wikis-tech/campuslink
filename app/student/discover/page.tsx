import Link from 'next/link'
import { redirect } from 'next/navigation'
import { Bookmark, MapPin, Search, ShieldCheck, Star } from 'lucide-react'
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

  const vendorIds = (campusLinks || []).map((row) => row.vendor_id)
  let vendors: any[] = []
  let services: any[] = []

  if (vendorIds.length) {
    const [vendorResult, serviceResult] = await Promise.all([
      supabase
        .from('vendor_profiles')
        .select('id,business_name,slug,description,location_text,logo_url,cover_url,average_rating,review_count,verification_status,marketplace_status,suspended_until')
        .in('id', vendorIds)
        .eq('verification_status', 'approved')
        .order('average_rating', { ascending: false }),
      supabase
        .from('vendor_services')
        .select('id,vendor_id,category_id,name,price_from,is_active')
        .in('vendor_id', vendorIds)
        .eq('is_active', true),
    ])
    vendors = (vendorResult.data || []).filter(vendorIsSafe)
    const safeIds = new Set(vendors.map((vendor) => vendor.id))
    services = (serviceResult.data || []).filter((service) => safeIds.has(service.vendor_id))
  }

  const categoryById = new Map((categories || []).map((item) => [item.id, item]))
  const servicesByVendor = new Map<string, any[]>()
  for (const service of services) {
    const list = servicesByVendor.get(service.vendor_id) || []
    list.push(service)
    servicesByVendor.set(service.vendor_id, list)
  }

  const filtered = vendors.filter((vendor) => {
    const vendorServices = servicesByVendor.get(vendor.id) || []
    const searchable = [vendor.business_name, vendor.description, vendor.location_text, ...vendorServices.map((s) => s.name)].filter(Boolean).join(' ').toLowerCase()
    const matchesQ = !q || searchable.includes(q)
    const matchesCategory = !category || vendorServices.some((service) => categoryById.get(service.category_id)?.slug === category)
    const matchesRating = !minRating || Number(vendor.average_rating || 0) >= minRating
    return matchesQ && matchesCategory && matchesRating
  })

  const savedSet = new Set((saved || []).map((row) => row.vendor_id))

  return (
    <main className="cl-student-page">
      <StudentMarketplaceHeader firstName={profile.first_name} schoolName={institution?.name} />
      <section className="cl-student-shell">
        <div className="student-head"><div><h1>Find the right person for the job.</h1><p>Compare only vendors that are identity verified, campus approved and currently available in your institution's marketplace.</p></div><span className="campus-chip"><MapPin size={16}/> {institution?.name || 'Your campus'}</span></div>
        {params.error ? <div className="notice error">{params.error}</div> : null}

        <form className="search-panel" method="get">
          <label className="search-field"><Search size={18}/><input name="q" defaultValue={params.q || ''} placeholder="Search products, services or vendor names..."/></label>
          <select name="category" defaultValue={category}><option value="">All categories</option>{(categories || []).map((item) => <option key={item.id} value={item.slug}>{item.name}</option>)}</select>
          <select name="rating" defaultValue={params.rating || ''}><option value="">Any rating</option><option value="4">4.0+ rating</option><option value="4.5">4.5+ rating</option></select>
          <button className="search-button" type="submit">Search</button>
        </form>

        <div className="result-meta"><span>{filtered.length} verified vendor{filtered.length === 1 ? '' : 's'} found</span><span><ShieldCheck size={13}/> Campus + safety filters enforced</span></div>

        {filtered.length ? <div className="cl-vendor-grid-fiverr">{filtered.map((vendor) => {
          const vendorServices = servicesByVendor.get(vendor.id) || []
          const savedVendor = savedSet.has(vendor.id)
          const initial = vendor.business_name?.slice(0, 1)?.toUpperCase() || 'V'
          return <article className="cl-vendor-tile" key={vendor.id}>
            <Link href={`/student/vendors/${vendor.slug}`} style={{color:'inherit',textDecoration:'none'}}>
              <div className="cl-vendor-tile-cover">{vendor.cover_url ? <img src={vendor.cover_url} alt=""/> : null}</div>
              <div className="cl-vendor-tile-body">
                <div className="cl-vendor-tile-top"><span className="cl-vendor-tile-logo">{vendor.logo_url ? <img src={vendor.logo_url} alt={`${vendor.business_name} logo`}/> : initial}</span><div><strong>{vendor.business_name}</strong><div className="cl-safety-note"><ShieldCheck size={13}/> Campus approved</div></div></div>
                <p>{vendor.description || 'Verified service provider on your campus.'}</p>
                <div className="service-tags">{vendorServices.slice(0,3).map((service) => <span className="service-tag" key={service.id}>{service.name}</span>)}</div>
                <div className="cl-vendor-meta"><span><Star size={13} fill="currentColor"/> {Number(vendor.average_rating || 0).toFixed(1)} ({vendor.review_count || 0})</span><span className="verified">View profile</span></div>
              </div>
            </Link>
            <div className="vendor-card-actions" style={{padding:'0 15px 15px'}}><form action={toggleSavedVendor}><input type="hidden" name="vendor_id" value={vendor.id}/><input type="hidden" name="return_to" value="/student/discover"/><button className={`save-btn${savedVendor ? ' saved' : ''}`} aria-label={savedVendor ? 'Remove saved vendor' : 'Save vendor'}><Bookmark size={16} fill={savedVendor ? 'currentColor' : 'none'}/> {savedVendor ? 'Saved' : 'Save'}</button></form></div>
          </article>
        })}</div> : <div className="empty-state"><Search size={34}/><h2>No matching vendors yet</h2><p>Try another service, category or keyword. Campus Link intentionally hides vendors who are not approved for your institution or are currently under safety review.</p></div>}
      </section>
    </main>
  )
}
