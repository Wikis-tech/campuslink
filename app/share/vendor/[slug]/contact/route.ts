import { NextResponse } from 'next/server'
import { createClient } from '@/lib/supabase/server'
import { createAdminClient } from '@/lib/supabase/admin'

function go(request: Request, path: string) {
  const response = NextResponse.redirect(new URL(path, request.url))
  response.headers.set('Cache-Control', 'private, no-store')
  return response
}

// WhatsApp contact is allowed for verified, onboarded Students across campuses.
// The number never appears in a guest-visible page or client bundle; campus
// approval and marketplace safety are checked on every request.
export async function GET(request: Request, context: { params: Promise<{ slug: string }> }) {
  const { slug } = await context.params
  const safeSlug = encodeURIComponent(slug)
  const back = `/share/vendor/${safeSlug}`
  const supabase = await createClient()
  const { data: { user }, error: authError } = await supabase.auth.getUser()
  if (authError || !user) {
    return go(request, `/login?next=${encodeURIComponent(back)}`)
  }

  const admin = createAdminClient()
  const [{ data: profile }, { data: globalAdmin }, { data: schoolAdmins }] = await Promise.all([
    supabase.from('profiles')
      .select('account_type,institution_id,onboarding_completed_at,student_verification_status')
      .eq('id', user.id).maybeSingle(),
    admin.from('admin_memberships').select('user_id')
      .eq('user_id', user.id).eq('is_active', true).maybeSingle(),
    admin.from('institution_admin_assignments').select('user_id')
      .eq('user_id', user.id).eq('is_active', true).limit(1),
  ])

  if (globalAdmin || (schoolAdmins || []).length || profile?.account_type !== 'student') {
    return go(request, '/dashboard')
  }
  if (!profile.institution_id || !profile.onboarding_completed_at) {
    return go(request, '/onboarding/student')
  }
  if (profile.student_verification_status !== 'verified') {
    return go(request, `${back}?contact=verification_required`)
  }

  const { data: vendor } = await admin.from('vendor_profiles')
    .select('id,business_name,whatsapp_number,verification_status,marketplace_status,suspended_until')
    .eq('slug', slug).eq('verification_status', 'approved').maybeSingle()

  const safe = vendor && (
    vendor.marketplace_status === 'active' ||
    (vendor.marketplace_status === 'suspended' &&
      vendor.suspended_until && new Date(vendor.suspended_until) <= new Date())
  )
  if (!safe || !vendor) return go(request, `${back}?contact=unavailable`)

  const { data: approvals } = await admin.from('vendor_institutions')
    .select('institution_id,institutions!inner(is_active)')
    .eq('vendor_id', vendor.id).eq('status', 'approved')
    .eq('institutions.is_active', true).limit(1)
  if (!approvals?.length) return go(request, `${back}?contact=unavailable`)

  const number = (vendor.whatsapp_number || '').replace(/\D/g, '').replace(/^0/, '234')
  if (!/^\d{10,15}$/.test(number)) return go(request, `${back}?contact=missing`)

  const url = new URL(request.url)
  const productId = url.searchParams.get('product')
  const serviceId = url.searchParams.get('service')
  const validId = (id: string) => /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(id)
  if ((productId && !validId(productId)) || (serviceId && !validId(serviceId)) || (productId && serviceId)) {
    return go(request, `${back}?contact=unavailable`)
  }

  let itemName = ''
  if (productId) {
    const { data } = await admin.from('vendor_products').select('name')
      .eq('id', productId).eq('vendor_id', vendor.id).eq('is_active', true).maybeSingle()
    if (!data) return go(request, `${back}?contact=unavailable`)
    itemName = data.name
  }
  if (serviceId) {
    const { data } = await admin.from('vendor_services').select('name')
      .eq('id', serviceId).eq('vendor_id', vendor.id).eq('is_active', true).maybeSingle()
    if (!data) return go(request, `${back}?contact=unavailable`)
    itemName = data.name
  }

  // Respect existing short-window deduplication without altering the
  // school-scoped RPCs used by the Student's own-campus marketplace.
  const recentTime = new Date(Date.now() - 2 * 60 * 1000).toISOString()
  const { data: recent } = await admin.from('contact_events').select('id')
    .eq('student_id', user.id).eq('vendor_id', vendor.id)
    .eq('channel', 'whatsapp').gte('created_at', recentTime).limit(1)
  if (!recent?.length) {
    const { error } = await admin.from('contact_events').insert({
      student_id: user.id, vendor_id: vendor.id,
      institution_id: profile.institution_id, channel: 'whatsapp',
    })
    if (error) console.error('CampusMode cross-campus contact evidence unavailable', { code: error.code })
  }

  let recentAnalyticsQuery = admin.from('vendor_analytics_events')
    .select('id').eq('actor_id', user.id).eq('vendor_id', vendor.id)
    .eq('event_type', 'whatsapp_click').gte('occurred_at', recentTime)
  recentAnalyticsQuery = productId
    ? recentAnalyticsQuery.eq('product_id', productId)
    : recentAnalyticsQuery.is('product_id', null)
  recentAnalyticsQuery = serviceId
    ? recentAnalyticsQuery.eq('service_id', serviceId)
    : recentAnalyticsQuery.is('service_id', null)
  const { data: recentAnalytics } = await recentAnalyticsQuery.limit(1)
  if (!recentAnalytics?.length) {
    const { error } = await admin.from('vendor_analytics_events').insert({
      vendor_id: vendor.id, actor_id: user.id,
      institution_id: profile.institution_id, event_type: 'whatsapp_click',
      product_id: productId || null, service_id: serviceId || null,
      metadata: { cross_campus: true },
    })
    if (error) console.error('CampusMode cross-campus vendor analytics unavailable', { code: error.code })
  }

  const contextText = itemName
    ? ` about ${itemName.replace(/[\r\n]/g, ' ').slice(0, 120)}`
    : ' about your products or services'
  const message = encodeURIComponent(
    `Hi ${vendor.business_name}, I found your business on CampusMode and would like to ask${contextText}.`
  )
  const response = NextResponse.redirect(`https://wa.me/${number}?text=${message}`)
  response.headers.set('Cache-Control', 'private, no-store')
  response.headers.set('Referrer-Policy', 'no-referrer')
  return response
}
