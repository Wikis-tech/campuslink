import { redirect } from 'next/navigation'
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

  if (profile?.account_type !== 'vendor') redirect('/student')

  const { data: vendor } = await supabase
    .from('vendor_profiles')
    .select('business_name,verification_status')
    .eq('id', userId)
    .maybeSingle()

  return (
    <main className="dash-shell">
      <section className="dash-card">
        <p className="eyebrow">Vendor dashboard</p>
        <h1>{vendor?.business_name || 'Set up your vendor profile'}</h1>
        <p>Your account is connected. The next step is business profile setup, school selection and verification documents.</p>
        <div className="status-pill">Verification: {vendor?.verification_status || 'not submitted'}</div>
        <form action="/auth/signout" method="post"><button className="btn btn-ghost">Sign out</button></form>
      </section>
    </main>
  )
}
