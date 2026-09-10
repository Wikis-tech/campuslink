import 'server-only'

import { createAdminClient } from '@/lib/supabase/admin'
import { verifyPaystackTransaction } from '@/lib/paystack'

type ReconcileResult = {
  ok: boolean
  status: 'active' | 'pending' | 'failed'
  vendorId?: string
  subscriptionId?: string
  tier?: string
  message?: string
}

function periodEndFromInterval(start: Date, interval: string | null | undefined) {
  const next = new Date(start)
  switch (interval) {
    case 'daily':
      next.setUTCDate(next.getUTCDate() + 1)
      break
    case 'weekly':
      next.setUTCDate(next.getUTCDate() + 7)
      break
    case 'quarterly':
      next.setUTCMonth(next.getUTCMonth() + 3)
      break
    case 'biannually':
      next.setUTCMonth(next.getUTCMonth() + 6)
      break
    case 'yearly':
    case 'annually':
      next.setUTCFullYear(next.getUTCFullYear() + 1)
      break
    case 'monthly':
    default:
      next.setUTCMonth(next.getUTCMonth() + 1)
      break
  }
  return next.toISOString()
}

/**
 * Server-side recovery/confirmation path for a Campus Link Paystack checkout.
 * A browser can trigger a page load, but cannot grant Pro: this function always
 * verifies the transaction with Paystack, compares it with the server-created
 * payment record, activates only that exact subscription, then refreshes derived
 * entitlements through the service-role-only RPC.
 */
export async function reconcileSuccessfulPaystackPayment(reference: string, expectedVendorId?: string): Promise<ReconcileResult> {
  const admin = createAdminClient()

  const { data: payment, error: paymentError } = await admin
    .from('payments')
    .select('id,vendor_id,subscription_id,amount_kobo,currency,status')
    .eq('reference', reference)
    .maybeSingle()

  if (paymentError) throw paymentError
  if (!payment) return { ok: false, status: 'failed', message: 'payment_not_found' }
  if (expectedVendorId && payment.vendor_id !== expectedVendorId) {
    return { ok: false, status: 'failed', message: 'payment_vendor_mismatch' }
  }
  if (!payment.subscription_id) return { ok: false, status: 'failed', message: 'subscription_not_linked' }

  const verified = await verifyPaystackTransaction(reference)
  const tx: any = verified.data
  const txStatus = String(tx?.status || '')

  if (txStatus !== 'success') {
    return {
      ok: false,
      status: ['failed', 'abandoned', 'reversed'].includes(txStatus) ? 'failed' : 'pending',
      vendorId: payment.vendor_id,
      subscriptionId: payment.subscription_id,
      message: `paystack_${txStatus || 'pending'}`,
    }
  }

  if (String(tx.reference) !== reference) throw new Error('Paystack reference mismatch')
  if (Number(tx.amount) !== Number(payment.amount_kobo)) throw new Error('Paystack amount mismatch')
  if (String(tx.currency || '').toUpperCase() !== String(payment.currency || 'NGN').toUpperCase()) {
    throw new Error('Paystack currency mismatch')
  }

  const { data: subscription, error: subscriptionError } = await admin
    .from('subscriptions')
    .select('id,vendor_id,plan_id,status,current_period_end,provider_subscription_code')
    .eq('id', payment.subscription_id)
    .maybeSingle()

  if (subscriptionError) throw subscriptionError
  if (!subscription || subscription.vendor_id !== payment.vendor_id) {
    throw new Error('Campus Link subscription mismatch')
  }

  const { data: plan, error: planError } = await admin
    .from('subscription_plans')
    .select('id,slug,tier,billing_interval,paystack_plan_code,is_active')
    .eq('id', subscription.plan_id)
    .maybeSingle()

  if (planError) throw planError
  if (!plan || plan.tier !== 'pro' || !plan.is_active) throw new Error('Invalid Campus Link Pro plan')

  const txPlanCode = tx?.plan?.plan_code || tx?.plan_code || null
  if (txPlanCode && plan.paystack_plan_code && String(txPlanCode) !== String(plan.paystack_plan_code)) {
    throw new Error('Paystack plan mismatch')
  }

  const paidAt = tx.paid_at || tx.paidAt || new Date().toISOString()
  const customerCode = tx?.customer?.customer_code || null
  const subscriptionCode = tx?.subscription?.subscription_code || tx?.subscription_code || subscription.provider_subscription_code || null
  const periodEnd = subscription.current_period_end || periodEndFromInterval(new Date(paidAt), plan.billing_interval)

  const { error: paymentUpdateError } = await admin
    .from('payments')
    .update({
      status: 'successful',
      provider_transaction_id: tx?.id ? String(tx.id) : null,
      channel: tx?.channel || null,
      paid_at: paidAt,
      failure_reason: null,
      updated_at: new Date().toISOString(),
    })
    .eq('id', payment.id)
  if (paymentUpdateError) throw paymentUpdateError

  if (customerCode) {
    const { error: customerError } = await admin
      .from('billing_customers')
      .upsert(
        { vendor_id: payment.vendor_id, provider_customer_code: customerCode, updated_at: new Date().toISOString() },
        { onConflict: 'vendor_id' },
      )
    if (customerError) throw customerError
  }

  const { error: subscriptionUpdateError } = await admin
    .from('subscriptions')
    .update({
      status: 'active',
      provider_customer_code: customerCode || undefined,
      provider_subscription_code: subscriptionCode || undefined,
      last_payment_at: paidAt,
      current_period_start: paidAt,
      current_period_end: periodEnd,
      cancel_at_period_end: false,
      grace_period_ends_at: null,
      updated_at: new Date().toISOString(),
    })
    .eq('id', subscription.id)
  if (subscriptionUpdateError) throw subscriptionUpdateError

  // Audit the recovery/verified activation without duplicating the same reference.
  const eventInsert = await admin.from('subscription_events').insert({
    subscription_id: subscription.id,
    vendor_id: payment.vendor_id,
    event_type: 'verified_payment_activation',
    source: 'system',
    provider_event_id: `verified:${reference}`,
    previous_status: subscription.status,
    new_status: 'active',
    metadata: { reference, plan_slug: plan.slug },
  })
  if (eventInsert.error && eventInsert.error.code !== '23505') throw eventInsert.error

  const { error: entitlementError } = await admin.rpc('refresh_vendor_entitlements_after_billing', {
    target_vendor: payment.vendor_id,
  })
  if (entitlementError) throw entitlementError

  return {
    ok: true,
    status: 'active',
    vendorId: payment.vendor_id,
    subscriptionId: subscription.id,
    tier: 'pro',
  }
}
