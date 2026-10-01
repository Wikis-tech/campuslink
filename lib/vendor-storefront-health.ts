export type VendorStorefrontHealthInput = {
  logoUrl?: string | null
  coverUrl?: string | null
  description?: string | null
  whatsappNumber?: string | null
  businessEmail?: string | null
  activeProductCount: number
  activeServiceCount: number
  productsWithoutPrice: number
  activePortfolioCount: number
}

export type VendorStorefrontHealth = {
  score: number
  improvements: string[]
  label: 'Strong setup' | 'Good foundation' | 'Needs attention'
}

export function calculateVendorStorefrontHealth(input: VendorStorefrontHealthInput): VendorStorefrontHealth {
  const activeListingCount = input.activeProductCount + input.activeServiceCount
  const factors = [
    { ready: Boolean(input.logoUrl), weight: 15, improvement: 'Add a business logo.' },
    { ready: Boolean(input.coverUrl), weight: 10, improvement: 'Add a cover image.' },
    {
      ready: Boolean(input.description && input.description.trim().length >= 40),
      weight: 15,
      improvement: 'Strengthen your business description so students quickly understand what you offer.',
    },
    {
      ready: Boolean(input.whatsappNumber || input.businessEmail),
      weight: 10,
      improvement: 'Add a reliable contact method.',
    },
    {
      ready: activeListingCount > 0,
      weight: 15,
      improvement: 'Add at least one active product or service.',
    },
    {
      ready: activeListingCount >= 2,
      weight: 10,
      improvement: 'Add another active listing so students have more to compare.',
    },
    {
      ready: input.productsWithoutPrice === 0,
      weight: 10,
      improvement: 'Add clear pricing or choose contact-for-price on every active product.',
    },
    {
      ready: input.activePortfolioCount > 0,
      weight: 15,
      improvement: 'Add at least one proof-of-work portfolio item.',
    },
  ]

  const score = factors.reduce((total, factor) => total + (factor.ready ? factor.weight : 0), 0)
  const improvements = factors.filter((factor) => !factor.ready).map((factor) => factor.improvement)
  const label = score >= 85 ? 'Strong setup' : score >= 60 ? 'Good foundation' : 'Needs attention'

  return { score, improvements, label }
}
