import Link from 'next/link'
import { redirect } from 'next/navigation'
import { AlertTriangle, BadgeCheck, Flag, Handshake, LockKeyhole, ShieldCheck, UsersRound } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { checkPhase5gReadiness } from '@/lib/phase5g-readiness'
import { StudentMarketplaceHeader } from '@/components/student-marketplace-header'

const categoryLabel: Record<string,string> = {
  fraud_scam:'Fraud / scam', harassment:'Harassment', fake_product:'Fake product', misrepresentation:'Misrepresentation', unsafe_behavior:'Unsafe behaviour', spam:'Spam', prohibited_item:'Prohibited item', other:'Other'
}

export default async function StudentSafetyPage() {
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const { data: profile } = await supabase.from('profiles').select('first_name,account_type,institution_id,onboarding_completed_at').eq('id', userId).maybeSingle()
  if (!profile || profile.account_type !== 'student') redirect('/dashboard')
  if (!profile.onboarding_completed_at || !profile.institution_id) redirect('/onboarding/student')

  const readiness = await checkPhase5gReadiness(supabase)

  const [{ data: institution }, reportResult] = await Promise.all([
    supabase.from('institutions').select('name').eq('id', profile.institution_id).maybeSingle(),
    readiness.schemaReady
      ? supabase.from('complaints').select('id,vendor_id,title,category,status,created_at').eq('reporter_id', userId).order('created_at',{ascending:false}).limit(20)
      : supabase.from('complaints').select('id,vendor_id,title,status,created_at').eq('reporter_id', userId).order('created_at',{ascending:false}).limit(20),
  ])
  const reports = (reportResult.data || []).map((row:any) => ({ ...row, category: row.category || 'other' }))
  const vendorIds = Array.from(new Set((reports || []).map((r:any)=>r.vendor_id).filter(Boolean))) as string[]
  const { data: vendors } = vendorIds.length ? await supabase.from('vendor_profiles').select('id,business_name,slug').in('id',vendorIds) : { data: [] as any[] }
  const vendorMap = new Map((vendors || []).map((v:any)=>[v.id,v]))

  return <main className="cl-student-page">
    <StudentMarketplaceHeader firstName={profile.first_name} schoolName={institution?.name}/>
    <section className="cl-student-shell phase5g-safety-page">
      {!readiness.ready ? <div className="notice error">Some advanced trust features are temporarily unavailable while Campus Link finishes a safety-system update. Safety guidance remains available.</div> : null}
      <header className="phase5g-safety-hero"><span><ShieldCheck size={16}/> Campus Link Safety Centre</span><h1>Trade around campus with more confidence.</h1><p>Campus Link helps you identify approved businesses, understand trust signals and report concerns privately. Campus Link does not process the payment between you and a Vendor.</p></header>

      <section className="phase5g-safety-grid">
        <article className="v3-surface phase5g-safety-card"><BadgeCheck/><h2>Check trust signals</h2><p>Look for identity verification, campus approval, account standing and verified-contact reviews. Paid plans never buy these trust indicators.</p></article>
        <article className="v3-surface phase5g-safety-card"><UsersRound/><h2>Meet safely</h2><p>For in-person exchanges, prefer public campus areas and tell someone where you are going when the situation calls for it.</p></article>
        <article className="v3-surface phase5g-safety-card"><LockKeyhole/><h2>Protect your money</h2><p>Confirm the item, service, price and Vendor details before paying. Be cautious when someone pressures you to pay unusually fast or outside agreed terms.</p></article>
        <article className="v3-surface phase5g-safety-card"><Handshake/><h2>Keep useful records</h2><p>Keep relevant messages, receipts and transaction evidence. They can help Campus Link Admin understand a report later.</p></article>
      </section>

      <section className="v3-surface phase5g-safety-warning"><AlertTriangle size={20}/><div><strong>Something feels unsafe?</strong><p>Do not continue an interaction just because a Vendor is listed on Campus Link. Stop the interaction when needed and report the concern from the Vendor profile. For immediate physical danger, contact the appropriate local or campus emergency authority.</p></div></section>

      <section className="cl-student-section"><div className="cl-student-section-head"><div><h2>Your reports</h2><p>Reports are private to you and authorized Campus Link Admins. Vendors do not receive your investigation notes.</p></div></div>
        <div className="phase5g-report-history">{(reports || []).map((report:any)=>{ const vendor=vendorMap.get(report.vendor_id); return <article className="v3-surface" key={report.id}><div><span className={`phase5g-case-status ${report.status}`}>{report.status}</span><strong>{report.title}</strong><small>{categoryLabel[report.category] || 'Other'} · {new Date(report.created_at).toLocaleDateString('en-NG')}</small></div>{vendor ? <Link href={`/student/vendors/${vendor.slug}`}>{vendor.business_name}</Link> : <span>Vendor unavailable</span>}</article> })}{!reports?.length ? <div className="v3-surface phase5g-empty"><Flag size={20}/><strong>No reports submitted</strong><p>If you ever need to report a Vendor, use the private report form on that Vendor's profile.</p></div> : null}</div>
      </section>
    </section>
  </main>
}
