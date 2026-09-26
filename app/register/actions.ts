'use server'

import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'

export async function register(formData: FormData) {
  const firstName = String(formData.get('first_name') || '').trim().slice(0, 80)
  const lastName = String(formData.get('last_name') || '').trim().slice(0, 80)
  const email = String(formData.get('email') || '').trim().toLowerCase().slice(0, 254)
  const password = String(formData.get('password') || '')
  const accountType = String(formData.get('account_type') || 'student') === 'vendor' ? 'vendor' : 'student'

  if (!firstName || !lastName || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    redirect('/register?error=Enter%20your%20name%20and%20a%20valid%20email%20address')
  }

  if (password.length < 8 || password.length > 128) {
    redirect('/register?error=Use%20a%20password%20between%208%20and%20128%20characters')
  }

  const supabase = await createClient()
  const { data: signupData, error } = await supabase.auth.signUp({
    email,
    password,
    options: {
      data: {
        first_name: firstName,
        last_name: lastName,
        account_type: accountType,
      },
    },
  })

  if (error) {
    const normalized = error.message.toLowerCase()
    if (normalized.includes('already') || normalized.includes('registered') || normalized.includes('exists')) {
      redirect('/register?error=An%20account%20may%20already%20exist%20for%20this%20email.%20Try%20signing%20in%20instead')
    }
    if (normalized.includes('password')) {
      redirect('/register?error=That%20password%20does%20not%20meet%20the%20security%20requirements')
    }
    if (normalized.includes('email') || normalized.includes('smtp')) {
      redirect('/register?error=We%20could%20not%20send%20the%20verification%20email.%20Please%20try%20again%20in%20a%20moment')
    }
    redirect('/register?error=We%20could%20not%20create%20your%20account.%20Please%20try%20again')
  }

  // With email confirmation enabled, Supabase intentionally returns an obfuscated
  // user for duplicate sign-ups to reduce email-enumeration risk. That response has
  // no identities. Treat it as an existing/pending account instead of pretending a
  // fresh verification email was sent.
  if (signupData.user && Array.isArray(signupData.user.identities) && signupData.user.identities.length === 0) {
    redirect(`/login?notice=${encodeURIComponent('An account already exists or is awaiting email verification. Sign in with this email; if verification is still required, we will guide you to it.')}&email=${encodeURIComponent(email)}`)
  }

  redirect(`/register/check-email?email=${encodeURIComponent(email)}`)
}
