'use client'

import Link from 'next/link'
import { usePathname } from 'next/navigation'
import { BarChart3, CalendarClock, CircleDollarSign, ImagePlus, LayoutDashboard, ListChecks, LogOut, Menu, MessageSquareText, Package, Store, TrendingUp } from 'lucide-react'
import { ThemeToggle } from '@/components/theme-toggle'

const items = [
  { href: '/vendor-v2', label: 'Overview', icon: LayoutDashboard },
  { href: '/vendor-v2/products', label: 'Products', icon: Package },
  { href: '/vendor-v2/services', label: 'Services', icon: ListChecks },
  { href: '/vendor-v2/portfolio', label: 'Portfolio', icon: ImagePlus },
  { href: '/vendor-v2/profile', label: 'Business profile', icon: Store },
  { href: '/vendor-v2/availability', label: 'Availability', icon: CalendarClock },
  { href: '/vendor-v2/reviews', label: 'Reviews', icon: MessageSquareText },
  { href: '/vendor-v2/analytics', label: 'Analytics', icon: BarChart3 },
  { href: '/vendor-v2/growth', label: 'Growth', icon: TrendingUp },
  { href: '/vendor-v2/billing', label: 'Billing', icon: CircleDollarSign },
]

export function VendorWorkspaceSidebar(_: { storefrontHref?: string }) {
  const pathname = usePathname()
  const renderItem = ({ href, label, icon: Icon }: (typeof items)[number], mobile = false) => {
    const active = href === '/vendor-v2' ? pathname === href : pathname.startsWith(href)
    return <Link key={`${mobile ? 'm-' : ''}${href}`} href={href} className={active ? 'active' : ''}><Icon size={mobile ? 19 : 17}/><span>{label}</span></Link>
  }

  return <>
    <aside className="v5e-sidebar v5e-sidebar-shared">
      <Link href="/vendor-v2" className="v3-brand">Campus<span>Link</span></Link>
      <nav aria-label="Vendor workspace">{items.map((item) => renderItem(item))}</nav>
      <div className="v5e-side-footer">
        <ThemeToggle compact/>
        <form action="/auth/signout" method="post"><button className="v5e-signout" type="submit">Sign out</button></form>
      </div>
    </aside>

    <nav className="v5e-mobile-bottom-nav" aria-label="Vendor mobile navigation">
      <div className="v5e-mobile-scroll">{items.map((item) => renderItem(item, true))}</div>
      <details className="v5e-mobile-more">
        <summary aria-label="Open vendor account actions"><Menu size={19}/><span>More</span></summary>
        <div className="v5e-mobile-sheet">
          <Link href="/vendor-v2/profile"><Store size={17}/> Business profile</Link>
          <div className="v5e-mobile-theme"><ThemeToggle compact/></div>
          <form action="/auth/signout" method="post"><button type="submit"><LogOut size={17}/> Sign out</button></form>
        </div>
      </details>
    </nav>
  </>
}
