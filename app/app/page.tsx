import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'

export default async function PwaEntryPage() {
  const supabase = await createClient()
  const { data } = await supabase.auth.getUser()

  if (data.user) redirect('/dashboard')
  redirect('/login?source=pwa')
}
