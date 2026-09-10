import Link from 'next/link'
import { notFound, redirect } from 'next/navigation'
import { ArrowLeft, MessageCircle, Phone, ShieldCheck, Store } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'

export default async function StudentProductPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  const supabase = await createClient()
  const { data: userData } = await supabase.auth.getUser()
  const user = userData.user
  if (!user) redirect('/login')

  const { data: profile } = await supabase.from('profiles').select('account_type,institution_id,onboarding_completed_at').eq('id', user.id).maybeSingle()
  if (!profile || profile.account_type !== 'student') redirect('/dashboard')
  if (!profile.institution_id || !profile.onboarding_completed_at) redirect('/onboarding/student')

  const { data: product } = await supabase.from('vendor_products').select('id,vendor_id,name,description,price_ngn,pricing_type,cover_image_url').eq('id', id).eq('is_active', true).maybeSingle()
  if (!product) notFound()

  const { data: vendor } = await supabase.from('vendor_profiles').select('id,business_name,slug,logo_url,average_rating,review_count,verification_status').eq('id', product.vendor_id).maybeSingle()
  if (!vendor || vendor.verification_status !== 'approved') notFound()

  const priceLabel = product.pricing_type === 'contact' ? 'Contact for price' : `${product.pricing_type === 'from' ? 'From ' : ''}₦${Number(product.price_ngn || 0).toLocaleString()}`
  const itemParam = encodeURIComponent(product.name)

  return <main className="student-app">
    <header className="student-nav"><Link href="/student" className="student-brand">Campus<span>Link</span></Link><nav className="student-navlinks"><Link href="/student/discover">Discover</Link><Link href="/student/saved">Saved</Link></nav></header>
    <section className="student-shell" style={{maxWidth:1080}}>
      <Link href="/student" className="btn btn-ghost" style={{marginBottom:18}}><ArrowLeft size={16}/> Back</Link>
      <article style={{display:'grid',gridTemplateColumns:'minmax(0,1.1fr) minmax(320px,.9fr)',gap:28,alignItems:'start'}}>
        <div style={{background:'var(--cl-soft)',borderRadius:24,overflow:'hidden',minHeight:380}}>{product.cover_image_url ? <img src={product.cover_image_url} alt={product.name} style={{width:'100%',height:'100%',minHeight:380,objectFit:'cover'}}/> : <div style={{minHeight:380,display:'grid',placeItems:'center'}}><Store size={48}/></div>}</div>
        <section className="cl-editorial-surface" style={{padding:28}}>
          <span className="verified-line"><ShieldCheck size={15}/> Campus Link verified vendor</span>
          <h1 style={{fontSize:'clamp(30px,4vw,52px)',margin:'12px 0 8px'}}>{product.name}</h1>
          <strong style={{fontSize:24}}>{priceLabel}</strong>
          {product.description ? <p style={{color:'var(--cl-muted)',lineHeight:1.7,fontSize:15}}>{product.description}</p> : null}
          <div style={{borderTop:'1px solid var(--cl-line)',marginTop:22,paddingTop:20}}><span style={{fontSize:12,color:'var(--cl-muted)',textTransform:'uppercase',letterSpacing:'.08em'}}>Offered by</span><Link href={`/student/vendors/${vendor.slug}`} style={{display:'flex',gap:12,alignItems:'center',marginTop:10,textDecoration:'none',color:'inherit'}}><div className="vendor-avatar" style={{position:'static'}}>{vendor.logo_url ? <img src={vendor.logo_url} alt=""/> : vendor.business_name.slice(0,1)}</div><div><strong>{vendor.business_name}</strong><div style={{fontSize:13,color:'var(--cl-muted)'}}>{Number(vendor.average_rating || 0).toFixed(1)} · {vendor.review_count || 0} reviews</div></div></Link></div>
          <div style={{display:'grid',gap:10,marginTop:24}}><Link href={`/student/vendors/${vendor.slug}/contact?item=${itemParam}`} className="btn btn-primary"><MessageCircle size={17}/> Ask on WhatsApp</Link><Link href={`/student/vendors/${vendor.slug}/contact?channel=phone&item=${itemParam}`} className="btn btn-ghost"><Phone size={17}/> Call vendor</Link><Link href={`/student/vendors/${vendor.slug}`} className="btn btn-ghost">View full vendor profile</Link></div>
          <p style={{fontSize:12,color:'var(--cl-muted)',marginTop:16}}>Campus Link helps you discover and contact vendors. Payment and fulfilment happen directly with the vendor, not through Campus Link.</p>
        </section>
      </article>
    </section>
  </main>
}
