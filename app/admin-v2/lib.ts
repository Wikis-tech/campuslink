import { notFound, redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'
import { createAdminClient } from '@/lib/supabase/admin'

export type AdminContext = {
  userId: string
  globalRole: string | null
  schoolAssignments: Array<{ institution_id: string; role: string; is_active: boolean }>
  isGlobalAdmin: boolean
}

export async function requireAdminContext(): Promise<AdminContext> {
  const supabase = await createClient()
  const { data: userData } = await supabase.auth.getUser()
  const user = userData.user
  if (!user) notFound()

  // Use the server-only client only to establish whether this account is an
  // administrator before MFA. Normal Admin data access below still uses the
  // signed-in user's session and RLS.
  const admin = createAdminClient()
  const [{ data: globalMembership }, { data: scopedMemberships }] = await Promise.all([
    admin.from('admin_memberships').select('role,is_active').eq('user_id', user.id).eq('is_active', true).maybeSingle(),
    admin.from('institution_admin_assignments').select('institution_id,role,is_active').eq('user_id', user.id).eq('is_active', true),
  ])
  if (!globalMembership && !(scopedMemberships || []).length) notFound()

  const { data: aal, error: aalError } = await supabase.auth.mfa.getAuthenticatorAssuranceLevel()
  if (aalError || aal?.currentLevel !== 'aal2') redirect('/admin-login-campus/mfa')

  // Membership was already established with the server-only Admin client.
  // Do not re-read membership through the browser session here: those tables
  // intentionally have restrictive RLS and a refreshed route request can
  // otherwise turn a valid AAL2 Admin navigation into a false 404.
  //
  // Security is still enforced by BOTH:
  // 1) a real active Admin membership from the server-only lookup above; and
  // 2) the signed-in user's AAL2 MFA session.
  // Individual Admin data queries continue to use the signed-in Supabase
  // client, so their RLS/school-scope policies remain in force.
  const assignments = scopedMemberships || []

  return {
    userId: user.id,
    globalRole: globalMembership?.role || null,
    schoolAssignments: assignments,
    isGlobalAdmin: Boolean(globalMembership),
  }
}

export function canManageSchools(role: string | null) {
  return ['super_admin','operations_admin','content_admin'].includes(role || '')
}

export function canManageAdmins(role: string | null) {
  return ['super_admin','operations_admin'].includes(role || '')
}

export function canReviewVendorIdentity(role: string | null) {
  return ['super_admin','operations_admin','verification_admin'].includes(role || '')
}
