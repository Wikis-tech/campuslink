import Link from 'next/link'
import { redirect } from 'next/navigation'
import { ImagePlus, PauseCircle, PlayCircle, Plus, ShieldCheck, Store, Trash2 } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { createProduct, deleteProduct, setProductActive } from './actions'

export default async function VendorProductsPage({ searchParams }: { searchParams: Promise<{ success?: string; error?: string }> }) {
  const params = await searchParams
  const supabase = await createClient()
  const { data: userData } = await supabase.auth.getUser()
  const user = userData.user
  if (!user) redirect('/login')

  const { data: profile } = await supabase.from('profiles').select('account_type').eq('id', user.id).maybeSingle()
  if (!profile || profile.account_type !== 'vendor') redirect('/dashboard')

  const [{ data: products }, { data: categories }, { data: entitlementRows }] = await Promise.all([
    supabase.from('vendor_products').select('id,name,description,price_ngn,pricing_type,cover_image_url,is_active,created_at').eq('vendor_id', user.id).order('created_at', { ascending: false }),
    supabase.from('categories').select('id,name').eq('is_active', true).order('name'),
    supabase.rpc('get_my_vendor_entitlements'),
  ])

  const entitlement = Array.isArray(entitlementRows) ? entitlementRows[0] : null
  const tier = entitlement?.tier === 'pro' ? 'Pro' : 'Free'
  const productLimit = Number(entitlement?.entitlements?.product_limit || 5)
  const activeCount = (products || []).filter((item) => item.is_active).length
  const atLimit = activeCount >= productLimit

  return (
    <main className="portal-shell">
      <header className="portal-topbar">
        <Link href="/vendor-v2" className="brand">Campus<span>Link</span></Link>
        <nav style={{display:'flex',gap:10,flexWrap:'wrap'}}><Link className="btn btn-ghost" href="/vendor-v2">Overview</Link><Link className="btn btn-ghost" href="/vendor-v2/services">Services</Link><Link className="btn btn-ghost" href="/vendor-v2/portfolio">Portfolio</Link><Link className="btn btn-ghost" href="/vendor-v2/growth">Plans & growth</Link></nav>
      </header>

      <section className="portal-hero phase45-vendor-hero">
        <div><span className="admin-pill"><Store size={15}/> Product catalogue</span><h1>Show students what you sell.</h1><p>Products are individual items students can discover, open and ask you about. Campus Link does not process the sale; contact continues through WhatsApp or phone.</p></div>
        <div className="status-card"><ShieldCheck size={22}/><div><span>{tier} plan</span><strong>{activeCount} / {productLimit} active products</strong></div></div>
      </section>

      {params.success ? <div className="notice success">{params.success}</div> : null}
      {params.error ? <div className="notice error">{params.error}</div> : null}

      <section className="portal-grid" style={{gridTemplateColumns:'minmax(0,1.4fr) minmax(300px,.7fr)'}}>
        <section className="cl-editorial-surface" style={{padding:24}}>
          <div className="panel-heading"><span>Your catalogue</span><strong>{activeCount} active · {(products || []).length} total</strong></div>
          <div style={{display:'grid',gridTemplateColumns:'repeat(auto-fit,minmax(210px,1fr))',gap:16,marginTop:18}}>
            {(products || []).map((product) => <article key={product.id} style={{border:'1px solid var(--cl-line)',borderRadius:18,overflow:'hidden',background:'var(--cl-surface)'}}>
              <div style={{aspectRatio:'4/3',background:'var(--cl-soft)',overflow:'hidden'}}>{product.cover_image_url ? <img src={product.cover_image_url} alt={product.name} style={{width:'100%',height:'100%',objectFit:'cover'}}/> : <div style={{height:'100%',display:'grid',placeItems:'center'}}><ImagePlus size={32}/></div>}</div>
              <div style={{padding:16}}><div style={{display:'flex',justifyContent:'space-between',gap:12,alignItems:'flex-start'}}><div><strong style={{display:'block',fontSize:16}}>{product.name}</strong><span style={{fontSize:13,color:'var(--cl-muted)'}}>{product.pricing_type === 'contact' ? 'Contact for price' : `${product.pricing_type === 'from' ? 'From ' : ''}₦${Number(product.price_ngn || 0).toLocaleString()}`}</span></div><span className={`status-badge status-${product.is_active ? 'approved' : 'pending'}`}>{product.is_active ? 'Live' : 'Paused'}</span></div>{product.description ? <p style={{fontSize:13,color:'var(--cl-muted)',lineHeight:1.55}}>{product.description}</p> : null}<div style={{display:'flex',gap:8,marginTop:12,flexWrap:'wrap'}}><form action={setProductActive}><input type="hidden" name="product_id" value={product.id}/><input type="hidden" name="active" value={product.is_active ? 'false' : 'true'}/><button className="btn btn-ghost" type="submit">{product.is_active ? <PauseCircle size={15}/> : <PlayCircle size={15}/>} {product.is_active ? 'Pause' : 'Make live'}</button></form><form action={deleteProduct}><input type="hidden" name="product_id" value={product.id}/><button className="btn btn-ghost" type="submit"><Trash2 size={15}/> Remove</button></form></div></div>
            </article>)}
            {!products?.length ? <div className="empty-state"><Store size={30}/><h2>No products yet</h2><p>Add a product such as clothing, perfume, gadgets, food items or anything students can ask you about directly.</p></div> : null}
          </div>
        </section>

        <aside className="cl-editorial-surface" style={{padding:24,alignSelf:'start'}}>
          <div className="panel-heading"><span>Add product</span><strong>{atLimit ? 'Your active limit is full' : 'Create a new listing'}</strong></div>
          {atLimit ? <div className="notice error" style={{marginTop:14}}>Your {tier} plan allows {productLimit} active products. Pause one or compare Pro before adding another.</div> : null}
          <form action={createProduct} style={{display:'grid',gap:14,marginTop:18}}>
            <label><span>Product name</span><input name="name" required maxLength={140} placeholder="e.g. Black oversized hoodie" disabled={atLimit}/></label>
            <label><span>Category</span><select name="category_id" defaultValue="" disabled={atLimit}><option value="">Choose category</option>{(categories || []).map((category) => <option key={category.id} value={category.id}>{category.name}</option>)}</select></label>
            <label><span>Pricing</span><select name="pricing_type" defaultValue="fixed" disabled={atLimit}><option value="fixed">Fixed price</option><option value="from">Starting from</option><option value="contact">Contact for price</option></select></label>
            <label><span>Price (NGN)</span><input name="price_ngn" type="number" min="0" step="50" placeholder="5000" disabled={atLimit}/></label>
            <label><span>Description</span><textarea name="description" maxLength={1800} placeholder="Size, colour, condition, what is included…" disabled={atLimit}/></label>
            <label><span>Product image</span><input name="image" type="file" accept="image/jpeg,image/png,image/webp" disabled={atLimit}/></label>
            <button className="btn btn-primary" type="submit" disabled={atLimit}><Plus size={16}/> Add product</button>
          </form>
          <p style={{fontSize:12,color:'var(--cl-muted)',marginTop:14}}>Products are for discovery and contact only. Students are not charged through Campus Link.</p>
        </aside>
      </section>
    </main>
  )
}
