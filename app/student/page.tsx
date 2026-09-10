import Link from 'next/link'
import { redirect } from 'next/navigation'
import { BadgeCheck, BookOpen, Bookmark, Camera, Laptop, MapPin, MessageCircle, Package, Search, Scissors, Shapes, ShieldCheck, Shirt, Sparkles, Star, UtensilsCrossed, Wrench } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { DynamicGreeting } from '@/components/dynamic-greeting'
import { StudentMarketplaceHeader } from '@/components/student-marketplace-header'

function CategoryIcon({ name }: { name: string }) {
  const value = name.toLowerCase()
  if (value.includes('hair') || value.includes('beauty') || value.includes('barb')) return <Scissors size={18}/>
  if (value.includes('food') || value.includes('cater') || value.includes('meal')) return <UtensilsCrossed size={18}/>
  if (value.includes('repair') || value.includes('fix')) return <Wrench size={18}/>
  if (value.includes('tech') || value.includes('computer') || value.includes('phone')) return <Laptop size={18}/>
  if (value.includes('tutor') || value.includes('academic') || value.includes('education')) return <BookOpen size={18}/>
  if (value.includes('photo') || value.includes('video')) return <Camera size={18}/>
  if (value.includes('fashion') || value.includes('laundry') || value.includes('cloth')) return <Shirt size={18}/>
  if (value.includes('event') || value.includes('creative')) return <Sparkles size={18}/>
  return <Shapes size={18}/>
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

  const [institutionResult, savedResult, reviewResult, contactResult, categoryResult] = await Promise.all([
    profile.institution_id ? supabase.from('institutions').select('name,city,state').eq('id', profile.institution_id).maybeSingle() : Promise.resolve({ data: null }),
    supabase.from('saved_vendors').select('vendor_id', { count: 'exact' }).eq('student_id', userId),
    supabase.from('reviews').select('id', { count: 'exact', head: true }).eq('student_id', userId),
    supabase.from('contact_events').select('id', { count: 'exact', head: true }).eq('student_id', userId),
    supabase.from('categories').select('id,name,slug').eq('is_active', true).order('name').limit(12),
  ])

  const institution = institutionResult.data
  const school = institution?.name || null
  const location = [institution?.city, institution?.state].filter(Boolean).join(', ')
  const status = profile.student_verification_status || 'pending'
  const setupComplete = Boolean(profile.onboarding_completed_at)
  const savedCount = savedResult.count ?? savedResult.data?.length ?? 0
  const reviewCount = reviewResult.count || 0
  const contactCount = contactResult.count || 0
  const categories = categoryResult.data || []

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
          supabase.from('vendor_products').select('id,vendor_id,name,description,price_ngn,pricing_type,cover_image_url,created_at').in('vendor_id', safeVendorIds).eq('is_active', true).order('created_at', { ascending: false }).limit(12),
          supabase.from('vendor_services').select('id,vendor_id,name,price_from,description').in('vendor_id', safeVendorIds).eq('is_active', true).order('created_at', { ascending: false }).limit(12),
        ])
        products = productResult.data || []
        services = serviceResult.data || []
      }
    }
  }

  const vendorMap = new Map(vendors.map((vendor) => [vendor.id, vendor]))
  const featuredVendors = vendors.slice(0, 6)
  const featuredServices = services.slice(0, 8)

  return (
    <main className="cl-student-page">
      <StudentMarketplaceHeader firstName={profile.first_name} schoolName={school} />

      <section className="cl-student-shell">
        <section className="cl-student-hero">
          <div className="cl-student-hero-copy">
            <span className="cl-student-eyebrow"><MapPin size={15}/> {school ? `${school}${location ? ` · ${location}` : ''}` : 'Set your campus to personalize discovery'}</span>
            <DynamicGreeting firstName={profile.first_name} sessionSeed={sessionSeed} role="student" />
            <p>Find products and services from vendors approved for your campus, compare their work, reviews and reputation, then contact them directly.</p>
            <form className="cl-student-hero-search" action="/student/discover" method="get">
              <Search size={20}/>
              <input name="q" placeholder="Try “phone repair”, “braids”, “cakes”, “graphic design”..." aria-label="Search your campus marketplace" />
              <button type="submit">Search</button>
            </form>
          </div>
          <aside className="cl-student-hero-aside">
            <div className="cl-student-campus-card">
              <small>Your campus marketplace</small>
              <strong>{school || 'Add your university'}</strong>
              <p>{school ? `${vendors.length} approved vendor${vendors.length === 1 ? '' : 's'} currently visible to students at your institution.` : 'Campus Link keeps discovery school-specific so you see businesses that can actually serve your community.'}</p>
              <div className="cl-student-campus-trust"><ShieldCheck size={16}/> Identity + campus approval are required for visibility.</div>
            </div>
          </aside>
        </section>

        {categories.length ? <section className="cl-student-section">
          <div className="cl-student-section-head"><div><h2>Explore by category</h2><p>Start with what you need, then compare real campus vendors.</p></div><Link href="/student/discover">See all categories</Link></div>
          <div className="cl-student-categories">
            {categories.slice(0, 6).map((category) => <Link className="cl-student-category" href={`/student/discover?category=${encodeURIComponent(category.slug)}`} key={category.id}><span className="cl-student-category-icon"><CategoryIcon name={category.name}/></span><strong>{category.name}</strong></Link>)}
          </div>
        </section> : null}

        <section className="cl-student-section">
          <div className="cl-student-section-head"><div><h2>Fresh around your campus</h2><p>Individual products you can open, compare and ask the vendor about.</p></div><Link href="/student/discover">Explore marketplace</Link></div>
          {products.length ? <div className="cl-gig-grid">
            {products.slice(0, 8).map((product) => {
              const vendor = vendorMap.get(product.vendor_id)
              const price = product.pricing_type === 'contact' ? 'Ask for price' : `${product.pricing_type === 'from' ? 'From ' : ''}₦${Number(product.price_ngn || 0).toLocaleString()}`
              return <Link href={`/student/products/${product.id}`} className="cl-gig-card" key={product.id}>
                <div className="cl-gig-image">{product.cover_image_url ? <img src={product.cover_image_url} alt={product.name}/> : <div className="cl-gig-placeholder"><Package size={38}/></div>}<span className="cl-gig-badge">Campus approved</span></div>
                <div className="cl-gig-copy">
                  <div className="cl-gig-seller"><span className="cl-gig-seller-avatar">{vendor?.logo_url ? <img src={vendor.logo_url} alt=""/> : (vendor?.business_name || 'V').slice(0,1)}</span><span>{vendor?.business_name || 'Campus vendor'}</span><ShieldCheck size={12}/></div>
                  <div className="cl-gig-title">{product.name}</div>
                  <div className="cl-gig-rating"><Star size={13} fill="currentColor"/> {Number(vendor?.average_rating || 0).toFixed(1)} <span style={{color:'var(--v3-muted)'}}>({vendor?.review_count || 0})</span></div>
                  <div className="cl-gig-price">Starting at <strong>{price}</strong></div>
                </div>
              </Link>
            })}
          </div> : <div className="v3-surface"><strong>No products have been listed on your campus yet.</strong><p style={{color:'var(--v3-muted)'}}>Approved vendors will appear here as they add products. You can already browse services and vendor profiles.</p></div>}
        </section>

        {featuredServices.length ? <section className="cl-student-section">
          <div className="cl-student-section-head"><div><h2>Services students are offering</h2><p>Open the vendor profile to compare portfolio proof, ratings and contact options.</p></div><Link href="/student/discover">Browse all services</Link></div>
          <div className="cl-gig-grid">
            {featuredServices.map((service) => {
              const vendor = vendorMap.get(service.vendor_id)
              if (!vendor) return null
              return <Link href={`/student/vendors/${vendor.slug}`} className="cl-gig-card" key={service.id}>
                <div className="cl-gig-image">{vendor.cover_url ? <img src={vendor.cover_url} alt=""/> : vendor.logo_url ? <img src={vendor.logo_url} alt=""/> : <div className="cl-gig-placeholder"><MessageCircle size={38}/></div>}<span className="cl-gig-badge">Service</span></div>
                <div className="cl-gig-copy"><div className="cl-gig-seller"><span className="cl-gig-seller-avatar">{vendor.logo_url ? <img src={vendor.logo_url} alt=""/> : vendor.business_name.slice(0,1)}</span><span>{vendor.business_name}</span><ShieldCheck size={12}/></div><div className="cl-gig-title">{service.name}</div><div className="cl-gig-rating"><Star size={13} fill="currentColor"/> {Number(vendor.average_rating || 0).toFixed(1)} <span style={{color:'var(--v3-muted)'}}>({vendor.review_count || 0})</span></div><div className="cl-gig-price">{service.price_from ? <>Starting at <strong>₦{Number(service.price_from).toLocaleString()}</strong></> : <strong>Ask vendor for price</strong>}</div></div>
              </Link>
            })}
          </div>
        </section> : null}

        <section className="cl-student-section">
          <div className="cl-student-section-head"><div><h2>Trusted businesses on your campus</h2><p>Profiles combine products, services, portfolio proof, reviews and direct contact.</p></div><Link href="/student/discover">Discover more</Link></div>
          {featuredVendors.length ? <div className="cl-vendor-grid-fiverr">{featuredVendors.map((vendor) => <Link href={`/student/vendors/${vendor.slug}`} className="cl-vendor-tile" key={vendor.id}><div className="cl-vendor-tile-cover">{vendor.cover_url ? <img src={vendor.cover_url} alt=""/> : null}</div><div className="cl-vendor-tile-body"><div className="cl-vendor-tile-top"><span className="cl-vendor-tile-logo">{vendor.logo_url ? <img src={vendor.logo_url} alt=""/> : vendor.business_name.slice(0,1)}</span><div><strong>{vendor.business_name}</strong><div className="cl-safety-note"><ShieldCheck size={13}/> Campus approved</div></div></div><p>{vendor.description || 'Approved Campus Link vendor.'}</p><div className="cl-vendor-meta"><span><Star size={13} fill="currentColor"/> {Number(vendor.average_rating || 0).toFixed(1)} ({vendor.review_count || 0})</span><span className="verified">View profile</span></div></div></Link>)}</div> : <div className="v3-surface"><strong>Your campus vendor network is still growing.</strong><p style={{color:'var(--v3-muted)'}}>Campus Link only surfaces businesses after identity verification and campus approval.</p></div>}
        </section>

        <section className="cl-student-section cl-student-utility-grid">
          <div className="cl-student-utility"><h3>Your Campus Link activity</h3><p>Useful shortcuts without turning the Student experience into an analytics dashboard.</p><div className="cl-student-stat-row"><div><small>Saved</small><strong>{savedCount}</strong></div><div><small>Contacts</small><strong>{contactCount}</strong></div><div><small>Reviews</small><strong>{reviewCount}</strong></div><div><small>Verification</small><strong style={{fontSize:14,textTransform:'capitalize'}}>{status.replaceAll('_',' ')}</strong></div></div></div>
          <div className="cl-student-utility"><h3>Account & safety</h3><p>Your verification improves review integrity. Vendor payments never purchase trust or campus approval.</p><div className="cl-student-actions"><Link href="/student/saved"><Bookmark size={18}/> Saved vendors</Link><Link href="/student/discover"><Search size={18}/> Discover campus vendors</Link>{!setupComplete || status !== 'verified' ? <Link href="/onboarding/student"><BadgeCheck size={18}/> Complete Student verification</Link> : <Link href="/onboarding/student"><ShieldCheck size={18}/> Review your verified profile</Link>}</div></div>
        </section>
      </section>
    </main>
  )
}
