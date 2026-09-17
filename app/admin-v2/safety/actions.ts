'use server'

import { revalidatePath } from 'next/cache'
import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'

function text(fd: FormData, key: string, max = 2000) { return String(fd.get(key) || '').trim().slice(0,max) }
function done(type:'ok'|'error', message:string): never { redirect(`/admin-v2/safety?${type}=${encodeURIComponent(message)}`) }

export async function updateSafetyCase(formData: FormData) {
  const complaintId = text(formData,'complaint_id',80)
  const status = text(formData,'status',30)
  const note = text(formData,'internal_note',3000)
  const resolution = text(formData,'resolution',120)
  if (!complaintId || !['open','reviewing','resolved','closed'].includes(status)) done('error','Choose a valid case status.')

  const supabase = await createClient()
  const { error } = await supabase.rpc('admin_update_safety_case', {
    target_complaint: complaintId,
    next_status: status,
    internal_note: note || null,
    resolution: resolution || null,
  })
  if (error) done('error', error.message || 'Could not update the safety case.')
  revalidatePath('/admin-v2/safety')
  revalidatePath('/admin-v2/reports')
  done('ok', `Safety case moved to ${status}.`)
}

export async function setVendorSafetyStatus(formData: FormData) {
  const vendorId = text(formData,'vendor_id',80)
  const status = text(formData,'status',30)
  const note = text(formData,'note',1000)
  const daysRaw = Number(formData.get('suspension_days') || 0)
  const days = status === 'suspended' && Number.isInteger(daysRaw) && daysRaw > 0 ? daysRaw : null
  if (!vendorId || !['active','under_review','suspended'].includes(status)) done('error','Choose a valid Vendor marketplace status.')
  if (status === 'suspended' && (!days || days < 1 || days > 365)) done('error','Suspensions must be between 1 and 365 days.')

  const supabase = await createClient()
  const { error } = await supabase.rpc('admin_set_vendor_marketplace_status', {
    target_vendor: vendorId,
    next_status: status,
    suspension_days: days,
    note: note || null,
  })
  if (error) done('error', error.message || 'Could not update Vendor safety status.')
  revalidatePath('/admin-v2/safety')
  revalidatePath('/admin-v2/vendors')
  revalidatePath('/student')
  revalidatePath('/student/discover')
  done('ok', `Vendor marketplace status changed to ${status.replaceAll('_',' ')}.`)
}

export async function moderateVendorResponse(formData: FormData) {
  const reviewId = text(formData,'review_id',80)
  const status = text(formData,'status',20)
  if (!reviewId || !['published','hidden'].includes(status)) done('error','Choose a valid response state.')
  const supabase = await createClient()
  const { error } = await supabase.rpc('admin_moderate_vendor_response', { target_review: reviewId, next_status: status })
  if (error) done('error', error.message || 'Could not moderate this Vendor response.')
  revalidatePath('/admin-v2/safety')
  revalidatePath('/admin-v2/reviews')
  done('ok', `Vendor response marked ${status}.`)
}
