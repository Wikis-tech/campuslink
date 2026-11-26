'use server'

import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'
import { getSiteUrl } from '@/lib/site-url'

export async function requestPasswordReset(formData: FormData) {
  const email = String(formData.get('email') || '').trim().toLowerCase().slice(0, 254)
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    redirect('/forgot-password?error=Enter%20a%20valid%20email%20address')
  }

  const supabase = await createClient()
  // Use the existing current-domain auth callback, which accepts recovery
  // verification and then navigates to the password update form.
  // Always show the same result to avoid revealing registered email addresses.
  const { error } = await supabase.auth.resetPasswordForEmail(email, {
    redirectTo: `${getSiteUrl()}/auth/confirm?next=%2Freset-password`,
  })
  if (error) console.warn('CampusMode recovery delivery request was not accepted', { code: error.code || 'unknown' })
  redirect('/forgot-password?sent=1')
}
