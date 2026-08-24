import Link from 'next/link'
import {
  ArrowRight,
  BadgeCheck,
  BookOpenCheck,
  CheckCircle2,
  Compass,
  Flag,
  LockKeyhole,
  MessageCircle,
  Search,
  ShieldCheck,
  Sparkles,
  Star,
  Store,
  Users,
} from 'lucide-react'
import { Brand } from '@/components/Brand'

const categories = [
  'Hair & Beauty',
  'Phone & Laptop Repair',
  'Tutoring',
  'Food & Catering',
  'Photography',
  'Laundry & Cleaning',
  'Design & Printing',
  'Fashion & Tailoring',
]

const steps = [
  { icon: Search, title: 'Discover', copy: 'Search by service, category or campus and compare relevant vendors around you.' },
  { icon: BadgeCheck, title: 'Check trust signals', copy: 'Review verification status, ratings, service information and campus coverage before you reach out.' },
  { icon: MessageCircle, title: 'Connect directly', copy: 'Contact the vendor directly when you are comfortable with the profile and service information.' },
  { icon: Star, title: 'Share your experience', copy: 'Verified students can leave moderated reviews that help the next person make a better choice.' },
]

export default function HomePage() {
  return (
    <main className="landing-page">
      <header className="site-nav">
        <div className="site-nav-inner">
          <Brand />
          <nav className="desktop-nav" aria-label="Primary navigation">
            <a href="#explore">Explore</a>
            <a href="#how">How it works</a>
            <a href="#trust">Safety</a>
            <a href="#vendors">For vendors</a>
          </nav>
          <div className="nav-ctas">
            <Link className="btn btn-ghost" href="/login">Sign in</Link>
            <Link className="btn btn-primary" href="/register">Get started <ArrowRight size={16} /></Link>
          </div>
        </div>
      </header>

      <section className="landing-hero">
        <div className="hero-orbit hero-orbit-one" />
        <div className="hero-orbit hero-orbit-two" />
        <div className="landing-container hero-layout">
          <div className="hero-copy-block reveal-up">
            <div className="eyebrow"><ShieldCheck size={16} /> Campus-specific service discovery</div>
            <h1>Find trusted campus services <em>without the guesswork.</em></h1>
            <p>
              Campus Link helps students discover verified vendors and service providers around their school,
              compare trust signals and connect directly when they are ready.
            </p>

            <div className="hero-search-shell" role="search">
              <Search size={20} />
              <input aria-label="Search Campus Link" placeholder="Search phone repair, braids, tutor, photography…" />
              <Link href="/register" className="hero-search-btn">Start exploring <ArrowRight size={17} /></Link>
            </div>

            <div className="hero-actions-row">
              <Link href="/register" className="btn btn-primary btn-lg"><Compass size={18} /> Join Campus Link</Link>
              <a href="#how" className="btn btn-soft btn-lg">See how it works</a>
            </div>

            <div className="trust-inline">
              <span><CheckCircle2 size={16} /> Campus-scoped discovery</span>
              <span><CheckCircle2 size={16} /> Admin-reviewed vendors</span>
              <span><CheckCircle2 size={16} /> Moderated reviews</span>
            </div>
          </div>

          <div className="hero-showcase reveal-scale" aria-label="Campus Link experience preview">
            <div className="showcase-glow" />
            <div className="showcase-window glass-panel">
              <div className="showcase-topbar">
                <div><span className="mini-dot" /><span className="mini-dot" /><span className="mini-dot" /></div>
                <span>Popular around your campus</span>
              </div>

              <div className="featured-vendor-row">
                <div className="vendor-avatar">CF</div>
                <div className="vendor-summary">
                  <strong>Campus Fix</strong>
                  <span>Phone & Laptop Repair</span>
                  <small><Star size={13} fill="currentColor" /> 4.9 · Campus approved</small>
                </div>
                <BadgeCheck size={22} />
              </div>

              <div className="showcase-grid">
                <div className="showcase-stat">
                  <Users />
                  <strong>Students</strong>
                  <span>Find relevant services faster.</span>
                </div>
                <div className="showcase-stat">
                  <Store />
                  <strong>Vendors</strong>
                  <span>Reach the campuses you actually serve.</span>
                </div>
              </div>

              <div className="showcase-trust-card">
                <ShieldCheck size={19} />
                <div><strong>Trust is part of the product.</strong><span>Identity, campus and document checks are built into onboarding.</span></div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section className="landing-strip">
        <div className="landing-container strip-inner">
          <div><ShieldCheck /><span>Verified profiles</span></div>
          <div><BookOpenCheck /><span>Campus-linked accounts</span></div>
          <div><MessageCircle /><span>Direct contact</span></div>
          <div><Flag /><span>Report & review tools</span></div>
        </div>
      </section>

      <section className="landing-section" id="explore">
        <div className="landing-container">
          <div className="section-heading reveal-up">
            <span className="section-kicker">Explore campus services</span>
            <h2>What do you need today?</h2>
            <p>Start with a category, then narrow the results to vendors approved for your school.</p>
          </div>
          <div className="category-cloud">
            {categories.map((category) => (
              <Link key={category} href="/register" className="category-chip">
                <Sparkles size={16} /> {category} <ArrowRight size={15} />
              </Link>
            ))}
          </div>
        </div>
      </section>

      <section className="landing-section section-muted" id="how">
        <div className="landing-container">
          <div className="section-heading reveal-up">
            <span className="section-kicker">Simple by design</span>
            <h2>Discover. Check. Connect.</h2>
            <p>The experience stays focused on finding the right person, not adding unnecessary complexity.</p>
          </div>
          <div className="steps-grid">
            {steps.map(({ icon: Icon, title, copy }, index) => (
              <article key={title} className="step-card reveal-up" style={{ animationDelay: `${index * 90}ms` }}>
                <span className="step-number">0{index + 1}</span>
                <div className="step-icon"><Icon size={22} /></div>
                <h3>{title}</h3>
                <p>{copy}</p>
              </article>
            ))}
          </div>
        </div>
      </section>

      <section className="trust-section-modern" id="trust">
        <div className="trust-image" aria-hidden="true" />
        <div className="trust-overlay" />
        <div className="landing-container trust-layout">
          <div className="trust-copy reveal-up">
            <span className="section-kicker light">Safety first</span>
            <h2>Trust has to be earned before a vendor gets visibility.</h2>
            <p>
              Campus Link separates registration from approval. A vendor can create an account, but public visibility only comes after verification and campus approval.
            </p>
          </div>
          <div className="trust-points glass-panel-dark">
            <div><BadgeCheck /><span><strong>Identity & document review</strong><small>Verification evidence stays private and is reviewed before approval.</small></span></div>
            <div><LockKeyhole /><span><strong>Permission-controlled accounts</strong><small>Students, vendors and admins do not share the same access privileges.</small></span></div>
            <div><Flag /><span><strong>Complaints & moderation</strong><small>Reviews and reports can be investigated instead of silently disappearing.</small></span></div>
          </div>
        </div>
      </section>

      <section className="landing-section" id="vendors">
        <div className="landing-container vendor-cta">
          <div>
            <span className="section-kicker">For campus vendors</span>
            <h2>Be easier to discover by the students you already serve.</h2>
            <p>Create your profile, choose your campus, add your services and submit your verification for review.</p>
          </div>
          <Link href="/register" className="btn btn-primary btn-lg"><Store size={18} /> Register as a vendor</Link>
        </div>
      </section>

      <footer className="site-footer">
        <div className="landing-container footer-inner">
          <Brand light />
          <p>Discover trusted services around your campus.</p>
          <div className="footer-links"><Link href="/login">Sign in</Link><Link href="/register">Create account</Link></div>
        </div>
      </footer>
    </main>
  )
}
