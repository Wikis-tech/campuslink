import { ShieldCheck, UserCog, Users } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { canManageAdmins, requireAdminContext } from '../lib'
import { assignGlobalAdmin, assignSchoolAdmin } from '../actions'

export default async function AdminsManagementPage({ searchParams }: { searchParams: Promise<{ success?: string; error?: string }> }) {
  const params = await searchParams
  const context = await requireAdminContext()
  const supabase = await createClient()
  const manageable = canManageAdmins(context.globalRole)

  const [{ data: schools }, { data: schoolAssignments }, { data: globalAdmins }] = await Promise.all([
    supabase.from('institutions').select('id,name,is_active').order('name'),
    supabase.from('institution_admin_assignments').select('user_id,institution_id,role,is_active,created_at').order('created_at',{ascending:false}).limit(100),
    context.isGlobalAdmin ? supabase.from('admin_memberships').select('user_id,role,is_active,created_at').order('created_at',{ascending:false}).limit(100) : Promise.resolve({data:[] as any[]}),
  ])
  const schoolMap = new Map((schools || []).map((school)=>[school.id,school.name]))

  return <>
    <header className="admin-topbar"><div><span className="admin-pill"><UserCog size={15}/> Admin access</span><h1>Admins & school teams</h1><p>Delegate work without giving every staff member platform-wide power.</p></div></header>
    {params.success ? <div className="admin-success">{params.success}</div> : null}{params.error ? <div className="admin-error">{params.error}</div> : null}

    <section className="admin-section"><div className="admin-section-head"><div><h2>Role model</h2><p>Use the smallest role that can do the job.</p></div><ShieldCheck size={20}/></div><div className="admin-section-body queue-grid">
      <article className="queue-card"><h3>Global control plane</h3><p><strong>Super Admin</strong> — everything, including admin assignment.<br/><strong>Operations Admin</strong> — schools and operational staff.<br/><strong>Verification Admin</strong> — student/vendor identity.<br/><strong>Support Admin</strong> — complaints and trust cases.<br/><strong>Content Admin</strong> — school/reference content.<br/><strong>Finance Admin</strong> — subscriptions/payments when Phase 5 opens.<br/><strong>Analyst</strong> — read-only operational visibility.</p></article>
      <article className="queue-card"><h3>School-scoped teams</h3><p><strong>School Admin</strong> — broad operational control for one assigned school.<br/><strong>School Verifier</strong> — student verification and vendor campus approval only.<br/><strong>School Support</strong> — reports/support tied to that school.<br/><br/>These roles cannot become global admins or approve vendor identity.</p></article>
    </div></section>

    {manageable ? <section className="admin-section"><div className="admin-section-head"><div><h2>Assign a school sub-admin</h2><p>The person must already have a Campus Link account. Enter the account email and choose exactly one school role.</p></div><Users size={20}/></div><div className="admin-section-body"><form action={assignSchoolAdmin} className="admin-form"><div className="admin-form-grid"><div className="admin-field"><label>Campus Link account email</label><input type="email" name="email" required placeholder="staff@example.com"/></div><div className="admin-field"><label>School</label><select name="institution_id" required defaultValue=""><option value="" disabled>Select school</option>{(schools || []).filter(s=>s.is_active).map((school)=><option key={school.id} value={school.id}>{school.name}</option>)}</select></div><div className="admin-field"><label>School role</label><select name="role" defaultValue="school_verifier"><option value="school_admin">School Admin</option><option value="school_verifier">School Verifier</option><option value="school_support">School Support</option></select></div></div><div><button className="admin-action primary">Assign school role</button></div></form></div></section> : null}

    {context.globalRole === 'super_admin' ? <section className="admin-section"><div className="admin-section-head"><div><h2>Assign a global admin</h2><p>Global roles are powerful. Only Super Admin can grant them.</p></div></div><div className="admin-section-body"><form action={assignGlobalAdmin} className="admin-form"><div className="admin-form-grid"><div className="admin-field"><label>Campus Link account email</label><input type="email" name="email" required placeholder="staff@example.com"/></div><div className="admin-field"><label>Global role</label><select name="role" defaultValue="verification_admin"><option value="operations_admin">Operations Admin</option><option value="verification_admin">Verification Admin</option><option value="support_admin">Support Admin</option><option value="finance_admin">Finance Admin</option><option value="content_admin">Content Admin</option><option value="analyst">Analyst</option><option value="super_admin">Super Admin</option></select></div></div><div><button className="admin-action primary">Grant global role</button></div></form></div></section> : null}

    <section className="admin-section"><div className="admin-section-head"><div><h2>School assignments</h2><p>Current school-scoped permissions visible to your role.</p></div></div><div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>User ID</th><th>School</th><th>Role</th><th>Status</th></tr></thead><tbody>{(schoolAssignments || []).map((assignment)=><tr key={`${assignment.user_id}-${assignment.institution_id}`}><td><span className="admin-sub">{assignment.user_id}</span></td><td><span className="admin-name">{schoolMap.get(assignment.institution_id) || 'School'}</span></td><td>{assignment.role.replaceAll('_',' ')}</td><td><span className={`status-badge ${assignment.is_active?'status-approved':'status-rejected'}`}>{assignment.is_active?'active':'disabled'}</span></td></tr>)}{!schoolAssignments?.length?<tr><td colSpan={4}><div className="empty-admin">No school admin assignments yet.</div></td></tr>:null}</tbody></table></div></section>

    {context.isGlobalAdmin ? <section className="admin-section"><div className="admin-section-head"><div><h2>Global admin memberships</h2><p>Platform-wide roles.</p></div></div><div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>User ID</th><th>Role</th><th>Status</th><th>Created</th></tr></thead><tbody>{(globalAdmins || []).map((admin)=><tr key={admin.user_id}><td><span className="admin-sub">{admin.user_id}</span></td><td><span className="admin-name">{admin.role.replaceAll('_',' ')}</span></td><td><span className={`status-badge ${admin.is_active?'status-approved':'status-rejected'}`}>{admin.is_active?'active':'disabled'}</span></td><td>{new Date(admin.created_at).toLocaleDateString()}</td></tr>)}{!globalAdmins?.length?<tr><td colSpan={4}><div className="empty-admin">No global admins visible.</div></td></tr>:null}</tbody></table></div></section> : null}
  </>
}
