import Link from 'next/link'
import { ArrowLeft, ShieldCheck } from 'lucide-react'
import { login } from './actions'
import { Brand } from '@/components/Brand'
import { AuthBackdrop } from '@/components/AuthBackdrop'

export default async function LoginPage({
  searchParams,
}: {
  searchParams: Promise<{ error?: string; notice?: string; email?: string }>
}) {
  const { error, notice, email = '' } = await searchParams

  return (
    <main className="auth-scene">
      <AuthBackdrop />
      <div className="auth-scene-inner">
        <section className="auth-story reveal-up">
          <Brand light />
          <div className="auth-story-copy">
            <span className="auth-kicker"><ShieldCheck size={16} /> Welcome back</span>
            <h1>Welcome back to your campus network.</h1>
            <p>Sign in to continue to your Campus Link account.</p>
            
          </div>
          <Link className="auth-back-link" href="/"><ArrowLeft size={16} /> Back to Campus Link</Link>
        </section>

        <section className="auth-glass-panel reveal-scale">
          <div className="auth-panel-head">
            <span className="auth-kicker dark">Welcome back</span>
            <h2>Student / Vendor sign in</h2>
            <p>Use the email and password attached to your Campus Link account.</p>
          </div>

          {error ? <div className="auth-error">{error}</div> : null}
          {notice ? <div className="auth-success">{notice}</div> : null}

          <form action={login} className="auth-form modern-auth-form">
            <label>
              Email address
              <input name="email" type="email" autoComplete="email" placeholder="you@example.com" defaultValue={email} required />
            </label>
            <label>
              Password
              <input name="password" type="password" autoComplete="current-password" minLength={8} placeholder="Enter your password" required />
            </label>
            <button className="btn btn-primary auth-primary-action" type="submit">Sign in</button>
          </form>

          <p className="auth-foot">New to Campus Link? <Link href="/register">Create an account</Link></p>
        </section>
      </div>
    </main>
  )
}
