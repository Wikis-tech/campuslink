import Link from 'next/link'
import { notFound } from 'next/navigation'
import { AlertTriangle, BadgeCheck, Building2, MessageSquareText, Package, ShieldCheck, Store, Wrench } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { requireAdminContext } from '../../lib'

export default async function AdminVendorDetailPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  await requireAdminContext()
  const supabase = await createClient()

  const { data: vendor } = await supabase.from('vendor_profiles').select('id,business_name,slug,description,business_email,whatsapp_number,location_text,verification_status,marketplace_status,risk_report_count,suspended_until,suspension_reason,average_rating,review_count,onboarding_completed_at,created_at').eq('id', id).maybeSingle()
  if (!vendor) notFound()

  const [{ data: campusLinks }, { data: products }, { data: services }, { data: portfolio }, { data: reviews }, { data: reports }] = await Promise.all([
    supabase.from('vendor_institutions').select('institution_id,status,is_primary,review_note,created_at').eq('vendor_id', id),
    supabase.from('vendor_products').select('id,name,is_active,price_ngn,pricing_type,created_at').eq('vendor_id', id).order('created_at', { ascending: false }).limit(30),
    supabase.from('vendor_services').select('id,name,is_active,price_from,created_at').eq('vendor_id', id).order('created_at', { ascending: false }).limit(30),
    supabase.from('vendor_portfolio_items').select('id,title,is_active,created_at').eq('vendor_id', id).order('created_at', { ascending: false }).limit(30),
    supabase.from('reviews').select('id,student_id,rating,comment,status,created_at').eq('vendor_id', id).order('created_at', { ascending: false }).limit(30),
    supabase.from('complaints').select('id,reporter_id,title,status,created_at').eq('vendor_id', id).order('created_at', { ascending: false }).limit(30),
  ])

  const institutionIds = (campusLinks || []).map((link) => link.institution_id)
  const { data: institutions } = institutionIds.length ? await supabase.from('institutions').select('id,name').in('id', institutionIds) : { data: [] as any[] }
  const schoolMap = new Map((institutions || []).map((school) => [school.id, school.name]))
  const visibleCampusCount = (campusLinks || []).filter((link) => link.status === 'approved').length
  const safetyBlocked = ['under_review','suspended'].includes(vendor.marketplace_status)

  return <>
    <header className="admin-topbar"><div><span className="admin-pill"><Store size={15}/> Vendor 360</span><h1>{vendor.business_name}</h1><p>One connected view of identity, campus access, storefront inventory, reputation and safety state.</p></div><div className="admin-actions"><Link className="admin-action" href="/admin-v2/vendors">Back to vendors</Link><Link className="admin-action primary" href="/admin-v2/reports">Open reports</Link></div></header>

    <section className="admin-grid">
      <article className="admin-stat"><span>Identity</span><strong>{vendor.verification_status.replaceAll('_',' ')}</strong><small>{vendor.onboarding_completed_at ? 'Business setup completed.' : 'Business setup incomplete.'}</small></article>
      <article className="admin-stat"><span>Campus access</span><strong>{visibleCampusCount}</strong><small>Approved institution{visibleCampusCount === 1 ? '' : 's'}.</small></article>
      <article className="admin-stat"><span>Marketplace</span><strong>{vendor.marketplace_status.replaceAll('_',' ')}</strong><small>{safetyBlocked ? 'Student discovery is restricted by safety state.' : 'Eligible when campus approval also passes.'}</small></article>
      <article className="admin-stat"><span>Reputation</span><strong>{Number(vendor.average_rating || 0).toFixed(1)}</strong><small>{vendor.review_count || 0} published reviews.</small></article>
    </section>

    {safetyBlocked ? <div className="admin-error"><AlertTriangle size={16}/> Safety state: {vendor.marketplace_status.replaceAll('_',' ')}{vendor.suspended_until ? ` until ${new Date(vendor.suspended_until).toLocaleString()}` : ''}. {vendor.suspension_reason || 'Review Admin reports before restoring visibility.'}</div> : <div className="admin-success"><ShieldCheck size={16}/> No active marketplace safety hold is recorded for this Vendor.</div>}

    <section className="admin-section"><div className="admin-section-head"><div><h2>Business & campus access</h2><p>Identity approval and campus approval remain separate controls.</p></div></div><div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>Business</th><th>Contact</th><th>Campus</th><th>Status</th></tr></thead><tbody>{(campusLinks || []).map((link) => <tr key={link.institution_id}><td><span className="admin-name">{vendor.business_name}</span><span className="admin-sub">{vendor.location_text || 'Location not set'}</span></td><td><span className="admin-sub">{vendor.business_email || 'No business email'}</span><span className="admin-sub">{vendor.whatsapp_number || 'No WhatsApp number'}</span></td><td><span className="admin-name"><Building2 size={13}/> {schoolMap.get(link.institution_id) || 'Institution'}</span>{link.is_primary ? <span className="admin-sub">Primary campus</span> : null}</td><td><span className={`status-badge status-${link.status}`}>{link.status}</span>{link.review_note ? <span className="admin-sub">{link.review_note}</span> : null}</td></tr>)}{!campusLinks?.length ? <tr><td colSpan={4}><div className="empty-admin">No campus access records are visible.</div></td></tr> : null}</tbody></table></div></section>

    <section className="admin-grid">
      <article className="admin-stat"><span>Products</span><strong><Package size={24}/> {(products || []).filter((item) => item.is_active).length}</strong><small>{products?.length || 0} recent records in view.</small></article>
      <article className="admin-stat"><span>Services</span><strong><Wrench size={24}/> {(services || []).filter((item) => item.is_active).length}</strong><small>{services?.length || 0} recent records in view.</small></article>
      <article className="admin-stat"><span>Portfolio</span><strong>{(portfolio || []).filter((item) => item.is_active).length}</strong><small>{portfolio?.length || 0} recent proof-of-work items.</small></article>
      <article className="admin-stat"><span>Open safety reports</span><strong>{(reports || []).filter((report) => ['open','reviewing'].includes(report.status)).length}</strong><small>{vendor.risk_report_count || 0} distinct-report risk count on profile.</small></article>
    </section>

    <section className="admin-section"><div className="admin-section-head"><div><h2>Recent reputation & safety</h2><p>Reviews and reports are connected to this same Vendor record for investigation.</p></div></div><div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>Type</th><th>Details</th><th>Status</th><th>Open</th></tr></thead><tbody>
      {(reports || []).slice(0,10).map((report) => <tr key={`report-${report.id}`}><td><span className="admin-name"><AlertTriangle size={13}/> Report</span></td><td><span className="admin-name">{report.title}</span><span className="admin-sub">{new Date(report.created_at).toLocaleString()}</span></td><td><span className={`status-badge status-${report.status}`}>{report.status}</span></td><td><Link className="admin-link" href="/admin-v2/reports">Moderate</Link></td></tr>)}
      {(reviews || []).slice(0,10).map((review) => <tr key={`review-${review.id}`}><td><span className="admin-name"><MessageSquareText size={13}/> Review</span></td><td><span className="admin-name">{review.rating}/5</span><span className="admin-sub">{review.comment || 'Rating only'}</span></td><td><span className={`status-badge status-${review.status === 'published' ? 'approved' : 'reviewing'}`}>{review.status}</span></td><td><Link className="admin-link" href="/admin-v2/reviews">Moderate</Link></td></tr>)}
      {!reports?.length && !reviews?.length ? <tr><td colSpan={4}><div className="empty-admin">No recent reviews or reports are visible for this Vendor.</div></td></tr> : null}
    </tbody></table></div></section>
  </>
}
