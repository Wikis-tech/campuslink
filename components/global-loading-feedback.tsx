'use client'

import { useEffect, useRef, useState } from 'react'
import { usePathname } from 'next/navigation'

const MAX_WAIT_MS = 10000
const SHOW_DELAY_MS = 140

function isInternalNavigation(anchor: HTMLAnchorElement) {
  if (anchor.target && anchor.target !== '_self') return false
  if (anchor.hasAttribute('download')) return false
  const href = anchor.getAttribute('href')
  if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:')) return false

  try {
    const url = new URL(anchor.href, window.location.href)
    if (url.origin !== window.location.origin) return false
    if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) return false
    return true
  } catch {
    return false
  }
}

export function GlobalLoadingFeedback() {
  const pathname = usePathname()
  const [visible, setVisible] = useState(false)
  const showTimer = useRef<number | null>(null)
  const safetyTimer = useRef<number | null>(null)

  const stop = () => {
    if (showTimer.current) window.clearTimeout(showTimer.current)
    if (safetyTimer.current) window.clearTimeout(safetyTimer.current)
    showTimer.current = null
    safetyTimer.current = null
    setVisible(false)
    document.documentElement.removeAttribute('data-campuslink-loading')
  }

  const start = () => {
    if (showTimer.current || visible) return
    document.documentElement.setAttribute('data-campuslink-loading', 'true')
    showTimer.current = window.setTimeout(() => {
      setVisible(true)
      showTimer.current = null
    }, SHOW_DELAY_MS)
    safetyTimer.current = window.setTimeout(stop, MAX_WAIT_MS)
  }

  useEffect(() => {
    stop()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pathname])

  useEffect(() => {
    const onClick = (event: MouseEvent) => {
      if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return
      const target = event.target as Element | null
      const anchor = target?.closest('a[href]') as HTMLAnchorElement | null
      if (anchor && isInternalNavigation(anchor)) start()
    }

    const onSubmit = (event: SubmitEvent) => {
      if (event.defaultPrevented) return
      const form = event.target as HTMLFormElement | null
      if (!form) return
      const target = form.getAttribute('target')
      if (target && target !== '_self') return
      start()
    }

    const onPageShow = () => stop()
    const onPopState = () => stop()
    const onVisibility = () => { if (document.visibilityState === 'visible') stop() }

    document.addEventListener('click', onClick, true)
    document.addEventListener('submit', onSubmit, true)
    window.addEventListener('pageshow', onPageShow)
    window.addEventListener('popstate', onPopState)
    document.addEventListener('visibilitychange', onVisibility)

    return () => {
      document.removeEventListener('click', onClick, true)
      document.removeEventListener('submit', onSubmit, true)
      window.removeEventListener('pageshow', onPageShow)
      window.removeEventListener('popstate', onPopState)
      document.removeEventListener('visibilitychange', onVisibility)
      stop()
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [visible])

  if (!visible) return null

  return (
    <div className="campuslink-loading-overlay" role="status" aria-live="polite" aria-label="Campus Link is loading">
      <div className="campuslink-loading-card">
        <div className="campuslink-loading-spinner" aria-hidden="true" />
        <div>
          <strong>CampusLink</strong>
          <span>Loading…</span>
        </div>
      </div>
    </div>
  )
}
