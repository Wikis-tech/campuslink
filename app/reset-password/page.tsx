import type { Metadata } from 'next'
import Link from 'next/link'
import { Brand } from '@/components/Brand'
import { createClient } from '@/lib/supabase/server'
import { setRecoveredPassword } from './actions'

export const metadata: Metadata = {
  title: 'Choose a new password',
  robots: { index: false, follow: false, noarchive: true },
}

export default async function ResetPasswordPage({
  searchParams,
}: {
  searchParams: Promise<{ error?: string }>
}) {
  const { error } = await searchParams
  const supabase = await createClient()
  const { data } = await supabase.auth.getUser()
  return (
    <main className="auth-shell">
      <section className="auth-card auth-center">
        <Brand />
        <h1>Choose your new password</h1>
        {error ? <div className="auth-error" role="alert">{error}</div> : null}
        {!data.user ? (
          <>
            <p className="auth-copy">This secure password setup requires a valid recovery session. Open the recovery link from your email or request a fresh one.</p>
            <p className="auth-foot"><Link href="/forgot-password">Request a recovery link</Link></p>
          </>
        ) : (
          <>
            <p className="auth-copy">Choose a strong new password for your CampusMode account.</p>
            <form action={setRecoveredPassword} className="auth-form">
              <label>New password
                <input type="password" name="password" autoComplete="new-password" minLength={12} maxLength={128} required />
              </label>
              <label>Confirm new password
                <input type="password" name="confirm_password" autoComplete="new-password" minLength={12} maxLength={128} required />
              </label>
              <button type="submit" className="btn btn-primary">Update password</button>
            </form>
          </>
        )}
        <p className="auth-foot"><Link href="/login">Back to sign in</Link></p>
      </section>
    </main>
  )
}
