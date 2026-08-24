import { ArrowRight, BadgeCheck, Search, Store, Users } from 'lucide-react'

const categories = ['Hair & Beauty', 'Phone & Laptop Repair', 'Tutors', 'Food', 'Photography', 'Laundry']

export default function HomePage() {
  return (
    <main>
      <header className="nav-wrap">
        <a className="brand" href="#">Campus<span>Link</span></a>
        <nav className="nav-actions">
          <a href="#how">How it works</a>
          <a href="#vendors">For vendors</a>
          <a className="btn btn-ghost" href="/login">Sign in</a>
          <a className="btn btn-primary" href="/register">Get started</a>
        </nav>
      </header>

      <section className="hero">
        <div className="hero-copy">
          <div className="eyebrow"><BadgeCheck size={16} /> Verified campus services</div>
          <h1>Find the right person<br />for what you need <span>on campus.</span></h1>
          <p>Discover verified vendors, compare ratings and connect directly — without depending on random referrals.</p>

          <div className="search-shell">
            <Search size={20} />
            <input aria-label="Search services" placeholder="Try “phone repair”, “braids” or “math tutor”" />
            <button>Search <ArrowRight size={17} /></button>
          </div>

          <div className="category-row">
            {categories.map((category) => <button key={category}>{category}</button>)}
          </div>
        </div>

        <div className="hero-panel" aria-label="Campus Link preview">
          <div className="preview-top"><span>Popular around your campus</span><span className="online-dot">Live</span></div>
          <div className="vendor-card featured">
            <div className="avatar">CF</div>
            <div><strong>Campus Fix</strong><p>Phone & Laptop Repair</p><span>★ 4.9 · Verified</span></div>
          </div>
          <div className="vendor-grid">
            <div className="metric"><Users /><strong>Students</strong><span>Find trusted services faster</span></div>
            <div className="metric"><Store /><strong>Vendors</strong><span>Reach students who need you</span></div>
          </div>
          <div className="trust-line"><BadgeCheck size={18} /> Identity, campus and vendor verification built into the experience.</div>
        </div>
      </section>

      <section className="proof" id="how">
        <span>Discover</span><i>→</i><span>Compare</span><i>→</i><span>Connect</span>
      </section>
    </main>
  )
}
