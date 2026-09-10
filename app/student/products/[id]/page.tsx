import Link from 'next/link'
import { notFound, redirect } from 'next/navigation'
import { ArrowLeft, MessageCircle, Phone, ShieldCheck, Store } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { VendorAnalyticsBeacon } from '@/components/vendor-analytics-beacon'
import { StudentMarketplaceHeader } from '@/components/student-marketplace-header'

export default async function StudentProductPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  const supabase = await createClient()
  const { data: userData } = await supabase.auth.getUser()
  const user = userData.user
  if (!user) redirect('/login')

  const { data: profile } = await supabase.from('profiles').select('first_name,account_type,institution_id,onboarding_completed_at').eq('id', user.id).maybeSingle()
  if (!profile || profile.account_type !== 'student') redirect('/dashboard')
  if (!profile.institution_id || !profile.onboarding_completed_at) redirect('/onboarding/student')

  const [{ data: institution }, { data: product }] = await Promise.all([
    supabase.from('institutions').select('name').eq('id', profile.institution_id).maybeSingle(),
    supabase.from('vendor_products').select('id,vendor_id,name,description,price_ngn,pricing_type,cover_image_url').eq('id', id).eq('is_active', true).maybeSingle(),
  ])
  if (!product) notFound()

  const [{ data: vendor }, { data: campusApproval }] = await Promise.all([
    supabase.from('vendor_profiles').select('id,business_name,slug,logo_url,average_rating,review_count,verification_status,marketplace_status,suspended_until').eq('id', product.vendor_id).maybeSingle(),
    supabase.from('vendor_institutions').select('vendor_id').eq('vendor_id', product.vendor_id).eq('institution_id', profile.institution_id).eq('status', 'approved').maybeSingle(),
  ])

  if (!vendor || vendor.verification_status !== 'approved' || !campusApproval) notFound()
  const safetyAllowed = vendor.marketplace_status === 'active' || (vendor.marketplace_status === 'suspended' && vendor.suspended_until && new Date(vendor.suspended_until) <= new Date())
  if (!safetyAllowed) notFound()

  const price = product.pricing_type === 'contact' ? 'Contact for price' : `${product.pricing_type === 'from' ? 'From ' : ''}₦${Number(product.price_ngn || 0).toLocaleString()}`
  const item = encodeURIComponent(product.name)

  return <main className="cl-student-page">
    <VendorAnalyticsBeacon vendorId={vendor.id} event="product_view" productId={product.id}/>
    <StudentMarketplaceHeader firstName={profile.first_name} schoolName={institution?.name}/>
    <section className="cl-student-shell">
      <Link href="/student" style={{color:'var(--v3-ink)',textDecoration:'none',fontWeight:800,display:'inline-flex',gap:7,alignItems:'center',marginBottom:18}}><ArrowLeft size={16}/> Back to marketplace</Link>
      <div className="v3-split">
        <div className="v3-product-image" style={{borderRadius:16,minHeight:430}}>{product.cover_image_url ? <img src={product.cover_image_url} alt={product.name}/> : <Store size={48}/>}</div>
        <section className="v3-surface" style={{borderRadius:16}}>
          <span className="v3-trust-line" style={{borderTop:0,paddingTop:0}}><ShieldCheck size={16}/> Identity verified + campus approved</span>
          <h1 style={{fontSize:'clamp(32px,5vw,52px)',letterSpacing:'-.045em',margin:'16px 0 6px'}}>{product.name}</h1>
          <strong style={{fontSize:24}}>{price}</strong>
          {product.description ? <p style={{color:'var(--v3-muted)',lineHeight:1.7}}>{product.description}</p> : null}
          <div style={{borderTop:'1px solid var(--v3-line)',marginTop:24,paddingTop:20}}>
            <small style={{color:'var(--v3-muted)',textTransform:'uppercase',letterSpacing:'.08em',fontWeight:800}}>Offered by</small>
            <Link href={`/student/vendors/${vendor.slug}`} style={{display:'flex',gap:12,alignItems:'center',marginTop:10,textDecoration:'none',color:'inherit'}}>
              <div style={{width:46,height:46,borderRadius:'50%',overflow:'hidden',background:'var(--v3-soft-blue)',display:'grid',placeItems:'center',fontWeight:900}}>{vendor.logo_url ? <img src={vendor.logo_url} alt="" style={{width:'100%',height:'100%',objectFit:'cover'}}/> : vendor.business_name.slice(0,1)}</div>
              <div><strong>{vendor.business_name}</strong><div style={{fontSize:13,color:'var(--v3-muted)'}}>{Number(vendor.average_rating || 0).toFixed(1)} · {vendor.review_count || 0} reviews</div></div>
            </Link>
          </div>
          <div style={{display:'grid',gap:10,marginTop:24}}>
            <Link href={`/student/vendors/${vendor.slug}/contact?item=${item}&product=${product.id}`} className="btn btn-primary"><MessageCircle size={17}/> Ask on WhatsApp</Link>
            <Link href={`/student/vendors/${vendor.slug}/contact?channel=phone&item=${item}&product=${product.id}`} className="btn btn-ghost"><Phone size={17}/> Call vendor</Link>
            <Link href={`/student/vendors/${vendor.slug}`} className="btn btn-ghost">View full storefront</Link>
          </div>
          <p style={{fontSize:12,color:'var(--v3-muted)',marginTop:18}}>Campus Link helps you discover and contact approved vendors. Payment and fulfilment remain directly between you and the vendor.</p>
        </section>
      </div>
    </section>
  </main>
}
