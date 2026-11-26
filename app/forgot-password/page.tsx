import type { Metadata } from 'next'
import Link from 'next/link'
import { Brand } from '@/components/Brand'
import { requestPasswordReset } from './actions'

export const metadata: Metadata = {
  title: 'Reset your password',
  robots: { index: false, follow: false, noarchive: true },
}

export default async function ForgotPasswordPage({
  searchParams,
}: {
  searchParams: Promise<{ sent?: string; error?: string }>
}) {
  const { sent, error } = await searchParams
  return (
    <main className="auth-shell">
      <section className="auth-card auth-center">
        <Brand />
        <h1>Reset your CampusMode password</h1>
        <p className="auth-copy">Enter the email address on your Student or Vendor account. If an account exists, we'll send a secure recovery link.</p>
        {error ? <div className="auth-error" role="alert">{error}</div> : null}
        {sent ? <div className="auth-success" role="status">If that email has a CampusMode account, recovery instructions will arrive shortly. Check your spam folder too.</div> : null}
        <form action={requestPasswordReset} className="auth-form">
          <label>Email address
            <input type="email" name="email" autoComplete="email" placeholder="you@example.com" maxLength={254} required />
          </label>
          <button className="btn btn-primary" type="submit">Send recovery instructions</button>
        </form>
        <p className="auth-foot"><Link href="/login">Back to sign in</Link></p>
      </section>
    </main>
  )
}
