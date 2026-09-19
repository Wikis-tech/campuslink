'use server'

import { createHash } from 'node:crypto'
import { headers } from 'next/headers'
import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'
import { createAdminClient } from '@/lib/supabase/admin'

function genericFailure(): never {
  redirect('/admin-login-campus?error=Invalid%20administrator%20credentials')
}

export async function adminLogin(formData: FormData) {
  const email = String(formData.get('email') || '').trim().toLowerCase()
  const password = String(formData.get('password') || '')
  if (!email || password.length < 12) genericFailure()

  const headerStore = await headers()
  const forwarded = headerStore.get('x-forwarded-for') || ''
  const ip = forwarded.split(',')[0]?.trim() || headerStore.get('x-real-ip') || 'unknown'
  const attemptKey = createHash('sha256').update(`${email}|${ip}`).digest('hex')
  const admin = createAdminClient()
  const cutoff = new Date(Date.now() - 15 * 60 * 1000).toISOString()

  const { count } = await admin
    .from('admin_login_attempts')
    .select('id', { count: 'exact', head: true })
    .eq('attempt_key', attemptKey)
    .eq('success', false)
    .gte('attempted_at', cutoff)

  if ((count || 0) >= 5) {
    redirect('/admin-login-campus?error=Too%20many%20attempts.%20Try%20again%20later.')
  }

  const supabase = await createClient()
  const { data: authData, error } = await supabase.auth.signInWithPassword({ email, password })

  if (error || !authData.user) {
    await admin.from('admin_login_attempts').insert({ attempt_key: attemptKey, success: false })
    genericFailure()
  }

  const [{ data: globalAdmin }, { data: schoolRoles }] = await Promise.all([
    admin.from('admin_memberships').select('user_id').eq('user_id', authData.user.id).eq('is_active', true).maybeSingle(),
    admin.from('institution_admin_assignments').select('user_id').eq('user_id', authData.user.id).eq('is_active', true).limit(1),
  ])

  if (!globalAdmin && !(schoolRoles || []).length) {
    await admin.from('admin_login_attempts').insert({ attempt_key: attemptKey, success: false })
    await supabase.auth.signOut()
    genericFailure()
  }

  await admin.from('admin_login_attempts').delete().eq('attempt_key', attemptKey)
  redirect('/admin-login-campus/mfa')
}
