import { NextResponse } from 'next/server'
import { createClient } from '@/lib/supabase/server'

export async function GET(_request: Request, context: { params: Promise<{ slug: string }> }) {
  const { slug } = await context.params
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) return NextResponse.redirect(new URL('/login', _request.url))

  const { data: profile } = await supabase
    .from('profiles')
    .select('account_type,institution_id,onboarding_completed_at')
    .eq('id', userId)
    .single()

  if (!profile || profile.account_type !== 'student' || !profile.institution_id || !profile.onboarding_completed_at) {
    return NextResponse.redirect(new URL('/dashboard', _request.url))
  }

  const { data: vendor } = await supabase
    .from('vendor_profiles')
    .select('id,business_name,slug,whatsapp_number,verification_status')
    .eq('slug', slug)
    .eq('verification_status', 'approved')
    .maybeSingle()

  if (!vendor?.whatsapp_number) return NextResponse.redirect(new URL(`/student/vendors/${slug}?error=This%20vendor%20has%20not%20added%20a%20WhatsApp%20number`, _request.url))

  const { data: approval } = await supabase
    .from('vendor_institutions')
    .select('vendor_id')
    .eq('vendor_id', vendor.id)
    .eq('institution_id', profile.institution_id)
    .eq('status', 'approved')
    .maybeSingle()

  if (!approval) return NextResponse.redirect(new URL('/student/discover', _request.url))

  await supabase.from('contact_events').insert({ student_id: userId, vendor_id: vendor.id, channel: 'whatsapp' })

  const phone = vendor.whatsapp_number.replace(/\D/g, '').replace(/^0/, '234')
  const message = encodeURIComponent(`Hi ${vendor.business_name}, I found your profile on Campus Link and would like to ask about your service.`)
  return NextResponse.redirect(`https://wa.me/${phone}?text=${message}`)
}
