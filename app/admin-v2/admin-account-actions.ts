'use server'

import { revalidatePath } from 'next/cache'
import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'
import { createAdminClient } from '@/lib/supabase/admin'

function fail(message: string): never {
  redirect(`/control-center/admins?error=${encodeURIComponent(message)}`)
}

export async function createAdminAccount(formData: FormData) {
  const email = String(formData.get('email') || '').trim().toLowerCase()
  const password = String(formData.get('password') || '')
  const scope = String(formData.get('scope') || 'school')
  const role = String(formData.get('role') || '')
  const institutionId = String(formData.get('institution_id') || '')

  if (!email.includes('@')) fail('Enter a valid admin email address.')
  if (password.length < 12) fail('Admin passwords must be at least 12 characters.')

  const supabase = await createClient()
  const { data: userData } = await supabase.auth.getUser()
  const actor = userData.user
  if (!actor) redirect('/admin-login-campus')

  const { data: membership } = await supabase
    .from('admin_memberships')
    .select('role,is_active')
    .eq('user_id', actor.id)
    .eq('is_active', true)
    .maybeSingle()

  if (!membership || !['super_admin','operations_admin'].includes(membership.role)) {
    fail('You do not have permission to create admin accounts.')
  }
  if (scope === 'global' && membership.role !== 'super_admin') {
    fail('Only Super Admin can create global administrators.')
  }
  if (scope === 'school' && !institutionId) fail('Choose a school for this admin account.')

  const admin = createAdminClient()
  const { data: created, error: createError } = await admin.auth.admin.createUser({
    email,
    password,
    email_confirm: true,
    user_metadata: { campuslink_admin_account: true, account_type: 'admin' },
  })

  if (createError || !created.user) {
    fail(createError?.message?.includes('already') ? 'An account already exists for that email.' : (createError?.message || 'Could not create the admin account.'))
  }

  const assignment = scope === 'global'
    ? await supabase.rpc('admin_assign_global_role_by_email', { target_email: email, target_role: role })
    : await supabase.rpc('admin_assign_school_admin_by_email', { target_email: email, target_institution: institutionId, assignment_role: role })

  if (assignment.error) {
    await admin.auth.admin.deleteUser(created.user.id)
    fail(`The account was not kept because role assignment failed: ${assignment.error.message}`)
  }

  revalidatePath('/control-center/admins')
  redirect('/control-center/admins?success=Admin%20login%20created%20successfully')
}
