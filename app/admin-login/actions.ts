'use server'

import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'

export async function adminLogin(formData: FormData) {
  const email = String(formData.get('email') || '').trim().toLowerCase()
  const password = String(formData.get('password') || '')
  if (!email || !password) redirect('/admin-login?error=Enter%20your%20admin%20email%20and%20password')

  const supabase = await createClient()
  const { data: authData, error } = await supabase.auth.signInWithPassword({ email, password })
  if (error || !authData.user) redirect('/admin-login?error=Invalid%20admin%20credentials')

  const [{ data: globalAdmin }, { data: schoolRoles }] = await Promise.all([
    supabase.from('admin_memberships').select('user_id').eq('user_id', authData.user.id).eq('is_active', true).maybeSingle(),
    supabase.from('institution_admin_assignments').select('user_id').eq('user_id', authData.user.id).eq('is_active', true).limit(1),
  ])

  if (!globalAdmin && !(schoolRoles || []).length) {
    await supabase.auth.signOut()
    redirect('/admin-login?error=This%20account%20does%20not%20have%20admin%20access')
  }

  redirect('/admin-v2')
}
