import { NextResponse, type NextRequest } from 'next/server'
import { createClient } from '@/lib/supabase/server'

function allowedSignoutOrigin(request: NextRequest) {
  const origin = request.headers.get('origin')
  if (!origin) return true

  let originUrl: URL
  try {
    originUrl = new URL(origin)
  } catch {
    return false
  }

  const requestUrl = new URL(request.url)
  if (originUrl.origin === requestUrl.origin) return true

  const allowedHosts = new Set([
    'campuslink.name.ng',
    'www.campuslink.name.ng',
    requestUrl.hostname,
  ])

  const configuredAppUrl = process.env.NEXT_PUBLIC_APP_URL
  if (configuredAppUrl) {
    try {
      const configured = new URL(configuredAppUrl)
      allowedHosts.add(configured.hostname)
      if (configured.hostname.startsWith('www.')) allowedHosts.add(configured.hostname.slice(4))
      else allowedHosts.add(`www.${configured.hostname}`)
    } catch {
      // Ignore a malformed optional app URL and keep the fixed allowlist.
    }
  }

  return originUrl.protocol === 'https:' && allowedHosts.has(originUrl.hostname)
}

export async function POST(request: NextRequest) {
  if (!allowedSignoutOrigin(request)) {
    return NextResponse.json({ ok: false }, { status: 403 })
  }

  const supabase = await createClient()
  await supabase.auth.signOut({ scope: 'local' })

  const redirectUrl = new URL('/login', request.url)
  redirectUrl.searchParams.set('signed_out', '1')
  return NextResponse.redirect(redirectUrl, { status: 303 })
}
