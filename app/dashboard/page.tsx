import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'

export default async function DashboardRouter() {
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub

  if (!userId) redirect('/login')

  const { data: admin } = await supabase
    .from('admin_memberships')
    .select('role,is_active')
    .eq('user_id', userId)
    .eq('is_active', true)
    .maybeSingle()

  if (admin) redirect('/admin-v2')

  let { data: profile, error: profileError } = await supabase
    .from('profiles')
    .select('account_type,onboarding_completed_at')
    .eq('id', userId)
    .maybeSingle()

  // Historical accounts may exist in auth.users without a public.profiles row.
  // The repair RPC is security-definer but can only repair auth.uid(), so one
  // signed-in user can never create or claim another user's Campus Link profile.
  if (!profile && !profileError) {
    const { data: repaired, error: repairError } = await supabase.rpc('ensure_current_user_profile')
    if (!repairError && repaired) {
      profile = Array.isArray(repaired) ? repaired[0] : repaired
    }
  }

  if (!profile) {
    console.error('Campus Link profile resolution failed', {
      userId,
      profileError: profileError?.message || null,
    })
    redirect('/login?error=We%20could%20not%20load%20your%20profile.%20Please%20try%20again')
  }

  if (profile.account_type === 'vendor') {
    // Vendors have their own workspace. The vendor dashboard decides which
    // setup/verification prompts to show without ever mixing student data.
    redirect('/vendor-v2')
  }

  // Students can enter their dashboard before verification. Verification
  // controls trust-sensitive features (for example publishing reviews), not login.
  redirect('/student')
}
