import { NextResponse } from 'next/server'
import { createAdminClient } from '@/lib/supabase/admin'
import { paystackEventFingerprint, verifyPaystackTransaction, verifyPaystackWebhookSignature } from '@/lib/paystack'

export const runtime = 'nodejs'

function safeEventPayload(event: any) {
  const data = event?.data || {}
  return {
    event: event?.event || null,
    reference: data.reference || null,
    transaction_id: data.id ? String(data.id) : null,
    status: data.status || null,
    amount: data.amount || null,
    currency: data.currency || null,
    paid_at: data.paid_at || data.paidAt || null,
    channel: data.channel || null,
    customer_code: data.customer?.customer_code || null,
    customer_email: data.customer?.email || null,
    subscription_code: data.subscription_code || data.subscription?.subscription_code || null,
    plan_code: data.plan?.plan_code || data.plan_code || null,
    next_payment_date: data.next_payment_date || data.subscription?.next_payment_date || null,
  }
}

async function refreshEntitlements(admin: ReturnType<typeof createAdminClient>, vendorId: string) {
  await admin.rpc('refresh_vendor_entitlements_after_billing', { target_vendor: vendorId })
}

export async function POST(request: Request) {
  const rawBody = await request.text()
  const signature = request.headers.get('x-paystack-signature')
  if (!verifyPaystackWebhookSignature(rawBody, signature)) {
    return NextResponse.json({ ok: false }, { status: 401 })
  }

  let event: any
  try { event = JSON.parse(rawBody) } catch { return NextResponse.json({ ok: false }, { status: 400 }) }
  const eventType = String(event?.event || '')
  if (!eventType) return NextResponse.json({ ok: false }, { status: 400 })

  const admin = createAdminClient()
  const eventId = paystackEventFingerprint(rawBody)
  const safePayload = safeEventPayload(event)

  const inserted = await admin.from('payment_events').insert({
    provider: 'paystack', provider_event_id: eventId, event_type: eventType, payload: safePayload,
    processing_started_at: new Date().toISOString(),
  }).select('id,processed_at').single()

  let eventRowId: number | null = inserted.data?.id || null
  if (inserted.error) {
    if (inserted.error.code !== '23505') return NextResponse.json({ ok: false }, { status: 500 })
    const existing = await admin.from('payment_events').select('id,processed_at,processing_started_at').eq('provider','paystack').eq('provider_event_id', eventId).maybeSingle()
    if (existing.data?.processed_at) return NextResponse.json({ ok: true, duplicate: true })
    eventRowId = existing.data?.id || null
    if (!eventRowId) return NextResponse.json({ ok: false }, { status: 500 })
    await admin.from('payment_events').update({ processing_started_at: new Date().toISOString(), processing_error: null }).eq('id', eventRowId)
  }

  try {
    const data = event.data || {}

    if (eventType === 'charge.success') {
      const reference = String(data.reference || '')
      if (reference) {
        const { data: payment } = await admin.from('payments').select('id,vendor_id,subscription_id,amount_kobo,currency,status').eq('reference', reference).maybeSingle()
        if (payment) {
          const verified = await verifyPaystackTransaction(reference)
          const tx = verified.data
          if (tx.status !== 'success' || String(tx.reference) !== reference || Number(tx.amount) !== Number(payment.amount_kobo) || String(tx.currency) !== 'NGN') {
            throw new Error('Verified Paystack transaction did not match Campus Link payment')
          }
          const customerCode = tx.customer?.customer_code || data.customer?.customer_code || null
          await admin.from('payments').update({
            status: 'successful', provider_transaction_id: String(tx.id), channel: tx.channel || null,
            paid_at: tx.paid_at || tx.paidAt || new Date().toISOString(), provider_payload: safeEventPayload({ event: eventType, data: tx }),
            updated_at: new Date().toISOString(),
          }).eq('id', payment.id)
          if (customerCode) {
            await admin.from('billing_customers').upsert({ vendor_id: payment.vendor_id, provider_customer_code: customerCode, updated_at: new Date().toISOString() }, { onConflict: 'vendor_id' })
            if (payment.subscription_id) await admin.from('subscriptions').update({ provider_customer_code: customerCode, last_payment_at: tx.paid_at || tx.paidAt || new Date().toISOString(), updated_at: new Date().toISOString() }).eq('id', payment.subscription_id)
          }
        }
      }
    }

    if (eventType === 'subscription.create') {
      const customerCode = data.customer?.customer_code
      const planCode = data.plan?.plan_code
      if (customerCode && planCode) {
        const [{ data: customer }, { data: plan }] = await Promise.all([
          admin.from('billing_customers').select('vendor_id').eq('provider_customer_code', customerCode).maybeSingle(),
          admin.from('subscription_plans').select('id').eq('paystack_plan_code', planCode).maybeSingle(),
        ])
        if (customer?.vendor_id && plan?.id) {
          const { data: existing } = await admin.from('subscriptions').select('id').eq('vendor_id', customer.vendor_id).eq('plan_id', plan.id).order('created_at',{ascending:false}).limit(1).maybeSingle()
          const values = {
            vendor_id: customer.vendor_id, plan_id: plan.id, provider: 'paystack', status: 'active', provider_customer_code: customerCode,
            provider_subscription_code: data.subscription_code || null, provider_email_token: data.email_token || null,
            starts_at: data.createdAt || new Date().toISOString(), current_period_start: data.createdAt || new Date().toISOString(),
            current_period_end: data.next_payment_date || null, cancel_at_period_end: false, grace_period_ends_at: null, updated_at: new Date().toISOString(),
          }
          if (existing?.id) await admin.from('subscriptions').update(values).eq('id', existing.id)
          else await admin.from('subscriptions').insert(values)
          await refreshEntitlements(admin, customer.vendor_id)
        }
      }
    }

    if (['subscription.not_renew','subscription.disable','invoice.payment_failed','invoice.update'].includes(eventType)) {
      const subscriptionCode = data.subscription_code || data.subscription?.subscription_code
      if (subscriptionCode) {
        const { data: localSub } = await admin.from('subscriptions').select('id,vendor_id').eq('provider_subscription_code', subscriptionCode).maybeSingle()
        if (localSub) {
          const now = new Date()
          if (eventType === 'subscription.not_renew') await admin.from('subscriptions').update({ status:'non_renewing', cancel_at_period_end:true, updated_at:now.toISOString() }).eq('id',localSub.id)
          if (eventType === 'subscription.disable') await admin.from('subscriptions').update({ status:'cancelled', cancelled_at:now.toISOString(), current_period_end:now.toISOString(), updated_at:now.toISOString() }).eq('id',localSub.id)
          if (eventType === 'invoice.payment_failed') await admin.from('subscriptions').update({ status:'attention', grace_period_ends_at:new Date(now.getTime()+3*86400000).toISOString(), updated_at:now.toISOString() }).eq('id',localSub.id)
          if (eventType === 'invoice.update' && data.paid === true) await admin.from('subscriptions').update({ status:'active', grace_period_ends_at:null, last_payment_at:now.toISOString(), current_period_end:data.subscription?.next_payment_date || data.next_payment_date || null, updated_at:now.toISOString() }).eq('id',localSub.id)
          await refreshEntitlements(admin, localSub.vendor_id)
        }
      }
    }

    if (eventRowId) await admin.from('payment_events').update({ processed_at: new Date().toISOString(), processing_error: null }).eq('id', eventRowId)
    return NextResponse.json({ ok: true })
  } catch (error) {
    console.error('Paystack webhook processing failed', error)
    if (eventRowId) await admin.from('payment_events').update({ processing_error: error instanceof Error ? error.message.slice(0,500) : 'unknown_error' }).eq('id', eventRowId)
    return NextResponse.json({ ok: false }, { status: 500 })
  }
}
