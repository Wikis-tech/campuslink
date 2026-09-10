import Link from 'next/link'

type BrandProps = {
  href?: string
  light?: boolean
  compact?: boolean
}

export function Brand({ href = '/', light = false, compact = false }: BrandProps) {
  return (
    <Link href={href} className={`cl-brand${light ? ' cl-brand-light' : ''}${compact ? ' cl-brand-compact' : ''}`} aria-label="Campus Link home">
      <span className="cl-brand-mark" aria-hidden="true">
        <img
          src="https://raw.githubusercontent.com/Wikis-tech/campuslink/master/assets/images/campuslink-logo-white.png"
          alt=""
          width={36}
          height={36}
        />
      </span>
      <span className="cl-brand-word">Campus<strong>Link</strong></span>
    </Link>
  )
}
