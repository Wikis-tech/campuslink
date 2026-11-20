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
        headers: { 'Cache-Control': 'no-store, max-age=0' },
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
          background: '#154597',
          borderRadius: Math.round(112 * scale),
        }}
      >
        <div
          style={{
            width: Math.round(368 * scale),
            height: Math.round(184 * scale),
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'flex-end',
            paddingRight: Math.round(20 * scale),
            background: '#ffffff',
            borderRadius: Math.round(92 * scale),
          }}
        >
          <div
            style={{
              width: Math.round(144 * scale),
              height: Math.round(144 * scale),
              borderRadius: 999,
              background: '#154597',
              display: 'flex',
            }}
          />
        </div>
      </div>
    ),
    {
      width: size,
      height: size,
      headers: { 'Cache-Control': 'no-store, max-age=0' },
    },
  )
}
