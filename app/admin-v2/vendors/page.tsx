import Link from 'next/link'
import { Building2, FileCheck2, ShieldCheck, Store } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { createAdminClient } from '@/lib/supabase/admin'
import { canReviewVendorIdentity, requireAdminContext } from '../lib'
import { reviewVendorCampus, reviewVendorIdentity } from '../actions'
import { sendOperationalReminder } from '../communications/actions'

export default async function VendorsAdminPage({ searchParams }: { searchParams: Promise<{ success?: string; error?: string }> }) {
  const params = await searchParams
  const context = await requireAdminContext()
  const supabase = await createClient()
  const canIdentity = canReviewVendorIdentity(context.globalRole)
  const canViewIncompleteVendorAccounts = ['super_admin','operations_admin'].includes(context.globalRole || '')

  const { data: vendorAccounts } = canViewIncompleteVendorAccounts
    ? await supabase.from('profiles')
        .select('id,first_name,last_name,created_at')
        .eq('account_type', 'vendor')
        .order('created_at', { ascending: false })
        .limit(200)
    : { data: [] as any[] }

  const { data: vendors } = await supabase
    .from('vendor_profiles')
    .select('id,business_name,slug,description,business_email,whatsapp_number,location_text,verification_status,marketplace_status,suspended_until,onboarding_completed_at,created_at')
    .order('created_at',{ascending:false})
    .limit(100)

  const vendorIds = (vendors || []).map((row) => row.id)
  const [{ data: campusLinks }, { data: documents }] = vendorIds.length ? await Promise.all([
    supabase.from('vendor_institutions').select('vendor_id,institution_id,status,is_primary,review_note,created_at').in('vendor_id',vendorIds),
    supabase.from('vendor_documents').select('id,vendor_id,document_type,storage_path,status,created_at').in('vendor_id',vendorIds).order('created_at',{ascending:false}),
  ]) : [{data:[] as any[]},{data:[] as any[]}]

  const institutionIds = Array.from(new Set((campusLinks || []).map((row) => row.institution_id))) as string[]
  const { data: institutions } = institutionIds.length
    ? await supabase.from('institutions').select('id,name').in('id',institutionIds)
    : { data: [] as any[] }
  const schoolMap = new Map((institutions || []).map((school) => [school.id,school.name]))
  const linksByVendor = new Map<string, any[]>()
  for (const link of campusLinks || []) linksByVendor.set(link.vendor_id,[...(linksByVendor.get(link.vendor_id)||[]),link])
  const docMap = new Map<string,any>()
  for (const doc of documents || []) if (!docMap.has(doc.vendor_id)) docMap.set(doc.vendor_id,doc)

  const signedDocs = new Map<string,string>()
  if (canIdentity) {
    for (const doc of documents || []) {
      if (!doc.storage_path || signedDocs.has(doc.vendor_id)) continue
      const { data } = await supabase.storage.from('verification-documents').createSignedUrl(doc.storage_path,300)
      if (data?.signedUrl) signedDocs.set(doc.vendor_id,data.signedUrl)
    }
  }

  const pendingIdentity = (vendors || []).filter((vendor) => ['pending','under_review'].includes(vendor.verification_status))
  const vendorProfileIds = new Set((vendors || []).map((vendor) => vendor.id))
  const incompleteVendorAccounts = context.isGlobalAdmin
    ? (vendorAccounts || []).filter((account:any) => !vendorProfileIds.has(account.id))
    : []
  const completedVendorProfiles = (vendors || []).filter((vendor) => Boolean(vendor.onboarding_completed_at))
  const identityApproved = (vendors || []).filter((vendor) => vendor.verification_status === 'approved')
  const campusApprovedIds = new Set((campusLinks || []).filter((link) => link.status === 'approved').map((link) => link.vendor_id))
  const studentVisibleIds = new Set((vendors || []).filter((vendor) => {
    const suspensionExpired = vendor.marketplace_status === 'suspended' && vendor.suspended_until && new Date(vendor.suspended_until) <= new Date()
    const safetyAllowed = vendor.marketplace_status === 'active' || Boolean(suspensionExpired)
    return Boolean(vendor.onboarding_completed_at) && vendor.verification_status === 'approved' && campusApprovedIds.has(vendor.id) && safetyAllowed
  }).map((vendor) => vendor.id))
  const registeredVendorCount = canViewIncompleteVendorAccounts ? (vendorAccounts?.length || 0) : (vendors?.length || 0)
  const authEmailMap = new Map<string,string>()
  if (canViewIncompleteVendorAccounts && incompleteVendorAccounts.length) {
    const adminClient = createAdminClient()
    const { data: authUsers } = await adminClient.auth.admin.listUsers({ page: 1, perPage: 1000 })
    for (const user of authUsers?.users || []) {
      if (user.email) authEmailMap.set(user.id, user.email)
    }
  }

  return <>
    <header className="admin-topbar"><div><span className="admin-pill"><Store size={15}/> Vendor trust</span><h1>Vendors</h1><p>Vendor identity and campus approval are deliberately separate. Open Vendor 360 for connected storefront, reputation and safety context.</p></div></header>
    {params.success ? <div className="admin-success">{params.success}</div> : null}{params.error ? <div className="admin-error">{params.error}</div> : null}
    <section className="admin-grid">
      <article className="admin-stat"><span>Registered Vendor accounts</span><strong>{registeredVendorCount}</strong><small>{context.isGlobalAdmin ? 'All Vendor-type accounts, including incomplete registrations.' : 'Vendor profiles visible to your school scope.'}</small></article>
      <article className="admin-stat"><span>Completed profiles</span><strong>{completedVendorProfiles.length}</strong><small>Vendors that completed business onboarding.</small></article>
      <article className="admin-stat"><span>Identity queue</span><strong>{pendingIdentity.length}</strong><small>Pending or under-review Vendor identity records.</small></article>
      <article className="admin-stat"><span>Campus requests</span><strong>{(campusLinks || []).filter((x)=>x.status==='pending').length}</strong><small>School-by-school visibility requests.</small></article>
    </section>

    <section className="admin-section"><div className="admin-section-head"><div><h2>Vendor registration funnel</h2><p>Understand where Vendor accounts are in the journey without treating incomplete registration as a failed verification.</p></div><ShieldCheck size={20}/></div><div className="admin-grid">
      <article className="admin-stat"><span>Accounts</span><strong>{registeredVendorCount}</strong><small>Vendor account type selected.</small></article>
      <article className="admin-stat"><span>Business profile started</span><strong>{vendors?.length || 0}</strong><small>Vendor profile record exists in your scope.</small></article>
      <article className="admin-stat"><span>Onboarding complete</span><strong>{completedVendorProfiles.length}</strong><small>Business setup completed.</small></article>
      <article className="admin-stat"><span>Identity approved</span><strong>{identityApproved.length}</strong><small>Global identity approval passed.</small></article>
      <article className="admin-stat"><span>Campus approved</span><strong>{campusApprovedIds.size}</strong><small>At least one campus approval passed.</small></article>
      <article className="admin-stat"><span>Student-visible</span><strong>{studentVisibleIds.size}</strong><small>Passes onboarding, identity, campus and safety gates.</small></article>
    </div></section>

    {canViewIncompleteVendorAccounts ? <section className="admin-section"><div className="admin-section-head"><div><h2>Incomplete Vendor registrations</h2><p>Vendor accounts that exist but have not created a business profile yet. Keep these separate from verification failures.</p></div>{incompleteVendorAccounts.length ? <form action={sendOperationalReminder}><input type="hidden" name="reminder_type" value="vendor_onboarding_incomplete"/><input type="hidden" name="return_to" value="/control-center/vendors"/><button className="admin-action primary">Remind all</button></form> : null}</div><div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>Account</th><th>Email</th><th>Joined</th><th>State</th><th>Action</th></tr></thead><tbody>{incompleteVendorAccounts.map((account:any)=><tr key={account.id}><td><span className="admin-name">{[account.first_name,account.last_name].filter(Boolean).join(' ') || 'Vendor account'}</span><span className="admin-sub">{account.id}</span></td><td>{authEmailMap.get(account.id) ? <a className="admin-link" href={`mailto:${authEmailMap.get(account.id)}`}>{authEmailMap.get(account.id)}</a> : 'Email unavailable'}</td><td>{account.created_at ? new Date(account.created_at).toLocaleString() : '—'}</td><td><span className="status-badge status-reviewing">onboarding incomplete</span></td><td><form action={sendOperationalReminder}><input type="hidden" name="reminder_type" value="vendor_onboarding_incomplete"/><input type="hidden" name="target_user_id" value={account.id}/><input type="hidden" name="return_to" value="/control-center/vendors"/><button className="admin-action">Send reminder</button></form></td></tr>)}{!incompleteVendorAccounts.length?<tr><td colSpan={5}><div className="empty-admin">No incomplete Vendor registrations.</div></td></tr>:null}</tbody></table></div></section> : null}

    <section className="admin-section"><div className="admin-section-head"><div><h2>Vendor review board</h2><p>School admins can manage only campus access. Global verification admins control vendor identity.</p></div></div><div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>Vendor</th><th>Identity</th><th>Campus access</th><th>Evidence</th><th>Actions</th></tr></thead><tbody>
      {(vendors || []).map((vendor) => {
        const links = linksByVendor.get(vendor.id) || []
        const doc = docMap.get(vendor.id)
        return <tr key={vendor.id}>
          <td><Link className="admin-name admin-link" href={`/control-center/vendors/${vendor.id}`}>{vendor.business_name}</Link><span className="admin-sub">{vendor.location_text || 'Location not set'}</span><span className="admin-sub">{vendor.business_email || vendor.whatsapp_number || vendor.slug}</span></td>
          <td><span className={`status-badge status-${vendor.verification_status}`}>{vendor.verification_status.replaceAll('_',' ')}</span><span className="admin-sub">{vendor.onboarding_completed_at ? 'Business setup completed' : 'Setup incomplete'}</span></td>
          <td>{links.length ? links.map((link) => <div key={link.institution_id} style={{marginBottom:8}}><span className="admin-name"><Building2 size={13}/> {schoolMap.get(link.institution_id) || 'School'}</span> <span className={`status-badge status-${link.status}`}>{link.status}</span>{link.review_note ? <span className="admin-sub">{link.review_note}</span> : null}</div>) : <span className="admin-sub">No campus request yet</span>}</td>
          <td>{doc ? <><span className="admin-sub"><FileCheck2 size={13}/> {doc.document_type.replaceAll('_',' ')}</span>{signedDocs.get(vendor.id) ? <a className="admin-link admin-sub" href={signedDocs.get(vendor.id)} target="_blank" rel="noreferrer">Open private evidence</a> : <span className="admin-sub">Identity evidence restricted to global verification staff</span>}</> : <span className="admin-sub">No evidence visible</span>}</td>
          <td><div className="admin-form" style={{minWidth:260}}><Link className="admin-action" href={`/control-center/vendors/${vendor.id}`}>Open Vendor 360</Link>
            {canIdentity ? <form action={reviewVendorIdentity} className="admin-form"><input type="hidden" name="vendor_id" value={vendor.id}/><div className="admin-field"><input name="note" placeholder="Identity review note" maxLength={800}/></div><div className="admin-actions"><button className="admin-action success" name="decision" value="approve">Approve identity</button><button className="admin-action danger" name="decision" value="reject">Reject</button>{vendor.verification_status==='approved'?<button className="admin-action danger" name="decision" value="suspend">Suspend</button>:null}</div></form> : null}
            {links.map((link) => <form action={reviewVendorCampus} className="admin-form" key={`${vendor.id}-${link.institution_id}`}><input type="hidden" name="vendor_id" value={vendor.id}/><input type="hidden" name="institution_id" value={link.institution_id}/><div className="admin-field"><input name="note" placeholder={`${schoolMap.get(link.institution_id) || 'Campus'} note`} maxLength={800}/></div><div className="admin-actions"><button className="admin-action success" name="decision" value="approve">Approve campus</button><button className="admin-action danger" name="decision" value="reject">Reject campus</button>{link.status==='approved'?<button className="admin-action danger" name="decision" value="suspend">Suspend</button>:null}</div></form>)}
          </div></td>
        </tr>
      })}
      {!vendors?.length ? <tr><td colSpan={5}><div className="empty-admin">No vendor profiles are visible in your admin scope.</div></td></tr> : null}
    </tbody></table></div></section>
  </>
}
