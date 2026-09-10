'use client'

import Link from 'next/link'
import { usePathname } from 'next/navigation'
import { BarChart3, CircleDollarSign, ImagePlus, LayoutDashboard, ListChecks, Package, Store, TrendingUp } from 'lucide-react'
import { ThemeToggle } from '@/components/theme-toggle'

const items = [
  { href: '/vendor-v2', label: 'Overview', icon: LayoutDashboard },
  { href: '/vendor-v2/products', label: 'Products', icon: Package },
  { href: '/vendor-v2/services', label: 'Services', icon: ListChecks },
  { href: '/vendor-v2/portfolio', label: 'Portfolio', icon: ImagePlus },
  { href: '/vendor-v2/analytics', label: 'Analytics', icon: BarChart3 },
  { href: '/vendor-v2/growth', label: 'Growth', icon: TrendingUp },
  { href: '/vendor-v2/billing', label: 'Billing', icon: CircleDollarSign },
]

export function VendorWorkspaceSidebar({ storefrontHref }: { storefrontHref?: string }) {
  const pathname = usePathname()
  return (
    <aside className="v5e-sidebar v5e-sidebar-shared">
      <Link href="/vendor-v2" className="v3-brand">Campus<span>Link</span></Link>
      <nav aria-label="Vendor workspace">
        {items.map(({ href, label, icon: Icon }) => {
          const active = href === '/vendor-v2' ? pathname === href : pathname.startsWith(href)
          return <Link key={href} href={href} className={active ? 'active' : ''}><Icon size={17}/>{label}</Link>
        })}
        {storefrontHref ? <Link href={storefrontHref}><Store size={17}/> Storefront</Link> : null}
      </nav>
      <div className="v5e-side-footer">
        <ThemeToggle compact/>
        <form action="/auth/signout" method="post"><button className="v5e-signout" type="submit">Sign out</button></form>
      </div>
    </aside>
  )
}
