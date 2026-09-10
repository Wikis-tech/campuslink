'use server'

import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'
import { createAdminClient } from '@/lib/supabase/admin'
import { generatePaystackSubscriptionManageLink, isTrustedPaystackManageUrl, sendPaystackSubscriptionManageEmail } from '@/lib/paystack'
import { syncVendorSubscriptionFromPaystack } from '@/lib/billing-subscription-sync'

function billingRedirect(type: 'success' | 'error', message: string): never {
  redirect(`/vendor-v2/billing?${type}=${encodeURIComponent(message)}`)
}

async function requireVendorWithSubscription() {
  const supabase = await createClient()
  const { data: userData } = await supabase.auth.getUser()
  const user = userData.user
  if (!user) redirect('/login')
  const { data: profile } = await supabase.from('profiles').select('account_type').eq('id', user.id).maybeSingle()
  if (!profile || profile.account_type !== 'vendor') redirect('/dashboard')
  const admin = createAdminClient()
  const { data: subscriptions, error } = await admin.from('subscriptions').select('id,vendor_id,status,provider_subscription_code,created_at,current_period_end').eq('vendor_id', user.id).order('created_at', { ascending: false }).limit(8)
  if (error) throw error
  const subscription = (subscriptions || []).find((item) => ['active','attention','non_renewing','past_due'].includes(item.status)) || subscriptions?.[0] || null
  return { user, admin, subscription }
}

export async function openSubscriptionManager() {
  const { user, admin, subscription } = await requireVendorWithSubscription()
  if (!subscription?.provider_subscription_code) billingRedirect('error', 'Your Paystack subscription is still syncing. Refresh billing status and try again.')
  try {
    const response = await generatePaystackSubscriptionManageLink(subscription.provider_subscription_code)
    const link = response.data?.link
    if (!link || !isTrustedPaystackManageUrl(link)) throw new Error('Paystack returned an invalid management link')
    await admin.from('subscription_events').insert({ subscription_id: subscription.id, vendor_id: user.id, event_type: 'vendor_opened_subscription_manager', source: 'system', metadata: { via: 'billing_page' } })
    redirect(link)
  } catch (error) {
    if ((error as any)?.digest?.startsWith?.('NEXT_REDIRECT')) throw error
    billingRedirect('error', error instanceof Error ? error.message : 'Unable to open Paystack subscription management right now.')
  }
}

export async function emailSubscriptionManager() {
  const { user, admin, subscription } = await requireVendorWithSubscription()
  if (!subscription?.provider_subscription_code) billingRedirect('error', 'Your Paystack subscription is still syncing. Try again shortly.')
  try {
    await sendPaystackSubscriptionManageEmail(subscription.provider_subscription_code)
    await admin.from('subscription_events').insert({ subscription_id: subscription.id, vendor_id: user.id, event_type: 'vendor_requested_subscription_email', source: 'system', metadata: { via: 'billing_page' } })
    billingRedirect('success', 'Paystack sent a secure subscription-management email to your billing email address.')
  } catch (error) {
    billingRedirect('error', error instanceof Error ? error.message : 'Unable to send the management email right now.')
  }
}

export async function refreshBillingStatus() {
  const { user, subscription } = await requireVendorWithSubscription()
  if (!subscription) billingRedirect('error', 'No subscription was found for this vendor yet.')
  if (!subscription.provider_subscription_code) billingRedirect('error', 'Your Paystack subscription code has not arrived yet. If you just paid, wait a moment and refresh again.')
  try {
    await syncVendorSubscriptionFromPaystack(subscription.id, user.id)
    billingRedirect('success', 'Billing status refreshed securely from Paystack.')
  } catch (error) {
    billingRedirect('error', error instanceof Error ? error.message : 'Unable to refresh billing status right now.')
  }
}
