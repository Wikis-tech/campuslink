'use client'

import { useEffect } from 'react'
import { useRouter } from 'next/navigation'

export function PwaLaunchSplash({ destination }: { destination: string }) {
  const router = useRouter()

  useEffect(() => {
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    const timer = window.setTimeout(() => router.replace(destination), reduced ? 120 : 760)
    return () => window.clearTimeout(timer)
  }, [destination, router])

  return (
    <main className="cl-pwa-launch" aria-label="Opening Campus Link">
      <div className="cl-pwa-launch-orbit" aria-hidden="true">
        <span className="cl-pwa-launch-ring" />
        <span className="cl-pwa-launch-ring cl-pwa-launch-ring-two" />
        <div className="cl-pwa-launch-logo">
          <img src="/brand/logo" alt="" width="76" height="76" />
        </div>
      </div>
      <div className="cl-pwa-launch-copy">
        <strong>Campus<span>Link</span></strong>
        <small>Connecting your campus</small>
      </div>
      <div className="cl-pwa-launch-progress" aria-hidden="true"><span /></div>
    </main>
  )
}
