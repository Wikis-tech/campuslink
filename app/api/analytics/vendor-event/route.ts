import { NextResponse } from 'next/server'
import { createClient } from '@/lib/supabase/server'

const allowedEvents = new Set([
  'profile_view','product_view','service_view','portfolio_view','search_impression','search_click',
])

export async function POST(request: Request) {
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) return NextResponse.json({ ok: false }, { status: 401 })

  const body = await request.json().catch(() => null)
  const vendorId = typeof body?.vendorId === 'string' ? body.vendorId : ''
  const event = typeof body?.event === 'string' ? body.event : ''
  const productId = typeof body?.productId === 'string' ? body.productId : null
  const serviceId = typeof body?.serviceId === 'string' ? body.serviceId : null

  if (!vendorId || !allowedEvents.has(event)) {
    return NextResponse.json({ ok: false }, { status: 400 })
  }

  const { error } = await supabase.rpc('record_vendor_analytics_event', {
    target_vendor: vendorId,
    event_name: event,
    target_product: productId,
    target_service: serviceId,
  })

  if (error) return NextResponse.json({ ok: false }, { status: 403 })
  return NextResponse.json({ ok: true })
}
