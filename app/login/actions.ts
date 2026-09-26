'use server'

import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'
import { createAdminClient } from '@/lib/supabase/admin'

export async function login(formData: FormData) {
  const email = String(formData.get('email') || '').trim().toLowerCase()
  const password = String(formData.get('password') || '')

  if (!email || !password) {
    redirect('/login?error=Enter%20your%20email%20and%20password')
  }

  const supabase = await createClient()
  const { data: authData, error } = await supabase.auth.signInWithPassword({ email, password })

  if (error || !authData.user) {
    const normalized = (error?.message || '').toLowerCase()
    if (normalized.includes('email not confirmed') || normalized.includes('email_not_confirmed')) {
      redirect(`/register/check-email?email=${encodeURIComponent(email)}&notice=${encodeURIComponent('This account exists, but the email still needs verification. Enter the code from your email or request a fresh one.')}`)
    }
    redirect('/login?error=Invalid%20email%20or%20password')
  }

  const admin = createAdminClient()
  const [{ data: globalAdmin }, { data: schoolAdmin }] = await Promise.all([
    admin.from('admin_memberships').select('user_id').eq('user_id', authData.user.id).eq('is_active', true).maybeSingle(),
    admin.from('institution_admin_assignments').select('user_id').eq('user_id', authData.user.id).eq('is_active', true).limit(1),
  ])

  if (globalAdmin || (schoolAdmin || []).length) {
    await supabase.auth.signOut()
    redirect('/login?error=This%20account%20uses%20a%20separate%20authorized%20sign-in%20flow')
  }

  redirect('/dashboard')
}
