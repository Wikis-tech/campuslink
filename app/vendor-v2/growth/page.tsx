import Link from 'next/link'
import { redirect } from 'next/navigation'
import {
  ArrowLeft,
  ArrowRight,
  BadgeCheck,
  BarChart3,
  Building2,
  Check,
  CircleDollarSign,
  Eye,
  ImagePlus,
  ListChecks,
  LockKeyhole,
  ShieldCheck,
  Sparkles,
  Store,
  UsersRound,
} from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { ThemeToggle } from '@/components/theme-toggle'
import { PaystackCheckoutButton } from '@/components/paystack-checkout-button'

function formatNaira(value: number | string | null | undefined) {
  const amount = Number(value || 0)
  return new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN', maximumFractionDigits: 0 }).format(amount)
}

export default async function VendorGrowthPage() {
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const { data: profile } = await supabase.from('profiles').select('account_type,first_name').eq('id', userId).maybeSingle()
  if (!profile || profile.account_type !== 'vendor') redirect('/dashboard')

  const [{ data: vendor }, { data: campus }, { data: plans }, { data: subscriptions }, { data: entitlementRows }] = await Promise.all([
    supabase.from('vendor_profiles').select('business_name,verification_status,onboarding_completed_at,logo_url,description').eq('id', userId).maybeSingle(),
    supabase.from('vendor_institutions').select('status,institution_id').eq('vendor_id', userId).eq('is_primary', true).maybeSingle(),
    supabase.from('subscription_plans').select('id,name,slug,tier,price_ngn,billing_interval,features,entitlements,is_active,paystack_plan_code').eq('is_active', true).eq('is_public', true).order('sort_order'),
    supabase.from('subscriptions').select('id,plan_id,status,starts_at,ends_at,current_period_end').eq('vendor_id', userId).in('status', ['active', 'attention', 'non_renewing', 'past_due']).order('created_at', { ascending: false }).limit(1),
    supabase.rpc('get_my_vendor_entitlements'),
  ])

  if (!vendor) redirect('/onboarding/vendor')

  let schoolName: string | null = null
  if (campus?.institution_id) {
    const { data: institution } = await supabase.from('institutions').select('name').eq('id', campus.institution_id).maybeSingle()
    schoolName = institution?.name || null
  }

  const activeSubscription = subscriptions?.[0] || null
  const activePlan = activeSubscription ? plans?.find((plan) => plan.id === activeSubscription.plan_id) : null
  const entitlement = Array.isArray(entitlementRows) ? entitlementRows[0] : null
  const currentTier = entitlement?.tier === 'pro' || activePlan?.tier === 'pro' ? 'Pro' : 'Free'
  const identityApproved = vendor.verification_status === 'approved'
  const campusApproved = campus?.status === 'approved'
  const setupComplete = Boolean(vendor.onboarding_completed_at)
  const trustReady = identityApproved && campusApproved && setupComplete

  const monthlyPro = plans?.find((plan) => plan.slug === 'pro-monthly')
  const annualPro = plans?.find((plan) => plan.slug === 'pro-annual')
  const freePlan = plans?.find((plan) => plan.slug === 'free') || plans?.find((plan) => Number(plan.price_ngn || 0) === 0)
  const monthlyAnnualised = Number(monthlyPro?.price_ngn || 2500) * 12
  const annualPrice = Number(annualPro?.price_ngn || 24000)
  const annualSaving = Math.max(monthlyAnnualised - annualPrice, 0)
  const currentServiceLimit = Number(entitlement?.entitlements?.service_limit || freePlan?.entitlements?.service_limit || 5)
  const currentPortfolioLimit = Number(entitlement?.entitlements?.portfolio_limit || freePlan?.entitlements?.portfolio_limit || 6)
  const checkoutReady = Boolean(monthlyPro?.paystack_plan_code && annualPro?.paystack_plan_code)

  return (
    <main className="phase5a-growth-shell">
      <header className="phase5a-topbar">
        <Link href="/vendor-v2" className="phase5a-back"><ArrowLeft size={17}/> Dashboard</Link>
        <Link href="/" className="phase5a-brand">Campus<span>Link</span></Link>
        <div className="phase5a-top-actions"><Link href="/vendor-v2/services">Services</Link><Link href="/vendor-v2/portfolio">Portfolio</Link><ThemeToggle compact/><form action="/auth/signout" method="post"><button>Sign out</button></form></div>
      </header>

      <section className="phase5a-content">
        <section className="phase5a-hero">
          <div className="phase5a-hero-copy">
            <span className="phase5a-kicker"><Sparkles size={15}/> Plans & growth</span>
            <h1>Free gets you listed. Pro helps you grow.</h1>
            <p>Campus Link is designed for student affordability and university trust: students stay free, vendors can start free, and paying never changes verification.</p>
            <div className="phase5a-inline-meta"><span><Store size={15}/>{vendor.business_name}</span><span><Building2 size={15}/>{schoolName || 'Campus not selected yet'}</span></div>
          </div>
          <div className="phase5a-current-plan">
            <span>Current plan</span>
            <strong>{currentTier}</strong>
            <small>{currentTier === 'Pro' ? 'Growth tools are active.' : `${currentServiceLimit} services · ${currentPortfolioLimit} portfolio items`}</small>
            <div className={`phase5a-trust-chip ${trustReady ? 'ready' : ''}`}><ShieldCheck size={16}/>{trustReady ? 'Trust ready' : 'Trust setup in progress'}</div>
          </div>
        </section>

        <section className="phase5a-trust-panel">
          <div className="phase5a-section-title"><div><span>Trust first</span><h2>Plan status and verification are deliberately separate.</h2></div><ShieldCheck size={24}/></div>
          <div className="phase5a-trust-steps">
            <div className={setupComplete ? 'done' : ''}><span>{setupComplete ? <Check/> : <Store/>}</span><div><strong>Business setup</strong><small>{setupComplete ? 'Business profile completed.' : 'Complete your business details first.'}</small></div></div>
            <div className={identityApproved ? 'done' : ''}><span>{identityApproved ? <Check/> : <BadgeCheck/>}</span><div><strong>Identity verification</strong><small>{identityApproved ? 'Approved by Campus Link.' : `Status: ${vendor.verification_status?.replace('_',' ') || 'pending'}.`}</small></div></div>
            <div className={campusApproved ? 'done' : ''}><span>{campusApproved ? <Check/> : <Building2/>}</span><div><strong>Campus approval</strong><small>{campusApproved ? `${schoolName || 'Your campus'} approved.` : `Status: ${campus?.status?.replace('_',' ') || 'not submitted'}.`}</small></div></div>
          </div>
          {!trustReady ? <Link href="/onboarding/vendor" className="phase5a-text-link">Continue trust setup <ArrowRight size={15}/></Link> : <div className="phase5a-university-note"><ShieldCheck size={17}/><span>Your trust status is independent from Free or Pro. Paying cannot unlock public discovery without verification and campus approval.</span></div>}
        </section>

        <section className="phase5a-plans-section">
          <div className="phase5a-section-title wide"><div><span>Two simple choices</span><h2>Start useful. Upgrade only when the extra tools are worth it.</h2></div><p>No student fees. No commission on student-to-vendor transactions. No paid verification.</p></div>

          <div className="phase5a-plan-grid">
            <article className={`phase5a-plan-card ${currentTier === 'Free' ? 'current' : ''}`}>
              <div className="phase5a-plan-head"><div><span>Campus Link</span><h3>Free</h3></div>{currentTier === 'Free' ? <em>Current plan</em> : null}</div>
              <div className="phase5a-price"><strong>{formatNaira(freePlan?.price_ngn || 0)}</strong><span>/ forever</span></div>
              <p>A complete starting point for trusted campus discovery. Free is useful on purpose.</p>
              <ul>
                <li><Check/> Verified vendor profile when approved</li>
                <li><Check/> 1 approved campus</li>
                <li><Check/> Up to {Number(freePlan?.entitlements?.service_limit || 5)} active services</li>
                <li><Check/> Up to {Number(freePlan?.entitlements?.portfolio_limit || 6)} portfolio items</li>
                <li><Check/> Reviews, ratings, saves and WhatsApp/contact access</li>
                <li><Check/> Basic dashboard and discovery</li>
              </ul>
              <div className="phase5a-plan-foot"><ShieldCheck size={17}/><span>Verification is earned, not purchased.</span></div>
            </article>

            <article className={`phase5a-plan-card pro ${currentTier === 'Pro' ? 'current' : ''}`}>
              <div className="phase5a-plan-head"><div><span>Campus Link</span><h3>Pro</h3></div>{currentTier === 'Pro' ? <em>Current plan</em> : <em>Growth tier</em>}</div>
              <div className="phase5a-price"><strong>{formatNaira(monthlyPro?.price_ngn || 2500)}</strong><span>/ month</span></div>
              <p>For vendors who want more room, clearer performance signals and future promotion tools.</p>
              <ul>
                <li><ListChecks/> Up to {Number(monthlyPro?.entitlements?.service_limit || 20)} active services</li>
                <li><ImagePlus/> Up to {Number(monthlyPro?.entitlements?.portfolio_limit || 30)} portfolio items</li>
                <li><BarChart3/> Advanced analytics and profile insights</li>
                <li><Eye/> Search, profile and contact visibility trends</li>
                <li><UsersRound/> Review and conversion trend tools</li>
                <li><Sparkles/> Featured and promotion eligibility</li>
              </ul>
              <div className="phase5a-price-note"><strong>{formatNaira(annualPro?.price_ngn || 24000)} / year</strong> · save {formatNaira(annualSaving)} versus paying monthly for 12 months.</div>
              {currentTier === 'Free' && checkoutReady ? <div className="phase5c-checkout-grid"><PaystackCheckoutButton planSlug="pro-monthly" label={`Upgrade monthly · ${formatNaira(monthlyPro?.price_ngn || 2500)}`}/><PaystackCheckoutButton planSlug="pro-annual" label={`Upgrade yearly · ${formatNaira(annualPro?.price_ngn || 24000)}`}/></div> : null}
              {currentTier === 'Free' && !checkoutReady ? <div className="phase5a-plan-foot"><LockKeyhole size={17}/><span>Secure checkout will appear as soon as the two Paystack TEST plan codes are connected.</span></div> : null}
              {currentTier === 'Pro' ? <div className="phase5a-plan-foot"><ShieldCheck size={17}/><span>Your Pro entitlement is active. Billing management arrives in Phase 5D.</span></div> : null}
            </article>
          </div>
        </section>

        <section className="phase5a-principles">
          <article><ShieldCheck/><div><strong>Trust is separate</strong><span>Paying can unlock tools, never identity approval, university approval or safety privileges.</span></div></article>
          <article><CircleDollarSign/><div><strong>Students stay free</strong><span>Students can search, save, review and contact approved vendors without a subscription.</span></div></article>
          <article><BarChart3/><div><strong>Pro must prove value</strong><span>Analytics will focus on visibility, profile visits and contact intent rather than vanity charts.</span></div></article>
        </section>

        <section className="phase5a-trust-panel">
          <div className="phase5a-section-title"><div><span>Use your plan</span><h2>Manage the parts students actually see.</h2></div></div>
          <div className="phase5a-trust-steps">
            <Link href="/vendor-v2/services" style={{textDecoration:'none',color:'inherit'}}><span><ListChecks/></span><div><strong>Services</strong><small>Add, pause and organise your active offer.</small></div></Link>
            <Link href="/vendor-v2/portfolio" style={{textDecoration:'none',color:'inherit'}}><span><ImagePlus/></span><div><strong>Portfolio</strong><small>Show genuine work within your current plan limit.</small></div></Link>
            <div><span><BarChart3/></span><div><strong>Analytics</strong><small>Advanced analytics arrives in Phase 5E after event tracking is complete.</small></div></div>
          </div>
        </section>
      </section>
    </main>
  )
}
