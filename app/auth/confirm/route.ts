import { type EmailOtpType } from '@supabase/supabase-js'
import { NextResponse, type NextRequest } from 'next/server'
import { createClient } from '@/lib/supabase/server'

export async function GET(request: NextRequest) {
  const { searchParams } = new URL(request.url)
  const tokenHash = searchParams.get('token_hash')
  const type = searchParams.get('type') as EmailOtpType | null
  const code = searchParams.get('code')
  const requestedNext = searchParams.get('next')
  // Treat the return path as untrusted. URL parsing normalizes backslashes and
  // encoded separators, so a string-prefix check alone can allow redirects
  // outside this application (for example /\\attacker.example).
  const fallback = new URL('/dashboard', request.url)
  let destination = fallback
  if (requestedNext && requestedNext.startsWith('/') && !requestedNext.startsWith('//') && !requestedNext.includes('\\\\')) {
    try {
      const candidate = new URL(requestedNext, request.url)
      if (candidate.origin === fallback.origin) destination = candidate
    } catch {
      destination = fallback
    }
  }
  const supabase = await createClient()

  let error: Error | null = null

  if (tokenHash && type) {
    const result = await supabase.auth.verifyOtp({ type, token_hash: tokenHash })
    error = result.error
  } else if (code) {
    const result = await supabase.auth.exchangeCodeForSession(code)
    error = result.error
  } else {
    error = new Error('Missing confirmation token')
  }

  if (error) {
    return NextResponse.redirect(new URL('/login?error=Confirmation%20link%20is%20invalid%20or%20expired', request.url))
  }

  return NextResponse.redirect(destination)
}
