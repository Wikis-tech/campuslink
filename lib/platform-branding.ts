import 'server-only'

import { cache } from 'react'
import { createAdminClient } from '@/lib/supabase/admin'

export type PlatformBranding = {
  website_logo_url: string | null
  favicon_url: string | null
  app_icon_url: string | null
  revision: number
  updated_at: string | null
}

export const DEFAULT_WEBSITE_LOGO =
  '/kampivo-logo.svg'

export const DEFAULT_FAVICON = '/kampivo-app-icon.svg'

export const getPlatformBranding = cache(async (): Promise<PlatformBranding> => {
  try {
    const admin = createAdminClient()
    const { data } = await admin
      .from('platform_branding')
      .select('website_logo_url,favicon_url,app_icon_url,revision,updated_at')
      .eq('id', 1)
      .maybeSingle()

    const storedRevision = Number(data?.revision || 1)
    const legacyCampusLinkBranding = storedRevision <= 3

    return {
      website_logo_url: legacyCampusLinkBranding ? null : (data?.website_logo_url || null),
      favicon_url: legacyCampusLinkBranding ? null : (data?.favicon_url || null),
      app_icon_url: legacyCampusLinkBranding ? null : (data?.app_icon_url || null),
      revision: legacyCampusLinkBranding ? 4 : storedRevision,
      updated_at: data?.updated_at || null,
    }
  } catch {
    return {
      website_logo_url: null,
      favicon_url: null,
      app_icon_url: null,
      revision: 1,
      updated_at: null,
    }
  }
})
