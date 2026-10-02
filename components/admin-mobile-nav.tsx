'use client'

import Link from 'next/link'
import { usePathname } from 'next/navigation'
import { Building2, CalendarRange, GraduationCap, Home, Mail, Menu, Palette, ShieldAlert, ShieldCheck, ShoppingBag, Store, UserCog } from 'lucide-react'
import { ThemeToggle } from '@/components/theme-toggle'

const nav = [
  { href:'/control-center', label:'Overview', icon:Home },
  { href:'/control-center/marketplace', label:'Market', icon:ShoppingBag },
  { href:'/control-center/campus-intelligence', label:'Campus', icon:CalendarRange },
  { href:'/control-center/safety', label:'Safety', icon:ShieldAlert },
  { href:'/control-center/students', label:'Students', icon:GraduationCap },
  { href:'/control-center/vendors', label:'Vendors', icon:Store },
  { href:'/control-center/communications', label:'Alerts', icon:Mail },
  { href:'/control-center/schools', label:'Schools', icon:Building2 },
  { href:'/control-center/admins', label:'Admins', icon:UserCog },
]

export function AdminMobileNav({ email, role, globalRole, isGlobalAdmin = false }: { email: string; role: string; globalRole: string | null; isGlobalAdmin?: boolean }) {
  const pathname = usePathname()
  const initial = (email || 'A').slice(0,1).toUpperCase()
  const isSchoolAdmin = !isGlobalAdmin
  const visibleNav = nav.filter((item) => {
    if (item.href === '/control-center/schools') return ['super_admin','operations_admin','content_admin'].includes(globalRole || '')
    if (item.href === '/control-center/admins') return ['super_admin','operations_admin'].includes(globalRole || '')
    if (item.href === '/control-center/campus-intelligence') return isSchoolAdmin || ['super_admin','operations_admin','content_admin'].includes(globalRole || '')
    if (item.href === '/control-center/safety') return isSchoolAdmin || ['super_admin','operations_admin','support_admin','verification_admin'].includes(globalRole || '')
    if (item.href === '/control-center/students' || item.href === '/control-center/vendors') return isSchoolAdmin || ['super_admin','operations_admin','verification_admin','support_admin','analyst'].includes(globalRole || '')
    if (item.href === '/control-center/marketplace') return isSchoolAdmin || ['super_admin','operations_admin','verification_admin','support_admin','analyst'].includes(globalRole || '')
    if (item.href === '/control-center/communications') return isSchoolAdmin || ['super_admin','operations_admin','content_admin','verification_admin'].includes(globalRole || '')
    return true
  })
  const canSeeReports = isSchoolAdmin || ['super_admin','operations_admin','support_admin','analyst'].includes(globalRole || '')
  return <>
    <header className="admin-mobile-topbar">
      <Link href="/control-center" className="admin-mobile-brand">Campus<span>Link</span> Admin</Link>
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
      <div>{[...visibleNav, ...(globalRole === 'super_admin' ? [{ href:'/control-center/branding', label:'Branding', icon:Palette }] : [])].map(({href,label,icon:Icon})=>{
        const active=href==='/control-center'?pathname===href:pathname.startsWith(href)
        return <Link prefetch={false} href={href} key={href} className={active?'active':''}><Icon size={18}/><span>{label}</span></Link>
      })}</div>
      {canSeeReports ? <Link prefetch={false} href="/control-center/reports" className={pathname.startsWith('/control-center/reports')?'active':''}><Menu size={18}/><span>More</span></Link> : null}
    </nav>
  </>
}
