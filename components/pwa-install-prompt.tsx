'use client'

import { Download, Share2, X } from 'lucide-react'
import Image from 'next/image'
import { usePathname } from 'next/navigation'
import { useEffect, useState } from 'react'

type InstallPromptChoice = {
  outcome: 'accepted' | 'dismissed'
  platform: string
}

interface BeforeInstallPromptEvent extends Event {
  prompt: () => Promise<void>
  userChoice: Promise<InstallPromptChoice>
}

const DISMISS_KEY = 'campuslink-pwa-install-dismissed-at'
const DISMISS_FOR_MS = 7 * 24 * 60 * 60 * 1000

function isStandaloneMode() {
  if (typeof window === 'undefined') return false

  const nav = window.navigator as Navigator & { standalone?: boolean }
  return window.matchMedia('(display-mode: standalone)').matches || nav.standalone === true
}

function isIosDevice() {
  if (typeof navigator === 'undefined') return false
  return /iphone|ipad|ipod/i.test(navigator.userAgent)
}

export function PwaInstallPrompt() {
  const pathname = usePathname()
  const [installEvent, setInstallEvent] = useState<BeforeInstallPromptEvent | null>(null)
  const [showIosHelp, setShowIosHelp] = useState(false)
  const [visible, setVisible] = useState(false)
  const [installing, setInstalling] = useState(false)

  const isAdminRoute = pathname.startsWith('/admin') || pathname.startsWith('/admin-login-campus')
  const isAppRoute =
    pathname === '/login' ||
    pathname === '/register' ||
    pathname === '/dashboard' ||
    pathname.startsWith('/student') ||
    pathname.startsWith('/vendor-v2') ||
    pathname.startsWith('/onboarding') ||
    pathname.startsWith('/verify')

  useEffect(() => {
    if (isAdminRoute || !isAppRoute || isStandaloneMode()) return

    let revealTimer: number | undefined

    const dismissedAt = Number(window.localStorage.getItem(DISMISS_KEY) || 0)
    const recentlyDismissed = dismissedAt > 0 && Date.now() - dismissedAt < DISMISS_FOR_MS
    if (recentlyDismissed) return

    const onBeforeInstallPrompt = (event: Event) => {
      event.preventDefault()
      setInstallEvent(event as BeforeInstallPromptEvent)
      revealTimer = window.setTimeout(() => setVisible(true), 900)
    }

    const onAppInstalled = () => {
      setVisible(false)
      setInstallEvent(null)
      window.localStorage.removeItem(DISMISS_KEY)
    }

    window.addEventListener('beforeinstallprompt', onBeforeInstallPrompt)
    window.addEventListener('appinstalled', onAppInstalled)

    if (isIosDevice()) {
      revealTimer = window.setTimeout(() => {
        setShowIosHelp(true)
        setVisible(true)
      }, 1600)
    }

    return () => {
      if (revealTimer) window.clearTimeout(revealTimer)
      window.removeEventListener('beforeinstallprompt', onBeforeInstallPrompt)
      window.removeEventListener('appinstalled', onAppInstalled)
    }
  }, [isAdminRoute, isAppRoute])

  if (isAdminRoute || !isAppRoute || !visible || (!installEvent && !showIosHelp)) return null

  const dismiss = () => {
    window.localStorage.setItem(DISMISS_KEY, String(Date.now()))
    setVisible(false)
  }

  const install = async () => {
    if (!installEvent || installing) return

    setInstalling(true)
    try {
      await installEvent.prompt()
      const choice = await installEvent.userChoice
      if (choice.outcome === 'accepted') {
        setVisible(false)
        setInstallEvent(null)
      } else {
        dismiss()
      }
    } finally {
      setInstalling(false)
    }
  }

  return (
    <aside className="cl-pwa-install" aria-label="Install Campus Link">
      <button className="cl-pwa-install-close" type="button" onClick={dismiss} aria-label="Dismiss install prompt">
        <X size={16} aria-hidden="true" />
      </button>

      <div className="cl-pwa-install-mark" aria-hidden="true">
        <Image src="/brand/app-icon/192" alt="" width={42} height={42} priority />
      </div>

      <div className="cl-pwa-install-copy">
        <strong>Keep Campus Link close</strong>
        {showIosHelp && !installEvent ? (
          <p>
            On iPhone or iPad, tap <Share2 size={14} aria-hidden="true" /> <b>Share</b>, then choose <b>Add to Home Screen</b>.
          </p>
        ) : (
          <p>Install Campus Link for a cleaner, app-like experience from your home screen.</p>
        )}
      </div>

      {installEvent ? (
        <button className="cl-pwa-install-button" type="button" onClick={install} disabled={installing}>
          <Download size={16} aria-hidden="true" />
          {installing ? 'Installing…' : 'Install'}
        </button>
      ) : null}
    </aside>
  )
}
