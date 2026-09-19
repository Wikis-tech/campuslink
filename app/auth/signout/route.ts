import { NextResponse, type NextRequest } from 'next/server'
import { createClient } from '@/lib/supabase/server'

export async function POST(request: NextRequest) {
  const origin = request.headers.get('origin')
  if (origin && origin !== new URL(request.url).origin) {
    return NextResponse.json({ ok: false }, { status: 403 })
  }

  const supabase = await createClient()
  await supabase.auth.signOut()
  return NextResponse.redirect(new URL('/login', request.url), { status: 302 })
}
