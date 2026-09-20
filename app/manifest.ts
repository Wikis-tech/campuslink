import type { MetadataRoute } from 'next'
import { getPlatformBranding } from '@/lib/platform-branding'

export const dynamic = 'force-dynamic'

export default async function manifest(): Promise<MetadataRoute.Manifest> {
  const branding = await getPlatformBranding()
  const revision = branding.revision || 1

  return {
    id: '/',
    name: 'Campus Link',
    short_name: 'CampusLink',
    description: 'Discover trusted campus vendors, products and services around your school.',
    start_url: '/app?source=pwa',
    scope: '/',
    display: 'standalone',
    orientation: 'any',
    background_color: '#f7faff',
    theme_color: '#0B3D91',
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
        purpose: 'any maskable',
      },
    ],
  }
}
