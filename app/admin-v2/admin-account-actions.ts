'use server'

import { revalidatePath } from 'next/cache'
import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'
import { createAdminClient } from '@/lib/supabase/admin'
import { sendCampusLinkEmail } from '@/lib/campuslink-email'
import { requireAdminContext } from './lib'

function fail(message: string): never {
  redirect('/control-center/admins?error=' + encodeURIComponent(message))
}

function success(message: string): never {
  revalidatePath('/control-center/admins')
  redirect('/control-center/admins?success=' + encodeURIComponent(message))
}

const GLOBAL_ROLES = new Set(['super_admin','operations_admin','verification_admin','support_admin','finance_admin','content_admin','analyst'])

async function findAuthUserByEmail(email: string) {
  const admin = createAdminClient()
  let page = 1
  while (page <= 10) {
    const { data, error } = await admin.auth.admin.listUsers({ page, perPage: 1000 })
    if (error) throw error
    const match = (data.users || []).find((user) => user.email?.toLowerCase() === email)
    if (match) return match
    if ((data.users || []).length < 1000) break
    page += 1
  }
  return null
}

export async function inviteAdminAccount(formData: FormData) {
  const context = await requireAdminContext()
  if (!['super_admin','operations_admin'].includes(context.globalRole || '')) fail('You do not have permission to invite administrators.')

  const email = String(formData.get('email') || '').trim().toLowerCase()
  const scope = String(formData.get('scope') || 'school')
  const role = String(formData.get('role') || '')
  const institutionId = String(formData.get('institution_id') || '')

  if (!email.includes('@')) fail('Enter a valid administrator email address.')
  if (scope === 'school' && role !== 'school_admin') fail('School-scoped invitations use the School Admin role.')
  if (scope === 'school' && !institutionId) fail('Choose a school for this administrator.')
  if (scope === 'global' && context.globalRole !== 'super_admin') fail('Only Super Admin can invite global administrators.')
  if (scope === 'global' && !GLOBAL_ROLES.has(role)) fail('Choose a valid global administrator role.')

  const existing = await findAuthUserByEmail(email)
  if (existing) fail('A CampusLink account already exists for this email. Use “Assign an existing account” instead.')

  const admin = createAdminClient()
  const { data: linkData, error: linkError } = await admin.auth.admin.generateLink({
    type: 'invite',
    email,
    options: {
      data: { campuslink_admin_account: true, account_type: 'admin' },
      redirectTo: 'https://campuslink.name.ng/admin-invite/setup',
    },
  })

  const createdUser = linkData?.user
  const tokenHash = linkData?.properties?.hashed_token
  if (linkError || !createdUser || !tokenHash) {
    fail(linkError?.message || 'Could not create the administrator invitation.')
  }

  const supabase = await createClient()
  const assignment = scope === 'global'
    ? await supabase.rpc('admin_assign_global_role_by_email', { target_email: email, target_role: role })
    : await supabase.rpc('admin_assign_school_admin_by_email', { target_email: email, target_institution: institutionId, assignment_role: 'school_admin' })

  if (assignment.error) {
    await admin.auth.admin.deleteUser(createdUser.id)
    fail('The invitation was not kept because role assignment failed: ' + assignment.error.message)
  }

  // Do not consume the Supabase one-time token on the first GET.
  // Email/security scanners often prefetch links. The accept page only
  // verifies the token after the human clicks the confirmation button.
  const inviteUrl = 'https://campuslink.name.ng/admin-invite/accept?token_hash='
    + encodeURIComponent(tokenHash)
    + '&type=invite'

  const emailResult = await sendCampusLinkEmail({
    to: email,
    recipientName: 'Administrator',
    subject: 'You have been invited to administer CampusLink',
    title: 'Set up your CampusLink Admin access',
    body: scope === 'school'
      ? 'You have been invited to become a School Admin on CampusLink. Use the secure button below to choose your own password. After that, CampusLink will require authenticator MFA before the Admin workspace opens.'
      : 'You have been invited to the CampusLink administration control plane. Use the secure button below to choose your own password. After that, CampusLink will require authenticator MFA before the Admin workspace opens.',
    ctaLabel: 'Accept Admin invitation',
    ctaUrl: inviteUrl,
    idempotencyKey: 'campuslink-admin-invite-' + createdUser.id,
  })

  if (!emailResult.ok) {
    if (scope === 'global') {
      await admin.from('admin_memberships').delete().eq('user_id', createdUser.id)
    } else {
      await admin.from('institution_admin_assignments').delete().eq('user_id', createdUser.id).eq('institution_id', institutionId)
    }
    await admin.auth.admin.deleteUser(createdUser.id)
    fail('The Admin account was not kept because the invitation email could not be delivered: ' + emailResult.error)
  }

  await admin.from('audit_logs').insert({
    actor_id: context.userId,
    action: 'admin.invited',
    entity_type: scope === 'global' ? 'admin_membership' : 'institution_admin_assignment',
    entity_id: createdUser.id,
    metadata: { email, scope, role, institution_id: scope === 'school' ? institutionId : null, email_provider_id: emailResult.id },
  })

  success('Admin invitation sent. The administrator will choose their own password and enroll MFA.')
}

export async function setSchoolAdminAccess(formData: FormData) {
  const context = await requireAdminContext()
  if (!['super_admin','operations_admin'].includes(context.globalRole || '')) fail('You do not have permission to manage School Admin access.')

  const userId = String(formData.get('user_id') || '')
  const institutionId = String(formData.get('institution_id') || '')
  const active = String(formData.get('active') || '') === 'true'
  if (!userId || !institutionId) fail('Missing School Admin assignment.')

  const admin = createAdminClient()
  const { data: assignment } = await admin.from('institution_admin_assignments')
    .select('user_id,institution_id,role,is_active')
    .eq('user_id',userId)
    .eq('institution_id',institutionId)
    .maybeSingle()
  if (!assignment) fail('School Admin assignment not found.')

  const { error } = await admin.from('institution_admin_assignments')
    .update({ is_active: active, updated_at: new Date().toISOString() })
    .eq('user_id',userId)
    .eq('institution_id',institutionId)
  if (error) fail(error.message)

  await admin.from('audit_logs').insert({
    actor_id: context.userId,
    action: active ? 'school_admin.enabled' : 'school_admin.disabled',
    entity_type: 'institution_admin_assignment',
    entity_id: userId,
    metadata: { institution_id: institutionId, role: assignment.role, previous_active: assignment.is_active },
  })

  success(active ? 'School Admin access enabled.' : 'School Admin access disabled. Audit history was preserved.')
}

export async function setGlobalAdminAccess(formData: FormData) {
  const context = await requireAdminContext()
  if (context.globalRole !== 'super_admin') fail('Only Super Admin can change global administrator access.')

  const userId = String(formData.get('user_id') || '')
  const active = String(formData.get('active') || '') === 'true'
  if (!userId) fail('Missing global administrator.')

  const admin = createAdminClient()
  const { data: membership } = await admin.from('admin_memberships')
    .select('user_id,role,is_active')
    .eq('user_id',userId)
    .maybeSingle()
  if (!membership) fail('Global administrator membership not found.')

  if (!active && membership.role === 'super_admin') {
    const { count } = await admin.from('admin_memberships')
      .select('user_id',{count:'exact',head:true})
      .eq('role','super_admin')
      .eq('is_active',true)
    if ((count || 0) <= 1) fail('CampusLink must keep at least one active Super Admin.')
  }

  const { error } = await admin.from('admin_memberships')
    .update({ is_active: active, updated_at: new Date().toISOString() })
    .eq('user_id',userId)
  if (error) fail(error.message)

  await admin.from('audit_logs').insert({
    actor_id: context.userId,
    action: active ? 'global_admin.enabled' : 'global_admin.disabled',
    entity_type: 'admin_membership',
    entity_id: userId,
    metadata: { role: membership.role, previous_active: membership.is_active },
  })

  success(active ? 'Global Admin access enabled.' : 'Global Admin access disabled. Audit history was preserved.')
}


export async function resendAdminSetup(formData: FormData) {
  const context = await requireAdminContext()
  const userId = String(formData.get('user_id') || '').trim()
  const scope = String(formData.get('scope') || '').trim()

  if (!userId || !['school','global'].includes(scope)) fail('Missing administrator setup target.')
  if (!['super_admin','operations_admin'].includes(context.globalRole || '')) {
    fail('You do not have permission to send administrator setup links.')
  }
  if (scope === 'global' && context.globalRole !== 'super_admin') {
    fail('Only Super Admin can send setup links to global administrators.')
  }

  const admin = createAdminClient()

  if (scope === 'global') {
    const { data: membership } = await admin.from('admin_memberships')
      .select('user_id,is_active,role')
      .eq('user_id',userId)
      .maybeSingle()
    if (!membership?.is_active) fail('That global administrator is not currently active.')
  } else {
    const { data: assignments } = await admin.from('institution_admin_assignments')
      .select('user_id,is_active')
      .eq('user_id',userId)
      .eq('is_active',true)
      .limit(1)
    if (!(assignments || []).length) fail('That School Admin has no active school assignment.')
  }

  const { data: userData, error: userError } = await admin.auth.admin.getUserById(userId)
  const email = userData?.user?.email?.trim().toLowerCase()
  if (userError || !email) fail('Could not resolve the administrator email address.')

  const { data: linkData, error: linkError } = await admin.auth.admin.generateLink({
    type: 'recovery',
    email,
    options: { redirectTo: 'https://campuslink.name.ng/admin-invite/setup' },
  })
  const tokenHash = linkData?.properties?.hashed_token
  if (linkError || !tokenHash) fail(linkError?.message || 'Could not create a fresh administrator setup link.')

  const setupUrl = 'https://campuslink.name.ng/admin-invite/accept?token_hash='
    + encodeURIComponent(tokenHash)
    + '&type=recovery'

  const emailResult = await sendCampusLinkEmail({
    to: email,
    recipientName: 'Administrator',
    subject: 'Complete your CampusLink Admin setup',
    title: 'Continue your secure Admin setup',
    body: 'A fresh secure setup link was requested for your CampusLink administrator account. Open the link, confirm the setup, choose your password, and complete authenticator MFA before entering the Admin workspace. If you did not expect this message, contact the CampusLink Super Admin.',
    ctaLabel: 'Continue Admin setup',
    ctaUrl: setupUrl,
    idempotencyKey: 'campuslink-admin-setup-' + userId + '-' + tokenHash.slice(0,16),
  })
  if (!emailResult.ok) fail('Could not send the administrator setup email: ' + emailResult.error)

  await admin.from('audit_logs').insert({
    actor_id: context.userId,
    action: 'admin.setup_link_sent',
    entity_type: scope === 'global' ? 'admin_membership' : 'institution_admin_assignment',
    entity_id: userId,
    metadata: { email, scope, email_provider_id: emailResult.id },
  })

  success('A fresh secure Admin setup link was sent.')
}
