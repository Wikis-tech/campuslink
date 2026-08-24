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
    .select('account_type')
    .eq('id', userId)
    .single()

  if (profile?.account_type === 'vendor') redirect('/vendor-v2')
  redirect('/student')
}
