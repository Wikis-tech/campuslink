import { getPlatformBranding, DEFAULT_WEBSITE_LOGO } from '@/lib/platform-branding'

export const dynamic = 'force-dynamic'

export async function GET(request: Request) {
  const branding = await getPlatformBranding()
  const source = branding.website_logo_url || DEFAULT_WEBSITE_LOGO

  if (source.startsWith('/')) {
    return Response.redirect(new URL(source, request.url), 307)
  }

  try {
    const response = await fetch(source, { cache: 'no-store' })
    if (!response.ok) throw new Error('Brand logo unavailable')
    return new Response(await response.arrayBuffer(), {
      headers: {
        'Content-Type': response.headers.get('content-type') || 'image/png',
        'Cache-Control': 'public, max-age=300, stale-while-revalidate=3600',
      },
    })
  } catch {
    return Response.redirect(new URL('/kampivo-logo.svg', request.url), 307)
  }
}
