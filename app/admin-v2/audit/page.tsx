import { notFound } from 'next/navigation'
import { Activity, ShieldCheck } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { requireAdminContext } from '../lib'

export default async function AuditPage() {
  const context = await requireAdminContext()
  if (!context.isGlobalAdmin) notFound()
  const supabase = await createClient()
  const { data: logs } = await supabase.from('audit_logs').select('id,actor_id,action,entity_type,entity_id,metadata,created_at').order('created_at',{ascending:false}).limit(200)

  return <>
    <header className="admin-topbar"><div><span className="admin-pill"><Activity size={15}/> Audit trail</span><h1>Platform audit</h1><p>Important trust and admin actions are written to an append-only operational log.</p></div></header>
    <section className="admin-section"><div className="admin-section-head"><div><h2>Recent admin activity</h2><p>Latest 200 auditable control-plane actions.</p></div></div><div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>When</th><th>Action</th><th>Entity</th><th>Actor</th><th>Metadata</th></tr></thead><tbody>{(logs || []).map((log)=><tr key={log.id}><td>{new Date(log.created_at).toLocaleString()}</td><td><span className="admin-name">{log.action.replaceAll('_',' ')}</span></td><td>{log.entity_type}<span className="admin-sub">{log.entity_id || '—'}</span></td><td><span className="admin-sub">{log.actor_id || 'system'}</span></td><td><span className="admin-sub">{JSON.stringify(log.metadata || {})}</span></td></tr>)}{!logs?.length?<tr><td colSpan={5}><div className="empty-admin">No audit events yet.</div></td></tr>:null}</tbody></table></div></section>
  </>
}
