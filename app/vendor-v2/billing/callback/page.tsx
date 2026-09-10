import Link from 'next/link'
import { redirect } from 'next/navigation'
import { CheckCircle2, Clock3, LockKeyhole, ShieldCheck, XCircle } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { reconcileSuccessfulPaystackPayment } from '@/lib/billing-reconcile'

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

  let state: 'active' | 'pending' | 'failed' = 'pending'
  try {
    // Safe fallback for delayed/missed webhook delivery. The browser still cannot
    // activate Pro: this server-side function verifies the transaction directly
    // with Paystack, validates amount/reference/currency against the Campus Link
    // ledger, activates the linked subscription, then refreshes entitlements.
    const reconciled = await reconcileSuccessfulPaystackPayment(reference, user.id)
    state = reconciled.status
  } catch (error) {
    console.error('Paystack callback reconciliation failed', error)
    state = payment.status === 'successful' ? 'pending' : 'pending'
  }

  const success = state === 'active'
  const failed = state === 'failed'

  return (
    <main className="phase5c-confirm-shell">
      <section className={`phase5c-confirm-card ${success ? 'success' : failed ? 'error' : 'pending'}`}>
        <div className="phase5c-confirm-icon">{success ? <CheckCircle2/> : failed ? <XCircle/> : <Clock3/>}</div>
        <span className="phase5c-confirm-kicker">Campus Link billing</span>
        <h1>{success ? 'Campus Link Pro is active.' : failed ? 'Payment was not completed.' : 'Payment confirmation is still in progress.'}</h1>
        <p>{success ? 'Your payment was verified directly with Paystack and your Pro business tools have been enabled. Verification and campus approval are still governed separately.' : failed ? 'No Pro access has been granted. You can safely return to Plans & growth and try again.' : 'We could not finish the secure confirmation yet. Return to Plans & growth in a moment; Paystack webhooks can also complete the update automatically.'}</p>
        <div className="phase5c-reference"><span>Reference</span><strong>{reference}</strong></div>
        <div className="phase5c-security-note"><ShieldCheck size={18}/><span>Payment can unlock business tools, but it can never buy verification or campus approval.</span></div>
        <div className="phase5c-confirm-actions"><Link href="/vendor-v2/growth" className="btn btn-primary">View my plan</Link><Link href="/vendor-v2" className="btn btn-ghost">Vendor dashboard</Link></div>
        <small><LockKeyhole size={14}/> Campus Link does not store raw card numbers or CVVs.</small>
      </section>
    </main>
  )
}
