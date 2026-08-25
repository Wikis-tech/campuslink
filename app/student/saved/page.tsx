import Link from 'next/link'
import { redirect } from 'next/navigation'
import { Bookmark, MapPin, ShieldCheck, Star } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { toggleSavedVendor } from '../actions'

export default async function SavedVendorsPage() {
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const { data: profile } = await supabase
    .from('profiles')
    .select('account_type,institution_id,onboarding_completed_at')
    .eq('id', userId)
    .single()

  if (!profile || profile.account_type !== 'student') redirect('/dashboard')
  if (!profile.onboarding_completed_at) redirect('/onboarding/student')

  const [{ data: institution }, { data: saved }] = await Promise.all([
    profile.institution_id ? supabase.from('institutions').select('name').eq('id', profile.institution_id).maybeSingle() : Promise.resolve({ data: null }),
    supabase.from('saved_vendors').select('vendor_id,created_at').eq('student_id', userId).order('created_at', { ascending: false }),
  ])

  const vendorIds = (saved || []).map((row) => row.vendor_id)
  let vendors: any[] = []
  let services: any[] = []
  if (vendorIds.length) {
    const [vendorResult, serviceResult] = await Promise.all([
      supabase.from('vendor_profiles').select('id,business_name,slug,description,location_text,logo_url,cover_url,average_rating,review_count,verification_status').in('id', vendorIds).eq('verification_status', 'approved'),
      supabase.from('vendor_services').select('id,vendor_id,name').in('vendor_id', vendorIds).eq('is_active', true),
    ])
    vendors = vendorResult.data || []
    services = serviceResult.data || []
  }

  const servicesByVendor = new Map<string, any[]>()
  for (const service of services) {
    const list = servicesByVendor.get(service.vendor_id) || []
    list.push(service)
    servicesByVendor.set(service.vendor_id, list)
  }

  return (
    <main className="student-app">
      <header className="student-nav">
        <Link href="/student" className="student-brand">Campus<span>Link</span></Link>
        <nav className="student-navlinks"><Link href="/student">Dashboard</Link><Link href="/student/discover">Discover</Link><Link href="/student/saved">Saved</Link><form action="/auth/signout" method="post"><button>Sign out</button></form></nav>
      </header>

      <section className="student-shell">
        <div className="student-head"><div><h1>Your saved vendors.</h1><p>Keep useful vendors close so you can find them again without starting another search.</p></div><span className="campus-chip"><MapPin size={16} /> {institution?.name || 'Your campus'}</span></div>

        {vendors.length ? <div className="vendor-grid">{vendors.map((vendor) => {
          const initial = vendor.business_name?.slice(0,1)?.toUpperCase() || 'V'
          const vendorServices = servicesByVendor.get(vendor.id) || []
          return <article className="vendor-card" key={vendor.id}>
            <div className="vendor-cover">{vendor.cover_url ? <img src={vendor.cover_url} alt="" /> : null}</div>
            <div className="vendor-avatar">{vendor.logo_url ? <img src={vendor.logo_url} alt={`${vendor.business_name} logo`} /> : initial}</div>
            <div className="vendor-card-body">
              <div className="vendor-card-top"><div><Link className="vendor-name" href={`/student/vendors/${vendor.slug}`}>{vendor.business_name}</Link><div className="verified-line"><ShieldCheck size={14}/> Campus verified</div></div><div className="rating"><Star size={15} fill="currentColor" /> {Number(vendor.average_rating || 0).toFixed(1)} <span>({vendor.review_count || 0})</span></div></div>
              <p className="vendor-desc">{vendor.description || 'Verified service provider on your campus.'}</p>
              <div className="service-tags">{vendorServices.slice(0,3).map((service) => <span className="service-tag" key={service.id}>{service.name}</span>)}</div>
              <div className="vendor-card-actions"><Link className="view-btn" href={`/student/vendors/${vendor.slug}`}>View profile</Link><form action={toggleSavedVendor}><input type="hidden" name="vendor_id" value={vendor.id}/><input type="hidden" name="return_to" value="/student/saved"/><button className="save-btn saved" aria-label="Remove saved vendor"><Bookmark size={16} fill="currentColor"/></button></form></div>
            </div>
          </article>
        })}</div> : <div className="empty-state"><Bookmark size={34}/><h2>No saved vendors yet</h2><p>When you find someone useful, save their profile here for quick access later.</p><p style={{marginTop:16}}><Link className="view-btn" href="/student/discover">Discover vendors</Link></p></div>}
      </section>

      <nav className="bottom-nav"><Link href="/student">Home</Link><Link href="/student/discover">Discover</Link><Link href="/student/saved">Saved</Link></nav>
    </main>
  )
}
