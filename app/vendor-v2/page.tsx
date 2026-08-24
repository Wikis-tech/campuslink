import Link from 'next/link'
import { redirect } from 'next/navigation'
import { BadgeCheck, BarChart3, Building2, MessageCircle } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'

export default async function VendorDashboard() {
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const { data: profile } = await supabase
    .from('profiles')
    .select('first_name,account_type')
    .eq('id', userId)
    .single()

  if (profile?.account_type !== 'vendor') redirect('/dashboard')

  const { data: vendor } = await supabase
    .from('vendor_profiles')
    .select('business_name,verification_status,onboarding_completed_at,total_contacts,average_rating,review_count')
    .eq('id', userId)
    .maybeSingle()

  if (!vendor?.onboarding_completed_at) redirect('/onboarding/vendor')

  const { data: campus } = await supabase
    .from('vendor_institutions')
    .select('status,institutions(name)')
    .eq('vendor_id', userId)
    .eq('is_primary', true)
    .maybeSingle()

  const school = Array.isArray(campus?.institutions) ? campus.institutions[0]?.name : (campus?.institutions as { name?: string } | null)?.name
  const status = vendor.verification_status || 'pending'

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
          <p>{school ? `Primary campus: ${school}.` : 'Campus selection submitted.'} Your business will only appear publicly after verification and campus approval.</p>
        </div>
        <div className={`status-card status-${status}`}>
          <BadgeCheck size={22} />
          <div><span>Vendor verification</span><strong>{status.replace('_', ' ')}</strong></div>
        </div>
      </section>

      <section className="metrics-row">
        <div><span>Profile contacts</span><strong>{vendor.total_contacts || 0}</strong></div>
        <div><span>Average rating</span><strong>{Number(vendor.average_rating || 0).toFixed(1)}</strong></div>
        <div><span>Reviews</span><strong>{vendor.review_count || 0}</strong></div>
      </section>

      <section className="portal-grid">
        <article className="portal-action primary-action"><Building2 size={24} /><div><strong>Campus approval</strong><span>{campus?.status === 'approved' ? 'Approved for your selected campus.' : 'Your selected campus is awaiting review.'}</span></div></article>
        <article className="portal-action"><MessageCircle size={24} /><div><strong>Student enquiries</strong><span>Contact analytics will appear here once discovery launches.</span></div></article>
        <article className="portal-action"><BarChart3 size={24} /><div><strong>Business analytics</strong><span>Views, saves and contacts will become measurable in Phase 3.</span></div></article>
      </section>
    </main>
  )
}
