import Link from 'next/link'
import { redirect } from 'next/navigation'
import { ArrowUpRight, BadgeCheck, BookOpen, Bookmark, Camera, Home, Laptop, MapPin, Package, Search, Scissors, Shapes, ShieldCheck, Shirt, Sparkles, Star, UtensilsCrossed, Wrench } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { ThemeToggle } from '@/components/theme-toggle'
import { DynamicGreeting } from '@/components/dynamic-greeting'

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
  if (!profile || profile.account_type === 'vendor') redirect('/dashboard')

  const [institutionResult, savedResult, reviewResult, contactResult, categoryResult] = await Promise.all([
    profile.institution_id ? supabase.from('institutions').select('name,city,state').eq('id', profile.institution_id).maybeSingle() : Promise.resolve({ data: null }),
    supabase.from('saved_vendors').select('vendor_id', { count: 'exact' }).eq('student_id', userId),
    supabase.from('reviews').select('id', { count: 'exact', head: true }).eq('student_id', userId),
    supabase.from('contact_events').select('id', { count: 'exact', head: true }).eq('student_id', userId),
    supabase.from('categories').select('id,name,slug').eq('is_active', true).order('name').limit(10),
  ])

  const school = institutionResult.data?.name || null
  const location = [institutionResult.data?.city, institutionResult.data?.state].filter(Boolean).join(', ')
  const status = profile.student_verification_status || 'pending'
  const setupComplete = Boolean(profile.onboarding_completed_at)
  const savedCount = savedResult.count ?? savedResult.data?.length ?? 0
  const reviewCount = reviewResult.count || 0
  const contactCount = contactResult.count || 0
  const categories = categoryResult.data || []

  let featured: any[] = []
  let products: any[] = []
  let services: any[] = []
  let campusVendorCount = 0

  if (profile.institution_id) {
    const { data: campusLinks } = await supabase.from('vendor_institutions').select('vendor_id').eq('institution_id', profile.institution_id).eq('status', 'approved').limit(60)
    const ids = (campusLinks || []).map((row) => row.vendor_id)
    campusVendorCount = ids.length
    if (ids.length) {
      const [vendorResult, productResult, serviceResult] = await Promise.all([
        supabase.from('vendor_profiles').select('id,business_name,slug,description,logo_url,cover_url,average_rating,review_count,marketplace_status,suspended_until').in('id', ids).eq('verification_status', 'approved').order('average_rating', { ascending: false }).limit(8),
        supabase.from('vendor_products').select('id,vendor_id,name,price_ngn,pricing_type,cover_image_url,created_at').in('vendor_id', ids).eq('is_active', true).order('created_at', { ascending: false }).limit(12),
        supabase.from('vendor_services').select('id,vendor_id,name,price_from,description').in('vendor_id', ids).eq('is_active', true).limit(10),
      ])
      featured = (vendorResult.data || []).filter((vendor) => vendor.marketplace_status === 'active' || (vendor.marketplace_status === 'suspended' && vendor.suspended_until && new Date(vendor.suspended_until) <= new Date()))
      products = productResult.data || []
      services = serviceResult.data || []
    }
  }

  const vendorMap = new Map(featured.map((vendor) => [vendor.id, vendor]))

  return (
    <main className="v3-student-page">
      <header className="v3-appbar">
        <div className="v3-appbar-inner">
          <Link href="/student" className="v3-brand">Campus<span>Link</span></Link>
          <nav className="v3-nav"><Link href="/student">Home</Link><Link href="/student/discover">Discover</Link><Link href="/student/saved">Saved</Link><Link href="/onboarding/student">Verification</Link><ThemeToggle compact/><form action="/auth/signout" method="post"><button>Sign out</button></form></nav>
        </div>
      </header>

      <section className="v3-shell">
        <section className="v3-hero">
          <div className="v3-hero-main">
            {school ? <div className="v3-campus-chip"><MapPin size={15}/><span>{school}{location ? ` · ${location}` : ''}</span></div> : null}
            <DynamicGreeting firstName={profile.first_name} sessionSeed={sessionSeed} role="student" />
            <p>{school ? 'Search products, services and trusted vendors already connected to your campus.' : 'Add your school to make Campus Link local to you, then discover vendors students can actually reach.'}</p>
            <form className="v3-search" action="/student/discover" method="get"><Search size={19}/><input name="q" placeholder="Search clothes, food, braids, repairs, tutors..."/><button type="submit">Search campus</button></form>
          </div>
          <aside className="v3-hero-side"><div><span>Your campus today</span><strong>{school || 'Set your school'}</strong><p>{school ? `${campusVendorCount} approved vendor${campusVendorCount === 1 ? '' : 's'} currently connected to your school.` : 'Once your school is set, Campus Link keeps discovery focused on people and businesses relevant to you.'}</p></div><div className="v3-trust-line"><ShieldCheck size={17}/> Payment never buys verification.</div></aside>
        </section>

        {categories.length ? <section className="v3-section"><div className="v3-section-head"><div><small>Browse by need</small><h2>What are you looking for?</h2></div><Link href="/student/discover">See everything <ArrowUpRight size={14}/></Link></div><div className="v3-category-rail">{categories.slice(0,6).map((category) => <Link className="v3-category-card" href={`/student/discover?category=${encodeURIComponent(category.slug)}`} key={category.id}><span><CategoryIcon name={category.name}/></span><strong>{category.name}</strong></Link>)}</div></section> : null}

        <section className="v3-section"><div className="v3-section-head"><div><small>Fresh around campus</small><h2>Products students can ask about</h2></div><Link href="/student/discover">Explore marketplace <ArrowUpRight size={14}/></Link></div>{products.length ? <div className="v3-product-rail">{products.map((product) => { const vendor = vendorMap.get(product.vendor_id); const price = product.pricing_type === 'contact' ? 'Ask for price' : `${product.pricing_type === 'from' ? 'From ' : ''}₦${Number(product.price_ngn || 0).toLocaleString()}`; return <Link href={`/student/products/${product.id}`} className="v3-product-card" key={product.id}><div className="v3-product-image">{product.cover_image_url ? <img src={product.cover_image_url} alt={product.name}/> : <Package size={36}/>}</div><div className="v3-product-copy"><strong>{product.name}</strong><p>{vendor?.business_name || 'Campus vendor'}</p><div className="v3-product-meta"><span>{price}</span><small>View item</small></div></div></Link>})}</div> : <div className="v3-surface"><strong>Products will appear here as vendors add their catalogues.</strong><p style={{color:'var(--v3-muted)'}}>Services are already available; product listings are being introduced separately so it stays clear what a vendor sells versus what they do.</p></div>}</section>

        <section className="v3-section"><div className="v3-section-head"><div><small>Services near you</small><h2>People you can contact today</h2></div><Link href="/student/discover">Browse services <ArrowUpRight size={14}/></Link></div>{featured.length ? <div className="v3-vendor-rail">{featured.map((vendor) => <Link href={`/student/vendors/${vendor.slug}`} className="v3-vendor-card" key={vendor.id}><div className="v3-product-image">{vendor.cover_url ? <img src={vendor.cover_url} alt=""/> : vendor.logo_url ? <img src={vendor.logo_url} alt=""/> : <ShieldCheck size={38}/>}</div><div className="v3-product-copy"><strong>{vendor.business_name}</strong><p>{vendor.description || 'Approved Campus Link vendor.'}</p><div className="v3-product-meta"><span><Star size={13} fill="currentColor"/> {Number(vendor.average_rating || 0).toFixed(1)}</span><small>Verified</small></div></div></Link>)}</div> : <div className="v3-surface"><strong>Your local vendor list is still growing.</strong><p style={{color:'var(--v3-muted)'}}>Approved vendors will show here as businesses are cleared for your campus.</p></div>}</section>

        <section className="v3-split v3-section">
          <div className="v3-surface"><div className="v3-section-head" style={{marginBottom:8}}><div><small>Your space</small><h2 style={{fontSize:23}}>Campus Link activity</h2></div></div><div className="v3-activity-strip"><div><small>Saved</small><strong>{savedCount}</strong><small>vendors</small></div><div><small>Contacts</small><strong>{contactCount}</strong><small>started</small></div><div><small>Reviews</small><strong>{reviewCount}</strong><small>shared</small></div><div><small>Verification</small><strong style={{fontSize:18,textTransform:'capitalize'}}>{status.replace('_',' ')}</strong><small>status</small></div></div></div>
          <div className="v3-surface"><div className="v3-section-head" style={{marginBottom:6}}><div><small>Quick actions</small><h2 style={{fontSize:23}}>Go straight there</h2></div></div><div className="v3-action-list"><Link href="/student/discover" className="v3-action-row"><span><Search size={19}/></span><div><strong>Discover vendors</strong><small>Search products, services and profiles.</small></div><ArrowUpRight size={16}/></Link><Link href="/student/saved" className="v3-action-row"><span><Bookmark size={19}/></span><div><strong>Saved vendors</strong><small>Return to businesses you shortlisted.</small></div><ArrowUpRight size={16}/></Link>{!setupComplete || status !== 'verified' ? <Link href="/onboarding/student" className="v3-action-row"><span><BadgeCheck size={19}/></span><div><strong>Complete verification</strong><small>Strengthen your account and review privileges.</small></div><ArrowUpRight size={16}/></Link> : null}</div></div>
        </section>
      </section>

      <nav className="v3-mobile-nav"><Link className="active" href="/student"><Home size={17}/><br/>Home</Link><Link href="/student/discover"><Search size={17}/><br/>Discover</Link><Link href="/student/saved"><Bookmark size={17}/><br/>Saved</Link><Link href="/onboarding/student"><BadgeCheck size={17}/><br/>Profile</Link></nav>
    </main>
  )
}
