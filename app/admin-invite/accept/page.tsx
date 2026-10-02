import { ShieldCheck } from 'lucide-react'
import { acceptAdminToken } from './actions'

export default async function AdminInviteAcceptPage({
  searchParams,
}: {
  searchParams: Promise<{ token_hash?: string; type?: string; error?: string }>
}) {
  const params = await searchParams
  const type = params.type === 'recovery' ? 'recovery' : 'invite'
  const tokenHash = String(params.token_hash || '')

  return <main className="admin-login-page">
    <section className="admin-login-card">
      <div className="admin-login-mark"><ShieldCheck size={24}/></div>
      <span>CampusLink Secure Admin Setup</span>
      <h1>Confirm administrator setup</h1>
      <p>For security, CampusLink does not activate one-time Admin links just because an email client or security scanner opened them. Confirm below to continue.</p>
      {params.error ? <div className="admin-login-error">{params.error}</div> : null}
      {tokenHash ? <form action={acceptAdminToken} className="admin-login-form">
        <input type="hidden" name="token_hash" value={tokenHash}/>
        <input type="hidden" name="type" value={type}/>
        <button type="submit">Confirm & continue securely</button>
      </form> : <div className="admin-login-note">This link is missing its secure token. Ask a Super Admin to send a fresh setup link.</div>}
      <small>The one-time token is consumed only after you press the confirmation button.</small>
    </section>
  </main>
}
