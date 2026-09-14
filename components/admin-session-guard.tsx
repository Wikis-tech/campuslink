'use client'

import { useEffect, useRef } from 'react'

const IDLE_MS = 20 * 60 * 1000

export function AdminSessionGuard() {
  const timer = useRef<ReturnType<typeof setTimeout> | null>(null)

  useEffect(() => {
    const logout = async () => {
      try { await fetch('/api/admin/session-timeout', { method: 'POST', credentials: 'same-origin' }) } catch {}
      window.location.replace('/admin-login?reason=idle')
    }
    const reset = () => {
      if (timer.current) clearTimeout(timer.current)
      timer.current = setTimeout(logout, IDLE_MS)
    }
    const events = ['pointerdown','keydown','scroll','touchstart','mousemove'] as const
    events.forEach((event) => window.addEventListener(event, reset, { passive: true }))
    reset()
    return () => {
      if (timer.current) clearTimeout(timer.current)
      events.forEach((event) => window.removeEventListener(event, reset))
    }
  }, [])

  return null
}
