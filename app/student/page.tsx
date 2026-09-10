import Link from 'next/link'
import { redirect } from 'next/navigation'
import { BadgeCheck, BookOpen, Bookmark, Camera, ChevronRight, Laptop, MapPin, MessageCircle, Package, Search, Scissors, Shapes, ShieldCheck, Shirt, Sparkles, Star, UtensilsCrossed, Wrench } from 'lucide-react'
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
  const popularSearches = ['Phone repair', 'Hair & beauty', 'Food', 'Graphic design'].filter((term, index) => index < 4)

  return (
    <main className="cl-fv-page">
      <StudentMarketplaceHeader firstName={profile.first_name} schoolName={school} />

      <div className="cl-fv-shell">
        <section className="cl-fv-welcome">
          <div className="cl-fv-welcome-main">
            <div className="cl-fv-overline"><MapPin size={15}/> {school ? `${school}${location ? ` · ${location}` : ''}` : 'Set your campus to personalize discovery'}</div>
            <DynamicGreeting firstName={profile.first_name} sessionSeed={sessionSeed} role="student" />
            <p>Find trusted products and services around your campus. Compare listings, check the vendor behind them, then contact the vendor directly.</p>

            <form className="cl-fv-search" action="/student/discover" method="get">
              <Search size={20}/>
              <input name="q" placeholder="What are you looking for today?" aria-label="Search Campus Link marketplace" />
              <button type="submit">Search</button>
            </form>

            <div className="cl-fv-popular">
              <small>Popular:</small>
              {popularSearches.map((term) => <Link key={term} href={`/student/discover?q=${encodeURIComponent(term)}`}>{term}</Link>)}
            </div>
          </div>

          <aside className="cl-fv-campus">
            <div>
              <div className="cl-fv-campus-top"><div><small>Your campus</small><h2>{school || 'Complete your campus profile'}</h2><p>{school ? `${vendors.length} approved vendor${vendors.length === 1 ? '' : 's'} are currently visible to students at your institution.` : 'Campus Link keeps discovery tied to your institution so results stay useful and local.'}</p></div><span className="cl-fv-campus-icon"><ShieldCheck size={21}/></span></div>
              <div className="cl-fv-campus-stats"><div><strong>{vendors.length}</strong><span>Approved vendors</span></div><div><strong>{products.length + services.length}</strong><span>Active listings loaded</span></div></div>
            </div>
            <div className="cl-fv-campus-trust"><ShieldCheck size={16}/> Every visible vendor must pass identity verification and campus approval. Paid plans do not purchase trust.</div>
          </aside>
        </section>

        {categories.length ? <section className="cl-fv-section">
          <div className="cl-fv-section-head"><div><h2>Explore by category</h2><p>Browse the marketplace the same way you think about what you need.</p></div><Link href="/student/discover">See all</Link></div>
          <div className="cl-fv-categories">
            {categories.slice(0, 6).map((category) => <Link className="cl-fv-category" href={`/student/discover?category=${encodeURIComponent(category.slug)}`} key={category.id}><span className="cl-fv-category-icon"><CategoryIcon name={category.name}/></span><strong>{category.name}</strong></Link>)}
          </div>
        </section> : null}

        <section className="cl-fv-section">
          <div className="cl-fv-section-head"><div><h2>Recommended around your campus</h2><p>Fresh product listings from approved vendors.</p></div><Link href="/student/discover">Explore marketplace</Link></div>
          {products.length ? <div className="cl-fv-listing-grid">
            {products.slice(0, 8).map((product) => {
              const vendor = vendorMap.get(product.vendor_id)
              const price = product.pricing_type === 'contact' ? 'Ask for price' : `${product.pricing_type === 'from' ? 'From ' : ''}₦${Number(product.price_ngn || 0).toLocaleString()}`
              return <Link href={`/student/products/${product.id}`} className="cl-fv-listing" key={product.id}>
                <div className="cl-fv-listing-media">{product.cover_image_url ? <img src={product.cover_image_url} alt={product.name}/> : <div className="cl-fv-listing-placeholder"><Package size={38}/></div>}<span className="cl-fv-chip">Product</span></div>
                <div className="cl-fv-listing-body">
                  <div className="cl-fv-seller"><span className="cl-fv-avatar">{vendor?.logo_url ? <img src={vendor.logo_url} alt=""/> : (vendor?.business_name || 'V').slice(0,1)}</span><span>{vendor?.business_name || 'Campus vendor'}</span><ShieldCheck size={12}/></div>
                  <div className="cl-fv-title">{product.name}</div>
                  <div className="cl-fv-rating"><Star size={13} fill="currentColor"/> {Number(vendor?.average_rating || 0).toFixed(1)} <span>({vendor?.review_count || 0})</span></div>
                  <div className="cl-fv-price"><span>Starting at</span><strong>{price}</strong></div>
                </div>
              </Link>
            })}
          </div> : <div className="cl-fv-empty"><strong>No product listings yet.</strong><p>Approved vendors will appear here as they add products. Services and Vendor profiles are still available.</p></div>}
        </section>

        {featuredServices.length ? <section className="cl-fv-section">
          <div className="cl-fv-section-head"><div><h2>Services you may need</h2><p>Fiverr-style service discovery, but limited to businesses approved for your campus.</p></div><Link href="/student/discover">Browse services</Link></div>
          <div className="cl-fv-listing-grid">
            {featuredServices.map((service) => {
              const vendor = vendorMap.get(service.vendor_id)
              if (!vendor) return null
              return <Link href={`/student/services/${service.id}`} className="cl-fv-listing" key={service.id}>
                <div className="cl-fv-listing-media">{vendor.cover_url ? <img src={vendor.cover_url} alt=""/> : vendor.logo_url ? <img src={vendor.logo_url} alt=""/> : <div className="cl-fv-listing-placeholder"><MessageCircle size={38}/></div>}<span className="cl-fv-chip">Service</span></div>
                <div className="cl-fv-listing-body"><div className="cl-fv-seller"><span className="cl-fv-avatar">{vendor.logo_url ? <img src={vendor.logo_url} alt=""/> : vendor.business_name.slice(0,1)}</span><span>{vendor.business_name}</span><ShieldCheck size={12}/></div><div className="cl-fv-title">{service.name}</div><div className="cl-fv-rating"><Star size={13} fill="currentColor"/> {Number(vendor.average_rating || 0).toFixed(1)} <span>({vendor.review_count || 0})</span></div><div className="cl-fv-price"><span>Starting at</span><strong>{service.price_from ? `₦${Number(service.price_from).toLocaleString()}` : 'Ask vendor'}</strong></div></div>
              </Link>
            })}
          </div>
        </section> : null}

        <section className="cl-fv-section">
          <div className="cl-fv-section-head"><div><h2>Trusted vendors on your campus</h2><p>Open a storefront to compare products, services, portfolio proof, reviews and contact options.</p></div><Link href="/student/discover">View all vendors</Link></div>
          {featuredVendors.length ? <div className="cl-fv-vendor-row">{featuredVendors.map((vendor) => <Link href={`/student/vendors/${vendor.slug}`} className="cl-fv-vendor" key={vendor.id}><span className="cl-fv-vendor-logo">{vendor.logo_url ? <img src={vendor.logo_url} alt=""/> : vendor.business_name.slice(0,1)}</span><div><strong>{vendor.business_name}<ShieldCheck size={13}/></strong><p>{vendor.description || 'Approved Campus Link vendor.'}</p><div className="cl-fv-vendor-meta"><span><Star size={12} fill="currentColor"/> <b>{Number(vendor.average_rating || 0).toFixed(1)}</b> ({vendor.review_count || 0})</span><span>View storefront</span></div></div></Link>)}</div> : <div className="cl-fv-empty"><strong>Your campus marketplace is still growing.</strong><p>Campus Link only surfaces vendors after identity verification and campus approval.</p></div>}
        </section>

        <section className="cl-fv-bottom">
          <div className="cl-fv-bottom-card"><h3>Your activity</h3><p>Useful account signals without turning the Student experience into a dashboard.</p><div className="cl-fv-stats"><div><small>Saved</small><strong>{savedCount}</strong></div><div><small>Contacts</small><strong>{contactCount}</strong></div><div><small>Reviews</small><strong>{reviewCount}</strong></div><div><small>Verification</small><strong style={{fontSize:13,textTransform:'capitalize'}}>{status.replaceAll('_',' ')}</strong></div></div></div>
          <div className="cl-fv-bottom-card"><h3>Account & safety</h3><p>Manage the parts of Campus Link that protect your identity and marketplace experience.</p><div className="cl-fv-links"><Link href="/student/saved"><span><Bookmark size={16}/> Saved vendors</span><ChevronRight size={16}/></Link><Link href="/student/discover"><span><Search size={16}/> Discover marketplace</span><ChevronRight size={16}/></Link>{!setupComplete || status !== 'verified' ? <Link href="/onboarding/student"><span><BadgeCheck size={16}/> Complete verification</span><ChevronRight size={16}/></Link> : <Link href="/onboarding/student"><span><ShieldCheck size={16}/> Review verified profile</span><ChevronRight size={16}/></Link>}</div></div>
        </section>
      </div>
    </main>
  )
}
