import Link from 'next/link'
import { redirect } from 'next/navigation'
import { BadgeCheck, BookOpen, Building2, Mail, Phone, ShieldCheck, UserRound } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { StudentMarketplaceHeader } from '@/components/student-marketplace-header'

export default async function StudentProfilePage() {
  const supabase = await createClient()
  const { data: userData } = await supabase.auth.getUser()
  const user = userData.user
  if (!user) redirect('/login')

  const { data: profile } = await supabase
    .from('profiles')
    .select('first_name,last_name,phone,account_type,institution_id,course_of_study,study_level,school_email,student_verification_status,onboarding_completed_at')
    .eq('id', user.id)
    .maybeSingle()

  if (!profile || profile.account_type !== 'student') redirect('/dashboard')
  if (!profile.onboarding_completed_at || !profile.institution_id) redirect('/onboarding/student')
  if (profile.student_verification_status !== 'verified') redirect('/onboarding/student')

  const { data: institution } = await supabase
    .from('institutions')
    .select('name,city,state')
    .eq('id', profile.institution_id)
    .maybeSingle()

  const fullName = [profile.first_name, profile.last_name].filter(Boolean).join(' ') || 'Student'
  const schoolLocation = [institution?.city, institution?.state].filter(Boolean).join(', ')

  return (
    <main className="cl-student-page">
      <StudentMarketplaceHeader firstName={profile.first_name} schoolName={institution?.name} verificationStatus={profile.student_verification_status}/>
      <section className="cl-student-shell">
        <div className="student-head">
          <div><h1>Your profile</h1><p>Your Student identity and campus details in one place. Your verification is already complete.</p></div>
          <span className="campus-chip"><BadgeCheck size={16}/> Verified Student</span>
        </div>

        <section className="v3-surface" style={{padding:24,marginTop:20}}>
          <div style={{display:'flex',gap:16,alignItems:'center',marginBottom:24,flexWrap:'wrap'}}>
            <span className="cl-student-avatar" style={{width:54,height:54,fontSize:20}}>{(profile.first_name || 'S').slice(0,1).toUpperCase()}</span>
            <div><h2 style={{margin:0}}>{fullName}</h2><p style={{margin:'5px 0 0',color:'var(--v3-muted, #667085)'}}>{institution?.name || 'Campus Link Student'}</p></div>
            <span style={{marginLeft:'auto',display:'inline-flex',alignItems:'center',gap:7,color:'var(--v3-success, #16a34a)',fontWeight:700}}><ShieldCheck size={17}/> Verified</span>
          </div>
          <div style={{display:'grid',gridTemplateColumns:'repeat(auto-fit,minmax(220px,1fr))',gap:14}}>
            <article className="v3-surface" style={{padding:16}}><UserRound size={18}/><small style={{display:'block',marginTop:8,color:'var(--v3-muted, #667085)'}}>Name</small><strong>{fullName}</strong></article>
            <article className="v3-surface" style={{padding:16}}><Building2 size={18}/><small style={{display:'block',marginTop:8,color:'var(--v3-muted, #667085)'}}>School</small><strong>{institution?.name || 'Not available'}</strong>{schoolLocation ? <div style={{marginTop:4,color:'var(--v3-muted, #667085)'}}>{schoolLocation}</div> : null}</article>
            <article className="v3-surface" style={{padding:16}}><BookOpen size={18}/><small style={{display:'block',marginTop:8,color:'var(--v3-muted, #667085)'}}>Course & level</small><strong>{profile.course_of_study || 'Not provided'}</strong>{profile.study_level ? <div style={{marginTop:4,color:'var(--v3-muted, #667085)'}}>{profile.study_level}</div> : null}</article>
            <article className="v3-surface" style={{padding:16}}><Phone size={18}/><small style={{display:'block',marginTop:8,color:'var(--v3-muted, #667085)'}}>Phone</small><strong>{profile.phone || 'Not provided'}</strong></article>
            <article className="v3-surface" style={{padding:16}}><Mail size={18}/><small style={{display:'block',marginTop:8,color:'var(--v3-muted, #667085)'}}>School email</small><strong>{profile.school_email || user.email || 'Not provided'}</strong></article>
          </div>
        </section>

        <section className="v3-surface" style={{padding:20,marginTop:18,display:'flex',justifyContent:'space-between',alignItems:'center',gap:16,flexWrap:'wrap'}}>
          <div><strong>Verification complete.</strong><p style={{margin:'5px 0 0',color:'var(--v3-muted, #667085)'}}>Campus Link keeps your verification evidence private. You will only be asked to verify again if an authorized Admin explicitly requires a new submission.</p></div>
          <Link href="/student" className="btn btn-ghost">Back to home</Link>
        </section>
      </section>
    </main>
  )
}
