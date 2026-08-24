import Link from 'next/link'
import { MailCheck, RefreshCw } from 'lucide-react'
import { verifyEmailCode, resendEmailCode } from './actions'

export default async function CheckEmailPage({
  searchParams,
}: {
  searchParams: Promise<{ email?: string; error?: string; sent?: string }>
}) {
  const { email = '', error, sent } = await searchParams

  return (
    <main className="auth-shell">
      <section className="auth-card auth-center">
        <div className="success-mark"><MailCheck size={24} /></div>
        <h1>Verify your email</h1>
        <p className="auth-copy">
          Enter the one-time verification code sent to <strong>{email || 'your email address'}</strong>.
        </p>

        {error ? <div className="auth-error">{error}</div> : null}
        {sent ? <div className="auth-success">A fresh verification email has been sent.</div> : null}

        <form action={verifyEmailCode} className="auth-form otp-form">
          <input type="hidden" name="email" value={email} />
          <label>
            Verification code
            <input
              name="token"
              inputMode="numeric"
              autoComplete="one-time-code"
              pattern="[0-9]*"
              minLength={6}
              maxLength={8}
              placeholder="Enter code"
              required
            />
          </label>
          <button className="btn btn-primary" type="submit">Verify email</button>
        </form>

        <form action={resendEmailCode} className="otp-resend-form">
          <input type="hidden" name="email" value={email} />
          <button type="submit" className="btn btn-ghost"><RefreshCw size={15} /> Resend code</button>
        </form>

        <p className="auth-foot">Already verified? <Link href="/login">Back to sign in</Link></p>
      </section>
    </main>
  )
}
