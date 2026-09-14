'use server'

import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'

export async function login(formData: FormData) {
  const email = String(formData.get('email') || '').trim().toLowerCase()
  const password = String(formData.get('password') || '')

  if (!email || !password) {
    redirect('/login?error=Enter%20your%20email%20and%20password')
  }

  const supabase = await createClient()
  const { data: authData, error } = await supabase.auth.signInWithPassword({ email, password })

  if (error || !authData.user) {
    redirect('/login?error=Invalid%20email%20or%20password')
  }

  const [{ data: globalAdmin }, { data: schoolAdmin }] = await Promise.all([
    supabase.from('admin_memberships').select('user_id').eq('user_id', authData.user.id).eq('is_active', true).maybeSingle(),
    supabase.from('institution_admin_assignments').select('user_id').eq('user_id', authData.user.id).eq('is_active', true).limit(1),
  ])

  if (globalAdmin || (schoolAdmin || []).length) {
    await supabase.auth.signOut()
    redirect('/admin-login?error=Admins%20must%20use%20the%20secure%20admin%20login')
  }

  redirect('/dashboard')
}
