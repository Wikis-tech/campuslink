import { getPlatformBranding } from '@/lib/platform-branding'

export const dynamic = 'force-dynamic'

export async function GET() {
  const branding = await getPlatformBranding()
  if (!branding.favicon_url) {
    return Response.redirect(new URL('/default-icon.svg', process.env.NEXT_PUBLIC_APP_URL || 'https://campuslink.name.ng'), 307)
  }

  try {
    const response = await fetch(branding.favicon_url, { cache: 'no-store' })
    if (!response.ok) throw new Error('Favicon unavailable')
    return new Response(await response.arrayBuffer(), {
      headers: {
        'Content-Type': response.headers.get('content-type') || 'image/png',
        'Cache-Control': 'public, max-age=60, stale-while-revalidate=300',
      },
    })
  } catch {
    return Response.redirect(new URL('/default-icon.svg', process.env.NEXT_PUBLIC_APP_URL || 'https://campuslink.name.ng'), 307)
  }
}
