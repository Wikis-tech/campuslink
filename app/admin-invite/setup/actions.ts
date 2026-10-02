'use server'

import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'
import { createAdminClient } from '@/lib/supabase/admin'

function fail(message: string): never {
  redirect('/admin-invite/setup?error=' + encodeURIComponent(message))
}

export async function completeAdminInvite(formData: FormData) {
  const password = String(formData.get('password') || '')
  const confirmPassword = String(formData.get('confirm_password') || '')

  if (password !== confirmPassword) fail('Passwords do not match.')
  if (password.length < 14) fail('Use at least 14 characters for an administrator password.')
  if (!/[a-z]/.test(password) || !/[A-Z]/.test(password) || !/\d/.test(password) || !/[^A-Za-z0-9]/.test(password)) {
    fail('Use uppercase, lowercase, a number and a symbol in the administrator password.')
  }

  const supabase = await createClient()
  const { data: userData } = await supabase.auth.getUser()
  const user = userData.user
  if (!user) redirect('/admin-login-campus')

  const admin = createAdminClient()
  const [{ data: globalMembership }, { data: schoolAssignments }] = await Promise.all([
    admin.from('admin_memberships').select('user_id').eq('user_id',user.id).eq('is_active',true).maybeSingle(),
    admin.from('institution_admin_assignments').select('user_id').eq('user_id',user.id).eq('is_active',true).limit(1),
  ])
  if (!globalMembership && !(schoolAssignments || []).length) fail('This invitation no longer has active administrator access.')

  const { error } = await supabase.auth.updateUser({ password })
  if (error) fail(error.message)

  await admin.from('audit_logs').insert({
    actor_id: user.id,
    action: 'admin.invite_completed',
    entity_type: 'admin_account',
    entity_id: user.id,
    metadata: { password_created: true },
  })

  redirect('/admin-login-campus/mfa')
}
