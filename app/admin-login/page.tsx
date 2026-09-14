import Link from 'next/link'
import { LockKeyhole, ShieldCheck } from 'lucide-react'
import { adminLogin } from './actions'
import '../admin-v2/admin-fiverr.css'

export default async function AdminLoginPage({ searchParams }: { searchParams: Promise<{ error?: string; reason?: string }> }) {
  const params = await searchParams
  return <main className="admin-login-page">
    <section className="admin-login-card">
      <div className="admin-login-mark"><ShieldCheck size={24}/></div>
      <span>CampusLink Admin</span>
      <h1>Secure admin access</h1>
      <p>Use the credentials issued by a Campus Link administrator. Student and Vendor accounts cannot sign in here.</p>
      {params.reason === 'idle' ? <div className="admin-login-note">You were signed out after 20 minutes of inactivity.</div> : null}
      {params.error ? <div className="admin-login-error">{params.error}</div> : null}
      <form action={adminLogin} className="admin-login-form">
        <label>Admin email<input name="email" type="email" autoComplete="username" required/></label>
        <label>Password<input name="password" type="password" autoComplete="current-password" minLength={8} required/></label>
        <button type="submit"><LockKeyhole size={17}/> Sign in securely</button>
      </form>
      <small>Admin access is role-scoped and audited. Inactive admin sessions expire automatically.</small>
      <Link href="/">Back to Campus Link</Link>
    </section>
  </main>
}
