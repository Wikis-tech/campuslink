'use server'

import { revalidatePath } from 'next/cache'
import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'

async function requireVendor() {
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')
  const { data: profile } = await supabase.from('profiles').select('account_type').eq('id', userId).maybeSingle()
  if (profile?.account_type !== 'vendor') redirect('/dashboard')
  return { supabase, userId }
}

function safeReturn(message: string, type: 'ok' | 'error' = 'ok'): never {
  redirect(`/vendor-v2/availability?${type}=${encodeURIComponent(message)}`)
}

export async function saveAvailability(formData: FormData) {
  const { supabase, userId } = await requireVendor()
  const manualStatus = String(formData.get('manual_status') || 'schedule')
  const allowed = new Set(['schedule','open','closed','busy','back_later','exam_mode'])
  if (!allowed.has(manualStatus)) safeReturn('Invalid availability status.', 'error')

  const statusMessage = String(formData.get('status_message') || '').trim().slice(0, 160) || null
  const backAtRaw = String(formData.get('back_at') || '').trim()
  const examUntilRaw = String(formData.get('exam_mode_until') || '').trim()
  const backAt = manualStatus === 'back_later' && backAtRaw ? new Date(backAtRaw) : null
  if (backAt && Number.isNaN(backAt.getTime())) safeReturn('Choose a valid back-at time.', 'error')

  const { error } = await supabase.from('vendor_availability').upsert({
    vendor_id: userId,
    manual_status: manualStatus,
    status_message: statusMessage,
    back_at: backAt ? backAt.toISOString() : null,
    exam_mode_until: manualStatus === 'exam_mode' && examUntilRaw ? examUntilRaw : null,
    updated_at: new Date().toISOString(),
  }, { onConflict: 'vendor_id' })
  if (error) safeReturn(`Could not update availability: ${error.message}`, 'error')
  revalidatePath('/vendor-v2')
  revalidatePath('/vendor-v2/availability')
  revalidatePath('/student')
  revalidatePath('/student/discover')
  safeReturn('Availability updated.')
}

export async function saveBusinessHours(formData: FormData) {
  const { supabase, userId } = await requireVendor()
  const rows = []
  for (let day = 0; day < 7; day++) {
    const closed = formData.get(`closed_${day}`) === 'on'
    const opens = String(formData.get(`opens_${day}`) || '').trim()
    const closes = String(formData.get(`closes_${day}`) || '').trim()
    if (!closed && (!opens || !closes)) safeReturn('Every open day needs both an opening and closing time.', 'error')
    rows.push({ vendor_id: userId, day_of_week: day, is_closed: closed, opens_at: closed ? null : opens, closes_at: closed ? null : closes, updated_at: new Date().toISOString() })
  }
  const { error } = await supabase.from('vendor_business_hours').upsert(rows, { onConflict: 'vendor_id,day_of_week' })
  if (error) safeReturn(`Could not save business hours: ${error.message}`, 'error')
  revalidatePath('/vendor-v2/availability')
  revalidatePath('/student/discover')
  safeReturn('Business hours saved.')
}

export async function saveServiceAreas(formData: FormData) {
  const { supabase, userId } = await requireVendor()
  const locationIds = formData.getAll('location_id').map(String).filter(Boolean)
  const { data: primaryCampus } = await supabase.from('vendor_institutions').select('institution_id').eq('vendor_id', userId).eq('is_primary', true).eq('status', 'approved').maybeSingle()
  if (!primaryCampus?.institution_id) safeReturn('Your primary campus must be approved before setting service areas.', 'error')

  if (locationIds.length) {
    const { data: validLocations, error: locationError } = await supabase.from('campus_locations').select('id').eq('institution_id', primaryCampus.institution_id).eq('is_active', true).in('id', locationIds)
    if (locationError) safeReturn(`Could not validate service areas: ${locationError.message}`, 'error')
    if ((validLocations || []).length !== new Set(locationIds).size) safeReturn('One or more selected locations are not valid for your campus.', 'error')
  }

  const { error: deleteError } = await supabase.from('vendor_service_areas').delete().eq('vendor_id', userId)
  if (deleteError) safeReturn(`Could not reset service areas: ${deleteError.message}`, 'error')
  if (locationIds.length) {
    const { error: insertError } = await supabase.from('vendor_service_areas').insert([...new Set(locationIds)].map((campus_location_id) => ({ vendor_id: userId, campus_location_id })))
    if (insertError) safeReturn(`Could not save service areas: ${insertError.message}`, 'error')
  }
  revalidatePath('/vendor-v2/availability')
  revalidatePath('/student/discover')
  safeReturn('Service areas updated.')
}
