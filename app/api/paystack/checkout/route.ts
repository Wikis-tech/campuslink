import { NextResponse } from 'next/server'
import { randomUUID } from 'node:crypto'
import { createClient } from '@/lib/supabase/server'
import { createAdminClient } from '@/lib/supabase/admin'
import { createPaystackCustomer, initializeSubscriptionTransaction } from '@/lib/paystack'

const ALLOWED_PLANS = new Set(['pro-monthly', 'pro-annual'])

export async function POST(request: Request) {
  try {
    const supabase = await createClient()
    const { data: userResult } = await supabase.auth.getUser()
    const user = userResult.user
    if (!user) return NextResponse.json({ error: 'Sign in to continue.' }, { status: 401 })

    const { data: profile } = await supabase.from('profiles').select('account_type,first_name,last_name,phone').eq('id', user.id).maybeSingle()
    if (!profile || profile.account_type !== 'vendor') return NextResponse.json({ error: 'Vendor account required.' }, { status: 403 })

    const body = await request.json().catch(() => ({})) as { planSlug?: string }
    const planSlug = String(body.planSlug || '')
    if (!ALLOWED_PLANS.has(planSlug)) return NextResponse.json({ error: 'Choose a valid Campus Link Pro plan.' }, { status: 400 })

    const admin = createAdminClient()
    const { data: plan } = await admin.from('subscription_plans')
      .select('id,slug,tier,price_ngn,price_kobo,currency,paystack_plan_code,is_active,is_public')
      .eq('slug', planSlug).eq('tier', 'pro').eq('is_active', true).eq('is_public', true).maybeSingle()

    if (!plan?.paystack_plan_code) return NextResponse.json({ error: 'Paystack test plan is not configured yet.' }, { status: 503 })

    const { data: currentSub } = await admin.from('subscriptions').select('id,status').eq('vendor_id', user.id)
      .in('status', ['active','attention','non_renewing']).limit(1).maybeSingle()
    if (currentSub) return NextResponse.json({ error: 'You already have an active Campus Link Pro subscription.' }, { status: 409 })

    const tenMinutesAgo = new Date(Date.now() - 10 * 60 * 1000).toISOString()
    const { count: recentAttempts } = await admin.from('payments').select('id', { count: 'exact', head: true })
      .eq('vendor_id', user.id).eq('provider', 'paystack').gte('created_at', tenMinutesAgo)
    if ((recentAttempts || 0) >= 5) return NextResponse.json({ error: 'Too many checkout attempts. Please wait a few minutes and try again.' }, { status: 429 })

    const email = user.email
    if (!email) return NextResponse.json({ error: 'Your account does not have a billing email.' }, { status: 400 })

    let { data: billingCustomer } = await admin.from('billing_customers').select('provider_customer_code').eq('vendor_id', user.id).maybeSingle()
    if (!billingCustomer) {
      const customer = await createPaystackCustomer({ email, firstName: profile.first_name, lastName: profile.last_name, phone: profile.phone, vendorId: user.id })
      const customerCode = customer.data.customer_code
      const insert = await admin.from('billing_customers').insert({ vendor_id: user.id, provider_customer_code: customerCode }).select('provider_customer_code').single()
      if (insert.error) {
        const existing = await admin.from('billing_customers').select('provider_customer_code').eq('vendor_id', user.id).maybeSingle()
        if (!existing.data) throw insert.error
        billingCustomer = existing.data
      } else billingCustomer = insert.data
    }

    const reference = `CL-${Date.now()}-${randomUUID().replace(/-/g, '').slice(0, 16)}`
    const { data: subscription, error: subscriptionError } = await admin.from('subscriptions').insert({
      vendor_id: user.id,
      plan_id: plan.id,
      status: 'inactive',
      provider: 'paystack',
      provider_customer_code: billingCustomer.provider_customer_code,
      metadata: { checkout_reference: reference, source: 'phase5c' },
    }).select('id').single()
    if (subscriptionError) throw subscriptionError

    const { error: paymentError } = await admin.from('payments').insert({
      vendor_id: user.id,
      subscription_id: subscription.id,
      provider: 'paystack',
      reference,
      amount_ngn: Number(plan.price_ngn),
      amount_kobo: Number(plan.price_kobo),
      currency: 'NGN',
      status: 'pending',
      metadata: { plan_slug: plan.slug },
    })
    if (paymentError) throw paymentError

    const appUrl = process.env.NEXT_PUBLIC_APP_URL || new URL(request.url).origin
    try {
      const initialized = await initializeSubscriptionTransaction({
        email,
        amountKobo: Number(plan.price_kobo),
        planCode: plan.paystack_plan_code,
        reference,
        callbackUrl: `${appUrl.replace(/\/$/, '')}/vendor-v2/billing/callback`,
        metadata: {
          campuslink_vendor_id: user.id,
          campuslink_plan_id: plan.id,
          campuslink_plan_slug: plan.slug,
          campuslink_subscription_id: subscription.id,
          campuslink_reference: reference,
        },
      })
      return NextResponse.json({ authorizationUrl: initialized.data.authorization_url, reference })
    } catch (error) {
      await admin.from('payments').update({ status: 'failed', failure_reason: 'checkout_initialization_failed', updated_at: new Date().toISOString() }).eq('reference', reference)
      await admin.from('subscriptions').update({ status: 'cancelled', cancelled_at: new Date().toISOString(), updated_at: new Date().toISOString() }).eq('id', subscription.id)
      throw error
    }
  } catch (error) {
    console.error('Paystack checkout initialization failed', error)
    return NextResponse.json({ error: 'We could not start secure checkout. Please try again.' }, { status: 500 })
  }
}
