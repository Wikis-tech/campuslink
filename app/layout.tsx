import type { Metadata, Viewport } from 'next'
import { DM_Sans, Sora } from 'next/font/google'
import { Suspense } from 'react'
import { ThemeFloatingControl } from '@/components/theme-floating-control'
import { GlobalLoadingFeedback } from '@/components/global-loading-feedback'
import { PwaInstallPrompt } from '@/components/pwa-install-prompt'
import { getPlatformBranding } from '@/lib/platform-branding'
import { PwaServiceWorker } from '@/components/pwa-service-worker'
import './globals.css'
import './auth.css'
import './phase45.css'
import './phase45-polish.css'
import './phase46.css'
import './phase45-46-redesign.css'
import './phase47-marketplace.css'
import './phase5a.css'
import './phase5b.css'
import './phase5c.css'
import './phase48-marketplace-command.css'
import './phase5e-dashboard.css'
import './phase5e-rework.css'
import './phase5e-student-marketplace.css'
import './student-fiverr.css'
import './vendor-polish.css'
import './vendor-profile-settings.css'
import './admin-v2/admin-fiverr.css'
import './admin-v2/admin-sidebar-scroll.css'
import './typography-polish.css'
import './phase5f.css'
import './phase5g.css'
import './phase5g-polish.css'
import './loading-feedback.css'
import './system-darkmode.css'
import './pwa-foundation.css'

const dmSans = DM_Sans({ subsets: ['latin'], variable: '--font-dm-sans', display: 'swap' })
const sora = Sora({ subsets: ['latin'], variable: '--font-sora', display: 'swap' })

export async function generateMetadata(): Promise<Metadata> {
  const branding = await getPlatformBranding()
  const revision = branding.revision || 1
  const base = new URL(process.env.NEXT_PUBLIC_APP_URL || 'https://campuslink.name.ng')
  const ogImage = new URL(`/api/og/site?v=${revision}`, base).toString()

  return {
    metadataBase: base,
    applicationName: 'Campus Link',
    title: {
      default: 'Campus Link',
      template: '%s · Campus Link',
    },
    description: 'Discover trusted campus vendors, products and services around your school.',
    alternates: { canonical: '/' },
    manifest: '/manifest.webmanifest',
    icons: {
      icon: [{ url: `/brand/favicon?v=${revision}`, type: 'image/png' }],
      shortcut: [{ url: `/brand/favicon?v=${revision}`, type: 'image/png' }],
      apple: [{ url: `/brand/app-icon/180?v=${revision}`, type: 'image/png', sizes: '180x180' }],
    },
    openGraph: {
      type: 'website',
      siteName: 'Campus Link',
      title: 'Campus Link — Trusted campus discovery',
      description: 'Discover verified Vendors, Products and Services approved for your university community.',
      url: '/',
      images: [{ url: ogImage, width: 1200, height: 630, alt: 'Campus Link — trusted campus discovery' }],
    },
    twitter: {
      card: 'summary_large_image',
      title: 'Campus Link — Trusted campus discovery',
      description: 'Discover verified Vendors, Products and Services approved for your university community.',
      images: [ogImage],
    },
    appleWebApp: {
      capable: true,
      title: 'Campus Link',
      statusBarStyle: 'black-translucent',
    },
    other: {
      'mobile-web-app-capable': 'yes',
    },
  }
}

export const viewport: Viewport = {
  width: 'device-width',
  initialScale: 1,
  viewportFit: 'cover',
  themeColor: '#0B3D91',
}

const themeScript = `
(function(){
  try {
    var saved = localStorage.getItem('campuslink-theme') || 'system';
    var resolved = saved === 'system'
      ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
      : saved;
    document.documentElement.dataset.theme = resolved;
    document.documentElement.dataset.themePreference = saved;
    document.documentElement.style.colorScheme = resolved;
  } catch (_) {}
})();
`

export default function RootLayout({ children }: Readonly<{ children: React.ReactNode }>) {
  return (
    <html lang="en" suppressHydrationWarning>
      <head><script dangerouslySetInnerHTML={{ __html: themeScript }} /></head>
      <body className={`${dmSans.variable} ${sora.variable}`}>{children}<Suspense fallback={null}><GlobalLoadingFeedback/></Suspense><PwaServiceWorker /><PwaInstallPrompt /><ThemeFloatingControl /></body>
    </html>
  )
}
