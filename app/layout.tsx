import type { Metadata } from 'next'
import { ThemeFloatingControl } from '@/components/theme-floating-control'
import './globals.css'
import './auth.css'
import './phase45.css'
import './phase45-polish.css'
import './phase46.css'
import './phase45-46-redesign.css'
import './phase47-marketplace.css'
import './phase5a.css'

export const metadata: Metadata = {
  title: 'Campus Link',
  description: 'Discover trusted, verified services around your campus.',
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
      <head>
        <script dangerouslySetInnerHTML={{ __html: themeScript }} />
      </head>
      <body>
        {children}
        <ThemeFloatingControl />
      </body>
    </html>
  )
}
