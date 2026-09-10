'use server'

import { revalidatePath } from 'next/cache'
import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'

async function studentContext() {
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const { data: profile } = await supabase
    .from('profiles')
    .select('id,account_type,institution_id,student_verification_status,onboarding_completed_at')
    .eq('id', userId)
    .single()

  if (!profile || profile.account_type !== 'student') redirect('/dashboard')
  if (!profile.onboarding_completed_at || !profile.institution_id) redirect('/onboarding/student')

  return { supabase, userId, profile }
}

async function assertVendorAvailableToStudent(supabase: Awaited<ReturnType<typeof createClient>>, institutionId: string, vendorId: string, returnTo: string) {
  const [{ data: vendor }, { data: campusApproval }] = await Promise.all([
    supabase.from('vendor_profiles').select('id,verification_status,marketplace_status,suspended_until').eq('id', vendorId).maybeSingle(),
    supabase.from('vendor_institutions').select('vendor_id').eq('vendor_id', vendorId).eq('institution_id', institutionId).eq('status', 'approved').maybeSingle(),
  ])

  const safetyAllowed = vendor?.marketplace_status === 'active' || (vendor?.marketplace_status === 'suspended' && vendor?.suspended_until && new Date(vendor.suspended_until) <= new Date())
  if (!vendor || vendor.verification_status !== 'approved' || !campusApproval || !safetyAllowed) {
    redirect(`${returnTo}${returnTo.includes('?') ? '&' : '?'}error=This%20vendor%20is%20not%20currently%20available%20to%20your%20campus`)
  }
}

export async function toggleSavedVendor(formData: FormData) {
  const vendorId = String(formData.get('vendor_id') || '')
  const returnTo = String(formData.get('return_to') || '/student/discover')
  if (!vendorId) redirect(returnTo)

  const { supabase, userId, profile } = await studentContext()
  await assertVendorAvailableToStudent(supabase, profile.institution_id, vendorId, returnTo)

  const { data: existing } = await supabase
    .from('saved_vendors')
    .select('vendor_id')
    .eq('student_id', userId)
    .eq('vendor_id', vendorId)
    .maybeSingle()

  if (existing) {
    await supabase.from('saved_vendors').delete().eq('student_id', userId).eq('vendor_id', vendorId)
    await supabase.rpc('record_vendor_analytics_event', { target_vendor: vendorId, event_name: 'unsave', target_product: null, target_service: null })
  } else {
    const { error } = await supabase.from('saved_vendors').insert({ student_id: userId, vendor_id: vendorId })
    if (error) redirect(`${returnTo}${returnTo.includes('?') ? '&' : '?'}error=We%20could%20not%20save%20this%20vendor`)
    await supabase.rpc('record_vendor_analytics_event', { target_vendor: vendorId, event_name: 'save', target_product: null, target_service: null })
  }

  revalidatePath('/student')
  revalidatePath('/student/discover')
  revalidatePath('/student/saved')
  revalidatePath(returnTo)
}

export async function submitReview(formData: FormData) {
  const vendorId = String(formData.get('vendor_id') || '')
  const slug = String(formData.get('slug') || '')
  const rating = Number(formData.get('rating'))
  const comment = String(formData.get('comment') || '').trim().slice(0, 1000)
  const returnTo = slug ? `/student/vendors/${encodeURIComponent(slug)}` : '/student/discover'

  if (!vendorId || !Number.isInteger(rating) || rating < 1 || rating > 5) {
    redirect(`${returnTo}?error=Choose%20a%20rating%20from%201%20to%205`)
  }

  const { supabase, userId, profile } = await studentContext()
  await assertVendorAvailableToStudent(supabase, profile.institution_id, vendorId, returnTo)
  if (profile.student_verification_status !== 'verified') {
    redirect(`${returnTo}?error=Your%20student%20account%20must%20be%20verified%20before%20you%20can%20review%20vendors`)
  }

  const { data: existingReview } = await supabase.from('reviews').select('id').eq('student_id', userId).eq('vendor_id', vendorId).maybeSingle()
  const { error } = await supabase.from('reviews').upsert(
    {
      student_id: userId,
      vendor_id: vendorId,
      rating,
      comment: comment || null,
      status: 'published',
      updated_at: new Date().toISOString(),
    },
    { onConflict: 'student_id,vendor_id' }
  )

  if (error) redirect(`${returnTo}?error=We%20could%20not%20save%20your%20review`)
  if (!existingReview) await supabase.rpc('record_vendor_analytics_event', { target_vendor: vendorId, event_name: 'review_received', target_product: null, target_service: null })
  revalidatePath(returnTo)
  redirect(`${returnTo}?review=saved`)
}

export async function reportVendor(formData: FormData) {
  const vendorId = String(formData.get('vendor_id') || '')
  const slug = String(formData.get('slug') || '')
  const title = String(formData.get('title') || '').trim().slice(0, 120)
  const description = String(formData.get('description') || '').trim().slice(0, 1500)
  const returnTo = slug ? `/student/vendors/${encodeURIComponent(slug)}` : '/student/discover'

  if (!vendorId || title.length < 4 || description.length < 10) {
    redirect(`${returnTo}?error=Please%20give%20us%20a%20clear%20reason%20for%20the%20report`)
  }

  const { supabase, userId, profile } = await studentContext()
  await assertVendorAvailableToStudent(supabase, profile.institution_id, vendorId, returnTo)

  const { error } = await supabase.from('complaints').insert({
    reporter_id: userId,
    vendor_id: vendorId,
    title,
    description,
  })

  if (error) {
    const message = error.code === '23505'
      ? 'You%20already%20have%20an%20open%20report%20for%20this%20vendor.%20Campus%20Link%20is%20reviewing%20it.'
      : 'We%20could%20not%20submit%20your%20report'
    redirect(`${returnTo}?error=${message}`)
  }
  revalidatePath('/admin-v2/reports')
  redirect(`${returnTo}?reported=1`)
}
