import { notFound, redirect } from 'next/navigation'
import { KeyRound, ShieldCheck } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { createAdminClient } from '@/lib/supabase/admin'
import { completeAdminInvite } from './actions'

export default async function AdminInviteSetupPage({ searchParams }: { searchParams: Promise<{ error?: string }> }) {
  const params = await searchParams
  const supabase = await createClient()
  const { data: userData } = await supabase.auth.getUser()
  const user = userData.user
  if (!user) redirect('/admin-login-campus')

  const admin = createAdminClient()
  const [{ data: globalMembership }, { data: schoolAssignments }] = await Promise.all([
    admin.from('admin_memberships').select('user_id').eq('user_id',user.id).eq('is_active',true).maybeSingle(),
    admin.from('institution_admin_assignments').select('user_id').eq('user_id',user.id).eq('is_active',true).limit(1),
  ])
  if (!globalMembership && !(schoolAssignments || []).length) notFound()

  return <main className="admin-login-page">
    <section className="admin-login-card">
      <div className="admin-login-mark"><ShieldCheck size={24}/></div>
      <span>Kampivo Admin Invitation</span>
      <h1>Create your administrator password</h1>
      <p>Your Admin role has already been assigned. Choose your own strong password; nobody at Kampivo needs to know it. MFA setup follows immediately after this step.</p>
      {params.error ? <div className="admin-login-error">{params.error}</div> : null}
      <form action={completeAdminInvite} className="admin-login-form">
        <label>New password<input name="password" type="password" minLength={14} autoComplete="new-password" required/></label>
        <label>Confirm password<input name="confirm_password" type="password" minLength={14} autoComplete="new-password" required/></label>
        <div className="admin-login-note"><KeyRound size={15}/> Minimum 14 characters with uppercase, lowercase, a number and a symbol.</div>
        <button type="submit">Save password & set up MFA</button>
      </form>
      <small>This setup page is available only to an authenticated invite with an active Kampivo Admin assignment.</small>
    </section>
  </main>
}
