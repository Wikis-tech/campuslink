import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'

export default async function StudentDashboard() {
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const { data: profile } = await supabase
    .from('profiles')
    .select('first_name,account_type,institution_id,student_verification_status')
    .eq('id', userId)
    .single()

  if (profile?.account_type === 'vendor') redirect('/vendor-v2')

  return (
    <main className="dash-shell">
      <section className="dash-card">
        <p className="eyebrow">Student dashboard</p>
        <h1>Welcome{profile?.first_name ? `, ${profile.first_name}` : ''}</h1>
        <p>Your account is connected. Next we’ll complete your school selection and verification, then unlock campus-specific vendor discovery.</p>
        <div className="status-pill">Verification: {profile?.student_verification_status || 'pending'}</div>
        <form action="/auth/signout" method="post"><button className="btn btn-ghost">Sign out</button></form>
      </section>
    </main>
  )
}
