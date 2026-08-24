import Link from 'next/link'
import { redirect } from 'next/navigation'
import { BadgeCheck, Bookmark, Search, ShieldCheck } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'

export default async function StudentDashboard() {
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const { data: profile } = await supabase
    .from('profiles')
    .select('first_name,account_type,institution_id,student_verification_status,onboarding_completed_at')
    .eq('id', userId)
    .single()

  if (profile?.account_type === 'vendor') redirect('/dashboard')
  if (!profile?.onboarding_completed_at) redirect('/onboarding/student')

  const [{ data: verification }, institutionResult] = await Promise.all([
    supabase
      .from('student_verifications')
      .select('status,verification_method,submitted_at,review_note')
      .eq('student_id', userId)
      .maybeSingle(),
    profile?.institution_id
      ? supabase.from('institutions').select('name').eq('id', profile.institution_id).maybeSingle()
      : Promise.resolve({ data: null }),
  ])

  const school = institutionResult.data?.name || null
  const status = verification?.status || profile?.student_verification_status || 'pending'

  return (
    <main className="portal-shell">
      <header className="portal-topbar">
        <Link href="/" className="brand">Campus<span>Link</span></Link>
        <form action="/auth/signout" method="post"><button className="btn btn-ghost">Sign out</button></form>
      </header>

      <section className="portal-hero">
        <div>
          <p className="eyebrow">Student dashboard</p>
          <h1>Hi{profile?.first_name ? `, ${profile.first_name}` : ''}.</h1>
          <p>{school ? `You’re connected to ${school}.` : 'Your campus profile is ready.'} Verified vendors for your school will appear here as Campus Link grows.</p>
        </div>
        <div className={`status-card status-${status}`}>
          <ShieldCheck size={22} />
          <div><span>Student verification</span><strong>{status.replace('_', ' ')}</strong></div>
        </div>
      </section>

      <section className="portal-grid">
        <article className="portal-action primary-action">
          <Search size={24} />
          <div><strong>Find a service</strong><span>Search verified vendors around your campus.</span></div>
          <span className="coming-soon">Discovery opens in Phase 3</span>
        </article>
        <article className="portal-action">
          <Bookmark size={24} />
          <div><strong>Saved vendors</strong><span>Keep the people you trust easy to find.</span></div>
        </article>
        <article className="portal-action">
          <BadgeCheck size={24} />
          <div><strong>Verification</strong><span>{status === 'verified' ? 'Your student account is verified.' : 'Your verification is being reviewed.'}</span></div>
        </article>
      </section>
    </main>
  )
}
