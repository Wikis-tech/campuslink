import Link from 'next/link'
import { AlertTriangle, BadgeCheck, Package, ShieldCheck, ShoppingBag, Store, Wrench } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { requireAdminContext } from '../lib'

function marketplaceEligible(vendor: any) {
  if (vendor.verification_status !== 'approved') return false
  if (vendor.marketplace_status === 'active') return true
  return vendor.marketplace_status === 'suspended' && vendor.suspended_until && new Date(vendor.suspended_until) <= new Date()
}

export default async function AdminMarketplacePage() {
  const context = await requireAdminContext()
  const supabase = await createClient()

  const assignmentIds = context.schoolAssignments.map((assignment) => assignment.institution_id)
  let institutionQuery = supabase.from('institutions').select('id,name,city,state,is_active').order('name')
  if (!context.isGlobalAdmin && assignmentIds.length) institutionQuery = institutionQuery.in('id', assignmentIds)

  const { data: institutions } = await institutionQuery
  const institutionIds = (institutions || []).map((institution) => institution.id)

  let links: any[] = []
  if (institutionIds.length) {
    const { data } = await supabase.from('vendor_institutions').select('vendor_id,institution_id,status').in('institution_id', institutionIds)
    links = data || []
  }

  const vendorIds = Array.from(new Set(links.map((link) => link.vendor_id))) as string[]
  let vendors: any[] = []
  let products: any[] = []
  let services: any[] = []
  let reports: any[] = []

  if (vendorIds.length) {
    const [vendorResult, productResult, serviceResult, reportResult] = await Promise.all([
      supabase.from('vendor_profiles').select('id,business_name,slug,verification_status,marketplace_status,suspended_until,risk_report_count,average_rating,review_count').in('id', vendorIds),
      supabase.from('vendor_products').select('id,vendor_id,is_active').in('vendor_id', vendorIds).eq('is_active', true),
      supabase.from('vendor_services').select('id,vendor_id,is_active').in('vendor_id', vendorIds).eq('is_active', true),
      supabase.from('complaints').select('id,vendor_id,status').in('vendor_id', vendorIds).in('status', ['open','reviewing']),
    ])
    vendors = vendorResult.data || []
    products = productResult.data || []
    services = serviceResult.data || []
    reports = reportResult.data || []
  }

  const vendorMap = new Map(vendors.map((vendor) => [vendor.id, vendor]))
  const approvedLinks = links.filter((link) => link.status === 'approved')
  const eligibleVendorIds = new Set(approvedLinks.filter((link) => marketplaceEligible(vendorMap.get(link.vendor_id))).map((link) => link.vendor_id))
  const underReview = vendors.filter((vendor) => ['under_review','suspended'].includes(vendor.marketplace_status)).length
  const eligibleProducts = products.filter((product) => eligibleVendorIds.has(product.vendor_id)).length
  const eligibleServices = services.filter((service) => eligibleVendorIds.has(service.vendor_id)).length
  const schoolMap = new Map((institutions || []).map((institution) => [institution.id, institution]))

  return <>
    <header className="admin-topbar"><div><span className="admin-pill"><ShoppingBag size={15}/> Student marketplace oversight</span><h1>Marketplace</h1><p>This is the Admin view of what can reach Students. It mirrors the same identity, campus-approval and safety rules used by Student discovery without impersonating a Student account.</p></div></header>

    <section className="admin-grid">
      <article className="admin-stat"><span>Eligible vendors</span><strong>{eligibleVendorIds.size}</strong><small>Identity approved, campus approved and currently safe to discover.</small></article>
      <article className="admin-stat"><span>Active products</span><strong>{eligibleProducts}</strong><small>Student-visible product listings from eligible vendors.</small></article>
      <article className="admin-stat"><span>Active services</span><strong>{eligibleServices}</strong><small>Student-visible services from eligible vendors.</small></article>
      <article className="admin-stat"><span>Safety attention</span><strong>{underReview}</strong><small>Vendors under review or suspension in your Admin scope.</small></article>
    </section>

    <section className="admin-section">
      <div className="admin-section-head"><div><h2>Campus marketplace health</h2><p>Use this before investigating Vendors, reports or gaps in Student discovery.</p></div></div>
      <div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>Campus</th><th>Approved vendors</th><th>Student-visible now</th><th>Safety / reports</th><th>Investigate</th></tr></thead><tbody>
        {(institutions || []).map((institution) => {
          const campusLinks = links.filter((link) => link.institution_id === institution.id)
          const approved = campusLinks.filter((link) => link.status === 'approved')
          const visible = approved.filter((link) => marketplaceEligible(vendorMap.get(link.vendor_id)))
          const campusVendorIds = new Set(campusLinks.map((link) => link.vendor_id))
          const campusReports = reports.filter((report) => campusVendorIds.has(report.vendor_id)).length
          const campusSafety = vendors.filter((vendor) => campusVendorIds.has(vendor.id) && ['under_review','suspended'].includes(vendor.marketplace_status)).length
          return <tr key={institution.id}>
            <td><span className="admin-name">{institution.name}</span><span className="admin-sub">{[institution.city,institution.state].filter(Boolean).join(', ') || 'Location not set'}</span></td>
            <td><span className="admin-name">{approved.length}</span><span className="admin-sub">Campus approval active.</span></td>
            <td><span className="admin-name"><BadgeCheck size={13}/> {visible.length}</span><span className="admin-sub">Pass all Student visibility gates.</span></td>
            <td><span className="admin-name">{campusSafety} safety holds</span><span className="admin-sub">{campusReports} open/reviewing reports</span></td>
            <td><div className="admin-actions"><Link className="admin-action primary" href="/admin-v2/vendors"><Store size={13}/> Vendors</Link><Link className="admin-action" href="/admin-v2/reports"><AlertTriangle size={13}/> Reports</Link></div></td>
          </tr>
        })}
        {!institutions?.length ? <tr><td colSpan={5}><div className="empty-admin">No institutions are available in your current Admin scope.</div></td></tr> : null}
      </tbody></table></div>
    </section>

    <section className="admin-section"><div className="admin-section-head"><div><h2>How the three workspaces connect</h2><p>Student actions create signals that Vendors use for business decisions and Admins use for governance.</p></div></div><div className="admin-grid">
      <article className="admin-stat"><span>Student → Vendor</span><strong><Package size={26}/></strong><small>Products/services, saves, reviews and contact intent feed the Vendor storefront and analytics.</small></article>
      <article className="admin-stat"><span>Student → Admin</span><strong><ShieldCheck size={26}/></strong><small>Reports and verification submissions flow into moderation and trust queues.</small></article>
      <article className="admin-stat"><span>Vendor → Student</span><strong><Wrench size={26}/></strong><small>Only active listings from identity-approved, campus-approved and safe Vendors are discoverable.</small></article>
      <article className="admin-stat"><span>Admin → Marketplace</span><strong><Store size={26}/></strong><small>Identity, campus and safety decisions immediately control Student visibility.</small></article>
    </div></section>
  </>
}
