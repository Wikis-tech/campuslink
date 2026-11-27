import type { Metadata } from 'next'
import Link from 'next/link'
import { notFound } from 'next/navigation'
import { BadgeCheck, MessageCircle, Package, ShieldCheck, Star, Wrench } from 'lucide-react'
import { createAdminClient } from '@/lib/supabase/admin'
import { createClient } from '@/lib/supabase/server'
import { getCanonicalOrigin, sanitizePublicSeoText } from '@/lib/seo-privacy'

const BASE_URL = getCanonicalOrigin()

function cleanDescription(value: string | null | undefined) {
  return sanitizePublicSeoText(value || 'Verified CampusMode vendor.', 180) || 'Verified CampusMode vendor.'
}

async function getShareVendor(slug: string) {
  const admin = createAdminClient()
  const { data: vendor } = await admin
    .from('vendor_profiles')
    .select('id,business_name,slug,description,logo_url,cover_url,average_rating,review_count,verification_status,marketplace_status,suspended_until,created_at')
    .eq('slug', slug)
    .eq('verification_status', 'approved')
    .maybeSingle()

  if (!vendor) return null
  const safe =
    vendor.marketplace_status === 'active' ||
    (vendor.marketplace_status === 'suspended' &&
      vendor.suspended_until &&
      new Date(vendor.suspended_until) <= new Date())
  if (!safe) return null

  const { data: links } = await admin
    .from('vendor_institutions')
    .select('institution_id,institutions!inner(is_active)')
    .eq('vendor_id', vendor.id)
    .eq('status', 'approved')
    .eq('institutions.is_active', true)

  if (!links?.length) return null

  const institutionIds = links.map((row) => row.institution_id)
  const { data: institutions } = institutionIds.length
    ? await admin.from('institutions').select('id,name').in('id', institutionIds)
    : { data: [] as Array<{ id: string; name: string }> }

  const [{ data: products }, { data: services }, { data: portfolio }] = await Promise.all([
    admin
      .from('vendor_products')
      .select('id,name,price_ngn,pricing_type,cover_image_url')
      .eq('vendor_id', vendor.id)
      .eq('is_active', true)
      .order('created_at', { ascending: false })
      .limit(24),
    admin
      .from('vendor_services')
      .select('id,name,price_from')
      .eq('vendor_id', vendor.id)
      .eq('is_active', true)
      .order('name')
      .limit(24),
    admin
      .from('vendor_portfolio_items')
      .select('id,title,description,image_url')
      .eq('vendor_id', vendor.id)
      .eq('is_active', true)
      .order('sort_order')
      .order('created_at', { ascending: false })
      .limit(18),
  ])

  return { vendor, institutions: institutions || [], products: products || [], services: services || [], portfolio: portfolio || [] }
}

export async function generateMetadata({ params }: { params: Promise<{ slug: string }> }): Promise<Metadata> {
  const { slug } = await params
  const result = await getShareVendor(slug)
  if (!result) return { title: 'Vendor not available · CampusMode', robots: { index: false, follow: false } }

  const { vendor } = result
  const title = `${vendor.business_name} · CampusMode`
  const description = cleanDescription(vendor.description)
  const url = `${BASE_URL}/share/vendor/${encodeURIComponent(vendor.slug)}`
  const image = `${BASE_URL}/api/og/vendor/${encodeURIComponent(vendor.slug)}`

  return {
    title,
    description,
    alternates: { canonical: url },
    openGraph: {
      type: 'website',
      siteName: 'CampusMode',
      title,
      description,
      url,
      images: [{ url: image, width: 1200, height: 630, alt: `${vendor.business_name} on CampusMode` }],
    },
    twitter: {
      card: 'summary_large_image',
      title,
      description,
      images: [image],
    },
    robots: {
      index: true,
      follow: true,
      googleBot: {
        index: true,
        follow: true,
        'max-image-preview': 'large',
        'max-snippet': 160,
        'max-video-preview': 0,
      },
    },
  }
}

export default async function SharedVendorPage({ params, searchParams }: { params: Promise<{ slug: string }>; searchParams: Promise<{ contact?: string }> }) {
  const { slug } = await params
  const { contact } = await searchParams
  const result = await getShareVendor(slug)
  if (!result) notFound()

  const { vendor, institutions, products, services, portfolio } = result
  // Share URLs must work for guests and search engines, while signed-in users
  // see their real session state. Do not expose protected contact information.
  const supabase = await createClient()
  const { data: { user } } = await supabase.auth.getUser()
  const { data: profile } = user
    ? await supabase.from('profiles')
        .select('account_type,institution_id,onboarding_completed_at,student_verification_status')
        .eq('id', user.id)
        .maybeSingle()
    : { data: null }

  const isStudent = profile?.account_type === 'student'
  const isOnboardedStudent = isStudent && Boolean(profile?.institution_id && profile?.onboarding_completed_at)
  const canContact = isOnboardedStudent && profile?.student_verification_status === 'verified'
  const whatsappPath = `/share/vendor/${encodeURIComponent(vendor.slug)}/contact`
  const approvedOnOwnCampus = isOnboardedStudent &&
    institutions.some((institution) => institution.id === profile?.institution_id)
  const vendorPath = `/student/vendors/${encodeURIComponent(vendor.slug)}`
  const studentReturnPath = '/student/explore-campuses'
  const initial = vendor.business_name?.slice(0, 1)?.toUpperCase() || 'V'
  const campusNames = institutions.map((item) => item.name).join(', ')
  const canonicalUrl = `${BASE_URL}/share/vendor/${encodeURIComponent(vendor.slug)}`
  const vendorJsonLd = {
    '@context': 'https://schema.org',
    '@type': 'Organization',
    name: vendor.business_name,
    url: canonicalUrl,
    description: cleanDescription(vendor.description),
    image: vendor.logo_url || vendor.cover_url || `${BASE_URL}/brand/logo`,
    areaServed: institutions.map((institution) => ({
      '@type': 'CollegeOrUniversity',
      name: institution.name,
    })),
    ...(Number(vendor.review_count || 0) > 0
      ? {
          aggregateRating: {
            '@type': 'AggregateRating',
            ratingValue: Number(vendor.average_rating || 0),
            reviewCount: Number(vendor.review_count || 0),
            bestRating: 5,
            worstRating: 1,
          },
        }
      : {}),
    isPartOf: {
      '@type': 'WebSite',
      name: 'CampusMode',
      url: BASE_URL,
    },
  }
  const jsonLd = JSON.stringify(vendorJsonLd).replace(/</g, '\\u003c')

  return (
    <main className="cl-share-vendor-page">
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: jsonLd }} />
      <header className="cl-share-topbar">
        <Link href="/" className="cl-share-brand" aria-label="CampusMode home">
          <img src="/brand/logo" alt="CampusMode" width="140" height="44" className="cl-share-wordmark" />
        </Link>
        <div className="cl-share-top-actions">
          {user ? (
            <>
              <span className="cl-share-session-label">Signed in</span>
              <Link href={isStudent ? studentReturnPath : '/dashboard'} className="btn btn-ghost">
                {isStudent ? 'Back to campuses' : 'Dashboard'}
              </Link>
            </>
          ) : (
            <>
              <Link href={`/login?next=${encodeURIComponent(vendorPath)}`} className="btn btn-ghost">Sign in</Link>
              <Link href="/register" className="btn btn-primary">Join CampusMode</Link>
            </>
          )}
        </div>
      </header>

      <section className="cl-share-shell">
        {contact === 'verification_required' ? <p className="cl-share-access-note">Complete Student verification to contact vendors through CampusMode.</p> : null}
        {contact === 'unavailable' ? <p className="cl-share-access-note">This vendor or selected item is currently unavailable for contact.</p> : null}
        {contact === 'missing' ? <p className="cl-share-access-note">This vendor has not added a valid WhatsApp number yet.</p> : null}
        <article className="v3-surface cl-share-hero">
          <div className="cl-share-cover">{vendor.cover_url ? <img src={vendor.cover_url} alt="" /> : null}</div>
          <div className="cl-share-profile">
            <div className="cl-share-logo">{vendor.logo_url ? <img src={vendor.logo_url} alt="" /> : initial}</div>
            <div className="cl-share-copy">
              <span className="cl-share-verified"><ShieldCheck size={15} /> Verified CampusMode vendor</span>
              <h1>{vendor.business_name}</h1>
              <div className="cl-share-meta">
                <span><Star size={15} fill="currentColor" /> {Number(vendor.average_rating || 0).toFixed(1)} ({vendor.review_count || 0} reviews)</span>
              </div>
              <p>{cleanDescription(vendor.description)}</p>
              {campusNames ? <div className="cl-share-campus"><BadgeCheck size={16} /> Approved for {campusNames}</div> : null}
            </div>
          </div>
        </article>

        <section className="cl-share-intro v3-surface">
          <div>
            <span className="cl-share-eyebrow">Shared from CampusMode</span>
            {approvedOnOwnCampus ? (
              <>
                <h2>Discover this vendor's products and projects</h2>
                <p>This vendor is approved for your institution. As a verified Student you can contact them on WhatsApp, or open the full CampusMode profile for reviews and safety actions.</p>
              </>
            ) : canContact ? (
              <>
                <h2>Contact a vendor from another campus</h2>
                <p>You are signed in and can WhatsApp this identity-approved, campus-approved vendor about products or services. Your school membership stays unchanged. Saves, reviews and reports remain tied to your own campus.</p>
              </>
            ) : isOnboardedStudent ? (
              <>
                <h2>Browse vendors across campuses</h2>
                <p>You are signed in. Complete Student verification to unlock WhatsApp contact with approved vendors from other universities.</p>
              </>
            ) : user ? (
              <>
                <h2>Public vendor storefront</h2>
                <p>You are signed in. Complete Student onboarding and verification to contact businesses across CampusMode campuses.</p>
              </>
            ) : (
              <>
                <h2>Want to contact this vendor?</h2>
                <p>Sign in as a verified Student to contact vendors approved at CampusMode universities. Your own campus remains your default marketplace.</p>
              </>
            )}
          </div>
          <div className="cl-share-contact-actions">
            {canContact ? (
              <a href={whatsappPath} className="btn btn-primary" rel="nofollow">
                <MessageCircle size={17} /> WhatsApp this vendor
              </a>
            ) : null}
            {approvedOnOwnCampus ? (
              <Link href={vendorPath} className="btn btn-ghost">Full campus profile</Link>
            ) : isStudent ? (
              <Link href={studentReturnPath} className="btn btn-ghost">Back to other campuses</Link>
            ) : user ? (
              <Link href="/dashboard" className="btn btn-ghost">Return to dashboard</Link>
            ) : (
              <Link href={`/login?next=${encodeURIComponent(vendorPath)}`} className="btn btn-primary">
                <MessageCircle size={17} /> Sign in to contact
              </Link>
            )}
          </div>
        </section>

        {products.length ? (
          <section className="cl-share-section">
            <div className="cl-share-section-head"><Package size={18} /><div><h2>Products</h2><p>A preview of what this vendor currently sells.</p></div></div>
            <div className="cl-share-list">
              {products.map((product) => (
                <div className="v3-surface cl-share-item" key={product.id}>
                  <strong>{sanitizePublicSeoText(product.name, 80) || 'Product'}</strong>
                  <div className="cl-share-item-end">
                    <span>{product.pricing_type === 'contact' ? 'Ask for price' : `${product.pricing_type === 'from' ? 'From ' : ''}₦${Number(product.price_ngn || 0).toLocaleString()}`}</span>
                    {canContact ? <a href={`${whatsappPath}?product=${product.id}`} rel="nofollow">Ask on WhatsApp</a> : null}
                  </div>
                </div>
              ))}
            </div>
          </section>
        ) : null}

        {services.length ? (
          <section className="cl-share-section">
            <div className="cl-share-section-head"><Wrench size={18} /><div><h2>Services</h2><p>A preview of services listed on CampusMode.</p></div></div>
            <div className="cl-share-list">
              {services.map((service) => (
                <div className="v3-surface cl-share-item" key={service.id}>
                  <strong>{sanitizePublicSeoText(service.name, 80) || 'Service'}</strong>
                  <div className="cl-share-item-end">
                    <span>{service.price_from ? `From ₦${Number(service.price_from).toLocaleString()}` : 'Ask vendor'}</span>
                    {canContact ? <a href={`${whatsappPath}?service=${service.id}`} rel="nofollow">Ask on WhatsApp</a> : null}
                  </div>
                </div>
              ))}
            </div>
          </section>
        ) : null}

        {portfolio.length ? (
          <section className="cl-share-section">
            <div className="cl-share-section-head"><ShieldCheck size={18} /><div><h2>Projects &amp; Portfolio</h2><p>Examples of this vendor's published work.</p></div></div>
            <div className="cl-share-portfolio-grid">
              {portfolio.map((project) => (
                <article className="v3-surface cl-share-project" key={project.id}>
                  {project.image_url ? <img src={project.image_url} alt={project.title} loading="lazy" /> : null}
                  <div className="cl-share-project-copy">
                    <h3>{sanitizePublicSeoText(project.title, 100) || 'Portfolio project'}</h3>
                    {project.description ? <p>{sanitizePublicSeoText(project.description, 300)}</p> : null}
                  </div>
                </article>
              ))}
            </div>
          </section>
        ) : null}

        <p className="cl-share-safety-note">
          <ShieldCheck size={15} /> CampusMode helps Students discover and assess approved Vendors. Transactions are completed directly between Students and Vendors.
        </p>
      </section>
    </main>
  )
}
