'use client'

import Link from 'next/link'
import { usePathname } from 'next/navigation'
import {
  BarChart3,
  CalendarClock,
  CircleDollarSign,
  LayoutDashboard,
  ListChecks,
  LogOut,
  Menu,
  MessageSquareText,
  Store,
  TrendingUp,
  X,
} from 'lucide-react'
import { useEffect, useRef, useState } from 'react'
import { ThemeToggle } from '@/components/theme-toggle'

const primary = [
  { href: '/vendor-v2', label: 'Overview', icon: LayoutDashboard },
  { href: '/vendor-v2/listings', label: 'Listings', icon: ListChecks },
  { href: '/vendor-v2/reviews', label: 'Reviews', icon: MessageSquareText },
]

const business = [
  { href: '/vendor-v2/profile', label: 'Business profile', icon: Store },
  { href: '/vendor-v2/availability', label: 'Availability', icon: CalendarClock },
]

const insights = [
  { href: '/vendor-v2/analytics', label: 'Analytics', icon: BarChart3 },
  { href: '/vendor-v2/growth', label: 'Growth', icon: TrendingUp },
]

const account = [
  { href: '/vendor-v2/billing', label: 'Billing', icon: CircleDollarSign },
]

export function VendorWorkspaceSidebar(_: { storefrontHref?: string }) {
  const pathname = usePathname()
  const [mobileMoreOpen, setMobileMoreOpen] = useState(false)
  const mobileMoreRef = useRef<HTMLDivElement | null>(null)

  useEffect(() => setMobileMoreOpen(false), [pathname])

  useEffect(() => {
    if (!mobileMoreOpen) return
    const onPointerDown = (event: PointerEvent) => {
      const target = event.target as Node | null
      if (target && !mobileMoreRef.current?.contains(target)) setMobileMoreOpen(false)
    }
    const onKeyDown = (event: KeyboardEvent) => {
      if (event.key === 'Escape') setMobileMoreOpen(false)
    }
    document.addEventListener('pointerdown', onPointerDown)
    document.addEventListener('keydown', onKeyDown)
    return () => {
      document.removeEventListener('pointerdown', onPointerDown)
      document.removeEventListener('keydown', onKeyDown)
    }
  }, [mobileMoreOpen])

  const isActive = (href: string) => {
    if (href === '/vendor-v2') return pathname === href
    if (href === '/vendor-v2/listings') {
      return pathname.startsWith('/vendor-v2/listings')
        || pathname.startsWith('/vendor-v2/products')
        || pathname.startsWith('/vendor-v2/services')
        || pathname.startsWith('/vendor-v2/portfolio')
    }
    return pathname.startsWith(href)
  }

  const renderItems = (items: typeof primary) => items.map(({ href, label, icon: Icon }) => (
    <Link key={href} href={href} className={isActive(href) ? 'active' : ''}>
      <Icon size={17}/>
      <span>{label}</span>
    </Link>
  ))

  return <>
    <aside className="v5e-sidebar v5e-sidebar-shared kv-vendor-sidebar">
      <Link href="/vendor-v2" className="v3-brand kv-vendor-brand" aria-label="Kampivo vendor home">
        <img src="/brand/logo" alt="Kampivo"/>
      </Link>

      <div className="kv-vendor-nav-group">
        <span>Primary</span>
        <nav aria-label="Vendor primary navigation">{renderItems(primary)}</nav>
      </div>

      <div className="kv-vendor-nav-group">
        <span>Business</span>
        <nav aria-label="Vendor business navigation">{renderItems(business)}</nav>
      </div>

      <div className="kv-vendor-nav-group">
        <span>Insights</span>
        <nav aria-label="Vendor insights navigation">{renderItems(insights)}</nav>
      </div>

      <div className="kv-vendor-nav-group">
        <span>Account</span>
        <nav aria-label="Vendor account navigation">{renderItems(account)}</nav>
      </div>

      <div className="v5e-side-footer kv-vendor-side-footer">
        <div className="kv-vendor-appearance"><ThemeToggle compact/><span>Appearance</span></div>
        <form action="/auth/signout" method="post">
          <button className="v5e-signout" type="submit"><LogOut size={16}/><span>Sign out</span></button>
        </form>
      </div>
    </aside>

    <nav className="v5e-mobile-bottom-nav kv-vendor-mobile-nav" aria-label="Vendor mobile navigation">
      <Link href="/vendor-v2" className={isActive('/vendor-v2') ? 'active' : ''}><LayoutDashboard size={19}/><span>Home</span></Link>
      <Link href="/vendor-v2/listings" className={isActive('/vendor-v2/listings') ? 'active' : ''}><ListChecks size={19}/><span>Listings</span></Link>
      <Link href="/vendor-v2/reviews" className={isActive('/vendor-v2/reviews') ? 'active' : ''}><MessageSquareText size={19}/><span>Reviews</span></Link>
      <Link href="/vendor-v2/analytics" className={isActive('/vendor-v2/analytics') ? 'active' : ''}><BarChart3 size={19}/><span>Analytics</span></Link>

      <div className={`v5e-mobile-more${mobileMoreOpen ? ' open' : ''}`} ref={mobileMoreRef}>
        <button
          type="button"
          className="v5e-mobile-more-button"
          data-no-loading="true"
          aria-label={mobileMoreOpen ? 'Close vendor menu' : 'Open vendor menu'}
          aria-expanded={mobileMoreOpen}
          aria-controls="vendor-mobile-more-sheet"
          onClick={() => setMobileMoreOpen((open) => !open)}
        >
          {mobileMoreOpen ? <X size={19}/> : <Menu size={19}/>}
          <span>More</span>
        </button>
        {mobileMoreOpen ? (
          <div className="v5e-mobile-sheet" id="vendor-mobile-more-sheet" role="dialog" aria-label="Vendor account actions">
            <Link href="/vendor-v2/profile"><Store size={17}/> Business profile</Link>
            <Link href="/vendor-v2/availability"><CalendarClock size={17}/> Availability</Link>
            <Link href="/vendor-v2/growth"><TrendingUp size={17}/> Growth</Link>
            <Link href="/vendor-v2/billing"><CircleDollarSign size={17}/> Billing</Link>
            <div className="v5e-mobile-appearance">
              <span>Appearance<small>Light · Dark · System</small></span>
              <ThemeToggle />
            </div>
            <form action="/auth/signout" method="post"><button type="submit"><LogOut size={17}/> Sign out</button></form>
          </div>
        ) : null}
      </div>
    </nav>
  </>
}
