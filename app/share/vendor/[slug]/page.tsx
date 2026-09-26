import type { Metadata } from 'next'
import Link from 'next/link'
import { notFound } from 'next/navigation'
import { BadgeCheck, MessageCircle, Package, ShieldCheck, Star, Wrench } from 'lucide-react'
import { createAdminClient } from '@/lib/supabase/admin'
import { getCanonicalOrigin, sanitizePublicSeoText } from '@/lib/seo-privacy'

const BASE_URL = getCanonicalOrigin()

function cleanDescription(value: string | null | undefined) {
  return sanitizePublicSeoText(value || 'Verified Campus Link vendor.', 180) || 'Verified Campus Link vendor.'
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
    .select('institution_id')
    .eq('vendor_id', vendor.id)
    .eq('status', 'approved')

  if (!links?.length) return null

  const institutionIds = links.map((row) => row.institution_id)
  const { data: institutions } = institutionIds.length
    ? await admin.from('institutions').select('id,name').in('id', institutionIds)
    : { data: [] as Array<{ id: string; name: string }> }

  const [{ data: products }, { data: services }] = await Promise.all([
    admin
      .from('vendor_products')
      .select('id,name,price_ngn,pricing_type,cover_image_url')
      .eq('vendor_id', vendor.id)
      .eq('is_active', true)
      .order('created_at', { ascending: false })
      .limit(4),
    admin
      .from('vendor_services')
      .select('id,name,price_from')
      .eq('vendor_id', vendor.id)
      .eq('is_active', true)
      .order('name')
      .limit(5),
  ])

  return { vendor, institutions: institutions || [], products: products || [], services: services || [] }
}

export async function generateMetadata({ params }: { params: Promise<{ slug: string }> }): Promise<Metadata> {
  const { slug } = await params
  const result = await getShareVendor(slug)
  if (!result) return { title: 'Vendor not available · Campus Link', robots: { index: false, follow: false } }

  const { vendor } = result
  const title = `${vendor.business_name} · Campus Link`
  const description = cleanDescription(vendor.description)
  const url = `${BASE_URL}/share/vendor/${encodeURIComponent(vendor.slug)}`
  const image = `${BASE_URL}/api/og/vendor/${encodeURIComponent(vendor.slug)}`

  return {
    title,
    description,
    alternates: { canonical: url },
    openGraph: {
      type: 'website',
      siteName: 'Campus Link',
      title,
      description,
      url,
      images: [{ url: image, width: 1200, height: 630, alt: `${vendor.business_name} on Campus Link` }],
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

export default async function SharedVendorPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params
  const result = await getShareVendor(slug)
  if (!result) notFound()

  const { vendor, institutions, products, services } = result
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
      name: 'Campus Link',
      url: BASE_URL,
    },
  }
  const jsonLd = JSON.stringify(vendorJsonLd).replace(/</g, '\\u003c')

  return (
    <main className="cl-share-vendor-page">
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: jsonLd }} />
      <header className="cl-share-topbar">
        <Link href="/" className="cl-share-brand">
          <img src="/brand/logo" alt="" width="34" height="34" />
          <span>Campus<strong>Link</strong></span>
        </Link>
        <div className="cl-share-top-actions">
          <Link href="/login" className="btn btn-ghost">Sign in</Link>
          <Link href="/register" className="btn btn-primary">Join Campus Link</Link>
        </div>
      </header>

      <section className="cl-share-shell">
        <article className="v3-surface cl-share-hero">
          <div className="cl-share-cover">{vendor.cover_url ? <img src={vendor.cover_url} alt="" /> : null}</div>
          <div className="cl-share-profile">
            <div className="cl-share-logo">{vendor.logo_url ? <img src={vendor.logo_url} alt="" /> : initial}</div>
            <div className="cl-share-copy">
              <span className="cl-share-verified"><ShieldCheck size={15} /> Verified Campus Link vendor</span>
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
            <span className="cl-share-eyebrow">Shared from Campus Link</span>
            <h2>Want to contact this vendor?</h2>
            <p>Sign in as a Student to view the full trusted profile, current campus availability, reviews and direct contact options.</p>
          </div>
          <Link href={`/login?next=/student/vendors/${encodeURIComponent(vendor.slug)}`} className="btn btn-primary">
            <MessageCircle size={17} /> Sign in to view vendor
          </Link>
        </section>

        {products.length ? (
          <section className="cl-share-section">
            <div className="cl-share-section-head"><Package size={18} /><div><h2>Products</h2><p>A preview of what this vendor currently sells.</p></div></div>
            <div className="cl-share-list">
              {products.map((product) => (
                <div className="v3-surface cl-share-item" key={product.id}>
                  <strong>{sanitizePublicSeoText(product.name, 80) || 'Product'}</strong>
                  <span>{product.pricing_type === 'contact' ? 'Ask for price' : `${product.pricing_type === 'from' ? 'From ' : ''}₦${Number(product.price_ngn || 0).toLocaleString()}`}</span>
                </div>
              ))}
            </div>
          </section>
        ) : null}

        {services.length ? (
          <section className="cl-share-section">
            <div className="cl-share-section-head"><Wrench size={18} /><div><h2>Services</h2><p>A preview of services listed on Campus Link.</p></div></div>
            <div className="cl-share-list">
              {services.map((service) => (
                <div className="v3-surface cl-share-item" key={service.id}>
                  <strong>{sanitizePublicSeoText(service.name, 80) || 'Service'}</strong>
                  <span>{service.price_from ? `From ₦${Number(service.price_from).toLocaleString()}` : 'Ask vendor'}</span>
                </div>
              ))}
            </div>
          </section>
        ) : null}

        <p className="cl-share-safety-note">
          <ShieldCheck size={15} /> Campus Link helps Students discover and assess approved Vendors. Transactions are completed directly between Students and Vendors.
        </p>
      </section>
    </main>
  )
}
