import Link from 'next/link'
import { redirect } from 'next/navigation'
import {
  AlertTriangle,
  ArrowLeft,
  BadgeCheck,
  CalendarClock,
  CheckCircle2,
  CircleDollarSign,
  CreditCard,
  ExternalLink,
  History,
  Mail,
  RefreshCw,
  ShieldCheck,
} from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { ThemeToggle } from '@/components/theme-toggle'
import { emailSubscriptionManager, openSubscriptionManager, refreshBillingStatus } from './actions'

function formatNaira(value: number | string | null | undefined) {
  return new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN', maximumFractionDigits: 0 }).format(Number(value || 0))
}

function formatDate(value: string | null | undefined) {
  if (!value) return 'Not available yet'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return 'Not available yet'
  return new Intl.DateTimeFormat('en-NG', { day: 'numeric', month: 'short', year: 'numeric' }).format(date)
}

function statusInfo(status: string | null | undefined) {
  switch (status) {
    case 'active': return { label: 'Active', tone: 'good', message: 'Your Pro subscription is active and set to renew.' }
    case 'non_renewing': return { label: 'Non-renewing', tone: 'warn', message: 'Your Pro access remains active until the current paid period ends. No new renewal will be charged.' }
    case 'attention': return { label: 'Payment issue', tone: 'warn', message: 'Paystack reported a renewal problem. Update your payment method before the grace period ends.' }
    case 'past_due': return { label: 'Past due', tone: 'danger', message: 'Your latest renewal needs attention.' }
    case 'cancelled': return { label: 'Cancelled', tone: 'muted', message: 'This subscription is no longer renewing.' }
    case 'expired': return { label: 'Expired', tone: 'muted', message: 'This paid subscription has ended.' }
    default: return { label: 'Free', tone: 'muted', message: 'You are currently using Campus Link Free.' }
  }
}

function paymentLabel(status: string) {
  if (status === 'successful') return 'Paid'
  if (status === 'refunded') return 'Refunded'
  if (status === 'reversed') return 'Reversed'
  if (status === 'failed') return 'Failed'
  return 'Pending'
}

export default async function VendorBillingPage({ searchParams }: { searchParams: Promise<{ success?: string; error?: string }> }) {
  const notices = await searchParams
  const supabase = await createClient()
  const { data: userData } = await supabase.auth.getUser()
  const user = userData.user
  if (!user) redirect('/login')

  const { data: profile } = await supabase.from('profiles').select('account_type,first_name,last_name').eq('id', user.id).maybeSingle()
  if (!profile || profile.account_type !== 'vendor') redirect('/dashboard')

  const [{ data: vendor }, { data: subscriptions }, { data: plans }, { data: payments }, { data: entitlementRows }] = await Promise.all([
    supabase.from('vendor_profiles').select('business_name,verification_status,marketplace_status').eq('id', user.id).maybeSingle(),
    supabase.from('subscriptions').select('id,plan_id,status,starts_at,ends_at,current_period_start,current_period_end,cancel_at_period_end,grace_period_ends_at,last_payment_at,provider_subscription_code,created_at').eq('vendor_id', user.id).order('created_at', { ascending: false }).limit(8),
    supabase.from('subscription_plans').select('id,name,slug,tier,price_ngn,billing_interval').eq('is_active', true),
    supabase.from('payments').select('id,reference,amount_ngn,amount_kobo,currency,status,channel,paid_at,created_at').eq('vendor_id', user.id).order('created_at', { ascending: false }).limit(12),
    supabase.rpc('get_my_vendor_entitlements'),
  ])

  if (!vendor) redirect('/onboarding/vendor')

  const currentSubscription = (subscriptions || []).find((item) => ['active','attention','non_renewing','past_due'].includes(item.status)) || subscriptions?.[0] || null
  const currentPlan = currentSubscription ? plans?.find((plan) => plan.id === currentSubscription.plan_id) : plans?.find((plan) => plan.slug === 'free')
  const entitlement = Array.isArray(entitlementRows) ? entitlementRows[0] : null
  const isPro = entitlement?.tier === 'pro' || currentPlan?.tier === 'pro'
  const info = statusInfo(isPro ? currentSubscription?.status : 'free')
  const billingManageReady = Boolean(currentSubscription?.provider_subscription_code && ['active','attention','non_renewing','past_due'].includes(currentSubscription.status))
  const renewDate = currentSubscription?.current_period_end || currentSubscription?.ends_at || null
  const lastSuccessfulPayment = (payments || []).find((payment) => payment.status === 'successful') || null
  const graceEnd = currentSubscription?.grace_period_ends_at || null

  return (
    <main className="phase5d-shell">
      <header className="phase5d-topbar">
        <Link href="/vendor-v2" className="phase5d-back"><ArrowLeft size={17}/> Dashboard</Link>
        <Link href="/" className="phase5d-brand">Campus<span>Link</span></Link>
        <div className="phase5d-nav"><Link href="/vendor-v2/growth">Plans & growth</Link><ThemeToggle compact/><form action="/auth/signout" method="post"><button>Sign out</button></form></div>
      </header>

      <section className="phase5d-content">
        {notices.success ? <div className="phase5d-notice success"><CheckCircle2 size={18}/><span>{notices.success}</span></div> : null}
        {notices.error ? <div className="phase5d-notice error"><AlertTriangle size={18}/><span>{notices.error}</span></div> : null}

        <section className="phase5d-hero">
          <div>
            <span className="phase5d-kicker"><CircleDollarSign size={15}/> Billing & subscription</span>
            <h1>Simple billing. Clear status. No hidden access.</h1>
            <p>Manage Campus Link Pro without mixing payment with verification. Your identity, campus approval and marketplace safety remain separate from billing.</p>
          </div>
          <div className={`phase5d-status ${info.tone}`}>
            <span>Current plan</span>
            <strong>{isPro ? 'Campus Link Pro' : 'Campus Link Free'}</strong>
            <em>{info.label}</em>
          </div>
        </section>

        <section className="phase5d-summary-grid">
          <article className="phase5d-summary primary">
            <div className="phase5d-summary-head"><div><span>Subscription</span><h2>{isPro ? currentPlan?.name || 'Campus Link Pro' : 'Campus Link Free'}</h2></div><BadgeCheck size={23}/></div>
            <p>{info.message}</p>
            {isPro ? <div className="phase5d-price"><strong>{formatNaira(currentPlan?.price_ngn)}</strong><span>/{currentPlan?.billing_interval === 'yearly' ? 'year' : 'month'}</span></div> : <div className="phase5d-price"><strong>₦0</strong><span>/ forever</span></div>}
            {currentSubscription?.status === 'active' ? <div className="phase5d-date-row"><CalendarClock size={17}/><span>Next renewal</span><strong>{formatDate(renewDate)}</strong></div> : null}
            {currentSubscription?.status === 'non_renewing' ? <div className="phase5d-date-row"><CalendarClock size={17}/><span>Pro access until</span><strong>{formatDate(renewDate)}</strong></div> : null}
            {currentSubscription?.status === 'attention' ? <div className="phase5d-warning"><AlertTriangle size={18}/><div><strong>Renewal needs attention</strong><span>{graceEnd ? `Your current grace period ends ${formatDate(graceEnd)}.` : 'Update your payment method to avoid interruption.'}</span></div></div> : null}
          </article>

          <article className="phase5d-summary">
            <div className="phase5d-summary-head"><div><span>Payment method</span><h2>Protected by Paystack</h2></div><CreditCard size={23}/></div>
            <p>Campus Link does not store card numbers, CVVs or PINs. Payment-method changes and cancellation are handled on Paystack's secure subscription page.</p>
            <div className="phase5d-date-row"><CreditCard size={17}/><span>Last channel</span><strong>{lastSuccessfulPayment?.channel ? lastSuccessfulPayment.channel.replace('_',' ') : 'Paystack'}</strong></div>
            <div className="phase5d-date-row"><History size={17}/><span>Last successful payment</span><strong>{lastSuccessfulPayment ? formatDate(lastSuccessfulPayment.paid_at || lastSuccessfulPayment.created_at) : 'None yet'}</strong></div>
          </article>
        </section>

        <section className="phase5d-manager">
          <div className="phase5d-section-heading"><div><span>Subscription controls</span><h2>Manage renewal without exposing payment details to Campus Link.</h2></div><ShieldCheck size={25}/></div>
          {billingManageReady ? <div className="phase5d-manager-grid">
            <form action={openSubscriptionManager} className="phase5d-control-card">
              <ExternalLink size={22}/><div><strong>Open secure subscription manager</strong><span>Replace your payment method or cancel future renewal directly on Paystack.</span></div><button type="submit">Open Paystack</button>
            </form>
            <form action={emailSubscriptionManager} className="phase5d-control-card">
              <Mail size={22}/><div><strong>Email me the secure link</strong><span>Ask Paystack to send the subscription-management link to your billing email.</span></div><button type="submit">Send email</button>
            </form>
            <form action={refreshBillingStatus} className="phase5d-control-card">
              <RefreshCw size={22}/><div><strong>Refresh billing status</strong><span>Securely compare Campus Link's subscription state with Paystack right now.</span></div><button type="submit">Refresh</button>
            </form>
          </div> : <div className="phase5d-empty-control"><ShieldCheck size={26}/><div><strong>{isPro ? 'Paystack management is still syncing.' : 'No paid subscription to manage yet.'}</strong><span>{isPro ? 'If you just upgraded, refresh your billing status once the Paystack subscription code arrives.' : 'Campus Link Free remains fully usable. Upgrade only when Pro tools are valuable to your business.'}</span></div><Link href="/vendor-v2/growth">Compare plans</Link></div>}
          <div className="phase5d-trust-note"><ShieldCheck size={17}/><span>Cancelling renewal does not erase your Vendor profile. Paystack first marks the subscription non-renewing; Campus Link keeps paid Pro access through the current paid period, then safely falls back to Free.</span></div>
        </section>

        <section className="phase5d-history">
          <div className="phase5d-section-heading"><div><span>Billing history</span><h2>A clear record of Campus Link subscription payments.</h2></div><History size={25}/></div>
          {(payments || []).length ? <div className="phase5d-history-table" role="table" aria-label="Billing history">
            <div className="phase5d-history-row head" role="row"><span>Date</span><span>Description</span><span>Reference</span><span>Amount</span><span>Status</span></div>
            {(payments || []).map((payment) => <div className="phase5d-history-row" role="row" key={payment.id}>
              <span data-label="Date">{formatDate(payment.paid_at || payment.created_at)}</span>
              <span data-label="Description">Campus Link {Number(payment.amount_ngn || 0) >= 20000 ? 'Pro Annual' : 'Pro Monthly'}</span>
              <code data-label="Reference">{payment.reference.length > 18 ? `${payment.reference.slice(0,8)}…${payment.reference.slice(-6)}` : payment.reference}</code>
              <strong data-label="Amount">{formatNaira(payment.amount_ngn || Number(payment.amount_kobo || 0) / 100)}</strong>
              <em className={`phase5d-payment-status ${payment.status}`}>{paymentLabel(payment.status)}</em>
            </div>)}
          </div> : <div className="phase5d-no-history"><CreditCard size={26}/><div><strong>No billing history yet.</strong><span>Your first Campus Link Pro test or live subscription payment will appear here after Paystack confirmation.</span></div></div>}
        </section>

        <section className="phase5d-principles">
          <article><ShieldCheck/><div><strong>Payment never buys verification</strong><span>A paid Vendor can still be pending, rejected, under review or suspended.</span></div></article>
          <article><CreditCard/><div><strong>Paystack handles sensitive payment details</strong><span>Campus Link stores transaction references and subscription state, not raw card credentials.</span></div></article>
          <article><CalendarClock/><div><strong>Cancellation is predictable</strong><span>Non-renewing Pro remains usable through its paid period before entitlements return to Free.</span></div></article>
        </section>
      </section>
    </main>
  )
}
