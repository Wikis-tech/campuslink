import Link from 'next/link'
import { AlertTriangle, BadgeCheck, FileWarning, ShieldCheck, Siren } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { checkPhase5gReadiness } from '@/lib/phase5g-readiness'
import { requireAdminContext } from '../lib'
import { moderateVendorResponse, setVendorSafetyStatus, updateSafetyCase } from './actions'

const categoryLabel: Record<string,string> = { fraud_scam:'Fraud / scam', harassment:'Harassment', fake_product:'Fake product', misrepresentation:'Misrepresentation', unsafe_behavior:'Unsafe behaviour', spam:'Spam', prohibited_item:'Prohibited item', other:'Other' }

export default async function AdminSafetyPage({ searchParams }: { searchParams: Promise<{ ok?: string; error?: string }> }) {
  const params = await searchParams
  const context = await requireAdminContext()
  const canControlMarketplace = ['super_admin','operations_admin','support_admin','verification_admin'].includes(context.globalRole || '')
  const supabase = await createClient()
  const readiness = await checkPhase5gReadiness(supabase)

  if (!readiness.ready) {
    return <>
      <header className="admin-topbar"><div><span className="admin-pill"><ShieldCheck size={15}/> Trust & safety</span><h1>Safety investigations</h1><p>Phase 5G is in the application, but its live database dependencies are not fully available yet.</p></div></header>
      <div className="admin-error phase5g-readiness-error">
        <strong>Phase 5G database setup is incomplete.</strong>
        <p>{readiness.issues.join(' ')}</p>
        <p>Apply these migrations in order, then refresh this page:</p>
        <code>202609170008_phase5g_trust_reviews_safety.sql</code>
        <code>202609170009_phase5g_interaction_integrity.sql</code>
      </div>
    </>
  }

  const { data: complaints } = await supabase.from('complaints').select('id,reporter_id,vendor_id,title,description,category,severity,status,assigned_to,resolution_code,resolved_at,created_at,updated_at').order('created_at',{ascending:false}).limit(100)
  const vendorIds = Array.from(new Set((complaints || []).map((r:any)=>r.vendor_id).filter(Boolean))) as string[]
  const reporterIds = Array.from(new Set((complaints || []).map((r:any)=>r.reporter_id).filter(Boolean))) as string[]
  const [{data:vendors},{data:students},{data:reviews},{data:notes}] = await Promise.all([
    vendorIds.length ? supabase.from('vendor_profiles').select('id,business_name,marketplace_status,risk_report_count,suspended_until').in('id',vendorIds) : Promise.resolve({data:[] as any[]}),
    reporterIds.length ? supabase.from('profiles').select('id,first_name,last_name').in('id',reporterIds) : Promise.resolve({data:[] as any[]}),
    vendorIds.length ? supabase.from('reviews').select('id,vendor_id,rating,comment,contact_verified_at,vendor_response,vendor_response_status,created_at').in('vendor_id',vendorIds).order('created_at',{ascending:false}).limit(200) : Promise.resolve({data:[] as any[]}),
    supabase.from('safety_case_notes').select('id,complaint_id,note,created_at').order('created_at',{ascending:false}).limit(200),
  ])
  const vendorMap = new Map((vendors || []).map((v:any)=>[v.id,v]))
  const studentMap = new Map((students || []).map((s:any)=>[s.id,[s.first_name,s.last_name].filter(Boolean).join(' ') || 'Student']))
  const noteMap = new Map<string,any[]>()
  for (const note of notes || []) { const list=noteMap.get((note as any).complaint_id)||[]; list.push(note); noteMap.set((note as any).complaint_id,list) }
  const openCases = (complaints || []).filter((r:any)=>['open','reviewing'].includes(r.status))
  const highCases = openCases.filter((r:any)=>['high','critical'].includes(r.severity))
  const underReview = (vendors || []).filter((v:any)=>v.marketplace_status==='under_review').length

  const riskFor = (vendorId:string) => {
    const recent=(complaints || []).filter((r:any)=>r.vendor_id===vendorId && ['open','reviewing'].includes(r.status) && Date.now()-new Date(r.created_at).getTime()<=30*86400000)
    const high=recent.filter((r:any)=>['high','critical'].includes(r.severity)).length
    const burst=(reviews || []).filter((r:any)=>r.vendor_id===vendorId && Date.now()-new Date(r.created_at).getTime()<=24*3600000).length
    const band=high>=3||recent.length>=5?'critical':high>=1||recent.length>=3?'high':recent.length>=1||burst>=8?'medium':'low'
    return { band, reports:recent.length, high, burst }
  }

  return <>
    <header className="admin-topbar"><div><span className="admin-pill"><ShieldCheck size={15}/> Trust & safety</span><h1>Safety investigations</h1><p>Review evidence, record internal notes and control marketplace visibility without turning raw reports into public accusations.</p></div></header>
    {params.ok ? <div className="admin-success">{params.ok}</div> : null}{params.error ? <div className="admin-error">{params.error}</div> : null}

    <section className="admin-grid phase5g-admin-stats"><article className="admin-stat"><span>Open cases</span><strong>{openCases.length}</strong><small>Reports still being investigated.</small></article><article className="admin-stat"><span>High severity</span><strong>{highCases.length}</strong><small>Server-derived triage signal, not a final verdict.</small></article><article className="admin-stat"><span>Vendors under review</span><strong>{underReview}</strong><small>Hidden from Student discovery until reviewed.</small></article><article className="admin-stat"><span>Safety principle</span><strong><ShieldCheck size={28}/></strong><small>Payment never overrides marketplace safety.</small></article></section>

    <section className="admin-section"><div className="admin-section-head"><div><h2>Vendor risk signals</h2><p>Signals help prioritize investigation. They never automatically prove misconduct.</p></div></div><div className="phase5g-risk-grid">{(vendors || []).map((vendor:any)=>{ const risk=riskFor(vendor.id); const verified=(reviews || []).filter((r:any)=>r.vendor_id===vendor.id && r.contact_verified_at).length; return <article className="phase5g-risk-card" key={vendor.id}><div><span className={`phase5g-risk-band ${risk.band}`}>{risk.band}</span><h3>{vendor.business_name}</h3><p>{risk.reports} unresolved report{risk.reports===1?'':'s'} · {risk.high} high severity · {verified} verified-contact reviews</p>{risk.burst>=8?<small><AlertTriangle size={13}/> Unusual review burst: {risk.burst} reviews in 24h. Review manually.</small>:null}</div><Link className="admin-link" href={`/admin-v2/vendors/${vendor.id}`}>Open Vendor 360</Link>{canControlMarketplace ? <form action={setVendorSafetyStatus} className="phase5g-risk-actions"><input type="hidden" name="vendor_id" value={vendor.id}/><select name="status" defaultValue={vendor.marketplace_status}><option value="active">Active</option><option value="under_review">Under review</option><option value="suspended">Suspended</option></select><input type="number" name="suspension_days" min="1" max="365" placeholder="Days"/><input name="note" maxLength={1000} placeholder="Reason / internal decision note"/><button className="admin-action primary">Apply</button></form> : <div className="admin-note">Your role can investigate this Vendor but cannot change marketplace safety state.</div>}</article> })}{!vendors?.length?<div className="empty-admin">No Vendor safety signals are visible in your scope.</div>:null}</div></section>

    <section className="admin-section"><div className="admin-section-head"><div><h2>Case queue</h2><p>Category and severity are for triage. Investigate the actual context before acting.</p></div></div><div className="phase5g-case-list">{(complaints || []).map((report:any)=>{ const vendor=vendorMap.get(report.vendor_id); const caseNotes=noteMap.get(report.id)||[]; return <article className="phase5g-case-card" key={report.id}><div className="phase5g-case-head"><div><span className={`phase5g-severity ${report.severity}`}>{report.severity}</span><span className="phase5g-category">{categoryLabel[report.category] || 'Other'}</span><h3>{report.title}</h3><p>{report.description}</p></div><span className={`status-badge status-${report.status}`}>{report.status}</span></div><div className="phase5g-case-context"><span><FileWarning size={14}/> Vendor: {vendor ? <Link href={`/admin-v2/vendors/${vendor.id}`}>{vendor.business_name}</Link> : 'Unavailable'}</span><span>Reporter: <Link href={`/admin-v2/students/${report.reporter_id}`}>{studentMap.get(report.reporter_id) || 'Student'}</Link></span><span>{new Date(report.created_at).toLocaleString('en-NG')}</span></div>{caseNotes.length?<div className="phase5g-case-notes"><strong>Internal notes</strong>{caseNotes.slice(0,3).map((n:any)=><p key={n.id}>{n.note}<small>{new Date(n.created_at).toLocaleString('en-NG')}</small></p>)}</div>:null}<form action={updateSafetyCase} className="phase5g-case-form"><input type="hidden" name="complaint_id" value={report.id}/><select name="status" defaultValue={report.status}><option value="open">Open</option><option value="reviewing">Reviewing</option><option value="resolved">Resolved</option><option value="closed">Closed</option></select><input name="resolution" maxLength={120} placeholder="Resolution code / outcome (when closing)"/><textarea name="internal_note" maxLength={3000} placeholder="Private investigation note. Do not put unnecessary sensitive data here."/><button className="admin-action primary">Save investigation update</button></form></article> })}{!complaints?.length?<div className="empty-admin"><Siren size={24}/> No safety cases are visible in your scope.</div>:null}</div></section>

    <section className="admin-section"><div className="admin-section-head"><div><h2>Vendor response moderation</h2><p>Hide abusive Vendor responses without rewriting the underlying Student review.</p></div></div><div className="phase5g-response-moderation">{(reviews || []).filter((r:any)=>r.vendor_response).map((review:any)=><article key={review.id}><div><strong>{vendorMap.get(review.vendor_id)?.business_name || 'Vendor'}</strong><p>{review.vendor_response}</p><small>Student review: {review.rating}/5 · {review.comment || 'Rating only'}</small></div><form action={moderateVendorResponse}><input type="hidden" name="review_id" value={review.id}/><select name="status" defaultValue={review.vendor_response_status}><option value="published">Published</option><option value="hidden">Hidden</option></select><button className="admin-action">Save</button></form></article>)}{!(reviews || []).some((r:any)=>r.vendor_response)?<div className="empty-admin"><BadgeCheck size={22}/> No Vendor responses require review.</div>:null}</div></section>
  </>
}
