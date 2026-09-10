import Link from 'next/link'
import { redirect } from 'next/navigation'
import { BadgeCheck, BarChart3, Building2, Check, CircleDollarSign, Eye, ImagePlus, ListChecks, LockKeyhole, ShieldCheck, Sparkles, Store, UsersRound } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { PaystackCheckoutButton } from '@/components/paystack-checkout-button'
import { VendorWorkspaceSidebar } from '@/components/vendor-workspace-sidebar'

function formatNaira(value: number | string | null | undefined) {
  return new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN', maximumFractionDigits: 0 }).format(Number(value || 0))
}

export default async function VendorGrowthPage() {
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')
  const { data: profile } = await supabase.from('profiles').select('account_type,first_name').eq('id', userId).maybeSingle()
  if (!profile || profile.account_type !== 'vendor') redirect('/dashboard')
  const [{ data: vendor }, { data: campus }, { data: plans }, { data: subscriptions }, { data: entitlementRows }] = await Promise.all([
    supabase.from('vendor_profiles').select('business_name,slug,verification_status,onboarding_completed_at,logo_url,description').eq('id', userId).maybeSingle(),
    supabase.from('vendor_institutions').select('status,institution_id').eq('vendor_id', userId).eq('is_primary', true).maybeSingle(),
    supabase.from('subscription_plans').select('id,name,slug,tier,price_ngn,billing_interval,features,entitlements,is_active,paystack_plan_code').eq('is_active', true).eq('is_public', true).order('sort_order'),
    supabase.from('subscriptions').select('id,plan_id,status,starts_at,ends_at,current_period_end').eq('vendor_id', userId).in('status', ['active','attention','non_renewing','past_due']).order('created_at',{ascending:false}).limit(1),
    supabase.rpc('get_my_vendor_entitlements'),
  ])
  if (!vendor) redirect('/onboarding/vendor')
  let schoolName:string|null=null
  if(campus?.institution_id){const {data:i}=await supabase.from('institutions').select('name').eq('id',campus.institution_id).maybeSingle();schoolName=i?.name||null}
  const activeSubscription=subscriptions?.[0]||null
  const activePlan=activeSubscription?plans?.find(p=>p.id===activeSubscription.plan_id):null
  const entitlement=Array.isArray(entitlementRows)?entitlementRows[0]:null
  const currentTier=entitlement?.tier==='pro'||activePlan?.tier==='pro'?'Pro':'Free'
  const identityApproved=vendor.verification_status==='approved', campusApproved=campus?.status==='approved', setupComplete=Boolean(vendor.onboarding_completed_at)
  const trustReady=identityApproved&&campusApproved&&setupComplete
  const monthlyPro=plans?.find(p=>p.slug==='pro-monthly'), annualPro=plans?.find(p=>p.slug==='pro-annual'), freePlan=plans?.find(p=>p.slug==='free')
  const monthlyAnnualised=Number(monthlyPro?.price_ngn||2500)*12, annualPrice=Number(annualPro?.price_ngn||24000), annualSaving=Math.max(monthlyAnnualised-annualPrice,0)
  const checkoutReady=Boolean(monthlyPro?.paystack_plan_code&&annualPro?.paystack_plan_code)

  return <main className="v5e-page">
    <VendorWorkspaceSidebar storefrontHref={vendor.slug ? `/student/vendors/${vendor.slug}` : undefined}/>
    <section className="v5e-content vendor-section-content">
      <header className="v5e-topbar vendor-section-header"><div><span className="v5e-section-label">Plans & growth</span><h1>Free gets you listed. Pro helps you grow.</h1><p>Students stay free, vendors can start free, and paying never changes verification, campus approval or safety status.</p></div><div className="vendor-plan-pill"><span>Current plan</span><strong>{currentTier}</strong><small>{schoolName||'Campus setup'}</small></div></header>
      <section className="growth-trust-strip"><ShieldCheck size={18}/><div><strong>{trustReady?'Trust setup complete':'Trust setup in progress'}</strong><span>Business setup, identity verification and campus approval remain separate from billing.</span></div></section>
      <section className="growth-trust-grid"><article className={setupComplete?'done':''}><Store/><strong>Business setup</strong><span>{setupComplete?'Completed':'Needs attention'}</span></article><article className={identityApproved?'done':''}><BadgeCheck/><strong>Identity</strong><span>{identityApproved?'Approved':vendor.verification_status||'Pending'}</span></article><article className={campusApproved?'done':''}><Building2/><strong>Campus</strong><span>{campusApproved?'Approved':campus?.status||'Not submitted'}</span></article></section>
      <section className="growth-plan-grid">
        <article className={`growth-plan-card ${currentTier==='Free'?'current':''}`}><span className="v5e-section-label">Campus Link Free</span><h2>Start useful.</h2><div className="growth-price"><strong>₦0</strong><span>forever</span></div><p>Everything needed to be discoverable and trusted without forcing a payment.</p><ul><li><Check/>1 approved campus</li><li><Check/>Up to {Number(freePlan?.entitlements?.product_limit||5)} products</li><li><Check/>Up to {Number(freePlan?.entitlements?.service_limit||5)} services</li><li><Check/>Up to {Number(freePlan?.entitlements?.portfolio_limit||6)} portfolio items</li><li><Check/>Reviews, saves and direct contact</li><li><Check/>Essential 7-day analytics</li></ul>{currentTier==='Free'?<em>Current plan</em>:null}</article>
        <article className={`growth-plan-card pro ${currentTier==='Pro'?'current':''}`}><span className="v5e-section-label">Campus Link Pro</span><h2>Grow with evidence.</h2><div className="growth-price"><strong>{formatNaira(monthlyPro?.price_ngn||2500)}</strong><span>/month</span></div><p>More capacity and deeper insight for vendors actively building a campus business.</p><ul><li><ListChecks/>Up to {Number(monthlyPro?.entitlements?.service_limit||20)} services</li><li><ImagePlus/>Up to {Number(monthlyPro?.entitlements?.portfolio_limit||30)} portfolio items</li><li><BarChart3/>Longer analytics history</li><li><Eye/>Listing and visibility insights</li><li><UsersRound/>Growth and conversion signals</li><li><Sparkles/>Future promotion eligibility</li></ul><div className="growth-annual"><strong>{formatNaira(annualPrice)}/year</strong><span>Save {formatNaira(annualSaving)}</span></div>{currentTier==='Free'&&checkoutReady?<div className="phase5c-checkout-grid"><PaystackCheckoutButton planSlug="pro-monthly" label={`Upgrade monthly · ${formatNaira(monthlyPro?.price_ngn||2500)}`}/><PaystackCheckoutButton planSlug="pro-annual" label={`Upgrade yearly · ${formatNaira(annualPrice)}`}/></div>:null}{currentTier==='Free'&&!checkoutReady?<div className="phase5a-plan-foot"><LockKeyhole size={17}/><span>Secure checkout is not ready yet.</span></div>:null}{currentTier==='Pro'?<Link className="growth-manage-link" href="/vendor-v2/billing"><CircleDollarSign size={16}/>Manage billing</Link>:null}</article>
      </section>
      <section className="v5e-panel growth-principles"><div><ShieldCheck/><strong>Payment never buys trust</strong><span>Paid plans cannot override identity, campus approval or safety review.</span></div><div><CircleDollarSign/><strong>Students stay free</strong><span>Discovery, saving, reviewing and contacting approved vendors remain free.</span></div><div><BarChart3/><strong>Pro should prove value</strong><span>Growth tools focus on real visibility and contact intent, not vanity metrics.</span></div></section>
    </section>
  </main>
}
