import type { Metadata } from 'next'
import Link from 'next/link'
import { LockKeyhole, ShieldCheck } from 'lucide-react'
import { adminLogin } from './actions'

export const metadata: Metadata = { title: 'Admin sign in', robots: { index: false, follow: false, noarchive: true } }

export default async function AdminCampusLoginPage({ searchParams }: { searchParams: Promise<{ error?: string; reason?: string }> }) {
  const params = await searchParams
  return <main className="admin-login-page">
    <section className="admin-login-card">
      <div className="admin-login-mark"><ShieldCheck size={24}/></div>
      <img src="/brand/logo" alt="CampusMode" width={142} height={44} style={{ display: "block", width: 142, height: 44, objectFit: "contain", marginBottom: 12 }} />
      <span>CampusMode Secure Access</span>
      <h1>Administrator sign in</h1>
      <p>Authorized CampusMode administrators only. Password sign-in is followed by authenticator verification before the control plane opens.</p>
      {params.reason === 'idle' ? <div className="admin-login-note">Your administrator session ended after 20 minutes of inactivity.</div> : null}
      {params.error ? <div className="admin-login-error">{params.error}</div> : null}
      <form action={adminLogin} className="admin-login-form">
        <label>Admin email<input name="email" type="email" autoComplete="username" required/></label>
        <label>Password<input name="password" type="password" autoComplete="current-password" minLength={12} required/></label>
        <button type="submit"><LockKeyhole size={17}/> Continue securely</button>
      </form>
      <small>Admin access is role-scoped, MFA protected, audited and automatically signed out after inactivity.</small>
      <Link href="/">Back to CampusMode</Link>
    </section>
  </main>
}
