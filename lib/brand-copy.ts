export function presentBrandCopy(value: string | null | undefined) {
  if (!value) return ''

  // Stage C is presentation-only: legacy stored copy may still contain the old
  // product name, while database identifiers, URLs and integration keys remain
  // unchanged until the later infrastructure migration.
  return value.replace(/\bCampus\s*Link\b/gi, 'Kampivo')
}
