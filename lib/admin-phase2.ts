export type SchoolReadinessInput = {
  name?: string | null
  city?: string | null
  state?: string | null
  country?: string | null
  verificationMode?: string | null
  emailDomain?: string | null
  allowedEmailDomains?: string[] | null
  verificationInstructions?: string | null
  activeLocationCount?: number
  schoolAdminCount?: number
}

export type SchoolReadiness = {
  status: 'ready' | 'needs_setup'
  blockingIssues: string[]
  recommendations: string[]
  completedCoreItems: number
  coreItems: number
}

export function calculateSchoolReadiness(input: SchoolReadinessInput): SchoolReadiness {
  const blockingIssues: string[] = []
  const recommendations: string[] = []

  const locationComplete = Boolean(input.city?.trim() && input.state?.trim() && input.country?.trim())
  if (!locationComplete) blockingIssues.push('Complete the school location')

  const mode = input.verificationMode || 'hybrid'
  const emailDomains = [
    input.emailDomain?.trim(),
    ...((input.allowedEmailDomains || []).map((value) => value?.trim())),
  ].filter(Boolean) as string[]
  const hasEmailVerification = emailDomains.length > 0
  const hasManualInstructions = Boolean(input.verificationInstructions?.trim())

  let verificationComplete = false
  if (mode === 'institution_email') verificationComplete = hasEmailVerification
  else if (mode === 'manual') verificationComplete = hasManualInstructions
  else verificationComplete = hasEmailVerification || hasManualInstructions

  if (!verificationComplete) {
    blockingIssues.push(
      mode === 'institution_email'
        ? 'Add a Student email domain'
        : mode === 'manual'
          ? 'Add manual verification instructions'
          : 'Add an email domain or manual verification instructions',
    )
  }

  const hasCampusLocation = Number(input.activeLocationCount || 0) > 0
  if (!hasCampusLocation) blockingIssues.push('Add at least one active campus location')

  if (Number(input.schoolAdminCount || 0) < 1) recommendations.push('Assign a School Admin')

  const coreItems = 3
  const completedCoreItems = coreItems - blockingIssues.length

  return {
    status: blockingIssues.length ? 'needs_setup' : 'ready',
    blockingIssues,
    recommendations,
    completedCoreItems,
    coreItems,
  }
}

export function waitingAge(iso?: string | null, now = Date.now()) {
  if (!iso) return null
  const ms = Math.max(0, now - new Date(iso).getTime())
  const minutes = Math.floor(ms / 60000)
  if (minutes < 1) return 'less than a minute'
  if (minutes < 60) return `${minutes}m`
  const hours = Math.floor(minutes / 60)
  if (hours < 24) return `${hours}h ${minutes % 60}m`
  const days = Math.floor(hours / 24)
  return `${days}d ${hours % 24}h`
}

export function dayGreeting(hour: number) {
  if (hour < 12) return 'Good morning'
  if (hour < 17) return 'Good afternoon'
  return 'Good evening'
}
