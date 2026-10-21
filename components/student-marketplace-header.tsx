'use client'

import Link from 'next/link'
import { usePathname } from 'next/navigation'
import { Bookmark, Home, LogOut, Search, ShieldCheck, UserRound } from 'lucide-react'
import { ThemeToggle } from '@/components/theme-toggle'

export function StudentMarketplaceHeader({
  firstName,
  schoolName,
  verificationStatus,
}: {
  firstName?: string | null
  schoolName?: string | null
  verificationStatus?: string | null
}) {
  const pathname = usePathname()
  const isVerified = verificationStatus === 'verified'
  const profileHref = isVerified ? '/student/profile' : '/onboarding/student'
  const initial = (firstName || 'S').slice(0, 1).toUpperCase()

  const mobileNav = [
    { href: '/student', label: 'Home', icon: Home },
    { href: '/student/discover', label: 'Discover', icon: Search },
    { href: '/student/saved', label: 'Saved', icon: Bookmark },
    { href: profileHref, label: 'Profile', icon: UserRound },
  ]

  return (
    <>
      <header className="cl-student-header cl-student-header-clean">
        <div className="cl-student-header-row">
          <Link href="/student" className="cl-student-brand" aria-label="Kampivo student home">
            <img src="/brand/logo" alt="Kampivo" />
          </Link>

          <form className="cl-student-global-search" action="/student/discover" method="get">
            <Search size={16} aria-hidden="true" />
            <input
              name="q"
              aria-label="Search Kampivo"
              placeholder="Search products, services or vendors"
            />
          </form>

          <div className="cl-student-account-tools">
            <ThemeToggle compact />
            <details className="cl-student-account-menu">
              <summary aria-label="Open student account menu">
                <span className="cl-student-avatar">{initial}</span>
              </summary>
              <div className="cl-student-account-popover">
                <div className="cl-student-account-summary">
                  <span className="cl-student-avatar">{initial}</span>
                  <div>
                    <strong>{firstName || 'Student'}</strong>
                    <small>{schoolName || 'Kampivo student'}</small>
                  </div>
                </div>
                <Link href={profileHref}><UserRound size={16}/> {isVerified ? 'Profile' : 'Profile & verification'}</Link>
                <Link href="/student/saved"><Bookmark size={16}/> Saved</Link>
                <Link href="/student/safety"><ShieldCheck size={16}/> Safety Centre</Link>
                <form action="/auth/signout" method="post">
                  <button type="submit"><LogOut size={16}/> Sign out</button>
                </form>
              </div>
            </details>
          </div>
        </div>
      </header>

      <nav className="cl-student-mobile-nav cl-student-mobile-nav-clean" aria-label="Student mobile navigation">
        {mobileNav.map(({ href, label, icon: Icon }) => {
          const active = href === '/student' ? pathname === '/student' : pathname.startsWith(href)
          return (
            <Link key={href} href={href} className={active ? 'active' : ''}>
              <Icon size={19}/>
              <span>{label}</span>
            </Link>
          )
        })}
      </nav>
    </>
  )
}
