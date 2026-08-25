import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'

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
  if (!user) redirect('/login')

  const [{ data: globalAdmin }, { data: schoolAssignments }] = await Promise.all([
    supabase
      .from('admin_memberships')
      .select('role,is_active')
      .eq('user_id', user.id)
      .eq('is_active', true)
      .maybeSingle(),
    supabase
      .from('institution_admin_assignments')
      .select('institution_id,role,is_active')
      .eq('user_id', user.id)
      .eq('is_active', true),
  ])

  const assignments = schoolAssignments || []
  if (!globalAdmin && assignments.length === 0) redirect('/dashboard')

  return {
    userId: user.id,
    globalRole: globalAdmin?.role || null,
    schoolAssignments: assignments,
    isGlobalAdmin: Boolean(globalAdmin),
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
