import 'server-only'

import { createHash, createHmac, timingSafeEqual } from 'node:crypto'

const PAYSTACK_BASE_URL = 'https://api.paystack.co'

export type PaystackResponse<T> = {
  status: boolean
  message: string
  data: T
}

function secretKey() {
  const key = process.env.PAYSTACK_SECRET_KEY
  if (!key) throw new Error('PAYSTACK_SECRET_KEY is not configured')
  return key
}

export function assertPaystackTestMode() {
  const key = secretKey()
  if (!key.startsWith('sk_test_') && process.env.PAYSTACK_ALLOW_LIVE !== 'true') {
    throw new Error('Live Paystack keys are blocked until Phase 5C test validation is complete')
  }
}

async function paystackRequest<T>(path: string, init?: RequestInit) {
  const response = await fetch(`${PAYSTACK_BASE_URL}${path}`, {
    ...init,
    headers: {
      Authorization: `Bearer ${secretKey()}`,
      'Content-Type': 'application/json',
      ...(init?.headers || {}),
    },
    cache: 'no-store',
  })

  const body = (await response.json().catch(() => null)) as PaystackResponse<T> | null
  if (!response.ok || !body?.status) {
    throw new Error(body?.message || `Paystack request failed with status ${response.status}`)
  }
  return body
}

export async function createPaystackCustomer(input: {
  email: string
  firstName?: string | null
  lastName?: string | null
  phone?: string | null
  vendorId: string
}) {
  return paystackRequest<{ customer_code: string; email: string; id: number }>('/customer', {
    method: 'POST',
    body: JSON.stringify({
      email: input.email,
      first_name: input.firstName || undefined,
      last_name: input.lastName || undefined,
      phone: input.phone || undefined,
      metadata: { campuslink_vendor_id: input.vendorId },
    }),
  })
}

export async function initializeSubscriptionTransaction(input: {
  email: string
  amountKobo: number
  planCode: string
  reference: string
  callbackUrl: string
  metadata: Record<string, string>
}) {
  assertPaystackTestMode()
  return paystackRequest<{ authorization_url: string; access_code: string; reference: string }>('/transaction/initialize', {
    method: 'POST',
    body: JSON.stringify({
      email: input.email,
      amount: String(input.amountKobo),
      currency: 'NGN',
      reference: input.reference,
      plan: input.planCode,
      channels: ['card'],
      callback_url: input.callbackUrl,
      metadata: JSON.stringify(input.metadata),
    }),
  })
}

export async function verifyPaystackTransaction(reference: string) {
  return paystackRequest<any>(`/transaction/verify/${encodeURIComponent(reference)}`)
}

export async function fetchPaystackSubscription(subscriptionCode: string) {
  assertPaystackTestMode()
  return paystackRequest<any>(`/subscription/${encodeURIComponent(subscriptionCode)}`)
}

export async function generatePaystackSubscriptionManageLink(subscriptionCode: string) {
  assertPaystackTestMode()
  return paystackRequest<{ link: string }>(`/subscription/${encodeURIComponent(subscriptionCode)}/manage/link`, {
    method: 'GET',
  })
}

export async function sendPaystackSubscriptionManageEmail(subscriptionCode: string) {
  assertPaystackTestMode()
  return paystackRequest<Record<string, never>>(`/subscription/${encodeURIComponent(subscriptionCode)}/manage/email`, {
    method: 'POST',
  })
}

export function isTrustedPaystackManageUrl(value: string) {
  try {
    const url = new URL(value)
    return url.protocol === 'https:' && (url.hostname === 'paystack.com' || url.hostname.endsWith('.paystack.com'))
  } catch {
    return false
  }
}

export function verifyPaystackWebhookSignature(rawBody: string, signature: string | null) {
  if (!signature || !/^[a-f0-9]{128}$/i.test(signature)) return false
  const expected = createHmac('sha512', secretKey()).update(rawBody, 'utf8').digest('hex')
  const expectedBuffer = Buffer.from(expected, 'utf8')
  const suppliedBuffer = Buffer.from(signature, 'utf8')
  return expectedBuffer.length === suppliedBuffer.length && timingSafeEqual(expectedBuffer, suppliedBuffer)
}

export function paystackEventFingerprint(rawBody: string) {
  return `ps_evt_${createHash('sha256').update(rawBody, 'utf8').digest('hex')}`
}
