'use server'

import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'
import { getSiteUrl } from '@/lib/site-url'

export async function verifyEmailCode(formData: FormData) {
  const email = String(formData.get('email') || '').trim().toLowerCase().slice(0, 254)
  const token = String(formData.get('token') || '').replace(/\D/g, '').slice(0, 8)

  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email) || token.length < 6) {
    redirect(`/register/check-email?email=${encodeURIComponent(email)}&error=Enter%20the%20verification%20code%20from%20your%20email`)
  }

  const supabase = await createClient()
  const { error } = await supabase.auth.verifyOtp({ email, token, type: 'signup' })

  if (error) {
    redirect(`/register/check-email?email=${encodeURIComponent(email)}&error=That%20code%20is%20invalid%20or%20has%20expired`)
  }

  redirect('/dashboard')
}

export async function resendEmailCode(formData: FormData) {
  const email = String(formData.get('email') || '').trim().toLowerCase().slice(0, 254)

  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    redirect('/register')
  }

  const supabase = await createClient()
  const { error } = await supabase.auth.resend({
    type: 'signup',
    email,
    options: { emailRedirectTo: `${getSiteUrl()}/auth/confirm` },
  })

  if (error) {
    redirect(`/register/check-email?email=${encodeURIComponent(email)}&error=We%20could%20not%20resend%20the%20email%20yet.%20Please%20wait%20and%20try%20again`)
  }

  redirect(`/register/check-email?email=${encodeURIComponent(email)}&sent=1`)
}
