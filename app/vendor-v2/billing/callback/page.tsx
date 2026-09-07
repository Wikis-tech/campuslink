import Link from 'next/link'
import { redirect } from 'next/navigation'
import { CheckCircle2, Clock3, LockKeyhole, ShieldCheck, XCircle } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { verifyPaystackTransaction } from '@/lib/paystack'

export default async function BillingCallbackPage({ searchParams }: { searchParams: Promise<{ reference?: string; trxref?: string }> }) {
  const params = await searchParams
  const reference = String(params.reference || params.trxref || '')
  const supabase = await createClient()
  const { data: userResult } = await supabase.auth.getUser()
  const user = userResult.user
  if (!user) redirect('/login')

  const { data: profile } = await supabase.from('profiles').select('account_type').eq('id', user.id).maybeSingle()
  if (!profile || profile.account_type !== 'vendor') redirect('/dashboard')

  if (!reference) {
    return <main className="phase5c-confirm-shell"><section className="phase5c-confirm-card error"><XCircle/><h1>We could not find that payment reference.</h1><p>Return to Plans & growth and start checkout again.</p><Link href="/vendor-v2/growth" className="btn btn-primary">Back to plans</Link></section></main>
  }

  const { data: payment } = await supabase.from('payments').select('reference,amount_ngn,status,created_at').eq('reference', reference).eq('vendor_id', user.id).maybeSingle()
  if (!payment) {
    return <main className="phase5c-confirm-shell"><section className="phase5c-confirm-card error"><XCircle/><h1>This payment does not belong to your account.</h1><p>No subscription change has been made.</p><Link href="/vendor-v2/growth" className="btn btn-primary">Back to plans</Link></section></main>
  }

  let verifiedStatus = 'pending'
  try {
    const verified = await verifyPaystackTransaction(reference)
    verifiedStatus = String(verified.data?.status || 'pending')
  } catch {
    verifiedStatus = payment.status === 'successful' ? 'success' : 'pending'
  }

  const success = verifiedStatus === 'success' || payment.status === 'successful'
  const failed = ['failed','abandoned','reversed'].includes(verifiedStatus) || ['failed','reversed'].includes(payment.status)

  return (
    <main className="phase5c-confirm-shell">
      <section className={`phase5c-confirm-card ${success ? 'success' : failed ? 'error' : 'pending'}`}>
        <div className="phase5c-confirm-icon">{success ? <CheckCircle2/> : failed ? <XCircle/> : <Clock3/>}</div>
        <span className="phase5c-confirm-kicker">Campus Link billing</span>
        <h1>{success ? 'Payment received. We are confirming Pro securely.' : failed ? 'Payment was not completed.' : 'Payment confirmation is still in progress.'}</h1>
        <p>{success ? 'Your browser does not activate Pro. Campus Link waits for a signed Paystack webhook before granting subscription entitlements.' : failed ? 'No Pro access has been granted. You can safely return to Plans & growth and try again.' : 'This can take a short moment. Your plan will only change after Campus Link receives and validates Paystack’s server notification.'}</p>
        <div className="phase5c-reference"><span>Reference</span><strong>{reference}</strong></div>
        <div className="phase5c-security-note"><ShieldCheck size={18}/><span>Verification and campus approval remain separate from billing.</span></div>
        <div className="phase5c-confirm-actions"><Link href="/vendor-v2/growth" className="btn btn-primary">Return to Plans & growth</Link><Link href="/vendor-v2" className="btn btn-ghost">Vendor dashboard</Link></div>
        <small><LockKeyhole size={14}/> Campus Link does not store raw card numbers or CVVs.</small>
      </section>
    </main>
  )
}
