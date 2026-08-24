import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'

export default async function AdminDashboard() {
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const { data: admin } = await supabase
    .from('admin_memberships')
    .select('role,is_active')
    .eq('user_id', userId)
    .eq('is_active', true)
    .maybeSingle()

  if (!admin) redirect('/dashboard')

  return (
    <main className="dash-shell">
      <section className="dash-card">
        <p className="eyebrow">Admin dashboard</p>
        <h1>Campus Link control centre</h1>
        <p>You’re signed in with <strong>{admin.role}</strong> access. Vendor approvals, complaints, finance, reviews, schools, plans and audit logs will be rebuilt here in the next admin phase.</p>
        <div className="status-pill">Admin access: active</div>
        <form action="/auth/signout" method="post"><button className="btn btn-ghost">Sign out</button></form>
      </section>
    </main>
  )
}
