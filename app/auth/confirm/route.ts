import { type EmailOtpType } from '@supabase/supabase-js'
import { NextResponse, type NextRequest } from 'next/server'
import { createClient } from '@/lib/supabase/server'

export async function GET(request: NextRequest) {
  const { searchParams } = new URL(request.url)
  const tokenHash = searchParams.get('token_hash')
  const type = searchParams.get('type') as EmailOtpType | null
  const code = searchParams.get('code')
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

  return NextResponse.redirect(new URL('/dashboard', request.url))
}
