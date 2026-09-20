import { ImageResponse } from 'next/og'

export const runtime = 'edge'

const BASE_URL = process.env.NEXT_PUBLIC_APP_URL || 'https://campuslink.name.ng'

export async function GET() {
  return new ImageResponse(
    <div style={{ width: '100%', height: '100%', display: 'flex', position: 'relative', overflow: 'hidden', background: '#071a36', color: 'white', fontFamily: 'sans-serif' }}>
      <div style={{ position: 'absolute', width: 520, height: 520, borderRadius: 520, right: -120, top: -180, background: 'rgba(30,169,82,.18)' }} />
      <div style={{ position: 'absolute', width: 480, height: 480, borderRadius: 480, left: -180, bottom: -260, background: 'rgba(11,61,145,.55)' }} />
      <div style={{ position: 'relative', width: '100%', height: '100%', display: 'flex', flexDirection: 'column', justifyContent: 'space-between', padding: '64px 72px' }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 18 }}>
          <img src={`${BASE_URL}/brand/logo`} width="82" height="82" style={{ objectFit: 'contain' }} />
          <div style={{ display: 'flex', flexDirection: 'column' }}>
            <span style={{ fontSize: 34, fontWeight: 900 }}>Campus Link</span>
            <span style={{ marginTop: 5, fontSize: 20, color: '#AFC3DD' }}>Trusted campus discovery</span>
          </div>
        </div>
        <div style={{ display: 'flex', flexDirection: 'column', maxWidth: 980 }}>
          <div style={{ fontSize: 68, lineHeight: 1.02, fontWeight: 900, letterSpacing: -2 }}>Find trusted campus services instantly.</div>
          <div style={{ marginTop: 22, fontSize: 29, lineHeight: 1.4, color: '#DCE8F7' }}>Discover verified Vendors, Products and Services approved for your university community.</div>
        </div>
        <div style={{ display: 'flex', alignItems: 'center', gap: 24, fontSize: 23, fontWeight: 800 }}>
          <span style={{ color: '#8BE0A8' }}>Verified Vendors</span>
          <span style={{ color: '#AFC3DD' }}>•</span>
          <span>Students browse free</span>
          <span style={{ color: '#AFC3DD' }}>•</span>
          <span>Direct contact</span>
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
