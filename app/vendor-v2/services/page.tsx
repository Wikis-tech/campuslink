import Link from 'next/link'
import { redirect } from 'next/navigation'
import { ArrowLeft, CheckCircle2, CirclePlus, Eye, EyeOff, ShieldCheck, Sparkles, Trash2 } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { ThemeToggle } from '@/components/theme-toggle'
import { addVendorService, deleteVendorService, toggleVendorService } from './actions'

function formatNaira(value: number | string | null | undefined) {
  if (value === null || value === undefined || value === '') return 'Price on request'
  return new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN', maximumFractionDigits: 0 }).format(Number(value || 0))
}

export default async function VendorServicesPage({ searchParams }: { searchParams: Promise<{ error?: string; added?: string; updated?: string; deleted?: string; limit?: string }> }) {
  const notices = await searchParams
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const { data: profile } = await supabase.from('profiles').select('account_type').eq('id', userId).maybeSingle()
  if (!profile || profile.account_type !== 'vendor') redirect('/dashboard')

  const [{ data: vendor }, { data: services }, { data: categories }, { data: entitlementRows }] = await Promise.all([
    supabase.from('vendor_profiles').select('business_name,verification_status').eq('id', userId).maybeSingle(),
    supabase.from('vendor_services').select('id,name,description,price_from,is_active,category_id,created_at').eq('vendor_id', userId).order('created_at', { ascending: false }),
    supabase.from('categories').select('id,name').eq('is_active', true).order('name'),
    supabase.rpc('get_my_vendor_entitlements'),
  ])

  if (!vendor) redirect('/onboarding/vendor')

  const entitlement = Array.isArray(entitlementRows) ? entitlementRows[0] : null
  const serviceLimit = Number(entitlement?.entitlements?.service_limit || 5)
  const tier = entitlement?.tier === 'pro' ? 'Pro' : 'Free'
  const activeCount = (services || []).filter((service) => service.is_active).length
  const remaining = Math.max(serviceLimit - activeCount, 0)
  const categoryMap = new Map((categories || []).map((category) => [category.id, category.name]))

  return (
    <main className="phase5b-shell">
      <header className="phase5b-topbar">
        <Link href="/vendor-v2" className="phase5b-back"><ArrowLeft size={17}/> Dashboard</Link>
        <Link href="/" className="phase5b-brand">Campus<span>Link</span></Link>
        <div className="phase5b-top-actions"><Link href="/vendor-v2/growth">Plans</Link><ThemeToggle compact/></div>
      </header>

      <section className="phase5b-content">
        <section className="phase5b-page-head">
          <div>
            <span className="phase5b-kicker"><Sparkles size={15}/> Services</span>
            <h1>Keep your offer clear and easy to trust.</h1>
            <p>Students should understand what you do, what it starts from and whether the service is currently available.</p>
          </div>
          <aside className="phase5b-limit-card">
            <span>{tier} plan</span>
            <strong>{activeCount} / {serviceLimit}</strong>
            <small>active services</small>
            <div className="phase5b-meter" aria-hidden="true"><span style={{ width: `${Math.min((activeCount / Math.max(serviceLimit, 1)) * 100, 100)}%` }}/></div>
            <p>{remaining > 0 ? `${remaining} active slot${remaining === 1 ? '' : 's'} remaining.` : 'Your active service limit is full.'}</p>
          </aside>
        </section>

        <div className="phase5b-trust-note"><ShieldCheck size={18}/><span>Plan limits affect business tools only. Verification and campus approval are never sold.</span></div>

        {notices.error ? <div className="notice error">{notices.error}</div> : null}
        {notices.limit ? <div className="phase5b-plan-notice"><Sparkles size={18}/><div><strong>You have reached your {notices.limit}-service limit.</strong><span>You can pause an active service to free a slot, or compare Pro when you need more room.</span></div><Link href="/vendor-v2/growth">Compare plans</Link></div> : null}
        {notices.added === '1' ? <div className="notice success">Service added.</div> : null}
        {notices.updated === '1' ? <div className="notice success">Service visibility updated.</div> : null}
        {notices.deleted === '1' ? <div className="notice success">Service removed.</div> : null}

        <section className="phase5b-workspace">
          <div className="phase5b-list-column">
            <div className="phase5b-section-heading"><div><span>Your offer</span><h2>{services?.length || 0} service{services?.length === 1 ? '' : 's'} saved</h2></div></div>

            {(services || []).length ? <div className="phase5b-service-list">
              {(services || []).map((service) => (
                <article key={service.id} className={`phase5b-service-row ${service.is_active ? '' : 'paused'}`}>
                  <div className="phase5b-service-main">
                    <div className="phase5b-service-state">{service.is_active ? <Eye size={16}/> : <EyeOff size={16}/>}</div>
                    <div>
                      <div className="phase5b-service-title"><strong>{service.name}</strong><span>{categoryMap.get(service.category_id) || 'Service'}</span></div>
                      {service.description ? <p>{service.description}</p> : null}
                      <small>{formatNaira(service.price_from)} · {service.is_active ? 'Visible when your vendor profile is discoverable' : 'Paused'}</small>
                    </div>
                  </div>
                  <div className="phase5b-service-actions">
                    <form action={toggleVendorService}>
                      <input type="hidden" name="service_id" value={service.id}/>
                      <input type="hidden" name="next_active" value={service.is_active ? 'false' : 'true'}/>
                      <button type="submit" className="phase5b-action-button">{service.is_active ? <><EyeOff size={15}/> Pause</> : <><Eye size={15}/> Activate</>}</button>
                    </form>
                    <form action={deleteVendorService}>
                      <input type="hidden" name="service_id" value={service.id}/>
                      <button type="submit" className="phase5b-action-button danger"><Trash2 size={15}/> Remove</button>
                    </form>
                  </div>
                </article>
              ))}
            </div> : <div className="phase5b-empty"><CirclePlus/><div><strong>Add the services students should know you for.</strong><span>Start with a clear service name and a realistic starting price if you have one.</span></div></div>}
          </div>

          <form action={addVendorService} className="phase5b-editor">
            <div className="phase5b-editor-head"><CirclePlus size={20}/><div><span>Add service</span><strong>{remaining > 0 ? `${remaining} active slot${remaining === 1 ? '' : 's'} available` : 'Active limit reached'}</strong></div></div>
            <label><span>Category</span><select name="category_id" required defaultValue=""><option value="" disabled>Choose category</option>{(categories || []).map((category) => <option value={category.id} key={category.id}>{category.name}</option>)}</select></label>
            <label><span>Service name</span><input name="name" required maxLength={120} placeholder="e.g. Laptop screen replacement"/></label>
            <label><span>Short description</span><textarea name="description" maxLength={500} placeholder="Tell students exactly what this service covers."/></label>
            <label><span>Starting price (optional)</span><div className="phase5b-price-input"><b>₦</b><input name="price_from" inputMode="decimal" placeholder="5000"/></div></label>
            <button className="phase5b-primary" type="submit" disabled={remaining <= 0}><CirclePlus size={17}/>{remaining > 0 ? 'Add service' : 'Limit reached'}</button>
            <small className="phase5b-editor-foot"><CheckCircle2 size={14}/> Clear pricing and descriptions help students make safer decisions.</small>
          </form>
        </section>
      </section>
    </main>
  )
}
