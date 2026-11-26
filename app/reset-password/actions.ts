'use server'

import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'
import { createAdminClient } from '@/lib/supabase/admin'

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
  // The profile type is not enough: an existing Admin may have a legacy
  // marketplace profile. Check authoritative active memberships as well.
  const admin = createAdminClient()
  const [{ data: globalAdmin }, { data: schoolAdmins }] = await Promise.all([
    admin.from('admin_memberships').select('user_id').eq('user_id', userResult.user.id).eq('is_active', true).maybeSingle(),
    admin.from('institution_admin_assignments').select('user_id').eq('user_id', userResult.user.id).eq('is_active', true).limit(1),
  ])
  if (profile?.account_type === 'admin' || globalAdmin || (schoolAdmins || []).length) {
    redirect('/admin-login-campus?error=Administrators%20must%20use%20the%20secure%20Admin%20setup%20process')
  }

  const { error } = await supabase.auth.updateUser({ password })
  if (error) {
    redirect('/reset-password?error=Unable%20to%20update%20the%20password.%20Request%20a%20new%20recovery%20link')
  }

  await supabase.auth.signOut()
  redirect('/login?notice=Password%20updated.%20Sign%20in%20with%20your%20new%20password')
}
