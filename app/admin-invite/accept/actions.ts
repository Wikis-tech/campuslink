'use server'

import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'
import { createAdminClient } from '@/lib/supabase/admin'

function back(type: string, message: string): never {
  redirect('/admin-invite/accept?type=' + encodeURIComponent(type) + '&error=' + encodeURIComponent(message))
}

export async function acceptAdminToken(formData: FormData) {
  const tokenHash = String(formData.get('token_hash') || '').trim()
  const type = String(formData.get('type') || '').trim()

  if (!tokenHash || !['invite','recovery'].includes(type)) {
    back(type || 'invite', 'This administrator setup link is incomplete. Ask a Super Admin to send a fresh setup link.')
  }

  const supabase = await createClient()
  const { data, error } = await supabase.auth.verifyOtp({
    token_hash: tokenHash,
    type: type as 'invite' | 'recovery',
  })

  if (error || !data.user) {
    back(type, 'This setup link has already been used or has expired. Ask a Super Admin to send a fresh setup link.')
  }

  const admin = createAdminClient()
  const [{ data: globalMembership }, { data: schoolAssignments }] = await Promise.all([
    admin.from('admin_memberships').select('user_id').eq('user_id',data.user.id).eq('is_active',true).maybeSingle(),
    admin.from('institution_admin_assignments').select('user_id').eq('user_id',data.user.id).eq('is_active',true).limit(1),
  ])

  if (!globalMembership && !(schoolAssignments || []).length) {
    await supabase.auth.signOut({ scope: 'local' })
    back(type, 'This administrator invitation no longer has active CampusLink access.')
  }

  redirect('/admin-invite/setup')
}
