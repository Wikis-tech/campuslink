import type { MetadataRoute } from 'next'
import { getPlatformBranding } from '@/lib/platform-branding'

export const dynamic = 'force-dynamic'

export default async function manifest(): Promise<MetadataRoute.Manifest> {
  const branding = await getPlatformBranding()
  const revision = branding.revision || 1

  return {
    id: '/',
    name: 'CampusMode',
    short_name: 'CampusMode',
    description: 'Campus life, made easy. Find trusted products, services and businesses around your campus.',
    start_url: '/app?source=pwa',
    scope: '/',
    display: 'standalone',
    orientation: 'any',
    background_color: '#F1F3F6',
    theme_color: '#154597',
    lang: 'en',
    dir: 'ltr',
    categories: ['education', 'business', 'shopping', 'lifestyle'],
    icons: [
      {
        src: `/brand/app-icon/192?v=${revision}`,
        sizes: '192x192',
        type: 'image/png',
        purpose: 'any',
      },
      {
        src: `/brand/app-icon/512?v=${revision}`,
        sizes: '512x512',
        type: 'image/png',
        purpose: 'maskable',
      },
    ],
  }
}
