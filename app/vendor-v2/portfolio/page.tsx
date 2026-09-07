import Link from 'next/link'
import { redirect } from 'next/navigation'
import { ArrowLeft, ImagePlus, ShieldCheck, Sparkles, Trash2 } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { ThemeToggle } from '@/components/theme-toggle'
import { addPortfolioItem, deletePortfolioItem } from './actions'

export default async function VendorPortfolioPage({ searchParams }: { searchParams: Promise<{ error?: string; added?: string; deleted?: string; limit?: string }> }) {
  const notices = await searchParams
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const { data: profile } = await supabase.from('profiles').select('account_type').eq('id', userId).single()
  if (!profile || profile.account_type !== 'vendor') redirect('/dashboard')

  const [{ data: items }, { data: entitlementRows }] = await Promise.all([
    supabase.from('vendor_portfolio_items').select('id,title,description,image_url,created_at').eq('vendor_id', userId).order('created_at', { ascending: false }),
    supabase.rpc('get_my_vendor_entitlements'),
  ])

  const entitlement = Array.isArray(entitlementRows) ? entitlementRows[0] : null
  const tier = entitlement?.tier === 'pro' ? 'Pro' : 'Free'
  const portfolioLimit = Number(entitlement?.entitlements?.portfolio_limit || 6)
  const used = items?.length || 0
  const remaining = Math.max(portfolioLimit - used, 0)

  return (
    <main className="phase5b-shell">
      <header className="phase5b-topbar"><Link href="/vendor-v2" className="phase5b-back"><ArrowLeft size={17}/> Dashboard</Link><Link href="/" className="phase5b-brand">Campus<span>Link</span></Link><div className="phase5b-top-actions"><Link href="/vendor-v2/services">Services</Link><Link href="/vendor-v2/growth">Plans</Link><ThemeToggle compact/></div></header>

      <section className="phase5b-content">
        <section className="phase5b-page-head">
          <div><span className="phase5b-kicker"><ImagePlus size={15}/> Portfolio</span><h1>Show real work. Build real confidence.</h1><p>Students make better decisions when they can see genuine examples. Your portfolio becomes public only when your vendor profile is eligible for discovery.</p></div>
          <aside className="phase5b-limit-card"><span>{tier} plan</span><strong>{used} / {portfolioLimit}</strong><small>portfolio items</small><div className="phase5b-meter" aria-hidden="true"><span style={{ width: `${Math.min((used / Math.max(portfolioLimit, 1)) * 100, 100)}%` }}/></div><p>{remaining > 0 ? `${remaining} upload slot${remaining === 1 ? '' : 's'} remaining.` : 'Your portfolio limit is full.'}</p></aside>
        </section>

        <div className="phase5b-trust-note"><ShieldCheck size={18}/><span>Portfolio capacity is a plan feature. Verification and campus approval remain separate trust decisions.</span></div>

        {notices.error ? <div className="notice error">{notices.error}</div> : null}
        {notices.limit ? <div className="phase5b-plan-notice"><Sparkles size={18}/><div><strong>You have reached your {notices.limit}-item portfolio limit.</strong><span>Remove an older item or compare Pro when you need more space.</span></div><Link href="/vendor-v2/growth">Compare plans</Link></div> : null}
        {notices.added === '1' ? <div className="notice success">Portfolio item added.</div> : null}
        {notices.deleted === '1' ? <div className="notice success">Portfolio item removed.</div> : null}

        <section className="phase5b-workspace phase5b-portfolio-workspace">
          <div className="phase5b-list-column">
            <div className="phase5b-section-heading"><div><span>Your work</span><h2>{used ? `${used} example${used === 1 ? '' : 's'} uploaded` : 'Start with your strongest work'}</h2></div></div>
            {used ? <div className="phase5b-portfolio-grid">{(items || []).map((item) => <article key={item.id} className="phase5b-portfolio-card"><img src={item.image_url} alt={item.title}/><div><strong>{item.title}</strong>{item.description ? <p>{item.description}</p> : null}<form action={deletePortfolioItem}><input type="hidden" name="item_id" value={item.id}/><button className="phase5b-action-button danger"><Trash2 size={15}/> Remove</button></form></div></article>)}</div> : <div className="phase5b-empty"><ImagePlus/><div><strong>No portfolio examples yet.</strong><span>Use clear, honest images of work you actually completed.</span></div></div>}
          </div>

          <form action={addPortfolioItem} encType="multipart/form-data" className="phase5b-editor">
            <div className="phase5b-editor-head"><ImagePlus size={20}/><div><span>Add work</span><strong>{remaining > 0 ? `${remaining} upload slot${remaining === 1 ? '' : 's'} available` : 'Portfolio limit reached'}</strong></div></div>
            <label><span>Title</span><input name="title" required maxLength={100} placeholder="e.g. Knotless braids"/></label>
            <label><span>Description</span><textarea name="description" maxLength={500} placeholder="Optional context about the work, materials or result."/></label>
            <label><span>Image</span><input name="image" type="file" accept="image/jpeg,image/png,image/webp" required disabled={remaining <= 0}/></label>
            <p className="phase5b-upload-help">JPG, PNG or WEBP · maximum 5 MB · natural portrait or landscape proportions are preserved.</p>
            <button className="phase5b-primary" type="submit" disabled={remaining <= 0}><ImagePlus size={17}/>{remaining > 0 ? 'Upload portfolio item' : 'Limit reached'}</button>
          </form>
        </section>
      </section>
    </main>
  )
}
