import type { MetadataRoute } from 'next'
import { createAdminClient } from '@/lib/supabase/admin'
import { getCanonicalOrigin } from '@/lib/seo-privacy'

const BASE_URL = getCanonicalOrigin()

function vendorIsPublic(vendor: {
  marketplace_status: string
  suspended_until: string | null
}) {
  if (vendor.marketplace_status === 'active') return true
  return (
    vendor.marketplace_status === 'suspended' &&
    Boolean(vendor.suspended_until) &&
    new Date(vendor.suspended_until as string) <= new Date()
  )
}

export const dynamic = 'force-dynamic'

export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const baseEntries: MetadataRoute.Sitemap = [
    {
      url: BASE_URL,
      changeFrequency: 'daily',
      priority: 1,
    },
  ]

  try {
    const admin = createAdminClient()
    const [{ data: vendors }, { data: campusLinks }] = await Promise.all([
      admin
        .from('vendor_profiles')
        .select('id,slug,updated_at,marketplace_status,suspended_until')
        .eq('verification_status', 'approved')
        .not('slug', 'is', null),
      admin
        .from('vendor_institutions')
        .select('vendor_id')
        .eq('status', 'approved'),
    ])

    const campusApproved = new Set((campusLinks || []).map((row) => row.vendor_id))

    const vendorEntries: MetadataRoute.Sitemap = (vendors || [])
      .filter((vendor) => campusApproved.has(vendor.id) && vendorIsPublic(vendor))
      .map((vendor) => ({
        url: `${BASE_URL}/share/vendor/${encodeURIComponent(vendor.slug)}`,
        lastModified: vendor.updated_at ? new Date(vendor.updated_at) : undefined,
        changeFrequency: 'weekly' as const,
        priority: 0.7,
      }))

    return [...baseEntries, ...vendorEntries]
  } catch {
    // SEO infrastructure must never take the public site down if the database
    // is temporarily unavailable. Google can still crawl the canonical homepage.
    return baseEntries
  }
}
