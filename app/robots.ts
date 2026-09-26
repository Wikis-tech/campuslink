import type { MetadataRoute } from 'next'
import { getCanonicalOrigin } from '@/lib/seo-privacy'

const BASE_URL = getCanonicalOrigin()

export default function robots(): MetadataRoute.Robots {
  return {
    rules: [
      {
        userAgent: '*',
        allow: '/',
        disallow: [
          '/student/',
          '/vendor-v2/',
          '/dashboard/',
          '/auth/',
          '/onboarding/',
          '/verify/',
          '/api/',
          '/app',
          '/offline',
          '/pwa/',
        ],
      },
    ],
    sitemap: `${BASE_URL}/sitemap.xml`,
    host: BASE_URL,
  }
}
