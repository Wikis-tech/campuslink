'use server'

import { revalidatePath } from 'next/cache'
import { createClient } from '@/lib/supabase/server'

function refreshNotificationSurfaces() {
  revalidatePath('/student')
  revalidatePath('/vendor-v2')
  revalidatePath('/control-center')
}

export async function markNotificationRead(formData: FormData) {
  const id = String(formData.get('notification_id') || '')
  if (!id) return

  const supabase = await createClient()
  const { data: userData } = await supabase.auth.getUser()
  if (!userData.user) return

  await supabase.from('notifications')
    .update({ read_at: new Date().toISOString() })
    .eq('id', id)
    .eq('user_id', userData.user.id)
    .is('dismissed_at', null)

  refreshNotificationSurfaces()
}

export async function dismissNotification(formData: FormData) {
  const id = String(formData.get('notification_id') || '')
  if (!id) return

  const supabase = await createClient()
  const { data: userData } = await supabase.auth.getUser()
  if (!userData.user) return

  const now = new Date().toISOString()
  await supabase.from('notifications')
    .update({ read_at: now, dismissed_at: now })
    .eq('id', id)
    .eq('user_id', userData.user.id)

  refreshNotificationSurfaces()
}

export async function markAllNotificationsRead() {
  const supabase = await createClient()
  const { data: userData } = await supabase.auth.getUser()
  if (!userData.user) return

  await supabase.from('notifications')
    .update({ read_at: new Date().toISOString() })
    .eq('user_id', userData.user.id)
    .is('read_at', null)
    .is('dismissed_at', null)

  refreshNotificationSurfaces()
}

export async function dismissAllReadNotifications() {
  const supabase = await createClient()
  const { data: userData } = await supabase.auth.getUser()
  if (!userData.user) return

  await supabase.from('notifications')
    .update({ dismissed_at: new Date().toISOString() })
    .eq('user_id', userData.user.id)
    .not('read_at', 'is', null)
    .is('dismissed_at', null)

  refreshNotificationSurfaces()
}
