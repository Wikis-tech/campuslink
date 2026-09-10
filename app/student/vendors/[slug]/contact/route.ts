import { NextResponse } from 'next/server'
import { createClient } from '@/lib/supabase/server'

export async function GET(request: Request, context: { params: Promise<{ slug: string }> }) {
  const { slug } = await context.params
  const url = new URL(request.url)
  const channel = url.searchParams.get('channel') === 'phone' ? 'phone' : 'whatsapp'
  const item = String(url.searchParams.get('item') || '').trim().slice(0,160)
  const productId = String(url.searchParams.get('product') || '').trim() || null
  const serviceId = String(url.searchParams.get('service') || '').trim() || null
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) return NextResponse.redirect(new URL('/login', request.url))
  const {data:profile}=await supabase.from('profiles').select('account_type,institution_id,onboarding_completed_at').eq('id',userId).single()
  if(!profile||profile.account_type!=='student'||!profile.institution_id||!profile.onboarding_completed_at) return NextResponse.redirect(new URL('/dashboard',request.url))
  const {data:vendor}=await supabase.from('vendor_profiles').select('id,business_name,slug,whatsapp_number,verification_status,marketplace_status,suspended_until').eq('slug',slug).eq('verification_status','approved').maybeSingle()
  if(!vendor) return NextResponse.redirect(new URL('/student/discover',request.url))
  const safetyAllowed=vendor.marketplace_status==='active'||(vendor.marketplace_status==='suspended'&&vendor.suspended_until&&new Date(vendor.suspended_until)<=new Date())
  if(!safetyAllowed) return NextResponse.redirect(new URL('/student/discover?error=Vendor%20temporarily%20unavailable',request.url))
  const {data:approval}=await supabase.from('vendor_institutions').select('vendor_id').eq('vendor_id',vendor.id).eq('institution_id',profile.institution_id).eq('status','approved').maybeSingle()
  if(!approval) return NextResponse.redirect(new URL('/student/discover',request.url))
  const raw=vendor.whatsapp_number||''; if(!raw) return NextResponse.redirect(new URL(`/student/vendors/${slug}?error=This%20vendor%20has%20not%20added%20a%20contact%20number`,request.url))

  await supabase.from('contact_events').insert({student_id:userId,vendor_id:vendor.id,channel})
  await supabase.rpc('record_vendor_analytics_event', {
    target_vendor: vendor.id,
    event_name: channel === 'phone' ? 'phone_click' : 'whatsapp_click',
    target_product: productId,
    target_service: serviceId,
  }).catch(() => null)

  const phone=raw.replace(/\D/g,'').replace(/^0/,'234')
  if(channel==='phone') return NextResponse.redirect(`tel:+${phone}`)
  const contextText=item?` about “${item}”`:' about your products or services'
  const message=encodeURIComponent(`Hi ${vendor.business_name}, I found you on Campus Link and would like to ask${contextText}.`)
  return NextResponse.redirect(`https://wa.me/${phone}?text=${message}`)
}
