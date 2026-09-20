import type { Metadata, Viewport } from 'next'
import { DM_Sans, Sora } from 'next/font/google'
import { Suspense } from 'react'
import { ThemeFloatingControl } from '@/components/theme-floating-control'
import { GlobalLoadingFeedback } from '@/components/global-loading-feedback'
import { PwaInstallPrompt } from '@/components/pwa-install-prompt'
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

export const metadata: Metadata = {
  applicationName: 'Campus Link',
  title: {
    default: 'Campus Link',
    template: '%s · Campus Link',
  },
  description: 'Discover trusted campus vendors, products and services around your school.',
  manifest: '/manifest.webmanifest',
  icons: {
    icon: [{ url: '/brand/favicon', type: 'image/png' }],
    apple: [{ url: '/brand/app-icon/180', type: 'image/png', sizes: '180x180' }],
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
      <body className={`${dmSans.variable} ${sora.variable}`}>{children}<Suspense fallback={null}><GlobalLoadingFeedback/></Suspense><PwaInstallPrompt /><ThemeFloatingControl /></body>
    </html>
  )
}
