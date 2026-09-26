'use client'

import { useEffect, useState } from 'react'
import { ShieldCheck } from 'lucide-react'
import { createClient } from '@/lib/supabase/client'

type Mode = 'loading' | 'enroll' | 'challenge'

export function AdminMfaGate() {
  const supabase = createClient()
  const [mode, setMode] = useState<Mode>('loading')
  const [factorId, setFactorId] = useState('')
  const [qr, setQr] = useState('')
  const [secret, setSecret] = useState('')
  const [code, setCode] = useState('')
  const [error, setError] = useState('')
  const [busy, setBusy] = useState(false)

  useEffect(() => {
    let cancelled = false
    ;(async () => {
      const factors = await supabase.auth.mfa.listFactors()
      if (cancelled) return
      if (factors.error) {
        setError('Could not load administrator security factors.')
        setMode('challenge')
        return
      }

      const verified = factors.data.totp.find((factor:any) => factor.status === 'verified') || factors.data.totp[0]
      if (verified?.id && verified.status === 'verified') {
        setFactorId(verified.id)
        setMode('challenge')
        return
      }

      const enrolled = await supabase.auth.mfa.enroll({ factorType: 'totp', friendlyName: 'Campus Link Admin' })
      if (cancelled) return
      if (enrolled.error) {
        setError(enrolled.error.message || 'Could not start authenticator setup.')
        setMode('enroll')
        return
      }
      setFactorId(enrolled.data.id)
      setQr(enrolled.data.totp.qr_code)
      setSecret(enrolled.data.totp.secret)
      setMode('enroll')
    })()

    return () => { cancelled = true }
  }, [])

  async function verify() {
    if (!factorId || !/^\d{6}$/.test(code.trim())) {
      setError('Enter the 6-digit code from your authenticator app.')
      return
    }
    setBusy(true)
    setError('')
    try {
      const challenge = await supabase.auth.mfa.challenge({ factorId })
      if (challenge.error) throw challenge.error
      const verified = await supabase.auth.mfa.verify({ factorId, challengeId: challenge.data.id, code: code.trim() })
      if (verified.error) throw verified.error
      window.location.replace('/control-center')
    } catch (err:any) {
      setError(err?.message || 'That authenticator code could not be verified.')
      setBusy(false)
    }
  }

  return <>
    <div className="admin-login-mark"><ShieldCheck size={24}/></div>
    <span>CampusLink Admin Security</span>
    <h1>{mode === 'enroll' ? 'Protect this administrator account' : 'Verify your authenticator'}</h1>
    {mode === 'loading' ? <p>Checking administrator security…</p> : null}
    {mode === 'enroll' ? <>
      <p>Scan this QR code with Google Authenticator, Microsoft Authenticator, 1Password, Authy or another TOTP app. This second factor is required for every Campus Link administrator.</p>
      {qr ? <img src={qr} alt="Campus Link authenticator QR code" style={{width:220,height:220,maxWidth:'100%',margin:'12px auto',display:'block',background:'#fff',padding:10,borderRadius:12}}/> : null}
      {secret ? <details><summary>Cannot scan the QR code?</summary><p style={{wordBreak:'break-all'}}>{secret}</p></details> : null}
    </> : mode === 'challenge' ? <p>Enter the current 6-digit code from the authenticator linked to this administrator account.</p> : null}
    {mode !== 'loading' ? <div className="admin-login-form">
      <label>Authenticator code<input value={code} onChange={(e)=>setCode(e.target.value.replace(/\D/g,'').slice(0,6))} inputMode="numeric" autoComplete="one-time-code" placeholder="000000"/></label>
      {error ? <div className="admin-login-error">{error}</div> : null}
      <button type="button" disabled={busy} onClick={verify}>{busy ? 'Verifying…' : mode === 'enroll' ? 'Enable MFA & continue' : 'Verify & continue'}</button>
    </div> : null}
    <small>Password access alone cannot open the Campus Link administration control plane.</small>
  </>
}
