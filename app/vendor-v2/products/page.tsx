import { redirect } from 'next/navigation'
import { ImagePlus, PauseCircle, PlayCircle, Plus, Store, Trash2 } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { VendorWorkspaceSidebar } from '@/components/vendor-workspace-sidebar'
import { createProduct, deleteProduct, setProductActive } from './actions'

export default async function VendorProductsPage({ searchParams }: { searchParams: Promise<{ success?: string; error?: string }> }) {
  const params = await searchParams
  const supabase = await createClient()
  const { data: userData } = await supabase.auth.getUser()
  const user = userData.user
  if (!user) redirect('/login')
  const { data: profile } = await supabase.from('profiles').select('account_type').eq('id', user.id).maybeSingle()
  if (!profile || profile.account_type !== 'vendor') redirect('/dashboard')
  const [{ data: products }, { data: categories }, { data: entitlementRows }, { data: vendor }] = await Promise.all([
    supabase.from('vendor_products').select('id,name,description,price_ngn,pricing_type,cover_image_url,is_active,created_at').eq('vendor_id',user.id).order('created_at',{ascending:false}),
    supabase.from('categories').select('id,name').eq('is_active',true).order('name'),
    supabase.rpc('get_my_vendor_entitlements'),
    supabase.from('vendor_profiles').select('slug').eq('id', user.id).maybeSingle(),
  ])
  const entitlement=Array.isArray(entitlementRows)?entitlementRows[0]:null
  const tier=entitlement?.tier==='pro'?'Pro':'Free'
  const productLimit=Number(entitlement?.entitlements?.product_limit||5)
  const activeCount=(products||[]).filter(x=>x.is_active).length
  const atLimit=activeCount>=productLimit

  return <main className="v5e-page">
    <VendorWorkspaceSidebar storefrontHref={vendor?.slug ? `/student/vendors/${vendor.slug}` : undefined}/>
    <section className="v5e-content vendor-section-content">
      <header className="v5e-topbar vendor-section-header">
        <div><span className="v5e-section-label">Product catalogue</span><h1>Show students what you sell.</h1><p>Keep products visual, clear and easy to contact you about. Campus Link helps students discover; the transaction still stays between you and the student.</p></div>
        <div className="vendor-plan-pill"><span>{tier} plan</span><strong>{activeCount} / {productLimit}</strong><small>active products</small></div>
      </header>
      {params.success?<div className="notice success">{params.success}</div>:null}
      {params.error?<div className="notice error">{params.error}</div>:null}
      <section className="phase5b-workspace">
        <div className="phase5b-list-column">
          <div className="phase5b-section-heading"><div><span>Your catalogue</span><h2>{(products||[]).length ? `${(products||[]).length} product${products?.length===1?'':'s'} saved` : 'Start with products students can recognize instantly'}</h2></div></div>
          <div className="vendor-product-grid">{(products||[]).map(product=><article className="v3-product-card" key={product.id}><div className="v3-product-image">{product.cover_image_url?<img src={product.cover_image_url} alt={product.name}/>:<ImagePlus size={30}/>}</div><div className="v3-product-copy"><div className="vendor-listing-state"><span className={product.is_active?'live':'paused'}>{product.is_active?'Live':'Paused'}</span></div><strong>{product.name}</strong><p>{product.pricing_type==='contact'?'Contact for price':`${product.pricing_type==='from'?'From ':''}₦${Number(product.price_ngn||0).toLocaleString()}`}</p>{product.description?<small>{product.description}</small>:null}<div className="vendor-card-actions"><form action={setProductActive}><input type="hidden" name="product_id" value={product.id}/><input type="hidden" name="active" value={product.is_active?'false':'true'}/><button className="btn btn-ghost">{product.is_active?<PauseCircle size={14}/>:<PlayCircle size={14}/>} {product.is_active?'Pause':'Make live'}</button></form><form action={deleteProduct}><input type="hidden" name="product_id" value={product.id}/><button className="btn btn-ghost"><Trash2 size={14}/> Remove</button></form></div></div></article>)}{!products?.length?<div className="v3-surface vendor-empty-state"><Store size={28}/><h3>No products yet</h3><p>Add clothes, gadgets, food items or anything students should be able to discover individually.</p></div>:null}</div>
        </div>
        <aside className="phase5b-editor vendor-editor-card">
          <div className="phase5b-editor-head"><Plus size={20}/><div><span>Add product</span><strong>{atLimit?'Active limit reached':`${productLimit-activeCount} active slot${productLimit-activeCount===1?'':'s'} available`}</strong></div></div>
          <form action={createProduct} style={{display:'grid',gap:13}}>
            <label><span>Product name</span><input name="name" required maxLength={140} placeholder="Black oversized hoodie" disabled={atLimit}/></label>
            <label><span>Category</span><select name="category_id" defaultValue="" disabled={atLimit}><option value="">Choose category</option>{(categories||[]).map(c=><option value={c.id} key={c.id}>{c.name}</option>)}</select></label>
            <label><span>Pricing</span><select name="pricing_type" defaultValue="fixed" disabled={atLimit}><option value="fixed">Fixed price</option><option value="from">Starting from</option><option value="contact">Contact for price</option></select></label>
            <label><span>Price (NGN)</span><input name="price_ngn" type="number" min="0" step="50" disabled={atLimit}/></label>
            <label><span>Description</span><textarea name="description" maxLength={1800} placeholder="Size, colour, condition, what is included…" disabled={atLimit}/></label>
            <label><span>Product image</span><input name="image" type="file" accept="image/jpeg,image/png,image/webp" disabled={atLimit}/></label>
            <button className="phase5b-primary" disabled={atLimit}><Plus size={16}/> {atLimit?'Limit reached':'Add product'}</button>
          </form>
        </aside>
      </section>
    </section>
  </main>
}
