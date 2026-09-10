export function getSiteUrl() {
  const configured = process.env.NEXT_PUBLIC_APP_URL?.trim()
  const vercelUrl = process.env.VERCEL_URL?.trim()

  if (configured && !configured.includes('localhost')) {
    return configured.replace(/\/$/, '')
  }

  if (vercelUrl) {
    return `https://${vercelUrl.replace(/^https?:\/\//, '').replace(/\/$/, '')}`
  }

  return (configured || 'http://localhost:3000').replace(/\/$/, '')
}
