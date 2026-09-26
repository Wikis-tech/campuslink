import Link from 'next/link'
import { BarChart3, Building2, CalendarRange, FolderKanban, GraduationCap, LayoutDashboard, LifeBuoy, MessageSquareText, Palette, ShieldAlert, ShieldCheck, Store, UserCog, ShoppingBag } from 'lucide-react'
import { ThemeToggle } from '@/components/theme-toggle'
import { AdminMobileNav } from '@/components/admin-mobile-nav'
import { AdminSessionGuard } from '@/components/admin-session-guard'
import { createClient } from '@/lib/supabase/server'
import { requireAdminContext } from './lib'
import './admin.css'

export default async function AdminLayout({ children }: { children: React.ReactNode }) {
  const context = await requireAdminContext()
  const supabase = await createClient()
  const { data: userData } = await supabase.auth.getUser()
  const email = userData.user?.email || 'admin'
  const roleLabel = context.globalRole
    ? context.globalRole.replaceAll('_', ' ')
    : context.schoolAssignments.length === 1
      ? context.schoolAssignments[0].role.replaceAll('_', ' ')
      : 'school admin'

  return (
    <main className="admin-app admin-fiverr-app">
      <AdminSessionGuard/>
      <AdminMobileNav email={email} role={roleLabel} isSuperAdmin={context.globalRole === 'super_admin'}/>
      <div className="admin-shell">
        <aside className="admin-sidebar admin-fiverr-sidebar">
          <Link prefetch={false} href="/control-center" className="admin-brand">Campus<span>Link</span> Admin</Link>
          <div className="admin-sidebar-tools">
            <div className="admin-profile-card"><span className="admin-profile-avatar">{email.slice(0,1).toUpperCase()}</span><div><strong>{email}</strong><small>{roleLabel}</small></div></div>
            <div className="admin-role"><strong>{roleLabel}</strong>{context.isGlobalAdmin ? 'Global control-plane access' : `${context.schoolAssignments.length} school assignment${context.schoolAssignments.length === 1 ? '' : 's'}`}</div>
            <ThemeToggle compact />
          </div>
          <nav className="admin-nav">
            <Link prefetch={false} href="/control-center"><LayoutDashboard /> Overview</Link>
            <Link prefetch={false} href="/control-center/marketplace"><ShoppingBag /> Marketplace</Link>
            <Link prefetch={false} href="/control-center/campus-intelligence"><CalendarRange /> Campus Intelligence</Link>
            <Link prefetch={false} href="/control-center/safety"><ShieldAlert /> Trust & Safety</Link>
            <Link prefetch={false} href="/control-center/schools"><Building2 /> Schools</Link>
            <Link prefetch={false} href="/control-center/students"><GraduationCap /> Students</Link>
            <Link prefetch={false} href="/control-center/vendors"><Store /> Vendors</Link>
            <Link prefetch={false} href="/control-center/reviews"><MessageSquareText /> Reviews</Link>
            <Link prefetch={false} href="/control-center/reports"><LifeBuoy /> Reports</Link>
            <Link prefetch={false} href="/control-center/categories"><FolderKanban /> Categories</Link>
            <Link prefetch={false} href="/control-center/admins"><UserCog /> Admins</Link>
            <Link prefetch={false} href="/control-center/audit"><BarChart3 /> Audit</Link>
            {context.globalRole === 'super_admin' ? <Link prefetch={false} href="/control-center/branding"><Palette /> Branding</Link> : null}
          </nav>
          <div className="admin-sidebar-foot">
            <div className="admin-security-note"><ShieldCheck size={15}/> Auto sign-out after 20 minutes idle</div>
            <form action="/auth/signout" method="post"><button type="submit">Sign out</button></form>
          </div>
        </aside>
        <section className="admin-main">{children}</section>
      </div>
    </main>
  )
}
