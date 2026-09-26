import { NextResponse, type NextRequest } from 'next/server'
import { updateSession } from '@/lib/supabase/proxy'

export async function proxy(request: NextRequest) {
  // Keep the implementation route private. Authenticated Admins use the
  // canonical /control-center URL, which Next.js rewrites internally.
  if (request.nextUrl.pathname === '/admin-v2' || request.nextUrl.pathname.startsWith('/admin-v2/')) {
    const hidden = request.nextUrl.clone()
    hidden.pathname = '/__campuslink_not_found__'
    hidden.search = ''
    return NextResponse.rewrite(hidden, { status: 404 })
  }
  return updateSession(request)
}

export const config = {
  matcher: [
    '/((?!_next/static|_next/image|favicon.ico|.*\\.(?:svg|png|jpg|jpeg|gif|webp)$).*)',
  ],
}
