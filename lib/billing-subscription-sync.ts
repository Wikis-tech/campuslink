import 'server-only'

import { createAdminClient } from '@/lib/supabase/admin'
import { fetchPaystackSubscription } from '@/lib/paystack'

type LocalSubscriptionStatus = 'active' | 'attention' | 'non_renewing' | 'cancelled' | 'expired' | 'past_due' | 'inactive'

function mapPaystackStatus(status: string | null | undefined): LocalSubscriptionStatus {
  switch (String(status || '').toLowerCase()) {
    case 'active': return 'active'
    case 'non-renewing':
    case 'non_renewing': return 'non_renewing'
    case 'attention': return 'attention'
    case 'cancelled': return 'cancelled'
    case 'complete':
    case 'completed': return 'expired'
    default: return 'inactive'
  }
}

export async function syncVendorSubscriptionFromPaystack(subscriptionId: string, vendorId: string) {
  const admin = createAdminClient()
  const { data: localSub, error: subError } = await admin
    .from('subscriptions')
    .select('id,vendor_id,plan_id,status,provider_subscription_code,provider_customer_code,provider_email_token,current_period_end')
    .eq('id', subscriptionId)
    .eq('vendor_id', vendorId)
    .maybeSingle()

  if (subError) throw subError
  if (!localSub) throw new Error('Subscription not found')
  if (!localSub.provider_subscription_code) throw new Error('Paystack subscription code is not available yet')

  const { data: plan, error: planError } = await admin
    .from('subscription_plans')
    .select('id,paystack_plan_code')
    .eq('id', localSub.plan_id)
    .maybeSingle()
  if (planError) throw planError
  if (!plan) throw new Error('Subscription plan not found')

  const remote = await fetchPaystackSubscription(localSub.provider_subscription_code)
  const data: any = remote.data
  const customerCode = data?.customer?.customer_code || data?.customer_code || null
  const planCode = data?.plan?.plan_code || data?.plan_code || null

  if (localSub.provider_customer_code && customerCode && String(localSub.provider_customer_code) !== String(customerCode)) {
    throw new Error('Paystack customer mismatch')
  }
  if (plan.paystack_plan_code && planCode && String(plan.paystack_plan_code) !== String(planCode)) {
    throw new Error('Paystack plan mismatch')
  }

  const mappedStatus = mapPaystackStatus(data?.status)
  const nextPaymentDate = data?.next_payment_date || null
  const emailToken = data?.email_token || localSub.provider_email_token || null
  const now = new Date().toISOString()

  const update: Record<string, unknown> = {
    status: mappedStatus,
    provider_customer_code: customerCode || localSub.provider_customer_code || null,
    provider_email_token: emailToken,
    updated_at: now,
  }

  if (nextPaymentDate) update.current_period_end = nextPaymentDate
  if (mappedStatus === 'non_renewing') update.cancel_at_period_end = true
  if (mappedStatus === 'active') {
    update.cancel_at_period_end = false
    update.grace_period_ends_at = null
  }
  if (mappedStatus === 'cancelled' || mappedStatus === 'expired') {
    update.cancelled_at = mappedStatus === 'cancelled' ? now : undefined
    update.grace_period_ends_at = null
  }

  const { error: updateError } = await admin.from('subscriptions').update(update).eq('id', localSub.id)
  if (updateError) throw updateError

  const { error: entitlementError } = await admin.rpc('refresh_vendor_entitlements_after_billing', { target_vendor: vendorId })
  if (entitlementError) throw entitlementError

  return {
    status: mappedStatus,
    nextPaymentDate,
  }
}
