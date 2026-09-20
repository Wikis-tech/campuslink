'use client'

import Link from 'next/link'
import { usePathname } from 'next/navigation'
import { Building2, CalendarRange, GraduationCap, Home, Menu, Palette, ShieldAlert, ShieldCheck, ShoppingBag, Store, UserCog } from 'lucide-react'
import { ThemeToggle } from '@/components/theme-toggle'

const nav = [
  { href:'/admin-v2', label:'Overview', icon:Home },
  { href:'/admin-v2/marketplace', label:'Market', icon:ShoppingBag },
  { href:'/admin-v2/campus-intelligence', label:'Campus', icon:CalendarRange },
  { href:'/admin-v2/safety', label:'Safety', icon:ShieldAlert },
  { href:'/admin-v2/students', label:'Students', icon:GraduationCap },
  { href:'/admin-v2/vendors', label:'Vendors', icon:Store },
  { href:'/admin-v2/schools', label:'Schools', icon:Building2 },
  { href:'/admin-v2/admins', label:'Admins', icon:UserCog },
]

export function AdminMobileNav({ email, role, isSuperAdmin = false }: { email: string; role: string; isSuperAdmin?: boolean }) {
  const pathname = usePathname()
  const initial = (email || 'A').slice(0,1).toUpperCase()
  return <>
    <header className="admin-mobile-topbar">
      <Link href="/admin-v2" className="admin-mobile-brand">Campus<span>Link</span> Admin</Link>
      <div className="admin-mobile-tools">
        <ThemeToggle compact/>
        <details className="admin-mobile-profile">
          <summary aria-label="Open admin profile"><span>{initial}</span></summary>
          <div className="admin-mobile-profile-popover">
            <strong>{email}</strong><small>{role}</small>
            <div className="admin-mobile-security"><ShieldCheck size={14}/> 20-minute idle timeout</div>
            <form action="/auth/signout" method="post"><button type="submit">Sign out</button></form>
          </div>
        </details>
      </div>
    </header>
    <nav className="admin-mobile-bottom-nav" aria-label="Admin mobile navigation">
      <div>{[...nav, ...(isSuperAdmin ? [{ href:'/admin-v2/branding', label:'Branding', icon:Palette }] : [])].map(({href,label,icon:Icon})=>{
        const active=href==='/admin-v2'?pathname===href:pathname.startsWith(href)
        return <Link href={href} key={href} className={active?'active':''}><Icon size={18}/><span>{label}</span></Link>
      })}</div>
      <Link href="/admin-v2/reports" className={pathname.startsWith('/admin-v2/reports')?'active':''}><Menu size={18}/><span>More</span></Link>
    </nav>
  </>
}
