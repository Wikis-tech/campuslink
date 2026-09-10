import Link from 'next/link'
import { redirect } from 'next/navigation'
import { Bookmark, MapPin, ShieldCheck, Star } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { StudentMarketplaceHeader } from '@/components/student-marketplace-header'
import { toggleSavedVendor } from '../actions'

function vendorIsSafe(vendor: any) {
  if (vendor.marketplace_status === 'active') return true
  return vendor.marketplace_status === 'suspended' && vendor.suspended_until && new Date(vendor.suspended_until) <= new Date()
}

export default async function SavedVendorsPage() {
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

  const [{ data: institution }, { data: saved }, { data: campusLinks }] = await Promise.all([
    supabase.from('institutions').select('name').eq('id', profile.institution_id).maybeSingle(),
    supabase.from('saved_vendors').select('vendor_id,created_at').eq('student_id', userId).order('created_at', { ascending: false }),
    supabase.from('vendor_institutions').select('vendor_id').eq('institution_id', profile.institution_id).eq('status', 'approved'),
  ])

  const campusApproved = new Set((campusLinks || []).map((row) => row.vendor_id))
  const vendorIds = (saved || []).map((row) => row.vendor_id).filter((id) => campusApproved.has(id))
  let vendors: any[] = []
  let services: any[] = []

  if (vendorIds.length) {
    const [vendorResult, serviceResult] = await Promise.all([
      supabase.from('vendor_profiles').select('id,business_name,slug,description,location_text,logo_url,cover_url,average_rating,review_count,verification_status,marketplace_status,suspended_until').in('id', vendorIds).eq('verification_status', 'approved'),
      supabase.from('vendor_services').select('id,vendor_id,name').in('vendor_id', vendorIds).eq('is_active', true),
    ])
    vendors = (vendorResult.data || []).filter(vendorIsSafe)
    const safeIds = new Set(vendors.map((vendor) => vendor.id))
    services = (serviceResult.data || []).filter((service) => safeIds.has(service.vendor_id))
  }

  const servicesByVendor = new Map<string, any[]>()
  for (const service of services) {
    const list = servicesByVendor.get(service.vendor_id) || []
    list.push(service)
    servicesByVendor.set(service.vendor_id, list)
  }

  return (
    <main className="cl-student-page">
      <StudentMarketplaceHeader firstName={profile.first_name} schoolName={institution?.name} />
      <section className="cl-student-shell">
        <div className="student-head"><div><h1>Saved for later.</h1><p>Your shortlist only shows vendors who are still approved for your campus and currently safe to discover.</p></div><span className="campus-chip"><MapPin size={16}/> {institution?.name || 'Your campus'}</span></div>

        {vendors.length ? <div className="cl-vendor-grid-fiverr">{vendors.map((vendor) => {
          const initial = vendor.business_name?.slice(0,1)?.toUpperCase() || 'V'
          const vendorServices = servicesByVendor.get(vendor.id) || []
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
            <div className="vendor-card-actions" style={{padding:'0 15px 15px'}}><form action={toggleSavedVendor}><input type="hidden" name="vendor_id" value={vendor.id}/><input type="hidden" name="return_to" value="/student/saved"/><button className="save-btn saved" aria-label="Remove saved vendor"><Bookmark size={16} fill="currentColor"/> Remove</button></form></div>
          </article>
        })}</div> : <div className="empty-state"><Bookmark size={34}/><h2>No available saved vendors</h2><p>Save a useful campus vendor to build your shortlist. If a previously saved vendor loses campus approval or enters safety review, Campus Link hides them from this list until they are eligible again.</p><p style={{marginTop:16}}><Link className="view-btn" href="/student/discover">Discover vendors</Link></p></div>}
      </section>
    </main>
  )
}
