import { ImageResponse } from 'next/og'
import { getPlatformBranding } from '@/lib/platform-branding'

const ALLOWED_SIZES = new Set([180, 192, 512])

export const dynamic = 'force-dynamic'

export async function GET(
  _request: Request,
  { params }: { params: Promise<{ size: string }> },
) {
  const { size: rawSize } = await params
  const size = Number(rawSize)

  if (!ALLOWED_SIZES.has(size)) {
    return new Response('Not found', { status: 404 })
  }

  const branding = await getPlatformBranding()

  if (branding.app_icon_url) {
    return new ImageResponse(
      (
        <div style={{ width: '100%', height: '100%', display: 'flex', background: '#082D6A' }}>
          <img
            src={branding.app_icon_url}
            alt=""
            width={size}
            height={size}
            style={{ width: '100%', height: '100%', objectFit: 'cover' }}
          />
        </div>
      ),
      {
        width: size,
        height: size,
        headers: { 'Cache-Control': 'public, max-age=300, stale-while-revalidate=3600' },
      },
    )
  }

  const scale = size / 512
  return new ImageResponse(
    (
      <div
        style={{
          width: '100%',
          height: '100%',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          position: 'relative',
          background: 'linear-gradient(145deg, #0B3D91, #082D6A)',
          borderRadius: Math.round(112 * scale),
        }}
      >
        <div style={{ position: 'relative', width: Math.round(260 * scale), height: Math.round(260 * scale), display: 'flex' }}>
          <div style={{ position: 'absolute', left: 0, top: 0, width: Math.round(224 * scale), height: Math.round(64 * scale), background: '#ffffff', borderRadius: Math.max(2, Math.round(8 * scale)) }} />
          <div style={{ position: 'absolute', left: 0, top: 0, width: Math.round(64 * scale), height: Math.round(224 * scale), background: '#ffffff', borderRadius: Math.max(2, Math.round(8 * scale)) }} />
          <div style={{ position: 'absolute', left: 0, top: Math.round(96 * scale), width: Math.round(192 * scale), height: Math.round(64 * scale), background: '#ffffff', borderRadius: Math.max(2, Math.round(8 * scale)) }} />
          <div style={{ position: 'absolute', right: 0, bottom: 0, width: Math.round(112 * scale), height: Math.round(112 * scale), background: '#1EA952', borderRadius: 999 }} />
        </div>
      </div>
    ),
    {
      width: size,
      height: size,
      headers: { 'Cache-Control': 'public, max-age=300, stale-while-revalidate=3600' },
    },
  )
}
