const DEFAULT_CANONICAL_ORIGIN = 'https://www.campuslink.name.ng'

export function getCanonicalOrigin() {
  const configured = process.env.NEXT_PUBLIC_APP_URL?.trim() || DEFAULT_CANONICAL_ORIGIN

  try {
    const url = new URL(configured)
    url.protocol = 'https:'
    if (url.hostname.toLowerCase() === 'campuslink.name.ng') {
      url.hostname = 'www.campuslink.name.ng'
    }
    url.pathname = ''
    url.search = ''
    url.hash = ''
    return url.origin
  } catch {
    return DEFAULT_CANONICAL_ORIGIN
  }
}

const EMAIL_PATTERN = /\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/gi
const URL_PATTERN = /\b(?:https?:\/\/|www\.)[^\s]+/gi
const PHONE_PATTERN = /(?:\+?\d[\d\s().-]{7,}\d)/g

/**
 * Public SEO/share pages must not accidentally expose contact details copied
 * into free-text profile fields. Full trusted profile/contact actions remain
 * inside authenticated Student flows.
 */
export function sanitizePublicSeoText(value: unknown, maxLength = 180) {
  const cleaned = String(value ?? '')
    .replace(EMAIL_PATTERN, ' ')
    .replace(URL_PATTERN, ' ')
    .replace(PHONE_PATTERN, ' ')
    .replace(/[\u0000-\u001f\u007f]/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()

  if (cleaned.length <= maxLength) return cleaned
  return cleaned.slice(0, Math.max(0, maxLength - 1)).trimEnd() + '…'
}
