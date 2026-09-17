'use server'

import { revalidatePath } from 'next/cache'
import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'

function fail(message: string): never {
  redirect(`/vendor-v2/reviews?error=${encodeURIComponent(message)}`)
}

export async function respondToReview(formData: FormData) {
  const reviewId = String(formData.get('review_id') || '')
  const response = String(formData.get('response') || '').trim().slice(0, 1000)
  if (!reviewId || response.length < 2) fail('Write a response before saving.')

  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const { data: profile } = await supabase.from('profiles').select('account_type').eq('id', userId).maybeSingle()
  if (profile?.account_type !== 'vendor') redirect('/dashboard')

  const { error } = await supabase.rpc('vendor_respond_to_review', {
    target_review: reviewId,
    response_text: response,
  })
  if (error) fail(error.message || 'We could not save your response.')

  revalidatePath('/vendor-v2/reviews')
  revalidatePath('/student')
  redirect('/vendor-v2/reviews?ok=Response%20published')
}
