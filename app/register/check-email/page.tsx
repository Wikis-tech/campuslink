import Link from 'next/link'

export default function CheckEmailPage() {
  return (
    <main className="auth-shell">
      <section className="auth-card auth-center">
        <div className="success-mark">✓</div>
        <p className="eyebrow">Almost there</p>
        <h1>Check your email</h1>
        <p className="auth-copy">We sent you a confirmation link. Open it to activate your Campus Link account, then you’ll continue to onboarding.</p>
        <Link className="btn btn-primary" href="/login">Back to sign in</Link>
      </section>
    </main>
  )
}
