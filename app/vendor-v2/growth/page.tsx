import Link from 'next/link'
import { redirect } from 'next/navigation'
import {
  ArrowLeft,
  ArrowUpRight,
  BadgeCheck,
  BarChart3,
  Building2,
  Check,
  CircleDollarSign,
  Eye,
  ImagePlus,
  LockKeyhole,
  ShieldCheck,
  Sparkles,
  Store,
  UsersRound,
} from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { ThemeToggle } from '@/components/theme-toggle'

function formatNaira(value: number | string | null | undefined) {
  const amount = Number(value || 0)
  return new Intl.NumberFormat('en-NG', {
    style: 'currency',
    currency: 'NGN',
    maximumFractionDigits: 0,
  }).format(amount)
}

export default async function VendorGrowthPage() {
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const { data: profile } = await supabase
    .from('profiles')
    .select('account_type,first_name')
    .eq('id', userId)
    .maybeSingle()

  if (!profile || profile.account_type !== 'vendor') redirect('/dashboard')

  const [{ data: vendor }, { data: campus }, { data: plans }, { data: subscriptions }] = await Promise.all([
    supabase
      .from('vendor_profiles')
      .select('business_name,verification_status,onboarding_completed_at,logo_url,description')
      .eq('id', userId)
      .maybeSingle(),
    supabase
      .from('vendor_institutions')
      .select('status,institution_id')
      .eq('vendor_id', userId)
      .eq('is_primary', true)
      .maybeSingle(),
    supabase
      .from('subscription_plans')
      .select('id,name,slug,price_ngn,billing_interval,features,is_active')
      .eq('is_active', true)
      .order('price_ngn'),
    supabase
      .from('subscriptions')
      .select('id,plan_id,status,starts_at,ends_at')
      .eq('vendor_id', userId)
      .in('status', ['active', 'past_due'])
      .order('created_at', { ascending: false })
      .limit(1),
  ])

  if (!vendor) redirect('/onboarding/vendor')

  let schoolName: string | null = null
  if (campus?.institution_id) {
    const { data: institution } = await supabase
      .from('institutions')
      .select('name')
      .eq('id', campus.institution_id)
      .maybeSingle()
    schoolName = institution?.name || null
  }

  const activeSubscription = subscriptions?.[0] || null
  const activePlan = activeSubscription ? plans?.find((plan) => plan.id === activeSubscription.plan_id) : null
  const currentTier = activePlan?.slug?.startsWith('pro') ? 'Pro' : 'Free'
  const identityApproved = vendor.verification_status === 'approved'
  const campusApproved = campus?.status === 'approved'
  const setupComplete = Boolean(vendor.onboarding_completed_at)
  const trustReady = identityApproved && campusApproved && setupComplete

  const monthlyPro = plans?.find((plan) => plan.slug === 'pro-monthly')
  const annualPro = plans?.find((plan) => plan.slug === 'pro-annual')
  const freePlan = plans?.find((plan) => plan.slug === 'free') || plans?.find((plan) => Number(plan.price_ngn || 0) === 0)

  return (
    <main className="phase5a-growth-shell">
      <header className="phase5a-topbar">
        <Link href="/vendor-v2" className="phase5a-back"><ArrowLeft size={17}/> Dashboard</Link>
        <Link href="/" className="phase5a-brand">Campus<span>Link</span></Link>
        <div className="phase5a-top-actions"><ThemeToggle compact/><form action="/auth/signout" method="post"><button>Sign out</button></form></div>
      </header>

      <section className="phase5a-content">
        <section className="phase5a-hero">
          <div className="phase5a-hero-copy">
            <span className="phase5a-kicker"><Sparkles size={15}/> Vendor growth</span>
            <h1>Grow your business without buying trust.</h1>
            <p>Campus Link keeps verification, campus approval and paid growth separate. Students and universities should always be able to tell the difference.</p>
            <div className="phase5a-inline-meta">
              <span><Store size={15}/>{vendor.business_name}</span>
              <span><Building2 size={15}/>{schoolName || 'Campus not selected yet'}</span>
            </div>
          </div>
          <div className="phase5a-current-plan">
            <span>Current access</span>
            <strong>{currentTier}</strong>
            <small>{currentTier === 'Pro' ? 'Growth tools are active.' : 'Core discovery stays useful on Free.'}</small>
            <div className={`phase5a-trust-chip ${trustReady ? 'ready' : ''}`}><ShieldCheck size={16}/>{trustReady ? 'Trust ready' : 'Trust setup in progress'}</div>
          </div>
        </section>

        <section className="phase5a-trust-panel">
          <div className="phase5a-section-title">
            <div><span>Trust first</span><h2>Paid access never changes your verification status.</h2></div>
            <ShieldCheck size={24}/>
          </div>
          <div className="phase5a-trust-steps">
            <div className={setupComplete ? 'done' : ''}><span>{setupComplete ? <Check/> : <Store/>}</span><div><strong>Business setup</strong><small>{setupComplete ? 'Business profile completed.' : 'Complete your business details first.'}</small></div></div>
            <div className={identityApproved ? 'done' : ''}><span>{identityApproved ? <Check/> : <BadgeCheck/>}</span><div><strong>Identity verification</strong><small>{identityApproved ? 'Approved by Campus Link.' : `Status: ${vendor.verification_status?.replace('_',' ') || 'pending'}.`}</small></div></div>
            <div className={campusApproved ? 'done' : ''}><span>{campusApproved ? <Check/> : <Building2/>}</span><div><strong>Campus approval</strong><small>{campusApproved ? `${schoolName || 'Your campus'} approved.` : `Status: ${campus?.status?.replace('_',' ') || 'not submitted'}.`}</small></div></div>
          </div>
          {!trustReady ? <Link href="/onboarding/vendor" className="phase5a-text-link">Continue trust setup <ArrowUpRight size={15}/></Link> : <div className="phase5a-university-note"><ShieldCheck size={17}/><span>Your trust status is independent from Free or Pro. This is the standard universities and students can rely on.</span></div>}
        </section>

        <section className="phase5a-plans-section">
          <div className="phase5a-section-title wide">
            <div><span>Simple plans</span><h2>Free gets you discovered. Pro helps you understand and grow demand.</h2></div>
            <p>No student fees. No commission on student-to-vendor transactions. No paid verification.</p>
          </div>

          <div className="phase5a-plan-grid">
            <article className={`phase5a-plan-card ${currentTier === 'Free' ? 'current' : ''}`}>
              <div className="phase5a-plan-head"><div><span>Campus Link</span><h3>Free</h3></div>{currentTier === 'Free' ? <em>Current plan</em> : null}</div>
              <div className="phase5a-price"><strong>{formatNaira(freePlan?.price_ngn || 0)}</strong><span>/ forever</span></div>
              <p>Everything a trusted campus vendor needs to build a real presence and be contacted by students.</p>
              <ul>
                <li><Check/> Verified vendor profile</li>
                <li><Check/> Campus-specific discovery</li>
                <li><Check/> Services and portfolio</li>
                <li><Check/> Reviews, ratings and saves</li>
                <li><Check/> Direct WhatsApp/contact access</li>
              </ul>
              <div className="phase5a-plan-foot"><ShieldCheck size={17}/><span>Verification is earned, not purchased.</span></div>
            </article>

            <article className={`phase5a-plan-card pro ${currentTier === 'Pro' ? 'current' : ''}`}>
              <div className="phase5a-plan-head"><div><span>Campus Link</span><h3>Pro</h3></div>{currentTier === 'Pro' ? <em>Current plan</em> : <em>Growth tier</em>}</div>
              <div className="phase5a-price"><strong>{formatNaira(monthlyPro?.price_ngn || 2500)}</strong><span>/ month</span></div>
              <p>For vendors ready to measure demand, improve their profile and become eligible for clearly labelled promotion.</p>
              <ul>
                <li><BarChart3/> Advanced performance analytics</li>
                <li><Eye/> Search and profile visibility insights</li>
                <li><UsersRound/> Contact and conversion trends</li>
                <li><ImagePlus/> Larger service and portfolio limits</li>
                <li><Sparkles/> Featured and promotion eligibility</li>
              </ul>
              <div className="phase5a-price-note">Annual option: {formatNaira(annualPro?.price_ngn || 24000)} / year</div>
              <div className="phase5a-plan-foot"><LockKeyhole size={17}/><span>Paystack checkout is connected in the payment phase, not from this foundation screen.</span></div>
            </article>
          </div>
        </section>

        <section className="phase5a-principles">
          <article><ShieldCheck/><div><strong>Trust is separate</strong><span>Paying can unlock tools, never identity approval, university approval or safety privileges.</span></div></article>
          <article><CircleDollarSign/><div><strong>Students stay free</strong><span>Students can search, save, review and contact approved vendors without a subscription.</span></div></article>
          <article><BarChart3/><div><strong>Analytics should be useful</strong><span>Pro will focus on visibility, profile visits and contact intent rather than vanity charts.</span></div></article>
        </section>
      </section>
    </main>
  )
}
