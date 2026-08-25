import Link from 'next/link'
import { BarChart3, Building2, ClipboardCheck, GraduationCap, LayoutDashboard, LifeBuoy, ShieldCheck, Store, UserCog } from 'lucide-react'
import { requireAdminContext } from './lib'
import './admin.css'

export default async function AdminLayout({ children }: { children: React.ReactNode }) {
  const context = await requireAdminContext()
  const roleLabel = context.globalRole
    ? context.globalRole.replaceAll('_', ' ')
    : context.schoolAssignments.length === 1
      ? context.schoolAssignments[0].role.replaceAll('_', ' ')
      : 'school admin'

  return (
    <main className="admin-app">
      <div className="admin-shell">
        <aside className="admin-sidebar">
          <Link href="/admin-v2" className="admin-brand">Campus<span>Link</span> Admin</Link>
          <div className="admin-role"><strong>{roleLabel}</strong>{context.isGlobalAdmin ? 'Global control-plane access' : `${context.schoolAssignments.length} school assignment${context.schoolAssignments.length === 1 ? '' : 's'}`}</div>
          <nav className="admin-nav">
            <Link href="/admin-v2"><LayoutDashboard /> Overview</Link>
            <Link href="/admin-v2/schools"><Building2 /> Schools</Link>
            <Link href="/admin-v2/students"><GraduationCap /> Students</Link>
            <Link href="/admin-v2/vendors"><Store /> Vendors</Link>
            <Link href="/admin-v2/reports"><LifeBuoy /> Reports</Link>
            <Link href="/admin-v2/admins"><UserCog /> Admins</Link>
            <Link href="/admin-v2/audit"><BarChart3 /> Audit</Link>
            <Link href="/student"><ClipboardCheck /> Student view</Link>
          </nav>
          <div className="admin-sidebar-foot">
            <Link href="/"><ShieldCheck /> Back to Campus Link</Link>
            <form action="/auth/signout" method="post"><button type="submit">Sign out</button></form>
          </div>
        </aside>
        <section className="admin-main">{children}</section>
      </div>
    </main>
  )
}
