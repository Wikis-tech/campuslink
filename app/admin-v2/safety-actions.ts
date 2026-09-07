'use server'

import { revalidatePath } from 'next/cache'
import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'

function text(formData: FormData, key: string, max = 800) {
  return String(formData.get(key) || '').trim().slice(0, max)
}

export async function setVendorMarketplaceStatus(formData: FormData) {
  const supabase = await createClient()
  const vendorId = text(formData, 'vendor_id', 80)
  const nextStatus = text(formData, 'marketplace_status', 30)
  const note = text(formData, 'note', 800)
  const rawDays = text(formData, 'suspension_days', 10)
  const suspensionDays = rawDays ? Number(rawDays) : null

  if (!['active','under_review','suspended'].includes(nextStatus)) {
    redirect('/admin-v2/vendors?error=Invalid%20marketplace%20status')
  }
  if (suspensionDays !== null && (!Number.isInteger(suspensionDays) || suspensionDays < 1 || suspensionDays > 365)) {
    redirect('/admin-v2/vendors?error=Suspension%20days%20must%20be%20between%201%20and%20365')
  }

  const { error } = await supabase.rpc('admin_set_vendor_marketplace_status', {
    target_vendor: vendorId,
    next_status: nextStatus,
    suspension_days: suspensionDays,
    note: note || null,
  })

  if (error) redirect(`/admin-v2/vendors?error=${encodeURIComponent(error.message)}`)
  revalidatePath('/admin-v2/vendors')
  revalidatePath('/admin-v2/reports')
  revalidatePath('/student')
  revalidatePath('/student/discover')
  revalidatePath('/vendor-v2')
  redirect(`/admin-v2/vendors?success=${encodeURIComponent(`Vendor marketplace status set to ${nextStatus.replace('_',' ')}.`)}`)
}
