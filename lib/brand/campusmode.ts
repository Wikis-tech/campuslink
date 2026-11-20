/**
 * CampusMode brand source-of-truth.
 *
 * Stage 1 only: this module does not automatically replace the current runtime
 * Kampivo identity. Visible integration happens in Stage 2.
 */
export const CAMPUSMODE_BRAND = {
  name: 'CampusMode',
  tagline: 'Campus life, made easy.',
  descriptor: 'Find trusted products, services and businesses around your campus.',
  architecture: {
    business: 'CampusMode Business',
    pro: 'CampusMode Pro',
    verified: 'CampusMode Verified',
    safety: 'CampusMode Safety',
    discover: 'CampusMode Discover',
    schools: 'CampusMode for Schools',
    alerts: 'CampusMode Alerts',
  },
  colors: {
    campusBlue: '#154597',
    modeGreen: '#20A953',
    midnight: '#08121F',
    ink: '#172033',
    cloud: '#F1F3F6',
    warmPaper: '#F0EDE8',
  },
  typography: {
    wordmark: 'Neue Machina',
    tagline: 'Open Sans',
  },
} as const
