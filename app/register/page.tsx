import Link from 'next/link'
import { register } from './actions'

export default async function RegisterPage({
  searchParams,
}: {
  searchParams: Promise<{ error?: string }>
}) {
  const { error } = await searchParams

  return (
    <main className="auth-shell">
      <section className="auth-card auth-card-wide">
        <div>
          <Link href="/" className="brand">Campus<span>Link</span></Link>
          <p className="eyebrow">Create your account</p>
          <h1>Join your campus network</h1>
          <p className="auth-copy">Choose how you want to use Campus Link. You can complete school and verification details after signup.</p>
        </div>

        {error ? <div className="auth-error">{error}</div> : null}

        <form action={register} className="auth-form">
          <fieldset className="account-choice">
            <legend>I am joining as</legend>
            <label><input type="radio" name="account_type" value="student" defaultChecked /> Student</label>
            <label><input type="radio" name="account_type" value="vendor" /> Vendor</label>
          </fieldset>

          <div className="auth-grid">
            <label>
              First name
              <input name="first_name" autoComplete="given-name" required />
            </label>
            <label>
              Last name
              <input name="last_name" autoComplete="family-name" required />
            </label>
          </div>

          <label>
            Email address
            <input name="email" type="email" autoComplete="email" required />
          </label>
          <label>
            Password
            <input name="password" type="password" autoComplete="new-password" minLength={8} required />
          </label>

          <button className="btn btn-primary" type="submit">Create account</button>
        </form>

        <p className="auth-foot">Already have an account? <Link href="/login">Sign in</Link></p>
      </section>
    </main>
  )
}
