import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'

export default async function DashboardRouter() {
  const supabase = await createClient()

  // getUser() is server-verified against Supabase Auth and gives us the exact
  // authenticated account that owns this session.
  const { data: userData, error: userError } = await supabase.auth.getUser()
  const user = userData.user

  if (userError || !user) redirect('/login')

  const { data: admin } = await supabase
    .from('admin_memberships')
    .select('role,is_active')
    .eq('user_id', user.id)
    .eq('is_active', true)
    .maybeSingle()

  if (admin) redirect('/admin-v2')

  // Resolve the account type through a security-definer RPC first. The RPC can
  // only repair/read auth.uid(), so it cannot cross account boundaries. This
  // also avoids a routing failure if a historical profile row is missing or an
  // older database schema is still catching up.
  const { data: resolvedType, error: resolveError } = await supabase.rpc('resolve_current_account_type')

  let accountType = resolvedType === 'vendor' ? 'vendor' : resolvedType === 'student' ? 'student' : null

  // Backwards-compatible fallback while the new RPC propagates through the
  // PostgREST schema cache. Only request the column actually required to route.
  if (!accountType) {
    const { data: profile, error: profileError } = await supabase
      .from('profiles')
      .select('account_type')
      .eq('id', user.id)
      .maybeSingle()

    if (profile?.account_type === 'vendor' || profile?.account_type === 'student') {
      accountType = profile.account_type
    } else {
      console.error('Campus Link profile resolution failed', {
        userId: user.id,
        resolveError: resolveError?.message || null,
        profileError: profileError?.message || null,
      })
    }
  }

  // Last-resort route hint from server-verified Auth metadata. The database RPC
  // remains the source of truth and self-repairs the profile on the next call.
  if (!accountType) {
    accountType = user.user_metadata?.account_type === 'vendor' ? 'vendor' : 'student'
  }

  if (accountType === 'vendor') redirect('/vendor-v2')
  redirect('/student')
}
