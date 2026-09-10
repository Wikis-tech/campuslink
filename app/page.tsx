import Link from 'next/link'
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

const categories = [
  [Scissors, 'Beauty & Grooming', '42 vendors'],
  [Smartphone, 'Tech & Gadgets', '38 vendors'],
  [BookOpen, 'Academic Support', '55 vendors'],
  [Utensils, 'Food & Catering', '61 vendors'],
  [Shirt, 'Fashion & Tailoring', '34 vendors'],
  [Printer, 'Printing & Stationery', '28 vendors'],
  [Wrench, 'Repairs & Maintenance', '22 vendors'],
  [Camera, 'Photography', '19 vendors'],
  [GraduationCap, 'Tutoring', '47 vendors'],
  [WashingMachine, 'Laundry Services', '15 vendors'],
  [Car, 'Transport & Logistics', '12 vendors'],
  [PartyPopper, 'Events & Décor', '18 vendors'],
] as const

const vendorSamples = [
  ['Campus Fix', 'Tech & Gadgets', 'Phone and laptop diagnostics, repairs and accessories.', 'Main Campus', 'From ₦3,000', '4.9'],
  ['Glow Studio', 'Beauty & Grooming', 'Braids, natural hair styling and beauty services for students.', 'Student Area', 'From ₦2,500', '4.8'],
  ['Scholar Hub', 'Academic Support', 'Tutoring and academic support across core undergraduate courses.', 'Near Campus', 'From ₦2,000', '4.7'],
] as const

export default function HomePage() {
  return (
    <main className="legacy-home">
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/Wikis-tech/campuslink@527f9fb9e1be4aa1fdbe198e86acc6833ac920ad/assets/css/main.css" />
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/Wikis-tech/campuslink@527f9fb9e1be4aa1fdbe198e86acc6833ac920ad/assets/css/hero.css" />

      <header className="site-header scrolled" id="siteHeader">
        <div className="header-inner container">
          <Link href="/" className="logo">
            <div className="logo-mark"><img src="https://raw.githubusercontent.com/Wikis-tech/campuslink/master/assets/images/campuslink-logo-white.png" alt="Campuslink Logo" width="36" height="36" /></div>
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
            <div className="stat-pill"><ShieldCheck /><div><strong className="stat-num">Verified</strong><span>Vendor identity review</span></div></div>
            <div className="strip-divider" />
            <div className="stat-pill"><GraduationCap /><div><strong className="stat-num">Campus-first</strong><span>School-specific discovery</span></div></div>
            <div className="strip-divider" />
            <div className="stat-pill"><Compass /><div><strong className="stat-num">Free</strong><span>Student browsing & contact</span></div></div>
            <div className="strip-divider" />
            <div className="stat-pill"><PhoneCall /><div><strong className="stat-num">Direct</strong><span>WhatsApp vendor contact</span></div></div>
          </div>
        </div>
      </section>

      <section className="section categories-section" id="categories">
        <div className="container">
          <div className="section-label"><div className="label-line" /><span>Explore</span><div className="label-line" /></div>
          <h2 className="section-headline">Browse by Category</h2>
          <p className="section-sub">Discover verified service providers across every campus need.</p>
          <div className="categories-grid">
            {categories.map(([Icon, name, count]) => <Link href="/register" className="cat-card" key={name}><div className="cat-icon"><Icon /></div><div className="cat-name">{name}</div><div className="cat-count">{count}</div></Link>)}
          </div>
        </div>
      </section>

      <section className="section featured-section" id="featured">
        <div className="container">
          <div className="featured-header"><div><div className="section-label"><div className="label-line" /><span>Handpicked</span><div className="label-line" /></div><h2 className="section-headline">Featured Vendors</h2><p className="section-sub">Top-rated, verified service providers trusted by your peers.</p></div><Link href="/register" className="btn btn-outline-primary">View All <ArrowRight size={16} /></Link></div>
          <div className="vendors-grid">
            {vendorSamples.map(([name, category, desc, location, price, rating]) => <article className="vendor-card" key={name}><div className="vendor-card-img legacy-vendor-placeholder"><div className="vendor-badge"><ShieldCheck size={14} /> Verified</div><div className="vendor-logo"><span>{name.slice(0, 2).toUpperCase()}</span></div></div><div className="vendor-card-body"><div className="vendor-card-header"><div><div className="vendor-name">{name}</div><span className="vendor-cat">{category}</span></div><div className="vendor-rating"><span className="stars">★★★★★</span><span className="rating-val">{rating}</span></div></div><p className="vendor-desc">{desc}</p><div className="vendor-meta"><div className="vendor-meta-item"><Tag size={14} /> {location}</div><div className="vendor-meta-item"><Tag size={14} /> {price}</div></div><div className="vendor-actions"><Link href="/register" className="btn btn-sm btn-outline">View</Link></div></div></article>)}
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

      <footer className="site-footer">
        <div className="container">
          <div className="footer-layout">
            <div className="footer-brand"><div className="footer-logo">Campus<strong>link</strong></div><p className="footer-desc">A secure, lightweight digital campus service directory connecting students with verified vendors within the university environment.</p><p className="footer-desc">CampusLink is a directory platform only. We do not provide services or process student-to-vendor transactions.</p><p className="footer-contact">campuslinkd@gmail.com</p></div>
            <div className="footer-links"><h4>Quick Links</h4><Link href="/">Home</Link><a href="#featured">Browse Services</a><a href="#categories">All Categories</a><a href="#how">How It Works</a><a href="#pricing">Vendor Pricing</a></div>
            <div className="footer-links"><h4>For Vendors</h4><Link href="/register">Register as Vendor</Link><Link href="/login">Vendor Login</Link><a href="#pricing">Free & Pro Plans</a><a href="#trust">Trust & Verification</a><a href="#trust">Complaint Resolution</a></div>
            <div className="footer-links"><h4>Legal & Policies</h4><a href="#">General Terms & Conditions</a><a href="#">User Terms & Conditions</a><a href="#">Vendor Terms & Conditions</a><a href="#">Privacy Policy</a><a href="#">Refund Policy</a></div>
          </div>
          <div className="footer-bottom"><p>© 2026 CampusLink. All rights reserved. | Governed by Nigerian Law</p><p>Built for the campus community</p></div>
        </div>
      </footer>
    </main>
  )
}
