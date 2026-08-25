import Link from 'next/link'
import { redirect } from 'next/navigation'
import { BadgeCheck, BarChart3, Bookmark, Building2, MessageCircle, Star } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'

export default async function VendorDashboard() {
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const { data: profile } = await supabase.from('profiles').select('first_name,account_type').eq('id', userId).single()
  if (profile?.account_type !== 'vendor') redirect('/dashboard')

  const { data: vendor } = await supabase
    .from('vendor_profiles')
    .select('business_name,slug,verification_status,onboarding_completed_at,average_rating,review_count')
    .eq('id', userId)
    .maybeSingle()

  if (!vendor?.onboarding_completed_at) redirect('/onboarding/vendor')

  const [{ data: campus }, contactResult, saveResult] = await Promise.all([
    supabase.from('vendor_institutions').select('status,institution_id').eq('vendor_id', userId).eq('is_primary', true).maybeSingle(),
    supabase.from('contact_events').select('id', { count: 'exact', head: true }).eq('vendor_id', userId),
    supabase.from('saved_vendors').select('vendor_id', { count: 'exact', head: true }).eq('vendor_id', userId),
  ])

  let school: string | null = null
  if (campus?.institution_id) {
    const { data: institution } = await supabase.from('institutions').select('name').eq('id', campus.institution_id).maybeSingle()
    school = institution?.name || null
  }

  const status = vendor.verification_status || 'pending'
  const discoverable = status === 'approved' && campus?.status === 'approved'

  return (
    <main className="portal-shell">
      <header className="portal-topbar">
        <Link href="/" className="brand">Campus<span>Link</span></Link>
        <form action="/auth/signout" method="post"><button className="btn btn-ghost">Sign out</button></form>
      </header>

      <section className="portal-hero">
        <div>
          <p className="eyebrow">Vendor dashboard</p>
          <h1>{vendor.business_name}</h1>
          <p>{discoverable ? `Your business is live for verified discovery around ${school || 'your approved campus'}.` : `${school ? `Primary campus: ${school}. ` : ''}Your business becomes discoverable only after both vendor verification and campus approval.`}</p>
        </div>
        <div className={`status-card status-${status}`}><BadgeCheck size={22}/><div><span>Vendor verification</span><strong>{status.replace('_',' ')}</strong></div></div>
      </section>

      <section className="metrics-row">
        <div><span>Student contacts</span><strong>{contactResult.count || 0}</strong></div>
        <div><span>Student saves</span><strong>{saveResult.count || 0}</strong></div>
        <div><span>Average rating</span><strong>{Number(vendor.average_rating || 0).toFixed(1)}</strong></div>
        <div><span>Reviews</span><strong>{vendor.review_count || 0}</strong></div>
      </section>

      <section className="portal-grid">
        <article className="portal-action primary-action"><Building2 size={24}/><div><strong>Campus visibility</strong><span>{campus?.status === 'approved' ? 'Approved for your selected campus.' : 'Your selected campus is awaiting review.'}</span></div></article>
        <article className="portal-action"><MessageCircle size={24}/><div><strong>Student enquiries</strong><span>{contactResult.count ? `${contactResult.count} student contact${contactResult.count === 1 ? '' : 's'} recorded.` : 'No student contacts recorded yet.'}</span></div></article>
        <article className="portal-action"><Bookmark size={24}/><div><strong>Saved by students</strong><span>{saveResult.count ? `${saveResult.count} student${saveResult.count === 1 ? '' : 's'} saved your profile.` : 'Your first save will appear here.'}</span></div></article>
        <article className="portal-action"><Star size={24}/><div><strong>Reputation</strong><span>{vendor.review_count ? `${vendor.review_count} review${vendor.review_count === 1 ? '' : 's'} averaging ${Number(vendor.average_rating || 0).toFixed(1)}/5.` : 'Reviews from verified students will appear here.'}</span></div></article>
        <article className="portal-action"><BarChart3 size={24}/><div><strong>Discovery status</strong><span>{discoverable ? 'Live — students at your approved campus can find you.' : 'Not public yet — verification controls are protecting student discovery.'}</span></div></article>
      </section>
    </main>
  )
}
