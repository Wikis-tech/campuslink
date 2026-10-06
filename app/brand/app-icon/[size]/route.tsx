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

  return new ImageResponse(
    (
      <div style={{ width: '100%', height: '100%', display: 'flex', background: '#082D6A' }}>
        <img
          src={new URL('/kampivo-app-icon.svg', _request.url).toString()}
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
