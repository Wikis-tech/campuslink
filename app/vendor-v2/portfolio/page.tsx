import { redirect } from 'next/navigation'
import { ImagePlus, ShieldCheck, Trash2 } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { VendorWorkspaceSidebar } from '@/components/vendor-workspace-sidebar'
import { addPortfolioItem, deletePortfolioItem } from './actions'

export default async function VendorPortfolioPage({ searchParams }: { searchParams: Promise<{ error?: string; added?: string; deleted?: string; limit?: string }> }) {
  const notices = await searchParams
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')
  const { data: profile } = await supabase.from('profiles').select('account_type').eq('id', userId).single()
  if (!profile || profile.account_type !== 'vendor') redirect('/dashboard')
  const [{ data: items }, { data: entitlementRows }, { data: vendor }] = await Promise.all([
    supabase.from('vendor_portfolio_items').select('id,title,description,image_url,created_at').eq('vendor_id', userId).order('created_at', { ascending: false }),
    supabase.rpc('get_my_vendor_entitlements'),
    supabase.from('vendor_profiles').select('slug').eq('id', userId).maybeSingle(),
  ])
  const entitlement = Array.isArray(entitlementRows) ? entitlementRows[0] : null
  const tier = entitlement?.tier === 'pro' ? 'Pro' : 'Free'
  const portfolioLimit = Number(entitlement?.entitlements?.portfolio_limit || 6)
  const used = items?.length || 0
  const remaining = Math.max(portfolioLimit - used, 0)

  return <main className="v5e-page">
    <VendorWorkspaceSidebar storefrontHref={vendor?.slug ? `/student/vendors/${vendor.slug}` : undefined}/>
    <section className="v5e-content vendor-section-content">
      <header className="v5e-topbar vendor-section-header">
        <div><span className="v5e-section-label">Portfolio</span><h1>Show real work. Build real confidence.</h1><p>Students make better decisions when they can see genuine examples of work you completed.</p></div>
        <div className="vendor-plan-pill"><span>{tier} plan</span><strong>{used} / {portfolioLimit}</strong><small>portfolio items</small></div>
      </header>
      <div className="phase5b-trust-note"><ShieldCheck size={18}/><span>Portfolio capacity is a plan feature. Verification and campus approval remain separate trust decisions.</span></div>
      {notices.error ? <div className="notice error">{notices.error}</div> : null}
      {notices.added === '1' ? <div className="notice success">Portfolio item added.</div> : null}
      {notices.deleted === '1' ? <div className="notice success">Portfolio item removed.</div> : null}
      <section className="phase5b-workspace phase5b-portfolio-workspace">
        <div className="phase5b-list-column">
          <div className="phase5b-section-heading"><div><span>Your work</span><h2>{used ? `${used} example${used === 1 ? '' : 's'} uploaded` : 'Start with your strongest work'}</h2></div></div>
          {used ? <div className="phase5b-portfolio-grid">{(items || []).map((item) => <article key={item.id} className="phase5b-portfolio-card"><img src={item.image_url} alt={item.title}/><div><strong>{item.title}</strong>{item.description ? <p>{item.description}</p> : null}<form action={deletePortfolioItem}><input type="hidden" name="item_id" value={item.id}/><button className="phase5b-action-button danger"><Trash2 size={15}/> Remove</button></form></div></article>)}</div> : <div className="phase5b-empty"><ImagePlus/><div><strong>No portfolio examples yet.</strong><span>Use clear, honest images of work you actually completed.</span></div></div>}
        </div>
        <form action={addPortfolioItem} encType="multipart/form-data" className="phase5b-editor vendor-editor-card">
          <div className="phase5b-editor-head"><ImagePlus size={20}/><div><span>Add work</span><strong>{remaining > 0 ? `${remaining} upload slot${remaining === 1 ? '' : 's'} available` : 'Portfolio limit reached'}</strong></div></div>
          <label><span>Title</span><input name="title" required maxLength={100} placeholder="e.g. Knotless braids"/></label>
          <label><span>Description</span><textarea name="description" maxLength={500} placeholder="Optional context about the work or result."/></label>
          <label><span>Image</span><input name="image" type="file" accept="image/jpeg,image/png,image/webp" required disabled={remaining <= 0}/></label>
          <p className="phase5b-upload-help">JPG, PNG or WEBP · maximum 5 MB.</p>
          <button className="phase5b-primary" type="submit" disabled={remaining <= 0}><ImagePlus size={17}/>{remaining > 0 ? 'Upload portfolio item' : 'Limit reached'}</button>
        </form>
      </section>
    </section>
  </main>
}
