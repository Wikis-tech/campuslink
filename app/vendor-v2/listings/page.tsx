import Link from 'next/link'
import { redirect } from 'next/navigation'
import { ImagePlus, Package, Plus, Store, Wrench } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { VendorWorkspaceSidebar } from '@/components/vendor-workspace-sidebar'

export default async function VendorListingsPage() {
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const [{ data: profile }, { data: vendor }, productResult, serviceResult, portfolioResult, { data: entitlementRows }] = await Promise.all([
    supabase.from('profiles').select('account_type').eq('id', userId).maybeSingle(),
    supabase.from('vendor_profiles').select('business_name,slug').eq('id', userId).maybeSingle(),
    supabase.from('vendor_products').select('id', { count: 'exact', head: true }).eq('vendor_id', userId).eq('is_active', true),
    supabase.from('vendor_services').select('id', { count: 'exact', head: true }).eq('vendor_id', userId).eq('is_active', true),
    supabase.from('vendor_portfolio_items').select('id', { count: 'exact', head: true }).eq('vendor_id', userId).eq('is_active', true),
    supabase.rpc('get_my_vendor_entitlements'),
  ])

  if (!profile || profile.account_type !== 'vendor') redirect('/dashboard')
  if (!vendor) redirect('/onboarding/vendor')

  const entitlement = Array.isArray(entitlementRows) ? entitlementRows[0] : null
  const limits = {
    products: Number(entitlement?.entitlements?.product_limit || 5),
    services: Number(entitlement?.entitlements?.service_limit || 5),
    portfolio: Number(entitlement?.entitlements?.portfolio_limit || 6),
  }

  const items = [
    {
      href: '/vendor-v2/products',
      label: 'Products',
      description: 'Physical or digital items Students can discover and contact you about.',
      count: productResult.count || 0,
      limit: limits.products,
      icon: Package,
      action: 'Manage products',
    },
    {
      href: '/vendor-v2/services',
      label: 'Services',
      description: 'What you do, your starting price and whether the offer is currently available.',
      count: serviceResult.count || 0,
      limit: limits.services,
      icon: Wrench,
      action: 'Manage services',
    },
    {
      href: '/vendor-v2/portfolio',
      label: 'Portfolio',
      description: 'Proof of work that helps Students judge quality before contacting you.',
      count: portfolioResult.count || 0,
      limit: limits.portfolio,
      icon: ImagePlus,
      action: 'Manage portfolio',
    },
  ]

  return (
    <main className="v5e-page kv-vendor-secondary-page">
      <VendorWorkspaceSidebar storefrontHref={vendor.slug ? `/student/vendors/${vendor.slug}` : undefined}/>
      <section className="v5e-content kv-vendor-secondary-content">
        <header className="kv-vendor-secondary-head">
          <div>
            <span className="kv-vendor-eyebrow">Listings</span>
            <h1>Manage what Students can discover.</h1>
            <p>Products, Services and Portfolio use the same campus approval and trust rules already protecting your storefront.</p>
          </div>
          {vendor.slug ? <Link className="kv-vendor-btn" href={`/student/vendors/${vendor.slug}`}><Store size={16}/> View storefront</Link> : null}
        </header>

        <section className="kv-vendor-listings-hub">
          {items.map(({ href, label, description, count, limit, icon: Icon, action }) => (
            <Link href={href} className="kv-vendor-listings-hub-card" key={href}>
              <span className="kv-vendor-listings-hub-icon"><Icon size={20}/></span>
              <div>
                <span className="kv-vendor-label">{label}</span>
                <h2>{count} active</h2>
                <p>{description}</p>
              </div>
              <div className="kv-vendor-listings-hub-foot">
                <span>{count} / {limit}</span>
                <strong>{action} →</strong>
              </div>
            </Link>
          ))}
        </section>

        <div className="kv-vendor-listings-note">
          <Plus size={17}/>
          <div>
            <strong>Add from the page that matches what you are offering.</strong>
            <p>Keeping Product, Service and Portfolio content separate makes the Student marketplace easier to understand.</p>
          </div>
        </div>
      </section>
    </main>
  )
}
