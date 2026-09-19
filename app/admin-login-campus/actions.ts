'use server'

import { createHash } from 'node:crypto'
import { headers } from 'next/headers'
import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'
import { createAdminClient } from '@/lib/supabase/admin'

function genericFailure(): never {
  redirect('/admin-login-campus?error=Invalid%20administrator%20credentials')
}

function attemptHash(scope: 'email' | 'ip', value: string) {
  return createHash('sha256').update(`${scope}:${value}`).digest('hex')
}

export async function adminLogin(formData: FormData) {
  const email = String(formData.get('email') || '').trim().toLowerCase()
  const password = String(formData.get('password') || '')
  if (!email || password.length < 12) genericFailure()

  const headerStore = await headers()
  const forwarded = headerStore.get('x-forwarded-for') || ''
  const ip = forwarded.split(',')[0]?.trim() || headerStore.get('x-real-ip') || 'unknown'
  const emailKey = attemptHash('email', email)
  const ipKey = ip === 'unknown' ? null : attemptHash('ip', ip)

  const admin = createAdminClient()
  const cutoff = new Date(Date.now() - 15 * 60 * 1000).toISOString()
  const stale = new Date(Date.now() - 24 * 60 * 60 * 1000).toISOString()

  // Opportunistic cleanup keeps the server-only throttle ledger small.
  await admin.from('admin_login_attempts').delete().lt('attempted_at', stale)

  const emailCountResult = await admin
    .from('admin_login_attempts')
    .select('id', { count: 'exact', head: true })
    .eq('attempt_key', emailKey)
    .eq('success', false)
    .gte('attempted_at', cutoff)

  let ipFailures = 0
  if (ipKey) {
    const ipCountResult = await admin
      .from('admin_login_attempts')
      .select('id', { count: 'exact', head: true })
      .eq('attempt_key', ipKey)
      .eq('success', false)
      .gte('attempted_at', cutoff)
    ipFailures = ipCountResult.count || 0
  }

  // Email throttling stops IP rotation; the broader IP threshold slows
  // credential stuffing without overly penalizing shared campus networks.
  if ((emailCountResult.count || 0) >= 5 || ipFailures >= 25) {
    redirect('/admin-login-campus?error=Too%20many%20attempts.%20Try%20again%20later.')
  }

  const supabase = await createClient()
  const { data: authData, error } = await supabase.auth.signInWithPassword({ email, password })

  if (error || !authData.user) {
    const attempts = [{ attempt_key: emailKey, success: false }]
    if (ipKey) attempts.push({ attempt_key: ipKey, success: false })
    await admin.from('admin_login_attempts').insert(attempts)
    genericFailure()
  }

  const [{ data: globalAdmin }, { data: schoolRoles }] = await Promise.all([
    admin.from('admin_memberships').select('user_id').eq('user_id', authData.user.id).eq('is_active', true).maybeSingle(),
    admin.from('institution_admin_assignments').select('user_id').eq('user_id', authData.user.id).eq('is_active', true).limit(1),
  ])

  if (!globalAdmin && !(schoolRoles || []).length) {
    const attempts = [{ attempt_key: emailKey, success: false }]
    if (ipKey) attempts.push({ attempt_key: ipKey, success: false })
    await admin.from('admin_login_attempts').insert(attempts)
    await supabase.auth.signOut()
    genericFailure()
  }

  // Successful authentication clears only the account-specific failures.
  // IP failures remain until expiry so one successful login cannot erase
  // credential-stuffing evidence for other accounts on the same source IP.
  await admin.from('admin_login_attempts').delete().eq('attempt_key', emailKey)
  redirect('/admin-login-campus/mfa')
}
