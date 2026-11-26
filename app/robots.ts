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
          '/control-center/',
          '/admin-v2/',
          '/admin-invite/',
          '/admin-login-campus/mfa/',
          '/student/'
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
