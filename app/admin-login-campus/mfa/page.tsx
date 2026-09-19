import { notFound, redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'
import { createAdminClient } from '@/lib/supabase/admin'
import { AdminMfaGate } from '@/components/admin-mfa-gate'

export default async function AdminMfaPage() {
  const supabase = await createClient()
  const { data: userData } = await supabase.auth.getUser()
  const user = userData.user
  if (!user) notFound()

  const admin = createAdminClient()
  const [{ data: globalAdmin }, { data: schoolRoles }] = await Promise.all([
    admin.from('admin_memberships').select('user_id').eq('user_id', user.id).eq('is_active', true).maybeSingle(),
    admin.from('institution_admin_assignments').select('user_id').eq('user_id', user.id).eq('is_active', true).limit(1),
  ])
  if (!globalAdmin && !(schoolRoles || []).length) notFound()

  const { data: aal } = await supabase.auth.mfa.getAuthenticatorAssuranceLevel()
  if (aal?.currentLevel === 'aal2') redirect('/admin-v2')

  return <main className="admin-login-page">
    <section className="admin-login-card">
      <AdminMfaGate />
    </section>
  </main>
}
