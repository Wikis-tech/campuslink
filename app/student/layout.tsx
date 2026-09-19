import { notFound } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'
import { createAdminClient } from '@/lib/supabase/admin'
import './phase3.css'

export default async function StudentLayout({ children }: { children: React.ReactNode }) {
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub

  if (userId) {
    const admin = createAdminClient()
    const [{ data: globalAdmin }, { data: schoolAdmin }] = await Promise.all([
      admin.from('admin_memberships').select('user_id').eq('user_id', userId).eq('is_active', true).maybeSingle(),
      admin.from('institution_admin_assignments').select('user_id').eq('user_id', userId).eq('is_active', true).limit(1),
    ])
    if (globalAdmin || (schoolAdmin || []).length) notFound()
  }

  return children
}
