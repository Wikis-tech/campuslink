import Link from 'next/link'
import { redirect } from 'next/navigation'
import { ArrowLeft, ImagePlus, Trash2 } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { addPortfolioItem, deletePortfolioItem } from './actions'

export default async function VendorPortfolioPage({ searchParams }: { searchParams: Promise<{ error?: string; added?: string; deleted?: string }> }) {
  const notices = await searchParams
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const { data: profile } = await supabase.from('profiles').select('account_type').eq('id', userId).single()
  if (!profile || profile.account_type !== 'vendor') redirect('/dashboard')

  const { data: items } = await supabase
    .from('vendor_portfolio_items')
    .select('id,title,description,image_url,created_at')
    .eq('vendor_id', userId)
    .order('created_at', { ascending: false })

  return (
    <main className="portal-shell">
      <header className="portal-topbar"><Link href="/vendor-v2" className="brand">Campus<span>Link</span></Link><Link className="btn btn-ghost" href="/vendor-v2"><ArrowLeft size={17}/> Dashboard</Link></header>

      <section className="portal-hero"><div><p className="eyebrow">Vendor portfolio</p><h1>Show students what you can do.</h1><p>Upload real examples of your work. Portfolio images remain attached to your vendor account and become visible when your profile is approved.</p></div></section>

      {notices.error ? <div className="notice error">{notices.error}</div> : null}
      {notices.added === '1' ? <div className="notice success">Portfolio item added.</div> : null}
      {notices.deleted === '1' ? <div className="notice success">Portfolio item removed.</div> : null}

      <section style={{display:'grid',gridTemplateColumns:'minmax(0,1fr) minmax(320px,420px)',gap:24,alignItems:'start'}}>
        <div>
          <h2 style={{fontFamily:'Sora,sans-serif'}}>Your work</h2>
          {(items || []).length ? <div style={{columns:'2 260px',columnGap:16}}>{(items || []).map((item) => <article key={item.id} style={{breakInside:'avoid',marginBottom:16,background:'#fff',border:'1px solid #e5e9f0',borderRadius:16,overflow:'hidden'}}><img src={item.image_url} alt={item.title} style={{width:'100%',height:'auto',display:'block'}}/><div style={{padding:14}}><strong>{item.title}</strong>{item.description ? <p style={{color:'#667085',fontSize:14}}>{item.description}</p> : null}<form action={deletePortfolioItem}><input type="hidden" name="item_id" value={item.id}/><button className="btn btn-ghost" style={{color:'#b42318'}}><Trash2 size={16}/> Remove</button></form></div></article>)}</div> : <div className="portal-action"><ImagePlus size={24}/><div><strong>No portfolio items yet</strong><span>Add your first example of completed work.</span></div></div>}
        </div>

        <form action={addPortfolioItem} encType="multipart/form-data" style={{background:'#fff',border:'1px solid #e5e9f0',borderRadius:18,padding:20,display:'grid',gap:14}}>
          <h2 style={{fontFamily:'Sora,sans-serif',margin:0}}>Add work</h2>
          <label><span style={{display:'block',fontWeight:700,marginBottom:6}}>Title</span><input name="title" required maxLength={100} placeholder="e.g. Knotless braids" style={{width:'100%',padding:12,border:'1px solid #d8e0ea',borderRadius:10}}/></label>
          <label><span style={{display:'block',fontWeight:700,marginBottom:6}}>Description</span><textarea name="description" maxLength={500} placeholder="Optional context about this work" style={{width:'100%',minHeight:90,padding:12,border:'1px solid #d8e0ea',borderRadius:10}}/></label>
          <label><span style={{display:'block',fontWeight:700,marginBottom:6}}>Image</span><input name="image" type="file" accept="image/jpeg,image/png,image/webp" required/></label>
          <p style={{margin:0,color:'#667085',fontSize:13}}>JPG, PNG or WEBP. Maximum 5 MB. Images keep their natural proportions on your profile.</p>
          <button className="btn btn-primary" type="submit"><ImagePlus size={17}/> Upload portfolio item</button>
        </form>
      </section>
    </main>
  )
}
