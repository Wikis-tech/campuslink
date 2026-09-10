import Link from 'next/link'
import { ArrowLeft, BadgeCheck, ShieldCheck, Store, UserRound } from 'lucide-react'
import { register } from './actions'
import { Brand } from '@/components/Brand'
import { AuthBackdrop } from '@/components/AuthBackdrop'

export default async function RegisterPage({
  searchParams,
}: {
  searchParams: Promise<{ error?: string }>
}) {
  const { error } = await searchParams

  return (
    <main className="auth-scene">
      <AuthBackdrop />
      <div className="auth-scene-inner auth-scene-inner-wide">
        <section className="auth-story reveal-up">
          <Brand light />
          <div className="auth-story-copy">
            <span className="auth-kicker"><ShieldCheck size={16} /> Join Campus Link</span>
            <h1>One campus network. Two ways to join.</h1>
            <p>Students discover trusted services. Vendors build verified visibility around the campuses they genuinely serve.</p>
            <div className="auth-story-points">
              <span><BadgeCheck size={17} /> Verification before public vendor visibility</span>
              <span><BadgeCheck size={17} /> Campus-scoped profiles and permissions</span>
              <span><BadgeCheck size={17} /> Private verification evidence</span>
            </div>
          </div>
          <Link className="auth-back-link" href="/"><ArrowLeft size={16} /> Back to Campus Link</Link>
        </section>

        <section className="auth-glass-panel reveal-scale">
          <div className="auth-panel-head">
            <span className="auth-kicker dark">Create your account</span>
            <h2>Get started</h2>
            <p>Choose your account type. School and verification details come next.</p>
          </div>

          {error ? <div className="auth-error">{error}</div> : null}

          <form action={register} className="auth-form modern-auth-form">
            <fieldset className="account-choice account-choice-modern">
              <legend>I am joining as</legend>
              <label>
                <input type="radio" name="account_type" value="student" defaultChecked />
                <span className="account-choice-icon"><UserRound size={18} /></span>
                <span><strong>Student</strong><small>Discover services around your school.</small></span>
              </label>
              <label>
                <input type="radio" name="account_type" value="vendor" />
                <span className="account-choice-icon"><Store size={18} /></span>
                <span><strong>Vendor</strong><small>Build a verified campus presence.</small></span>
              </label>
            </fieldset>

            <div className="auth-grid">
              <label>
                First name
                <input name="first_name" autoComplete="given-name" placeholder="First name" required />
              </label>
              <label>
                Last name
                <input name="last_name" autoComplete="family-name" placeholder="Last name" required />
              </label>
            </div>

            <label>
              Email address
              <input name="email" type="email" autoComplete="email" placeholder="you@example.com" required />
            </label>
            <label>
              Password
              <input name="password" type="password" autoComplete="new-password" minLength={8} placeholder="At least 8 characters" required />
            </label>

            <button className="btn btn-primary auth-primary-action" type="submit">Create account</button>
          </form>

          <p className="auth-foot">Already have an account? <Link href="/login">Sign in</Link></p>
        </section>
      </div>
    </main>
  )
}
