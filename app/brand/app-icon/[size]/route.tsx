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

  // Keep the fallback self-contained. next/og can reject fetched SVG assets on
  // some deployment paths, so drawing the mark directly avoids a runtime image
  // decoder failure while still producing a real PNG at every declared size.
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
          background: '#082D6A',
          borderRadius: Math.round(112 * scale),
          position: 'relative',
          color: '#ffffff',
          fontSize: Math.round(270 * scale),
          fontWeight: 900,
          letterSpacing: Math.round(-22 * scale),
        }}
      >
        K
        <span
          style={{
            position: 'absolute',
            right: Math.round(86 * scale),
            bottom: Math.round(82 * scale),
            width: Math.round(92 * scale),
            height: Math.round(92 * scale),
            borderRadius: 999,
            background: '#FF7A00',
            display: 'flex',
          }}
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
