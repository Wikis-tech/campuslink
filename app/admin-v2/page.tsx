import Link from 'next/link'
import { Activity, Building2, GraduationCap, LifeBuoy, ShieldCheck, Store } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
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

  return (
    <>
      <header className="admin-topbar">
        <div>
          <span className="admin-pill"><ShieldCheck size={15}/> Phase 4 control plane</span>
          <h1>Campus operations</h1>
          <p>{scoped ? 'You are seeing only the schools assigned to your admin account.' : 'Manage onboarding, trust, schools and platform operations from one place.'}</p>
        </div>
        <div className="admin-top-actions"><span className="status-badge status-approved">{role.replaceAll('_',' ')}</span></div>
      </header>

      <section className="admin-grid">
        <article className="admin-stat"><span>Students in scope</span><strong>{students.count || 0}</strong><small>Registered student profiles visible to your role.</small></article>
        <article className="admin-stat"><span>Vendors in scope</span><strong>{vendors.count || 0}</strong><small>Vendor businesses visible to your role.</small></article>
        <article className="admin-stat"><span>Active schools</span><strong>{schools.count || 0}</strong><small>Schools currently available in onboarding.</small></article>
        <article className="admin-stat"><span>Open reports</span><strong>{reports.count || 0}</strong><small>Complaints requiring attention.</small></article>
      </section>

      <section className="admin-section">
        <div className="admin-section-head"><div><h2>Trust & verification queues</h2><p>These queues control who becomes trusted and discoverable.</p></div><Activity size={20}/></div>
        <div className="admin-section-body queue-grid">
          <Link href="/admin-v2/students" className="queue-card"><GraduationCap/><h3>Student verification</h3><p>Review school evidence and student identity without blocking basic account access.</p><div className="queue-number">{pendingStudents.count || 0}</div></Link>
          <Link href="/admin-v2/vendors" className="queue-card"><Store/><h3>Vendor verification</h3><p>Identity approval is global; campus approval can be delegated to school admins.</p><div className="queue-number">{pendingVendors.count || 0}</div></Link>
          <Link href="/admin-v2/schools" className="queue-card"><Building2/><h3>School management</h3><p>Add institutions, configure verification modes and archive schools without deleting historical records.</p><div className="queue-number">{schools.count || 0}</div></Link>
          <Link href="/admin-v2/reports" className="queue-card"><LifeBuoy/><h3>Safety & reports</h3><p>Track suspicious vendors, complaints and support cases through a clear moderation lifecycle.</p><div className="queue-number">{reports.count || 0}</div></Link>
        </div>
      </section>

      <section className="admin-section">
        <div className="admin-section-head"><div><h2>Admin model</h2><p>Global roles stay powerful; school roles stay scoped.</p></div></div>
        <div className="admin-section-body admin-note">
          <strong>Recommended structure:</strong> Super Admin owns the platform. Operations Admin manages schools and staff. Verification Admin reviews identity. Support Admin handles reports. Content Admin manages public reference content. Analyst is read-only. School Admin, School Verifier and School Support are restricted to assigned institutions only.
        </div>
      </section>
    </>
  )
}
