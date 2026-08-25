import { LifeBuoy, ShieldAlert } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { requireAdminContext } from '../lib'
import { updateComplaint } from '../actions'

export default async function ReportsAdminPage({ searchParams }: { searchParams: Promise<{ success?: string; error?: string }> }) {
  const params = await searchParams
  await requireAdminContext()
  const supabase = await createClient()

  const { data: complaints } = await supabase
    .from('complaints')
    .select('id,reporter_id,vendor_id,title,description,status,created_at,updated_at')
    .order('created_at',{ascending:false})
    .limit(100)

  const vendorIds = Array.from(new Set((complaints || []).map((row)=>row.vendor_id).filter(Boolean))) as string[]
  const reporterIds = Array.from(new Set((complaints || []).map((row)=>row.reporter_id).filter(Boolean))) as string[]
  const [{data:vendors},{data:reporters}] = await Promise.all([
    vendorIds.length ? supabase.from('vendor_profiles').select('id,business_name').in('id',vendorIds) : Promise.resolve({data:[] as any[]}),
    reporterIds.length ? supabase.from('profiles').select('id,first_name,last_name').in('id',reporterIds) : Promise.resolve({data:[] as any[]}),
  ])
  const vendorMap = new Map((vendors || []).map((v)=>[v.id,v.business_name]))
  const reporterMap = new Map((reporters || []).map((p)=>[p.id,[p.first_name,p.last_name].filter(Boolean).join(' ') || 'Student']))
  const open = (complaints || []).filter((row)=>['open','reviewing'].includes(row.status)).length

  return <>
    <header className="admin-topbar"><div><span className="admin-pill"><LifeBuoy size={15}/> Safety operations</span><h1>Reports & complaints</h1><p>Keep user reports private, move them through a clear review lifecycle, and preserve an audit trail.</p></div></header>
    {params.success ? <div className="admin-success">{params.success}</div> : null}{params.error ? <div className="admin-error">{params.error}</div> : null}
    <section className="admin-grid"><article className="admin-stat"><span>Open / reviewing</span><strong>{open}</strong><small>Cases needing action.</small></article><article className="admin-stat"><span>Total in view</span><strong>{complaints?.length || 0}</strong><small>Latest 100 reports visible to your role.</small></article><article className="admin-stat"><span>Resolved / closed</span><strong>{(complaints?.length || 0)-open}</strong><small>Completed support outcomes.</small></article><article className="admin-stat"><span>Privacy</span><strong><ShieldAlert size={28}/></strong><small>Vendors cannot delete or hide reports filed against them.</small></article></section>
    <section className="admin-section"><div className="admin-section-head"><div><h2>Moderation queue</h2><p>School support admins see only reports tied to vendors serving their assigned institutions.</p></div></div><div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>Case</th><th>Vendor</th><th>Reporter</th><th>Status</th><th>Update</th></tr></thead><tbody>
      {(complaints || []).map((report)=><tr key={report.id}><td><span className="admin-name">{report.title}</span><span className="admin-sub">{report.description}</span><span className="admin-sub">{new Date(report.created_at).toLocaleString()}</span></td><td>{report.vendor_id ? vendorMap.get(report.vendor_id) || 'Vendor' : 'General report'}</td><td>{reporterMap.get(report.reporter_id) || 'User'}</td><td><span className={`status-badge status-${report.status}`}>{report.status}</span></td><td><form action={updateComplaint} className="admin-actions"><input type="hidden" name="complaint_id" value={report.id}/><select name="status" defaultValue={report.status} className="admin-action"><option value="open">Open</option><option value="reviewing">Reviewing</option><option value="resolved">Resolved</option><option value="closed">Closed</option></select><button className="admin-action primary" type="submit">Save</button></form></td></tr>)}
      {!complaints?.length?<tr><td colSpan={5}><div className="empty-admin">No reports are visible in your scope.</div></td></tr>:null}
    </tbody></table></div></section>
  </>
}
