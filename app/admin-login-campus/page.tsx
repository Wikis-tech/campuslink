import Link from 'next/link'
import { LockKeyhole, ShieldCheck } from 'lucide-react'
import { adminLogin } from './actions'

export default async function AdminCampusLoginPage({ searchParams }: { searchParams: Promise<{ error?: string; reason?: string }> }) {
  const params = await searchParams
  return <main className="admin-login-page">
    <section className="admin-login-card">
      <div className="admin-login-mark"><ShieldCheck size={24}/></div>
      <span>CampusLink Secure Access</span>
      <h1>Administrator sign in</h1>
      <p>Authorized Campus Link administrators only. Password sign-in is followed by authenticator verification before the control plane opens.</p>
      {params.reason === 'idle' ? <div className="admin-login-note">Your administrator session ended after 20 minutes of inactivity.</div> : null}
      {params.error ? <div className="admin-login-error">{params.error}</div> : null}
      <form action={adminLogin} className="admin-login-form">
        <label>Admin email<input name="email" type="email" autoComplete="username" required/></label>
        <label>Password<input name="password" type="password" autoComplete="current-password" minLength={12} required/></label>
        <button type="submit"><LockKeyhole size={17}/> Continue securely</button>
      </form>
      <small>Admin access is role-scoped, MFA protected, audited and automatically signed out after inactivity.</small>
      <Link href="/">Back to Campus Link</Link>
    </section>
  </main>
}
