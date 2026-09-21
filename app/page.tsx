import Link from 'next/link'
import './landing-legacy.css'
import { createAdminClient } from '@/lib/supabase/admin'
import {
  ArrowRight,
  Award,
  BookOpen,
  Camera,
  Car,
  CheckCircle2,
  Compass,
  Flag,
  Gavel,
  GraduationCap,
  Handshake,
  Info,
  LayoutGrid,
  Lock,
  PartyPopper,
  PhoneCall,
  Printer,
  Scissors,
  Search,
  ShieldCheck,
  Shirt,
  Smartphone,
  Sparkles,
  Star,
  Store,
  Tag,
  Utensils,
  WashingMachine,
  Wrench,
} from 'lucide-react'

const categoryIcons = [Scissors, Smartphone, BookOpen, Utensils, Shirt, Printer, Wrench, Camera, GraduationCap, WashingMachine, Car, PartyPopper] as const

function iconForCategory(name: string, index: number) {
  const value = name.toLowerCase()
  if (value.includes('beauty') || value.includes('hair') || value.includes('barb')) return Scissors
  if (value.includes('tech') || value.includes('phone') || value.includes('gadget')) return Smartphone
  if (value.includes('academic') || value.includes('book')) return BookOpen
  if (value.includes('food') || value.includes('cater')) return Utensils
  if (value.includes('fashion') || value.includes('cloth') || value.includes('tailor')) return Shirt
  if (value.includes('print') || value.includes('stationery')) return Printer
  if (value.includes('repair') || value.includes('maintenance')) return Wrench
  if (value.includes('photo') || value.includes('video')) return Camera
  if (value.includes('tutor') || value.includes('education')) return GraduationCap
  if (value.includes('laundry')) return WashingMachine
  if (value.includes('transport') || value.includes('logistic')) return Car
  if (value.includes('event') || value.includes('decor')) return PartyPopper
  return categoryIcons[index % categoryIcons.length]
}

function vendorIsPublic(vendor: any) {
  if (vendor.marketplace_status === 'active') return true
  return vendor.marketplace_status === 'suspended' && vendor.suspended_until && new Date(vendor.suspended_until) <= new Date()
}

async function getLandingData() {
  try {
    const admin = createAdminClient()
    const [{ data: rawVendors }, { data: categories }, { data: institutionLinks }] = await Promise.all([
      admin
        .from('vendor_profiles')
        .select('id,business_name,slug,description,location_text,logo_url,cover_url,average_rating,review_count,verification_status,marketplace_status,suspended_until')
        .eq('verification_status', 'approved'),
      admin.from('categories').select('id,name,slug').eq('is_active', true).order('name'),
      admin.from('vendor_institutions').select('vendor_id,institution_id').eq('status', 'approved'),
    ])

    const approvedCampusVendors = new Set((institutionLinks || []).map((row:any) => row.vendor_id))
    const vendors = (rawVendors || []).filter((vendor:any) => approvedCampusVendors.has(vendor.id) && vendorIsPublic(vendor))
    const vendorIds = vendors.map((vendor:any) => vendor.id)

    const [{ data: products }, { data: services }] = vendorIds.length
      ? await Promise.all([
          admin.from('vendor_products').select('id,vendor_id,category_id,name,price_ngn,pricing_type,cover_image_url').in('vendor_id', vendorIds).eq('is_active', true),
          admin.from('vendor_services').select('id,vendor_id,category_id,name,price_from').in('vendor_id', vendorIds).eq('is_active', true),
        ])
      : [{ data: [] as any[] }, { data: [] as any[] }] as any

    const vendorsByCategory = new Map<string, Set<string>>()
    for (const item of [...(products || []), ...(services || [])]) {
      if (!item.category_id) continue
      const set = vendorsByCategory.get(item.category_id) || new Set<string>()
      set.add(item.vendor_id)
      vendorsByCategory.set(item.category_id, set)
    }

    const categoryRows = (categories || []).map((category:any, index:number) => ({
      ...category,
      count: vendorsByCategory.get(category.id)?.size || 0,
      Icon: iconForCategory(category.name, index),
    }))

    const productByVendor = new Map<string, any[]>()
    const serviceByVendor = new Map<string, any[]>()
    for (const item of products || []) {
      const list = productByVendor.get(item.vendor_id) || []
      list.push(item)
      productByVendor.set(item.vendor_id, list)
    }
    for (const item of services || []) {
      const list = serviceByVendor.get(item.vendor_id) || []
      list.push(item)
      serviceByVendor.set(item.vendor_id, list)
    }

    const featured = [...vendors]
      .sort((a:any,b:any) => Number(b.average_rating || 0) - Number(a.average_rating || 0) || Number(b.review_count || 0) - Number(a.review_count || 0))
      .slice(0, 6)
      .map((vendor:any) => {
        const vendorProducts = productByVendor.get(vendor.id) || []
        const vendorServices = serviceByVendor.get(vendor.id) || []
        const prices = [
          ...vendorProducts.filter((x:any) => x.pricing_type !== 'contact' && x.price_ngn).map((x:any) => Number(x.price_ngn)),
          ...vendorServices.filter((x:any) => x.price_from).map((x:any) => Number(x.price_from)),
        ].filter((x:number) => Number.isFinite(x) && x > 0)
        return {
          ...vendor,
          startingPrice: prices.length ? Math.min(...prices) : null,
          listingCount: vendorProducts.length + vendorServices.length,
        }
      })

    return {
      categories: categoryRows,
      featured,
      vendorCount: vendors.length,
      productCount: (products || []).length,
      serviceCount: (services || []).length,
    }
  } catch {
    return { categories: [] as any[], featured: [] as any[], vendorCount: 0, productCount: 0, serviceCount: 0 }
  }
}

export default async function HomePage() {
  const landing = await getLandingData()
  const baseUrl = (process.env.NEXT_PUBLIC_APP_URL || 'https://campuslink.name.ng').replace(/\/$/, '')
  const websiteJsonLd = {
    '@context': 'https://schema.org',
    '@type': 'WebSite',
    name: 'Campus Link',
    alternateName: 'CampusLink',
    url: baseUrl,
    description: 'Discover trusted campus vendors, products and services around your school.',
    inLanguage: 'en',
  }
  const organizationJsonLd = {
    '@context': 'https://schema.org',
    '@type': 'Organization',
    name: 'Campus Link',
    url: baseUrl,
    logo: `${baseUrl}/brand/logo`,
    description: 'Campus-specific discovery platform connecting students with approved vendors, products and services.',
  }
  const jsonLd = JSON.stringify([websiteJsonLd, organizationJsonLd]).replace(/</g, '\\u003c')

  return (
    <main className="legacy-home">
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: jsonLd }} />
      <header className="site-header scrolled" id="siteHeader">
        <div className="header-inner container">
          <Link href="/" className="logo">
            <div className="logo-mark"><img src="/brand/logo" alt="Campuslink Logo" width="36" height="36" /></div>
            <span className="logo-text">Campus<strong>link</strong></span>
          </Link>

          <nav className="main-nav" id="mainNav">
            <Link href="/" className="nav-link active">Home</Link>
            <a href="#featured" className="nav-link">Browse</a>
            <a href="#categories" className="nav-link">Categories</a>
            <a href="#how" className="nav-link">How It Works</a>
            <a href="#pricing" className="nav-link">Pricing</a>
            <Link href="/register" className="nav-link nav-vendor-btn"><Store size={15} /> Register</Link>
            <Link href="/login" className="nav-link nav-login-btn">Login</Link>
          </nav>

          <div className="header-school-logo"><img src="https://raw.githubusercontent.com/Wikis-tech/campuslink/master/assets/images/Uat%20logo.png" alt="University Logo" width="40" height="40" /></div>
        </div>
      </header>

      <section className="hero" id="hero">
        <div className="hero-bg-shapes"><div className="shape shape-1" /><div className="shape shape-2" /><div className="shape shape-3" /></div>

        <div className="container hero-content">
          <h1 className="hero-headline animate-slide-up">Find trusted campus<br />services <em>instantly.</em></h1>
          <p className="hero-sub animate-fade-up delay-2">Campuslink connects students and campus community members with verified, reviewed service providers within your university — safely, transparently, and at no cost to browse.</p>

          <div className="hero-search animate-fade-up delay-3">
            <div className="search-wrap">
              <div className="search-category"><LayoutGrid size={16} /><select aria-label="Select category" defaultValue=""><option value="">All Categories</option><option value="beauty">Beauty & Grooming</option><option value="tech">Tech & Gadgets</option><option value="academic">Academic Support</option><option value="food">Food & Catering</option><option value="fashion">Fashion & Tailoring</option><option value="printing">Printing</option><option value="repairs">Repairs</option><option value="photography">Photography</option><option value="tutoring">Tutoring</option></select></div>
              <div className="search-divider" />
              <div className="search-input-wrap"><Search size={16} /><input type="text" placeholder="Search vendors or services…" aria-label="Search vendors" /></div>
              <Link className="search-btn" href="/register"><Search size={16} /><span>Search</span></Link>
            </div>
          </div>

          <div className="hero-actions animate-fade-up delay-4"><a href="#featured" className="btn btn-white"><Compass size={16} /> Browse Services</a><Link href="/register" className="btn btn-outline-white"><Store size={16} /> Register as Vendor</Link></div>
          <div className="hero-trust animate-fade-up delay-5"><div className="trust-pill"><CheckCircle2 size={14} /> Verified Vendors</div><div className="trust-pill"><ShieldCheck size={14} /> Students Browse Free</div><div className="trust-pill"><Star size={14} /> Moderated Reviews</div></div>
        </div>
        <div className="hero-scroll-indicator"><div className="scroll-dot" /></div>
      </section>

      <section className="stats-strip">
        <div className="container">
          <div className="stats-inner">
            <div className="stat-pill"><ShieldCheck /><div><strong className="stat-num">{landing.vendorCount}</strong><span>Approved vendors currently public</span></div></div>
            <div className="strip-divider" />
            <div className="stat-pill"><Store /><div><strong className="stat-num">{landing.productCount}</strong><span>Active products</span></div></div>
            <div className="strip-divider" />
            <div className="stat-pill"><Wrench /><div><strong className="stat-num">{landing.serviceCount}</strong><span>Active services</span></div></div>
            <div className="strip-divider" />
            <div className="stat-pill"><Compass /><div><strong className="stat-num">Free</strong><span>Student browsing & contact</span></div></div>
          </div>
        </div>
      </section>

      <section className="section categories-section" id="categories">
        <div className="container">
          <div className="section-label"><div className="label-line" /><span>Explore</span><div className="label-line" /></div>
          <h2 className="section-headline">Browse by Category</h2>
          <p className="section-sub">Discover verified service providers across every campus need.</p>
          <div className="categories-grid">
            {landing.categories.length ? landing.categories.map((category:any) => {
              const Icon = category.Icon
              return <Link href="/register" className="cat-card" key={category.id}><div className="cat-icon"><Icon /></div><div className="cat-name">{category.name}</div><div className="cat-count">{category.count} active vendor{category.count === 1 ? '' : 's'}</div></Link>
            }) : <div className="landing-empty-state">Categories will appear here as Campus Link activates real campus listings.</div>}
          </div>
        </div>
      </section>

      <section className="section featured-section" id="featured">
        <div className="container">
          <div className="featured-header"><div><div className="section-label"><div className="label-line" /><span>Handpicked</span><div className="label-line" /></div><h2 className="section-headline">Featured Vendors</h2><p className="section-sub">Top-rated, verified service providers trusted by your peers.</p></div><Link href="/register" className="btn btn-outline-primary">View All <ArrowRight size={16} /></Link></div>
          <div className="vendors-grid">
            {landing.featured.length ? landing.featured.map((vendor:any) => <article className="vendor-card" key={vendor.id}><div className="vendor-card-img legacy-vendor-placeholder">{vendor.cover_url ? <img src={vendor.cover_url} alt="" className="landing-vendor-cover"/> : null}<div className="vendor-badge"><ShieldCheck size={14} /> Verified</div><div className="vendor-logo">{vendor.logo_url ? <img src={vendor.logo_url} alt="" /> : <span>{vendor.business_name.slice(0, 2).toUpperCase()}</span>}</div></div><div className="vendor-card-body"><div className="vendor-card-header"><div><div className="vendor-name">{vendor.business_name}</div><span className="vendor-cat">{vendor.listingCount} active listing{vendor.listingCount === 1 ? '' : 's'}</span></div><div className="vendor-rating"><span className="stars">★★★★★</span><span className="rating-val">{Number(vendor.average_rating || 0).toFixed(1)}</span></div></div><p className="vendor-desc">{vendor.description || 'Approved Campus Link vendor.'}</p><div className="vendor-meta">{vendor.location_text ? <div className="vendor-meta-item"><Tag size={14} /> {vendor.location_text}</div> : null}<div className="vendor-meta-item"><Tag size={14} /> {vendor.startingPrice ? `From ₦${vendor.startingPrice.toLocaleString()}` : 'Contact for pricing'}</div></div><div className="vendor-actions"><Link href={`/share/vendor/${encodeURIComponent(vendor.slug)}`} className="btn btn-sm btn-outline">View vendor</Link></div></div></article>) : <div className="landing-empty-state">Approved vendors will appear here automatically as Campus Link grows.</div>}
          </div>
        </div>
      </section>

      <section className="section how-section" id="how">
        <div className="container">
          <div className="section-label"><div className="label-line" /><span>Simple Process</span><div className="label-line" /></div>
          <h2 className="section-headline">How Campuslink Works</h2>
          <div className="steps-timeline">
            <div className="step-item"><div className="step-connector-line" /><div className="step-dot"><span>01</span></div><div className="step-content glass-card"><div className="step-icon-wrap"><Search /></div><h3>Browse Vendors</h3><p>Search by category, name, price range, or rating. Filter to find the right service provider for your need.</p></div></div>
            <div className="step-item right"><div className="step-dot"><span>02</span></div><div className="step-content glass-card"><div className="step-icon-wrap"><PhoneCall /></div><h3>Contact Directly</h3><p>Reach vendors instantly via phone call or WhatsApp. No in-app messaging — communication stays direct and personal.</p></div><div className="step-connector-line" /></div>
            <div className="step-item"><div className="step-connector-line" /><div className="step-dot"><span>03</span></div><div className="step-content glass-card"><div className="step-icon-wrap"><Handshake /></div><h3>Complete Offline</h3><p>Negotiate, agree, and complete your transaction directly with the vendor. Campuslink does not process student-to-vendor payments.</p></div></div>
            <div className="step-item right"><div className="step-dot"><span>04</span></div><div className="step-content glass-card"><div className="step-icon-wrap"><Star /></div><h3>Leave a Review</h3><p>Rate your experience and help fellow students make better decisions. Reviews remain subject to moderation.</p></div><div className="step-connector-line" /></div>
            <div className="step-item"><div className="step-dot"><span>05</span></div><div className="step-content glass-card"><div className="step-icon-wrap"><Flag /></div><h3>Report Issues</h3><p>Encountered a problem? Submit a complaint and our admin team can investigate the vendor account and evidence.</p></div></div>
          </div>
          <div className="disclaimer-pill"><Info /><p>Campuslink operates as a digital directory only. We do not provide services, process student-to-vendor payments, or facilitate in-app messaging.</p></div>
        </div>
      </section>

      <section className="section trust-section" id="trust">
        <div className="trust-bg-img"><img src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=1400&auto=format&fit=crop&q=50" alt="" aria-hidden="true" /><div className="trust-bg-overlay" /></div>
        <div className="container">
          <div className="trust-grid">
            <div className="trust-content">
              <div className="section-label light"><div className="label-line light" /><span>Your Safety First</span><div className="label-line light" /></div>
              <h2 className="section-headline light">Built on Trust &amp; Transparency</h2>
              <p className="section-sub light">Vendor verification and campus approval are trust decisions. Paid plans only unlock business tools and never buy a verification badge.</p>
              <div className="trust-feats">
                <div className="trust-feat-item"><div className="tfi-icon"><ShieldCheck /></div><div><h4>Identity Verification</h4><p>Vendors are reviewed before they can become discoverable to students.</p></div></div>
                <div className="trust-feat-item"><div className="tfi-icon"><Lock /></div><div><h4>Payment Never Buys Trust</h4><p>Free and Pro affect vendor tools only. Campus approval and verification remain separate.</p></div></div>
                <div className="trust-feat-item"><div className="tfi-icon"><Gavel /></div><div><h4>Complaint Resolution</h4><p>Reports and reviews give Campus Link a documented safety trail for moderation and investigations.</p></div></div>
              </div>
            </div>
            <div className="trust-cards-col">
              <div className="tc-card glass-card-dark"><div className="tcc-icon"><Award /></div><strong>Vendor Verified</strong><span>Trust status is earned through review, not payment.</span></div>
              <div className="tc-card glass-card-dark shift"><div className="tcc-stars">★★★★★</div><p>Student feedback helps the campus community make more informed choices.</p><span>— Published reviews stay separate from paid promotion</span></div>
              <div className="tc-card glass-card-dark"><div className="tcc-stat">₦0</div><strong>Student access</strong><span>Browse, save, review and contact approved vendors for free.</span></div>
            </div>
          </div>
        </div>
      </section>

      <section className="cl-pricing-section" id="pricing">
        <div className="cl-pricing-wrap">
          <div className="cl-pricing-head"><span className="cl-pricing-kicker"><Sparkles size={15}/> Vendor pricing</span><h2>Start free. Pay only when growth tools become useful.</h2><p>Campus Link keeps student access free and keeps trust separate from payment. Vendors can build a real campus presence before deciding whether Pro is worth it.</p></div>
          <div className="cl-pricing-grid">
            <article className="cl-price-card">
              <div className="plan-top"><div className="plan-name">Free</div><span className="plan-badge">Useful by default</span></div>
              <div className="plan-price"><strong>₦0</strong><span>/ forever</span></div>
              <p className="plan-copy">For student entrepreneurs and campus businesses getting started.</p>
              <ul><li><CheckCircle2/> Verified vendor profile when approved</li><li><CheckCircle2/> 1 approved campus</li><li><CheckCircle2/> Up to 5 active services</li><li><CheckCircle2/> Up to 6 portfolio items</li><li><CheckCircle2/> Reviews, ratings, saves and direct contact</li><li><CheckCircle2/> Basic campus discovery dashboard</li></ul>
              <div className="plan-note">Free is intentionally useful. Verification is earned and is never part of a paid plan.</div>
              <Link href="/register" className="plan-cta">Start free <ArrowRight size={16}/></Link>
            </article>
            <article className="cl-price-card pro">
              <div className="plan-top"><div className="plan-name">Pro</div><span className="plan-badge">Growth tools</span></div>
              <div className="plan-price"><strong>₦2,500</strong><span>/ month</span></div>
              <p className="plan-copy">For vendors ready for more capacity, analytics and future promotion tools.</p>
              <ul><li><CheckCircle2/> Everything in Free</li><li><CheckCircle2/> Up to 20 active services</li><li><CheckCircle2/> Up to 30 portfolio items</li><li><CheckCircle2/> Advanced visibility and contact analytics</li><li><CheckCircle2/> Profile insights and improvement guidance</li><li><CheckCircle2/> Featured and promotion eligibility</li></ul>
              <div className="plan-note"><strong>₦24,000/year</strong> — save ₦6,000 versus 12 monthly payments. Paying for Pro never changes verification or campus approval.</div>
              <Link href="/register" className="plan-cta">Create vendor account <ArrowRight size={16}/></Link>
            </article>
          </div>
          <div className="cl-pricing-trust"><div><ShieldCheck size={18}/><div><strong>Students stay free</strong><span>No subscription to browse or contact approved vendors.</span></div></div><div><Lock size={18}/><div><strong>Trust is not for sale</strong><span>Paid plans never grant verification or campus approval.</span></div></div><div><Handshake size={18}/><div><strong>No service commission</strong><span>Student-to-vendor transactions remain direct between both parties.</span></div></div></div>
        </div>
      </section>

      <section className="section vendor-cta-section" id="vendor-cta">
        <div className="container"><div className="vcta-card"><div className="vcta-inner"><div className="vcta-text"><div className="section-label light"><div className="label-line light" /><span>For Service Providers</span><div className="label-line light" /></div><h2>Build trust first. <br />Grow from there.</h2><p>Create a useful vendor profile for free. When you need more capacity and business insights, Pro is there — without changing your verification status.</p></div><div className="vcta-actions"><Link href="/register" className="btn btn-vcta-primary"><Store size={16} /> Register as Vendor</Link><a href="#pricing" className="btn btn-vcta-ghost">Compare Free & Pro <ArrowRight size={16} /></a></div></div></div></div>
      </section>

      <footer className="cl-public-footer">
        <div className="container cl-public-footer-inner">
          <div className="cl-public-footer-grid">
            <div className="cl-public-footer-brand">
              <Link href="/" className="cl-public-footer-logo" aria-label="Campus Link home">
                <span className="cl-public-footer-logo-mark">
                  <img
                    src="/brand/logo"
                    alt=""
                    width="32"
                    height="32"
                  />
                </span>
                <span>Campus<strong>Link</strong></span>
              </Link>
              <p>Trusted campus discovery for students, vendors and university communities.</p>
              <p>Campus Link helps people discover and assess approved vendors. Transactions remain directly between students and vendors.</p>
              <a className="cl-public-footer-contact" href="mailto:campuslinkd@gmail.com">campuslinkd@gmail.com</a>
            </div>

            <div className="cl-public-footer-col">
              <h4>Explore</h4>
              <a href="#featured">Browse Vendors</a>
              <a href="#categories">Categories</a>
              <a href="#how">How It Works</a>
              <a href="#trust">Trust & Safety</a>
            </div>

            <div className="cl-public-footer-col">
              <h4>Vendors</h4>
              <Link href="/register">Create an Account</Link>
              <Link href="/login">Vendor Login</Link>
              <a href="#pricing">Free & Pro</a>
              <a href="#trust">Verification</a>
            </div>

            <div className="cl-public-footer-col">
              <h4>Campus Link</h4>
              <Link href="/register">Student Sign Up</Link>
              <Link href="/login">Sign In</Link>
              <a href="#pricing">Pricing</a>
              <a href="#trust">Safety Principles</a>
            </div>
          </div>

          <div className="cl-public-footer-divider" />

          <div className="cl-public-footer-bottom">
            <p>© 2026 Campus Link. All rights reserved.</p>
            <div className="cl-public-footer-principles" aria-label="Campus Link principles">
              <span>Students browse free</span>
              <span>Trust is not for sale</span>
              <span>Direct vendor contact</span>
            </div>
          </div>
        </div>
      </footer>
    </main>
  )
}
