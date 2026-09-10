import Link from 'next/link'
import { redirect } from 'next/navigation'
import { AlertTriangle, BadgeCheck, CalendarClock, CheckCircle2, CreditCard, ExternalLink, History, Mail, RefreshCw, ShieldCheck } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { VendorWorkspaceSidebar } from '@/components/vendor-workspace-sidebar'
import { emailSubscriptionManager, openSubscriptionManager, refreshBillingStatus } from './actions'

function formatNaira(value: number | string | null | undefined) { return new Intl.NumberFormat('en-NG',{style:'currency',currency:'NGN',maximumFractionDigits:0}).format(Number(value||0)) }
function formatDate(value: string | null | undefined) { if(!value)return 'Not available yet'; const d=new Date(value); return Number.isNaN(d.getTime())?'Not available yet':new Intl.DateTimeFormat('en-NG',{day:'numeric',month:'short',year:'numeric'}).format(d) }
function statusInfo(status:string|null|undefined){switch(status){case'active':return{label:'Active',tone:'good',message:'Your Pro subscription is active and set to renew.'};case'non_renewing':return{label:'Non-renewing',tone:'warn',message:'Your Pro access stays active until the current paid period ends.'};case'attention':return{label:'Payment issue',tone:'warn',message:'Paystack reported a renewal problem. Update your payment method before the grace period ends.'};case'past_due':return{label:'Past due',tone:'danger',message:'Your latest renewal needs attention.'};case'cancelled':return{label:'Cancelled',tone:'muted',message:'This subscription is no longer renewing.'};case'expired':return{label:'Expired',tone:'muted',message:'This paid subscription has ended.'};default:return{label:'Free',tone:'muted',message:'You are currently using Campus Link Free.'}}}
function paymentLabel(status:string){if(status==='successful')return'Paid';if(status==='refunded')return'Refunded';if(status==='reversed')return'Reversed';if(status==='failed')return'Failed';return'Pending'}

export default async function VendorBillingPage({searchParams}:{searchParams:Promise<{success?:string;error?:string}>}){
  const notices=await searchParams
  const supabase=await createClient(); const {data:userData}=await supabase.auth.getUser(); const user=userData.user
  if(!user)redirect('/login')
  const {data:profile}=await supabase.from('profiles').select('account_type').eq('id',user.id).maybeSingle(); if(!profile||profile.account_type!=='vendor')redirect('/dashboard')
  const [{data:vendor},{data:subscriptions},{data:plans},{data:payments},{data:entitlementRows}]=await Promise.all([
    supabase.from('vendor_profiles').select('business_name,slug').eq('id',user.id).maybeSingle(),
    supabase.from('subscriptions').select('id,plan_id,status,starts_at,ends_at,current_period_start,current_period_end,cancel_at_period_end,grace_period_ends_at,last_payment_at,provider_subscription_code,created_at').eq('vendor_id',user.id).order('created_at',{ascending:false}).limit(8),
    supabase.from('subscription_plans').select('id,name,slug,tier,price_ngn,billing_interval').eq('is_active',true),
    supabase.from('payments').select('id,reference,amount_ngn,amount_kobo,currency,status,channel,paid_at,created_at').eq('vendor_id',user.id).order('created_at',{ascending:false}).limit(12),
    supabase.rpc('get_my_vendor_entitlements'),
  ])
  if(!vendor)redirect('/onboarding/vendor')
  const currentSubscription=(subscriptions||[]).find(i=>['active','attention','non_renewing','past_due'].includes(i.status))||subscriptions?.[0]||null
  const currentPlan=currentSubscription?plans?.find(p=>p.id===currentSubscription.plan_id):plans?.find(p=>p.slug==='free')
  const entitlement=Array.isArray(entitlementRows)?entitlementRows[0]:null
  const isPro=entitlement?.tier==='pro'||currentPlan?.tier==='pro'
  const info=statusInfo(isPro?currentSubscription?.status:'free')
  const manageReady=Boolean(currentSubscription?.provider_subscription_code&&['active','attention','non_renewing','past_due'].includes(currentSubscription.status))
  const renewDate=currentSubscription?.current_period_end||currentSubscription?.ends_at||null
  const lastPayment=(payments||[]).find(p=>p.status==='successful')||null

  return <main className="v5e-page">
    <VendorWorkspaceSidebar storefrontHref={vendor.slug?`/student/vendors/${vendor.slug}`:undefined}/>
    <section className="v5e-content vendor-section-content">
      {notices.success?<div className="notice success"><CheckCircle2 size={17}/>{notices.success}</div>:null}
      {notices.error?<div className="notice error"><AlertTriangle size={17}/>{notices.error}</div>:null}
      <header className="v5e-topbar vendor-section-header"><div><span className="v5e-section-label">Billing</span><h1>Clear subscription status. Secure payment control.</h1><p>Campus Link shows the billing state; Paystack handles sensitive payment details and subscription management.</p></div><div className={`vendor-plan-pill ${info.tone}`}><span>Current plan</span><strong>{isPro?'Pro':'Free'}</strong><small>{info.label}</small></div></header>
      <section className="billing-overview-grid"><article className="v5e-panel"><div className="v5e-panel-head"><div><small>Subscription</small><h2>{isPro?currentPlan?.name||'Campus Link Pro':'Campus Link Free'}</h2></div><BadgeCheck size={22}/></div><p>{info.message}</p><div className="billing-price"><strong>{isPro?formatNaira(currentPlan?.price_ngn):'₦0'}</strong><span>{isPro?`/${currentPlan?.billing_interval==='yearly'?'year':'month'}`:'forever'}</span></div>{currentSubscription?.status==='active'?<div className="billing-meta-row"><CalendarClock size={16}/><span>Next renewal</span><strong>{formatDate(renewDate)}</strong></div>:null}{currentSubscription?.status==='non_renewing'?<div className="billing-meta-row"><CalendarClock size={16}/><span>Pro access until</span><strong>{formatDate(renewDate)}</strong></div>:null}</article><article className="v5e-panel"><div className="v5e-panel-head"><div><small>Payment protection</small><h2>Protected by Paystack</h2></div><CreditCard size={22}/></div><p>Campus Link does not store card numbers, CVVs or PINs.</p><div className="billing-meta-row"><CreditCard size={16}/><span>Last channel</span><strong>{lastPayment?.channel?lastPayment.channel.replace('_',' '):'Paystack'}</strong></div><div className="billing-meta-row"><History size={16}/><span>Last successful payment</span><strong>{lastPayment?formatDate(lastPayment.paid_at||lastPayment.created_at):'None yet'}</strong></div></article></section>
      <section className="v5e-panel billing-controls"><div className="v5e-panel-head"><div><small>Subscription controls</small><h2>Manage renewal securely</h2></div><ShieldCheck size={22}/></div>{manageReady?<div className="billing-control-grid"><form action={openSubscriptionManager}><ExternalLink/><strong>Open Paystack manager</strong><span>Update payment method or cancel future renewal.</span><button>Open securely</button></form><form action={emailSubscriptionManager}><Mail/><strong>Email secure link</strong><span>Ask Paystack to send your management link.</span><button>Send email</button></form><form action={refreshBillingStatus}><RefreshCw/><strong>Refresh billing status</strong><span>Compare Campus Link with Paystack now.</span><button>Refresh</button></form></div>:<div className="v5e-empty compact"><ShieldCheck size={24}/><strong>{isPro?'Subscription management is still syncing.':'No paid subscription to manage.'}</strong><p>{isPro?'Refresh once Paystack has issued the subscription code.':'Free remains fully usable. Upgrade only when Pro is useful.'}</p>{!isPro?<Link href="/vendor-v2/growth">Compare plans</Link>:null}</div>}</section>
      <section className="v5e-panel"><div className="v5e-panel-head"><div><small>Billing history</small><h2>Your Campus Link subscription payments</h2></div><History size={22}/></div>{(payments||[]).length?<div className="billing-history-table"><div className="billing-history-row head"><span>Date</span><span>Reference</span><span>Amount</span><span>Status</span></div>{(payments||[]).map(p=><div className="billing-history-row" key={p.id}><span>{formatDate(p.paid_at||p.created_at)}</span><code>{p.reference.length>18?`${p.reference.slice(0,8)}…${p.reference.slice(-6)}`:p.reference}</code><strong>{formatNaira(p.amount_ngn||Number(p.amount_kobo||0)/100)}</strong><em className={p.status}>{paymentLabel(p.status)}</em></div>)}</div>:<div className="v5e-empty compact"><CreditCard size={24}/><strong>No billing history yet.</strong><p>Your confirmed Campus Link Pro payments will appear here.</p></div>}</section>
    </section>
  </main>
}
