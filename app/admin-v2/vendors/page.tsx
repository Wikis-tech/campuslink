import Link from 'next/link'
import { Building2, FileCheck2, Search, ShieldCheck, Store } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { createAdminClient } from '@/lib/supabase/admin'
import { canReviewVendorIdentity, requireAdminContext } from '../lib'
import { reviewVendorCampus, reviewVendorIdentity } from '../actions'
import { sendOperationalReminder } from '../communications/actions'

export default async function VendorsAdminPage({
  searchParams,
}: {
  searchParams: Promise<{ success?: string; error?: string; status?: string; school?: string; q?: string }>
}) {
  const params = await searchParams
  const context = await requireAdminContext()
  const supabase = await createClient()
  const canIdentity = canReviewVendorIdentity(context.globalRole)
  const canViewIncompleteVendorAccounts = ['super_admin','operations_admin'].includes(context.globalRole || '')

  const { data: vendorAccounts } = canViewIncompleteVendorAccounts
    ? await supabase.from('profiles').select('id,first_name,last_name,created_at').eq('account_type','vendor').order('created_at',{ascending:false}).limit(500)
    : { data: [] as any[] }

  const { data: vendors } = await supabase
    .from('vendor_profiles')
    .select('id,business_name,slug,description,business_email,whatsapp_number,location_text,verification_status,marketplace_status,suspended_until,onboarding_completed_at,created_at')
    .order('created_at',{ascending:false})
    .limit(500)

  const vendorIds = (vendors || []).map((row) => row.id)
  const [{ data: campusLinks }, { data: documents }] = vendorIds.length ? await Promise.all([
    supabase.from('vendor_institutions').select('vendor_id,institution_id,status,is_primary,review_note,created_at').in('vendor_id',vendorIds),
    supabase.from('vendor_documents').select('id,vendor_id,document_type,storage_path,status,created_at').in('vendor_id',vendorIds).order('created_at',{ascending:false}),
  ]) : [{data:[] as any[]},{data:[] as any[]}]

  const institutionIds = Array.from(new Set((campusLinks || []).map((row) => row.institution_id))) as string[]
  const { data: institutions } = institutionIds.length
    ? await supabase.from('institutions').select('id,name').in('id',institutionIds).order('name')
    : { data: [] as any[] }

  const schoolMap = new Map((institutions || []).map((school) => [school.id,school.name]))
  const linksByVendor = new Map<string,any[]>()
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

  const vendorProfileIds = new Set((vendors || []).map((vendor) => vendor.id))
  const incompleteVendorAccounts = canViewIncompleteVendorAccounts
    ? (vendorAccounts || []).filter((account:any) => !vendorProfileIds.has(account.id))
    : []

  const isStudentVisible = (vendor:any) => {
    const links = linksByVendor.get(vendor.id) || []
    const campusApproved = links.some((link) => link.status === 'approved')
    const suspensionExpired = vendor.marketplace_status === 'suspended' && vendor.suspended_until && new Date(vendor.suspended_until) <= new Date()
    const safetyAllowed = vendor.marketplace_status === 'active' || Boolean(suspensionExpired)
    return Boolean(vendor.onboarding_completed_at) && vendor.verification_status === 'approved' && campusApproved && safetyAllowed
  }

  const vendorStateMatches = (vendor:any,status:string) => {
    const links = linksByVendor.get(vendor.id) || []
    if (status === 'awaiting_identity') return ['pending','under_review'].includes(vendor.verification_status)
    if (status === 'identity_approved') return vendor.verification_status === 'approved'
    if (status === 'awaiting_campus') return links.some((link) => link.status === 'pending')
    if (status === 'campus_approved') return links.some((link) => link.status === 'approved')
    if (status === 'under_review') return vendor.marketplace_status === 'under_review'
    if (status === 'suspended') return vendor.marketplace_status === 'suspended'
    if (status === 'student_visible') return isStudentVisible(vendor)
    return true
  }

  const allVendors = vendors || []
  const completedVendorProfiles = allVendors.filter((vendor) => Boolean(vendor.onboarding_completed_at))
  const identityApproved = allVendors.filter((vendor) => vendor.verification_status === 'approved')
  const awaitingIdentity = allVendors.filter((vendor) => ['pending','under_review'].includes(vendor.verification_status))
  const awaitingCampus = allVendors.filter((vendor) => (linksByVendor.get(vendor.id)||[]).some((link) => link.status === 'pending'))
  const campusApproved = allVendors.filter((vendor) => (linksByVendor.get(vendor.id)||[]).some((link) => link.status === 'approved'))
  const underReview = allVendors.filter((vendor) => vendor.marketplace_status === 'under_review')
  const suspended = allVendors.filter((vendor) => vendor.marketplace_status === 'suspended')
  const studentVisible = allVendors.filter(isStudentVisible)

  const selectedStatus = params.status || 'all'
  const selectedSchool = params.school || ''
  const q = String(params.q || '').trim().toLowerCase()

  const filteredVendors = allVendors.filter((vendor) => {
    if (selectedStatus !== 'all' && selectedStatus !== 'incomplete' && !vendorStateMatches(vendor,selectedStatus)) return false
    if (selectedStatus === 'incomplete') return false
    const links = linksByVendor.get(vendor.id) || []
    if (selectedSchool && !links.some((link) => link.institution_id === selectedSchool)) return false
    if (q) {
      const haystack = [vendor.business_name,vendor.business_email,vendor.whatsapp_number,vendor.slug,vendor.location_text].filter(Boolean).join(' ').toLowerCase()
      if (!haystack.includes(q)) return false
    }
    return true
  })

  const registeredVendorCount = canViewIncompleteVendorAccounts ? (vendorAccounts?.length || 0) : allVendors.length
  const authEmailMap = new Map<string,string>()
  if (canViewIncompleteVendorAccounts && incompleteVendorAccounts.length) {
    const adminClient = createAdminClient()
    const { data: authUsers } = await adminClient.auth.admin.listUsers({ page:1, perPage:1000 })
    for (const user of authUsers.users || []) if (user.email) authEmailMap.set(user.id,user.email)
  }

  const filterHref = (status:string) => {
    const query = new URLSearchParams()
    if (status !== 'all') query.set('status',status)
    if (selectedSchool) query.set('school',selectedSchool)
    if (params.q) query.set('q',params.q)
    const suffix = query.toString()
    return '/control-center/vendors' + (suffix ? '?' + suffix : '')
  }

  return <>
    <header className="admin-topbar"><div><span className="admin-pill"><Store size={15}/> Vendor management</span><h1>Vendors</h1><p>See the Vendor journey clearly: registration, identity, Campus access, marketplace safety and Student visibility are separate states.</p></div></header>
    {params.success ? <div className="admin-success">{params.success}</div> : null}
    {params.error ? <div className="admin-error">{params.error}</div> : null}

    <section className="admin-grid">
      <article className="admin-stat"><span>Registered Vendor accounts</span><strong>{registeredVendorCount}</strong><small>{context.isGlobalAdmin ? 'Includes accounts that have not created a business profile.' : 'Vendor profiles visible to your school scope.'}</small></article>
      <article className="admin-stat"><span>Completed profiles</span><strong>{completedVendorProfiles.length}</strong><small>Business onboarding completed.</small></article>
      <article className="admin-stat"><span>Approval queues</span><strong>{awaitingIdentity.length + awaitingCampus.length}</strong><small>{awaitingIdentity.length} identity · {awaitingCampus.length} Campus.</small></article>
      <article className="admin-stat"><span>Student-visible</span><strong>{studentVisible.length}</strong><small>Passes onboarding, identity, Campus and safety gates.</small></article>
    </section>

    <section className="admin-section">
      <div className="admin-section-head"><div><h2>Vendor directory</h2><p>Filter by the actual operational state instead of translating database fields in your head.</p></div><Search size={19}/></div>
      <div className="admin-section-body">
        <div className="admin-filter-bar">
          <Link className={selectedStatus === 'all' ? 'active' : ''} href={filterHref('all')}>All ({allVendors.length})</Link>
          {canViewIncompleteVendorAccounts ? <Link className={selectedStatus === 'incomplete' ? 'active' : ''} href={filterHref('incomplete')}>Incomplete registration ({incompleteVendorAccounts.length})</Link> : null}
          <Link className={selectedStatus === 'awaiting_identity' ? 'active' : ''} href={filterHref('awaiting_identity')}>Awaiting identity ({awaitingIdentity.length})</Link>
          <Link className={selectedStatus === 'identity_approved' ? 'active' : ''} href={filterHref('identity_approved')}>Identity approved ({identityApproved.length})</Link>
          <Link className={selectedStatus === 'awaiting_campus' ? 'active' : ''} href={filterHref('awaiting_campus')}>Awaiting Campus ({awaitingCampus.length})</Link>
          <Link className={selectedStatus === 'campus_approved' ? 'active' : ''} href={filterHref('campus_approved')}>Campus approved ({campusApproved.length})</Link>
          <Link className={selectedStatus === 'under_review' ? 'active' : ''} href={filterHref('under_review')}>Under review ({underReview.length})</Link>
          <Link className={selectedStatus === 'suspended' ? 'active' : ''} href={filterHref('suspended')}>Suspended ({suspended.length})</Link>
          <Link className={selectedStatus === 'student_visible' ? 'active' : ''} href={filterHref('student_visible')}>Student-visible ({studentVisible.length})</Link>
        </div>
        <form method="get" className="admin-filter-form">
          {selectedStatus !== 'all' ? <input type="hidden" name="status" value={selectedStatus}/> : null}
          <div className="admin-field"><label>School</label><select name="school" defaultValue={selectedSchool}><option value="">All visible schools</option>{(institutions || []).map((school) => <option key={school.id} value={school.id}>{school.name}</option>)}</select></div>
          <div className="admin-field"><label>Search</label><input name="q" defaultValue={params.q || ''} placeholder="Business, email, WhatsApp or location"/></div>
          <button className="admin-action primary" type="submit">Apply filters</button>
          {(selectedSchool || params.q) ? <Link className="admin-action" href={filterHref(selectedStatus)}>Clear search</Link> : null}
        </form>
      </div>

      {selectedStatus === 'incomplete' && canViewIncompleteVendorAccounts ? <div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>Account</th><th>Email</th><th>Joined</th><th>State</th><th>Action</th></tr></thead><tbody>
        {incompleteVendorAccounts.map((account:any) => <tr key={account.id}><td><span className="admin-name">{[account.first_name,account.last_name].filter(Boolean).join(' ') || 'Vendor account'}</span><span className="admin-sub">{account.id}</span></td><td>{authEmailMap.get(account.id) ? <a className="admin-link" href={'mailto:' + authEmailMap.get(account.id)}>{authEmailMap.get(account.id)}</a> : 'Email unavailable'}</td><td>{account.created_at ? new Date(account.created_at).toLocaleString() : '—'}</td><td><span className="status-badge status-reviewing">onboarding incomplete</span></td><td><form action={sendOperationalReminder}><input type="hidden" name="reminder_type" value="vendor_onboarding_incomplete"/><input type="hidden" name="target_user_id" value={account.id}/><input type="hidden" name="return_to" value="/control-center/vendors?status=incomplete"/><button className="admin-action primary">Send reminder</button></form></td></tr>)}
        {!incompleteVendorAccounts.length ? <tr><td colSpan={5}><div className="empty-admin">No incomplete Vendor registrations.</div></td></tr> : null}
      </tbody></table></div> : <div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>Vendor</th><th>Identity</th><th>Campus access</th><th>Marketplace</th><th>Action</th></tr></thead><tbody>
        {filteredVendors.map((vendor) => {
          const links = linksByVendor.get(vendor.id) || []
          return <tr key={vendor.id}>
            <td><Link className="admin-name admin-link" href={'/control-center/vendors/' + vendor.id}>{vendor.business_name}</Link><span className="admin-sub">{vendor.location_text || 'Location not set'}</span><span className="admin-sub">{vendor.business_email || vendor.whatsapp_number || vendor.slug}</span></td>
            <td><span className={'status-badge status-' + vendor.verification_status}>{vendor.verification_status.replaceAll('_',' ')}</span><span className="admin-sub">{vendor.onboarding_completed_at ? 'Business setup completed' : 'Setup incomplete'}</span></td>
            <td>{links.length ? links.map((link) => <div key={link.institution_id} style={{marginBottom:6}}><span className="admin-name"><Building2 size={13}/> {schoolMap.get(link.institution_id) || 'School'}</span> <span className={'status-badge status-' + link.status}>{link.status}</span></div>) : <span className="admin-sub">No Campus request yet</span>}</td>
            <td><span className={'status-badge status-' + vendor.marketplace_status}>{vendor.marketplace_status.replaceAll('_',' ')}</span>{isStudentVisible(vendor) ? <span className="admin-sub"><ShieldCheck size={13}/> Student-visible</span> : <span className="admin-sub">Not currently Student-visible</span>}</td>
            <td><Link className="admin-action primary" href={'/control-center/vendors/' + vendor.id}>Open Vendor 360</Link></td>
          </tr>
        })}
        {!filteredVendors.length ? <tr><td colSpan={5}><div className="empty-admin">No Vendor profiles match the current filters.</div></td></tr> : null}
      </tbody></table></div>}
    </section>

    {canViewIncompleteVendorAccounts && selectedStatus !== 'incomplete' ? <section className="admin-section"><div className="admin-section-head"><div><h2>Incomplete Vendor registrations</h2><p>Accounts without a business profile remain separate from identity failures.</p></div>{incompleteVendorAccounts.length ? <form action={sendOperationalReminder}><input type="hidden" name="reminder_type" value="vendor_onboarding_incomplete"/><input type="hidden" name="return_to" value="/control-center/vendors"/><button className="admin-action primary">Remind all</button></form> : null}</div><div className="admin-section-body"><div className="admin-note">{incompleteVendorAccounts.length} account{incompleteVendorAccounts.length === 1 ? '' : 's'} currently incomplete. Open the “Incomplete registration” filter to see emails and individual reminder actions.</div></div></section> : null}

    <section className="admin-section">
      <div className="admin-section-head"><div><h2>Approval workbench</h2><p>School Admins manage Campus access only. Global verification roles control Vendor identity.</p></div></div>
      <div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>Vendor</th><th>Identity evidence</th><th>Campus requests</th><th>Actions</th></tr></thead><tbody>
        {allVendors.filter((vendor) => ['pending','under_review'].includes(vendor.verification_status) || (linksByVendor.get(vendor.id)||[]).some((link) => link.status === 'pending')).map((vendor) => {
          const links = linksByVendor.get(vendor.id) || []
          const doc = docMap.get(vendor.id)
          return <tr key={vendor.id}>
            <td><Link className="admin-name admin-link" href={'/control-center/vendors/' + vendor.id}>{vendor.business_name}</Link><span className="admin-sub">{vendor.business_email || vendor.whatsapp_number || vendor.slug}</span></td>
            <td>{doc ? <><span className="admin-sub"><FileCheck2 size={13}/> {doc.document_type.replaceAll('_',' ')}</span>{signedDocs.get(vendor.id) ? <a className="admin-link admin-sub" href={signedDocs.get(vendor.id)} target="_blank" rel="noreferrer">Open private evidence</a> : <span className="admin-sub">Identity evidence restricted to global verification staff</span>}</> : <span className="admin-sub">No evidence visible</span>}</td>
            <td>{links.filter((link) => link.status === 'pending').map((link) => <div key={link.institution_id}><span className="admin-name">{schoolMap.get(link.institution_id) || 'School'}</span><span className="admin-sub">Campus approval waiting</span></div>)}</td>
            <td><div className="admin-form" style={{minWidth:260}}>
              {canIdentity && ['pending','under_review'].includes(vendor.verification_status) ? <form action={reviewVendorIdentity} className="admin-form"><input type="hidden" name="vendor_id" value={vendor.id}/><div className="admin-field"><input name="note" placeholder="Identity review note" maxLength={800}/></div><div className="admin-actions"><button className="admin-action success" name="decision" value="approve">Approve identity</button><button className="admin-action danger" name="decision" value="reject">Reject</button></div></form> : null}
              {links.filter((link) => link.status === 'pending').map((link) => <form action={reviewVendorCampus} className="admin-form" key={vendor.id + '-' + link.institution_id}><input type="hidden" name="vendor_id" value={vendor.id}/><input type="hidden" name="institution_id" value={link.institution_id}/><div className="admin-field"><input name="note" placeholder={(schoolMap.get(link.institution_id) || 'Campus') + ' note'} maxLength={800}/></div><div className="admin-actions"><button className="admin-action success" name="decision" value="approve">Approve Campus</button><button className="admin-action danger" name="decision" value="reject">Reject Campus</button></div></form>)}
            </div></td>
          </tr>
        })}
        {!allVendors.some((vendor) => ['pending','under_review'].includes(vendor.verification_status) || (linksByVendor.get(vendor.id)||[]).some((link) => link.status === 'pending')) ? <tr><td colSpan={4}><div className="empty-admin">No Vendor approval work is waiting in your scope.</div></td></tr> : null}
      </tbody></table></div>
    </section>
  </>
}
