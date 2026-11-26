'use server'

import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'

export async function setRecoveredPassword(formData: FormData) {
  const password = String(formData.get('password') || '')
  const confirmation = String(formData.get('confirm_password') || '')
  if (password.length < 12 || password.length > 128) {
    redirect('/reset-password?error=Use%20a%20password%20between%2012%20and%20128%20characters')
  }
  if (password !== confirmation) {
    redirect('/reset-password?error=The%20passwords%20do%20not%20match')
  }

  const supabase = await createClient()
  const { data: userResult } = await supabase.auth.getUser()
  if (!userResult.user) {
    redirect('/forgot-password?error=Your%20recovery%20link%20has%20expired.%20Request%20a%20new%20one')
  }

  // Dedicated administrator password setup is managed through the separate
  // invitation and MFA process, not the Student/Vendor recovery screen.
  const { data: profile } = await supabase.from('profiles').select('account_type').eq('id', userResult.user.id).maybeSingle()
  if (profile?.account_type === 'admin') {
    redirect('/admin-login-campus?error=Administrators%20must%20use%20the%20secure%20Admin%20setup%20process')
  }

  const { error } = await supabase.auth.updateUser({ password })
  if (error) {
    redirect('/reset-password?error=Unable%20to%20update%20the%20password.%20Request%20a%20new%20recovery%20link')
  }

  await supabase.auth.signOut()
  redirect('/login?notice=Password%20updated.%20Sign%20in%20with%20your%20new%20password')
}
