import { Building2, FileCheck2, ShieldCheck, Store } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { canReviewVendorIdentity, requireAdminContext } from '../lib'
import { reviewVendorCampus, reviewVendorIdentity } from '../actions'

export default async function VendorsAdminPage({ searchParams }: { searchParams: Promise<{ success?: string; error?: string }> }) {
  const params = await searchParams
  const context = await requireAdminContext()
  const supabase = await createClient()
  const canIdentity = canReviewVendorIdentity(context.globalRole)

  const { data: vendors } = await supabase
    .from('vendor_profiles')
    .select('id,business_name,slug,description,business_email,whatsapp_number,location_text,verification_status,onboarding_completed_at,created_at')
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

  return <>
    <header className="admin-topbar"><div><span className="admin-pill"><Store size={15}/> Vendor trust</span><h1>Vendors</h1><p>Vendor identity and campus approval are deliberately separate. A business is discoverable only when both are approved.</p></div></header>
    {params.success ? <div className="admin-success">{params.success}</div> : null}{params.error ? <div className="admin-error">{params.error}</div> : null}
    <section className="admin-grid">
      <article className="admin-stat"><span>Vendors in scope</span><strong>{vendors?.length || 0}</strong><small>Latest vendor profiles visible to your role.</small></article>
      <article className="admin-stat"><span>Identity queue</span><strong>{pendingIdentity.length}</strong><small>Global verification queue.</small></article>
      <article className="admin-stat"><span>Campus requests</span><strong>{(campusLinks || []).filter((x)=>x.status==='pending').length}</strong><small>School-by-school visibility requests.</small></article>
      <article className="admin-stat"><span>Trust model</span><strong><ShieldCheck size={28}/></strong><small>Identity approval + campus approval required.</small></article>
    </section>

    <section className="admin-section"><div className="admin-section-head"><div><h2>Vendor review board</h2><p>School admins can manage only campus access. Global verification admins control vendor identity.</p></div></div><div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>Vendor</th><th>Identity</th><th>Campus access</th><th>Evidence</th><th>Actions</th></tr></thead><tbody>
      {(vendors || []).map((vendor) => {
        const links = linksByVendor.get(vendor.id) || []
        const doc = docMap.get(vendor.id)
        return <tr key={vendor.id}>
          <td><span className="admin-name">{vendor.business_name}</span><span className="admin-sub">{vendor.location_text || 'Location not set'}</span><span className="admin-sub">{vendor.business_email || vendor.whatsapp_number || vendor.slug}</span></td>
          <td><span className={`status-badge status-${vendor.verification_status}`}>{vendor.verification_status.replaceAll('_',' ')}</span><span className="admin-sub">{vendor.onboarding_completed_at ? 'Business setup completed' : 'Setup incomplete'}</span></td>
          <td>{links.length ? links.map((link) => <div key={link.institution_id} style={{marginBottom:8}}><span className="admin-name"><Building2 size={13}/> {schoolMap.get(link.institution_id) || 'School'}</span> <span className={`status-badge status-${link.status}`}>{link.status}</span>{link.review_note ? <span className="admin-sub">{link.review_note}</span> : null}</div>) : <span className="admin-sub">No campus request yet</span>}</td>
          <td>{doc ? <><span className="admin-sub"><FileCheck2 size={13}/> {doc.document_type.replaceAll('_',' ')}</span>{signedDocs.get(vendor.id) ? <a className="admin-link admin-sub" href={signedDocs.get(vendor.id)} target="_blank" rel="noreferrer">Open private evidence</a> : <span className="admin-sub">Identity evidence restricted to global verification staff</span>}</> : <span className="admin-sub">No evidence visible</span>}</td>
          <td><div className="admin-form" style={{minWidth:260}}>
            {canIdentity ? <form action={reviewVendorIdentity} className="admin-form"><input type="hidden" name="vendor_id" value={vendor.id}/><div className="admin-field"><input name="note" placeholder="Identity review note" maxLength={800}/></div><div className="admin-actions"><button className="admin-action success" name="decision" value="approve">Approve identity</button><button className="admin-action danger" name="decision" value="reject">Reject</button>{vendor.verification_status==='approved'?<button className="admin-action danger" name="decision" value="suspend">Suspend</button>:null}</div></form> : null}
            {links.map((link) => <form action={reviewVendorCampus} className="admin-form" key={`${vendor.id}-${link.institution_id}`}><input type="hidden" name="vendor_id" value={vendor.id}/><input type="hidden" name="institution_id" value={link.institution_id}/><div className="admin-field"><input name="note" placeholder={`${schoolMap.get(link.institution_id) || 'Campus'} note`} maxLength={800}/></div><div className="admin-actions"><button className="admin-action success" name="decision" value="approve">Approve campus</button><button className="admin-action danger" name="decision" value="reject">Reject campus</button>{link.status==='approved'?<button className="admin-action danger" name="decision" value="suspend">Suspend</button>:null}</div></form>)}
          </div></td>
        </tr>
      })}
      {!vendors?.length ? <tr><td colSpan={5}><div className="empty-admin">No vendor profiles are visible in your admin scope.</div></td></tr> : null}
    </tbody></table></div></section>
  </>
}
