import { NextResponse } from 'next/server'
import { createClient } from '@/lib/supabase/server'
import { createAdminClient } from '@/lib/supabase/admin'

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

  if (!error) return NextResponse.json({ ok: true, recorded: true })

  // Server-side fallback keeps analytics resilient if an RPC permission/cache issue occurs.
  // We re-check Student, campus, safety and listing ownership before using service-role write access.
  try {
    const { data: profile } = await supabase.from('profiles')
      .select('account_type,institution_id,onboarding_completed_at')
      .eq('id', userId).maybeSingle()
    if (!profile || profile.account_type !== 'student' || !profile.institution_id || !profile.onboarding_completed_at) {
      return NextResponse.json({ ok: true, recorded: false }, { status: 202 })
    }

    const admin = createAdminClient()
    const [{ data: vendor }, { data: approval }] = await Promise.all([
      admin.from('vendor_profiles').select('id,verification_status,marketplace_status,suspended_until').eq('id', vendorId).maybeSingle(),
      admin.from('vendor_institutions').select('vendor_id').eq('vendor_id', vendorId).eq('institution_id', profile.institution_id).eq('status', 'approved').maybeSingle(),
    ])
    if (!vendor || vendor.verification_status !== 'approved' || !approval) return NextResponse.json({ ok: true, recorded: false }, { status: 202 })
    const safetyAllowed = vendor.marketplace_status === 'active' || (vendor.marketplace_status === 'suspended' && vendor.suspended_until && new Date(vendor.suspended_until) <= new Date())
    if (!safetyAllowed) return NextResponse.json({ ok: true, recorded: false }, { status: 202 })

    if (productId) {
      const { data: product } = await admin.from('vendor_products').select('id').eq('id', productId).eq('vendor_id', vendorId).eq('is_active', true).maybeSingle()
      if (!product) return NextResponse.json({ ok: true, recorded: false }, { status: 202 })
    }
    if (serviceId) {
      const { data: service } = await admin.from('vendor_services').select('id').eq('id', serviceId).eq('vendor_id', vendorId).eq('is_active', true).maybeSingle()
      if (!service) return NextResponse.json({ ok: true, recorded: false }, { status: 202 })
    }

    const twoMinutesAgo = new Date(Date.now() - 2 * 60 * 1000).toISOString()
    let recentQuery = admin.from('vendor_analytics_events').select('id', { count: 'exact', head: true })
      .eq('actor_id', userId).eq('vendor_id', vendorId).eq('event_type', event).gte('occurred_at', twoMinutesAgo)
    if (productId) recentQuery = recentQuery.eq('product_id', productId)
    else recentQuery = recentQuery.is('product_id', null)
    if (serviceId) recentQuery = recentQuery.eq('service_id', serviceId)
    else recentQuery = recentQuery.is('service_id', null)
    const { count } = await recentQuery
    if ((count || 0) >= 3) return NextResponse.json({ ok: true, recorded: false, deduped: true })

    const { error: fallbackError } = await admin.from('vendor_analytics_events').insert({
      vendor_id: vendorId,
      actor_id: userId,
      institution_id: profile.institution_id,
      event_type: event,
      product_id: productId,
      service_id: serviceId,
      metadata: {},
    })
    if (!fallbackError) return NextResponse.json({ ok: true, recorded: true, fallback: true })
  } catch {}

  // Analytics must never break Student browsing or flood the browser console with 4xx errors.
  return NextResponse.json({ ok: true, recorded: false }, { status: 202 })
}
