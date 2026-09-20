'use client'

import Link from 'next/link'
import { usePathname } from 'next/navigation'
import { BadgeCheck, Bookmark, Home, LogOut, Menu, Search, ShieldCheck, UserRound, X } from 'lucide-react'
import { useEffect, useRef, useState } from 'react'
import { ThemeToggle } from '@/components/theme-toggle'

const navItems = [
  { href: '/student', label: 'Home', icon: Home },
  { href: '/student/discover', label: 'Discover', icon: Search },
  { href: '/student/saved', label: 'Saved', icon: Bookmark },
  { href: '/student/safety', label: 'Safety', icon: ShieldCheck },
  { href: '/onboarding/student', label: 'Verification', icon: BadgeCheck },
]

export function StudentMarketplaceHeader({ firstName, schoolName }: { firstName?: string | null; schoolName?: string | null }) {
  const pathname = usePathname()
  const initial = (firstName || 'S').slice(0, 1).toUpperCase()
  const [mobileAccountOpen, setMobileAccountOpen] = useState(false)
  const mobileAccountRef = useRef<HTMLDivElement | null>(null)

  useEffect(() => {
    setMobileAccountOpen(false)
  }, [pathname])

  useEffect(() => {
    if (!mobileAccountOpen) return

    const onPointerDown = (event: PointerEvent) => {
      const target = event.target as Node | null
      if (target && !mobileAccountRef.current?.contains(target)) setMobileAccountOpen(false)
    }
    const onKeyDown = (event: KeyboardEvent) => {
      if (event.key === 'Escape') setMobileAccountOpen(false)
    }

    document.addEventListener('pointerdown', onPointerDown)
    document.addEventListener('keydown', onKeyDown)
    return () => {
      document.removeEventListener('pointerdown', onPointerDown)
      document.removeEventListener('keydown', onKeyDown)
    }
  }, [mobileAccountOpen])

  return (
    <>
      <header className="cl-student-header">
        <div className="cl-student-header-row">
          <Link href="/student" className="cl-student-brand" aria-label="Campus Link student home">
            Campus<span>Link</span>
          </Link>

          <form className="cl-student-global-search" action="/student/discover" method="get">
            <Search size={18} aria-hidden="true" />
            <input name="q" aria-label="Search Campus Link" placeholder="What service or product are you looking for?" />
            <button type="submit">Search</button>
          </form>

          <div className="cl-student-account-tools">
            <Link href="/onboarding/student" className="cl-student-campus-pill" title={schoolName || 'Complete your campus profile'}>
              <ShieldCheck size={15} />
              <span>{schoolName || 'Set campus'}</span>
            </Link>
            <ThemeToggle compact />
            <details className="cl-student-account-menu">
              <summary aria-label="Open student account menu">
                <span className="cl-student-avatar">{initial}</span>
                <span className="cl-student-account-copy"><strong>{firstName || 'Student'}</strong><small>Student account</small></span>
              </summary>
              <div className="cl-student-account-popover">
                <Link href="/onboarding/student"><UserRound size={16}/> Profile & verification</Link>
                <Link href="/student/saved"><Bookmark size={16}/> Saved vendors</Link>
                <Link href="/student/safety"><ShieldCheck size={16}/> Safety Centre</Link>
                <form action="/auth/signout" method="post"><button type="submit">Sign out</button></form>
              </div>
            </details>
          </div>
        </div>

        <nav className="cl-student-subnav" aria-label="Student marketplace navigation">
          {navItems.map(({ href, label }) => {
            const active = href === '/student' ? pathname === '/student' : pathname.startsWith(href)
            return <Link key={href} href={href} className={active ? 'active' : ''}>{label}</Link>
          })}
          <span className="cl-student-subnav-note">Verified campus vendors only</span>
        </nav>
      </header>

      <nav className="cl-student-mobile-nav" aria-label="Student mobile navigation">
        <div className="cl-student-mobile-scroll">
          {navItems.map(({ href, label, icon: Icon }) => {
            const active = href === '/student' ? pathname === '/student' : pathname.startsWith(href)
            return <Link key={href} href={href} className={active ? 'active' : ''}><Icon size={18}/><span>{label}</span></Link>
          })}
        </div>
        <div className={`cl-student-mobile-more${mobileAccountOpen ? ' open' : ''}`} ref={mobileAccountRef}>
          <button
            type="button"
            className="cl-student-mobile-more-button"
            data-no-loading="true"
            aria-label={mobileAccountOpen ? 'Close student account actions' : 'Open student account actions'}
            aria-expanded={mobileAccountOpen}
            aria-controls="student-mobile-account-sheet"
            onClick={() => setMobileAccountOpen((open) => !open)}
          >
            {mobileAccountOpen ? <X size={18}/> : <Menu size={18}/>}
            <span>Account</span>
          </button>
          {mobileAccountOpen ? <div className="cl-student-mobile-sheet" id="student-mobile-account-sheet" role="dialog" aria-label="Student account actions">
            <div className="cl-student-mobile-sheet-head">
              <span className="cl-student-avatar">{initial}</span>
              <div><strong>{firstName || 'Student'}</strong><small>{schoolName || 'Campus Link account'}</small></div>
            </div>
            <Link href="/onboarding/student"><UserRound size={16}/> Profile & verification</Link>
            <Link href="/student/saved"><Bookmark size={16}/> Saved vendors</Link>
            <Link href="/student/safety"><ShieldCheck size={16}/> Safety Centre</Link>
            <div className="cl-student-mobile-theme"><ThemeToggle compact /></div>
            <form action="/auth/signout" method="post"><button type="submit"><LogOut size={16}/> Sign out</button></form>
          </div> : null}
        </div>
      </nav>
    </>
  )
}
