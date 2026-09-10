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
  if (!profile.onboarding_completed_at) redirect('/onboarding/student')

  return { supabase, userId, profile }
}

export async function toggleSavedVendor(formData: FormData) {
  const vendorId = String(formData.get('vendor_id') || '')
  const returnTo = String(formData.get('return_to') || '/student/discover')
  if (!vendorId) redirect(returnTo)

  const { supabase, userId } = await studentContext()
  const { data: existing } = await supabase
    .from('saved_vendors')
    .select('vendor_id')
    .eq('student_id', userId)
    .eq('vendor_id', vendorId)
    .maybeSingle()

  if (existing) {
    await supabase.from('saved_vendors').delete().eq('student_id', userId).eq('vendor_id', vendorId)
  } else {
    await supabase.from('saved_vendors').insert({ student_id: userId, vendor_id: vendorId })
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
  if (profile.student_verification_status !== 'verified') {
    redirect(`${returnTo}?error=Your%20student%20account%20must%20be%20verified%20before%20you%20can%20review%20vendors`)
  }

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

  const { supabase, userId } = await studentContext()
  const { error } = await supabase.from('complaints').insert({
    reporter_id: userId,
    vendor_id: vendorId,
    title,
    description,
  })

  if (error) redirect(`${returnTo}?error=We%20could%20not%20submit%20your%20report`)
  redirect(`${returnTo}?reported=1`)
}
