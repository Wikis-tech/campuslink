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

  const { data: profile } = await supabase
    .from('profiles')
    .select('account_type,onboarding_completed_at')
    .eq('id', userId)
    .single()

  if (!profile) redirect('/login?error=We%20could%20not%20load%20your%20profile')

  if (profile.account_type === 'vendor') {
    const { data: vendor } = await supabase
      .from('vendor_profiles')
      .select('onboarding_completed_at')
      .eq('id', userId)
      .maybeSingle()

    if (!vendor?.onboarding_completed_at) redirect('/onboarding/vendor')
    redirect('/vendor-v2')
  }

  if (!profile.onboarding_completed_at) redirect('/onboarding/student')
  redirect('/student')
}
