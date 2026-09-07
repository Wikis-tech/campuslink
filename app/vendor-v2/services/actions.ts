'use server'

import { revalidatePath } from 'next/cache'
import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'

function clean(value: FormDataEntryValue | null, max = 180) {
  return String(value || '').trim().slice(0, max)
}

async function requireVendor() {
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const { data: profile } = await supabase.from('profiles').select('account_type').eq('id', userId).maybeSingle()
  if (!profile || profile.account_type !== 'vendor') redirect('/dashboard')
  return { supabase, userId }
}

async function getServiceLimit(supabase: Awaited<ReturnType<typeof createClient>>) {
  const { data } = await supabase.rpc('get_my_vendor_entitlements')
  const row = Array.isArray(data) ? data[0] : null
  const raw = row?.entitlements?.service_limit
  const parsed = Number(raw)
  return Number.isFinite(parsed) && parsed > 0 ? parsed : 5
}

export async function addVendorService(formData: FormData) {
  const { supabase, userId } = await requireVendor()
  const categoryId = clean(formData.get('category_id'), 80)
  const name = clean(formData.get('name'), 120)
  const description = clean(formData.get('description'), 500)
  const priceRaw = clean(formData.get('price_from'), 30)

  if (!categoryId || !name) {
    redirect('/vendor-v2/services?error=Choose%20a%20category%20and%20add%20a%20service%20name')
  }

  const price = priceRaw ? Number(priceRaw.replace(/,/g, '')) : null
  if (price !== null && (!Number.isFinite(price) || price < 0 || price > 100000000)) {
    redirect('/vendor-v2/services?error=Enter%20a%20valid%20starting%20price')
  }

  const [{ data: category }, activeCountResult, limit] = await Promise.all([
    supabase.from('categories').select('id').eq('id', categoryId).eq('is_active', true).maybeSingle(),
    supabase.from('vendor_services').select('id', { count: 'exact', head: true }).eq('vendor_id', userId).eq('is_active', true),
    getServiceLimit(supabase),
  ])

  if (!category) redirect('/vendor-v2/services?error=Choose%20a%20valid%20service%20category')
  if ((activeCountResult.count || 0) >= limit) {
    redirect(`/vendor-v2/services?limit=${limit}`)
  }

  const { error } = await supabase.from('vendor_services').insert({
    vendor_id: userId,
    category_id: categoryId,
    name,
    description: description || null,
    price_from: price,
    is_active: true,
  })

  if (error) {
    if (error.message.includes('PLAN_LIMIT_SERVICE')) redirect(`/vendor-v2/services?limit=${limit}`)
    redirect('/vendor-v2/services?error=We%20could%20not%20add%20that%20service')
  }

  revalidatePath('/vendor-v2/services')
  revalidatePath('/vendor-v2')
  redirect('/vendor-v2/services?added=1')
}

export async function toggleVendorService(formData: FormData) {
  const { supabase, userId } = await requireVendor()
  const serviceId = clean(formData.get('service_id'), 80)
  const nextActive = clean(formData.get('next_active'), 10) === 'true'
  if (!serviceId) redirect('/vendor-v2/services')

  if (nextActive) {
    const [{ count }, limit] = await Promise.all([
      supabase.from('vendor_services').select('id', { count: 'exact', head: true }).eq('vendor_id', userId).eq('is_active', true),
      getServiceLimit(supabase),
    ])
    if ((count || 0) >= limit) redirect(`/vendor-v2/services?limit=${limit}`)
  }

  const { error } = await supabase
    .from('vendor_services')
    .update({ is_active: nextActive })
    .eq('id', serviceId)
    .eq('vendor_id', userId)

  if (error) redirect('/vendor-v2/services?error=We%20could%20not%20update%20that%20service')

  revalidatePath('/vendor-v2/services')
  redirect('/vendor-v2/services?updated=1')
}

export async function deleteVendorService(formData: FormData) {
  const { supabase, userId } = await requireVendor()
  const serviceId = clean(formData.get('service_id'), 80)
  if (!serviceId) redirect('/vendor-v2/services')

  const { error } = await supabase.from('vendor_services').delete().eq('id', serviceId).eq('vendor_id', userId)
  if (error) redirect('/vendor-v2/services?error=We%20could%20not%20remove%20that%20service')

  revalidatePath('/vendor-v2/services')
  revalidatePath('/vendor-v2')
  redirect('/vendor-v2/services?deleted=1')
}
