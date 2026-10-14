import Link from 'next/link'

type BrandProps = {
  href?: string
  light?: boolean
  compact?: boolean
}

export function Brand({ href = '/', light = false, compact = false }: BrandProps) {
  return (
    <Link href={href} className={`cl-brand${light ? ' cl-brand-light' : ''}${compact ? ' cl-brand-compact' : ''}`} aria-label="Kampivo home">
      <img
        src="/brand/logo"
        alt="Kampivo"
        className="cl-brand-dynamic-logo"
        style={{ display: 'block', width: compact ? 112 : 138, height: compact ? 34 : 42, objectFit: 'contain', objectPosition: 'left center' }}
      />
    </Link>
  )
}
