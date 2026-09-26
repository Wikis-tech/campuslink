import Link from 'next/link'
import { redirect } from 'next/navigation'
import { Building2, Globe2, MapPin, Search, ShieldCheck, Star } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { createAdminClient } from '@/lib/supabase/admin'
import { StudentMarketplaceHeader } from '@/components/student-marketplace-header'

function vendorIsSafe(vendor: any) {
  if (vendor.marketplace_status === 'active') return true
  return vendor.marketplace_status === 'suspended' && vendor.suspended_until && new Date(vendor.suspended_until) <= new Date()
}

export default async function ExploreCampusesPage({ searchParams }: { searchParams: Promise<{ q?: string; campus?: string }> }) {
  const params = await searchParams
  const q = (params.q || '').trim().toLowerCase()
  const selectedCampus = (params.campus || '').trim()

  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const { data: profile } = await supabase
    .from('profiles')
    .select('first_name,account_type,institution_id')
    .eq('id', userId)
    .maybeSingle()

  if (!profile || profile.account_type !== 'student') redirect('/dashboard')

  const admin = createAdminClient()
  const { data: schools } = await admin
    .from('institutions')
    .select('id,name,city,state')
    .eq('is_active', true)
    .order('name')

  const otherSchools = (schools || []).filter((school) => school.id !== profile.institution_id)
  const eligibleSchoolIds = selectedCampus
    ? otherSchools.filter((school) => school.id === selectedCampus).map((school) => school.id)
    : otherSchools.map((school) => school.id)

  let links: any[] = []
  if (eligibleSchoolIds.length) {
    const { data } = await admin
      .from('vendor_institutions')
      .select('vendor_id,institution_id')
      .in('institution_id', eligibleSchoolIds)
      .eq('status', 'approved')
    links = data || []
  }

  const vendorIds = Array.from(new Set(links.map((row) => row.vendor_id))) as string[]
  let vendors: any[] = []
  if (vendorIds.length) {
    const { data } = await admin
      .from('vendor_profiles')
      .select('id,business_name,slug,description,location_text,logo_url,cover_url,average_rating,review_count,verification_status,marketplace_status,suspended_until')
      .in('id', vendorIds)
      .eq('verification_status', 'approved')
      .order('average_rating', { ascending: false })
    vendors = (data || []).filter(vendorIsSafe)
  }

  if (q) {
    vendors = vendors.filter((vendor) =>
      [vendor.business_name, vendor.description, vendor.location_text].filter(Boolean).join(' ').toLowerCase().includes(q)
    )
  }

  const schoolById = new Map(otherSchools.map((school) => [school.id, school]))
  const campusesByVendor = new Map<string, any[]>()
  for (const link of links) {
    const school = schoolById.get(link.institution_id)
    if (!school) continue
    campusesByVendor.set(link.vendor_id, [...(campusesByVendor.get(link.vendor_id) || []), school])
  }

  const ownSchool = (schools || []).find((school) => school.id === profile.institution_id)

  return <main className="cl-student-page">
    <StudentMarketplaceHeader firstName={profile.first_name} schoolName={ownSchool?.name || null}/>
    <section className="cl-student-shell">
      <div className="student-head">
        <div><div className="cl-safety-note"><Globe2 size={14}/> Cross-campus discovery</div><h1>Explore other Campus Link schools.</h1><p>Your own campus remains the default marketplace. This view lets you browse public storefront information from Vendors approved at other schools without changing your campus membership.</p></div>
        <span className="campus-chip"><ShieldCheck size={16}/> Read-only cross-campus view</span>
      </div>

      <div className="notice success">Campus approval still means approval for the Vendor's listed school, not yours. Contact, save, review and report actions remain governed by your own campus relationship.</div>

      <form className="search-panel" method="get">
        <label className="search-field"><Search size={18}/><input name="q" defaultValue={params.q || ''} placeholder="Search Vendor names or descriptions..."/></label>
        <select name="campus" defaultValue={selectedCampus}><option value="">All other campuses</option>{otherSchools.map((school) => <option key={school.id} value={school.id}>{school.name}</option>)}</select>
        <button className="search-button" type="submit">Explore</button>
      </form>

      <div className="result-meta"><span>{vendors.length} Vendor{vendors.length === 1 ? '' : 's'} across {selectedCampus ? 1 : otherSchools.length} other campus{(selectedCampus ? 1 : otherSchools.length) === 1 ? '' : 'es'}</span><span><ShieldCheck size={13}/> Identity + campus approval + safety status enforced</span></div>

      {vendors.length ? <section className="cl-student-section">
        <div className="cl-vendor-grid-fiverr">{vendors.slice(0,36).map((vendor) => {
          const campuses = campusesByVendor.get(vendor.id) || []
          return <article className="cl-vendor-tile" key={vendor.id}>
            <Link href={`/share/vendor/${encodeURIComponent(vendor.slug)}`} style={{color:'inherit',textDecoration:'none'}}>
              <div className="cl-vendor-tile-cover">{vendor.cover_url ? <img src={vendor.cover_url} alt=""/> : null}</div>
              <div className="cl-vendor-tile-body">
                <div className="cl-vendor-tile-top"><span className="cl-vendor-tile-logo">{vendor.logo_url ? <img src={vendor.logo_url} alt=""/> : vendor.business_name.slice(0,1)}</span><div><strong>{vendor.business_name}</strong><div className="cl-safety-note"><ShieldCheck size={13}/> Approved Vendor</div></div></div>
                <p>{vendor.description || 'Verified Campus Link vendor.'}</p>
                <div className="phase5f-area-tags">{campuses.slice(0,3).map((school) => <span key={school.id}><Building2 size={11}/>{school.name}</span>)}</div>
                {vendor.location_text ? <div className="cl-safety-note"><MapPin size={12}/>{vendor.location_text}</div> : null}
                <div className="cl-vendor-meta"><span><Star size={13} fill="currentColor"/> {Number(vendor.average_rating || 0).toFixed(1)} ({vendor.review_count || 0})</span><span className="verified">View public storefront</span></div>
              </div>
            </Link>
          </article>
        })}</div>
      </section> : <div className="empty-state"><Globe2 size={34}/><h2>No matching cross-campus Vendors yet</h2><p>Try another search or choose all campuses. Campus Link only shows identity-approved Vendors with an active campus approval and no active safety hold.</p></div>}
    </section>
  </main>
}
