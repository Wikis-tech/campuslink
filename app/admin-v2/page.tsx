import Link from 'next/link'
import { Activity, ArrowUpRight, Building2, GraduationCap, LifeBuoy, ShieldCheck, Store, UsersRound } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { AdminOverviewChart } from '@/components/dashboard-charts'
import { requireAdminContext } from './lib'

export default async function AdminDashboard() {
  const context = await requireAdminContext()
  const supabase = await createClient()

  const [students, vendors, pendingStudents, pendingVendors, schools, reports] = await Promise.all([
    supabase.from('profiles').select('id', { count: 'exact', head: true }).eq('account_type', 'student'),
    supabase.from('vendor_profiles').select('id', { count: 'exact', head: true }),
    supabase.from('student_verifications').select('student_id', { count: 'exact', head: true }).in('status', ['pending','under_review']),
    supabase.from('vendor_profiles').select('id', { count: 'exact', head: true }).in('verification_status', ['pending','under_review']),
    supabase.from('institutions').select('id', { count: 'exact', head: true }).eq('is_active', true),
    supabase.from('complaints').select('id', { count: 'exact', head: true }).in('status', ['open','reviewing']),
  ])

  const role = context.globalRole || context.schoolAssignments[0]?.role || 'admin'
  const scoped = !context.isGlobalAdmin
  const studentCount = students.count || 0
  const vendorCount = vendors.count || 0
  const schoolCount = schools.count || 0
  const reportCount = reports.count || 0
  const studentQueue = pendingStudents.count || 0
  const vendorQueue = pendingVendors.count || 0

  return (
    <>
      <header className="admin-hero phase45-admin-hero reveal-admin">
        <div className="admin-hero-copy">
          <div className="admin-hero-label"><ShieldCheck size={16}/> Campus Link Admin</div>
          <h1>Keep every campus safe, active and moving.</h1>
          <p>{scoped ? 'Your workspace is limited to the schools assigned to your admin account.' : 'Manage schools, verification, vendors, reports and platform trust from one operational view.'}</p>
        </div>
        <div className="admin-hero-side">
          <span>Signed in as</span>
          <strong>{role.replaceAll('_',' ')}</strong>
          <small>{context.isGlobalAdmin ? 'Platform-wide access' : `${context.schoolAssignments.length} assigned school${context.schoolAssignments.length === 1 ? '' : 's'}`}</small>
        </div>
      </header>

      <section className="cl-data-rail admin-data-rail" aria-label="Campus Link operational summary">
        <div className="brand"><span>Students in scope</span><strong>{studentCount}</strong><small>Registered student accounts</small></div>
        <div className="accent"><span>Vendors in scope</span><strong>{vendorCount}</strong><small>Businesses currently managed</small></div>
        <div><span>Active schools</span><strong>{schoolCount}</strong><small>Available in onboarding</small></div>
        <div><span>Open reports</span><strong>{reportCount}</strong><small>Cases requiring attention</small></div>
      </section>

      <section className="admin-overview-grid">
        <AdminOverviewChart students={studentCount} vendors={vendorCount} schools={schoolCount} reports={reportCount}/>
        <section className="admin-priority-card cl-editorial-surface">
          <div className="admin-priority-head"><div><span>Priority queue</span><strong>What needs attention now</strong></div><Activity size={20}/></div>
          <Link href="/admin-v2/students" className="priority-row"><div className="priority-icon blue"><GraduationCap/></div><div><strong>Student verification</strong><span>Review student identity and school evidence.</span></div><b>{studentQueue}</b><ArrowUpRight/></Link>
          <Link href="/admin-v2/vendors" className="priority-row"><div className="priority-icon green"><Store/></div><div><strong>Vendor verification</strong><span>Review identity before campus visibility.</span></div><b>{vendorQueue}</b><ArrowUpRight/></Link>
          <Link href="/admin-v2/reports" className="priority-row"><div className="priority-icon amber"><LifeBuoy/></div><div><strong>Safety reports</strong><span>Investigate complaints and suspicious activity.</span></div><b>{reportCount}</b><ArrowUpRight/></Link>
        </section>
      </section>

      <section className="admin-section modern-admin-section">
        <div className="admin-section-head"><div><h2>Operations shortcuts</h2><p>Move directly into the areas that keep Campus Link organised and trustworthy.</p></div><UsersRound size={20}/></div>
        <div className="admin-section-body queue-grid modern-queue-grid">
          <Link href="/admin-v2/schools" className="queue-card"><Building2/><h3>School management</h3><p>Add institutions, configure verification rules and archive schools without breaking historical records.</p><span className="queue-link">Manage schools <ArrowUpRight size={14}/></span></Link>
          <Link href="/admin-v2/students" className="queue-card"><GraduationCap/><h3>Students</h3><p>Review verification evidence, statuses and campus membership from one place.</p><span className="queue-link">Review students <ArrowUpRight size={14}/></span></Link>
          <Link href="/admin-v2/vendors" className="queue-card"><Store/><h3>Vendors</h3><p>Separate global identity approval from school-specific vendor visibility.</p><span className="queue-link">Manage vendors <ArrowUpRight size={14}/></span></Link>
          <Link href="/admin-v2/reports" className="queue-card"><LifeBuoy/><h3>Trust & safety</h3><p>Follow complaints through a clear moderation lifecycle and preserve accountability.</p><span className="queue-link">Open reports <ArrowUpRight size={14}/></span></Link>
        </div>
      </section>
    </>
  )
}
