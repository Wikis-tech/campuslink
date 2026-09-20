'use client'

import { useEffect, useState } from 'react'

export function PwaServiceWorker() {
  const [offline, setOffline] = useState(false)

  useEffect(() => {
    if (typeof window === 'undefined' || !('serviceWorker' in navigator)) return

    let reloading = false
    let registration: ServiceWorkerRegistration | null = null

    const updateOnlineState = () => setOffline(!navigator.onLine)
    updateOnlineState()
    window.addEventListener('online', updateOnlineState)
    window.addEventListener('offline', updateOnlineState)

    const onControllerChange = () => {
      if (reloading) return
      reloading = true
      window.location.reload()
    }
    navigator.serviceWorker.addEventListener('controllerchange', onControllerChange)

    navigator.serviceWorker
      .register('/sw.js', { scope: '/', updateViaCache: 'none' })
      .then((reg) => {
        registration = reg

        if (reg.waiting) reg.waiting.postMessage({ type: 'SKIP_WAITING' })

        reg.addEventListener('updatefound', () => {
          const worker = reg.installing
          if (!worker) return
          worker.addEventListener('statechange', () => {
            if (worker.state === 'installed' && navigator.serviceWorker.controller) {
              worker.postMessage({ type: 'SKIP_WAITING' })
            }
          })
        })

        // Check for a newer secure worker periodically while the app is open.
        const timer = window.setInterval(() => reg.update().catch(() => undefined), 60 * 60 * 1000)
        ;(reg as ServiceWorkerRegistration & { __campuslinkTimer?: number }).__campuslinkTimer = timer
      })
      .catch(() => {
        // Campus Link remains fully usable as a normal website if SW registration fails.
      })

    return () => {
      window.removeEventListener('online', updateOnlineState)
      window.removeEventListener('offline', updateOnlineState)
      navigator.serviceWorker.removeEventListener('controllerchange', onControllerChange)
      const timer = (registration as (ServiceWorkerRegistration & { __campuslinkTimer?: number }) | null)?.__campuslinkTimer
      if (timer) window.clearInterval(timer)
    }
  }, [])

  if (!offline) return null

  return (
    <div className="cl-offline-banner" role="status" aria-live="polite">
      <span className="cl-offline-dot" aria-hidden="true" />
      You&apos;re offline. Live Campus Link data needs an internet connection.
    </div>
  )
}
