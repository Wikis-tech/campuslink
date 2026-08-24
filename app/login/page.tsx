import Link from 'next/link'
import { login } from './actions'

export default async function LoginPage({
  searchParams,
}: {
  searchParams: Promise<{ error?: string }>
}) {
  const { error } = await searchParams

  return (
    <main className="auth-shell">
      <section className="auth-card">
        <div>
          <Link href="/" className="brand">Campus<span>Link</span></Link>
          <p className="eyebrow">Welcome back</p>
          <h1>Sign in to your account</h1>
          <p className="auth-copy">Students, vendors and admins use the same secure sign-in.</p>
        </div>

        {error ? <div className="auth-error">{error}</div> : null}

        <form action={login} className="auth-form">
          <label>
            Email address
            <input name="email" type="email" autoComplete="email" required />
          </label>
          <label>
            Password
            <input name="password" type="password" autoComplete="current-password" minLength={8} required />
          </label>
          <button className="btn btn-primary" type="submit">Sign in</button>
        </form>

        <p className="auth-foot">New to Campus Link? <Link href="/register">Create an account</Link></p>
      </section>
    </main>
  )
}
