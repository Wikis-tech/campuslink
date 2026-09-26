import { createClient } from '@/lib/supabase/server'
import { PwaLaunchSplash } from '@/components/pwa-launch-splash'

export default async function PwaEntryPage() {
  const supabase = await createClient()
  const { data } = await supabase.auth.getUser()
  const destination = data.user ? '/dashboard' : '/login?source=pwa'

  return <PwaLaunchSplash destination={destination} />
}
