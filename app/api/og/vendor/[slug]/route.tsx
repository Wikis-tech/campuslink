import { ImageResponse } from 'next/og'

export const runtime = 'edge'

const BASE_URL = process.env.NEXT_PUBLIC_APP_URL || 'https://campuslink.name.ng'

async function getVendor(slug: string) {
  const url = process.env.NEXT_PUBLIC_SUPABASE_URL
  const secret = process.env.SUPABASE_SECRET_KEY
  if (!url || !secret) return null

  const endpoint = new URL('/rest/v1/vendor_profiles', url)
  endpoint.searchParams.set('slug', `eq.${slug}`)
  endpoint.searchParams.set('verification_status', 'eq.approved')
  endpoint.searchParams.set('select', 'business_name,slug,description,location_text,logo_url,cover_url,average_rating,review_count,marketplace_status,suspended_until')
  endpoint.searchParams.set('limit', '1')

  const response = await fetch(endpoint, {
    headers: { apikey: secret, Authorization: `Bearer ${secret}` },
    cache: 'no-store',
  })
  if (!response.ok) return null
  const rows = await response.json()
  const vendor = rows?.[0]
  if (!vendor) return null

  const safe =
    vendor.marketplace_status === 'active' ||
    (vendor.marketplace_status === 'suspended' &&
      vendor.suspended_until &&
      new Date(vendor.suspended_until) <= new Date())
  return safe ? vendor : null
}

export async function GET(_: Request, { params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params
  const vendor = await getVendor(slug)

  if (!vendor) {
    return new ImageResponse(
      <div style={{ width: '100%', height: '100%', display: 'flex', alignItems: 'center', justifyContent: 'center', background: '#082D6A', color: 'white', fontSize: 56, fontWeight: 800 }}>
        Campus Link
      </div>,
      { width: 1200, height: 630 }
    )
  }

  const description = (vendor.description || 'Verified Campus Link vendor.').replace(/\s+/g, ' ').trim().slice(0, 150)
  const logo = vendor.logo_url || `${BASE_URL}/brand/app-icon/192`

  return new ImageResponse(
    <div style={{ width: '100%', height: '100%', display: 'flex', position: 'relative', overflow: 'hidden', background: '#071a36', color: 'white', fontFamily: 'sans-serif' }}>
      {vendor.cover_url ? <img src={vendor.cover_url} width="1200" height="630" style={{ position: 'absolute', inset: 0, width: '100%', height: '100%', objectFit: 'cover', opacity: 0.26 }} /> : null}
      <div style={{ position: 'absolute', inset: 0, display: 'flex', backgroundImage: 'linear-gradient(90deg, rgba(6,23,48,.98) 0%, rgba(8,45,106,.90) 58%, rgba(11,61,145,.60) 100%)' }} />
      <div style={{ position: 'relative', width: '100%', height: '100%', display: 'flex', flexDirection: 'column', justifyContent: 'space-between', padding: '64px 72px' }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 18 }}>
          <img src={logo} width="82" height="82" style={{ borderRadius: 22, objectFit: 'cover', background: 'white' }} />
          <div style={{ display: 'flex', flexDirection: 'column' }}>
            <span style={{ fontSize: 24, fontWeight: 800, color: '#8BE0A8' }}>VERIFIED CAMPUS LINK VENDOR</span>
            <span style={{ marginTop: 6, fontSize: 21, color: '#C9D7EA' }}>{vendor.location_text || 'Campus marketplace'}</span>
          </div>
        </div>

        <div style={{ display: 'flex', flexDirection: 'column', maxWidth: 920 }}>
          <div style={{ fontSize: 66, lineHeight: 1.02, fontWeight: 900, letterSpacing: -2 }}>{vendor.business_name}</div>
          <div style={{ marginTop: 18, fontSize: 27, lineHeight: 1.45, color: '#DBE7F5' }}>{description}</div>
          <div style={{ marginTop: 26, display: 'flex', gap: 22, fontSize: 24, fontWeight: 800 }}>
            <span style={{ color: '#F8D66D' }}>★ {Number(vendor.average_rating || 0).toFixed(1)}</span>
            <span style={{ color: '#DCE8F7' }}>{vendor.review_count || 0} reviews</span>
          </div>
        </div>

        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', fontSize: 23 }}>
          <span style={{ fontWeight: 800 }}>campuslink.name.ng</span>
          <span style={{ color: '#9DB2CC' }}>Trusted campus discovery</span>
        </div>
      </div>
    </div>,
    {
      width: 1200,
      height: 630,
      headers: { 'Cache-Control': 'public, max-age=300, stale-while-revalidate=3600' },
    }
  )
}
