'use server'

import { revalidatePath } from 'next/cache'
import { createClient } from '@/lib/supabase/server'

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

  revalidatePath('/student')
  revalidatePath('/vendor-v2')
}

export async function markAllNotificationsRead() {
  const supabase = await createClient()
  const { data: userData } = await supabase.auth.getUser()
  if (!userData.user) return

  await supabase.from('notifications')
    .update({ read_at: new Date().toISOString() })
    .eq('user_id', userData.user.id)
    .is('read_at', null)

  revalidatePath('/student')
  revalidatePath('/vendor-v2')
}
