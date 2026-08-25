'use client'

import { usePathname } from 'next/navigation'
import { ThemeToggle } from './theme-toggle'

export function ThemeFloatingControl() {
  const pathname = usePathname()
  const show = pathname.startsWith('/login') || pathname.startsWith('/register') || pathname.startsWith('/onboarding') || pathname.startsWith('/auth')
  if (!show) return null

  return (
    <div className="cl-floating-theme" aria-label="Appearance controls">
      <ThemeToggle />
    </div>
  )
}
