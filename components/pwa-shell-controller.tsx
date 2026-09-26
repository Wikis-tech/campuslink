'use client'

import { useEffect } from 'react'
import { usePathname, useRouter } from 'next/navigation'

const APP_PREFIXES = [
  '/app',
  '/login',
  '/register',
  '/dashboard',
  '/student',
  '/vendor-v2',
  '/onboarding',
  '/verify',
  '/auth',
  '/forgot-password',
  '/reset-password',
  '/offline',
]

function isStandalone() {
  if (typeof window === 'undefined') return false
  const nav = window.navigator as Navigator & { standalone?: boolean }
  return window.matchMedia('(display-mode: standalone)').matches || nav.standalone === true
}

function isAllowedAppPath(pathname: string) {
  return APP_PREFIXES.some((prefix) => pathname === prefix || pathname.startsWith(prefix + '/'))
}

export function PwaShellController() {
  const pathname = usePathname()
  const router = useRouter()

  useEffect(() => {
    const standalone = isStandalone()
    document.documentElement.dataset.pwaMode = standalone ? 'standalone' : 'browser'

    if (!standalone) return

    // Campus Link's installed app is deliberately a Student/Vendor product.
    // Public marketing pages and every Admin route stay in the normal website.
    const isAdminPath =
      pathname === '/admin' ||
      pathname.startsWith('/admin/') ||
      pathname === '/admin-v2' ||
      pathname.startsWith('/admin-v2/') ||
      pathname === '/control-center' ||
      pathname.startsWith('/control-center/') ||
      pathname === '/admin-login-campus' ||
      pathname.startsWith('/admin-login-campus/')

    if (isAdminPath || pathname === '/' || !isAllowedAppPath(pathname)) {
      router.replace('/app?source=pwa')
    }
  }, [pathname, router])

  return null
}
