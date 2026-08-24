import Link from 'next/link'
import { redirect } from 'next/navigation'
import { BadgeCheck, Building2, GraduationCap, ShieldCheck } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { completeStudentOnboarding, requestSchool } from './actions'

export default async function StudentOnboardingPage({
  searchParams,
}: {
  searchParams: Promise<{ error?: string; school_request?: string }>
}) {
  const params = await searchParams
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const { data: profile } = await supabase
    .from('profiles')
    .select('account_type,first_name,last_name,phone,institution_id,course_of_study,study_level,school_email,onboarding_completed_at')
    .eq('id', userId)
    .single()

  if (profile?.account_type === 'vendor') redirect('/onboarding/vendor')
  if (profile?.onboarding_completed_at) redirect('/student')

  const { data: institutions } = await supabase
    .from('institutions')
    .select('id,name,city,state')
    .eq('is_active', true)
    .order('name')

  return (
    <main className="onboarding-shell">
      <section className="onboarding-layout">
        <aside className="onboarding-aside">
          <Link href="/" className="brand">Campus<span>Link</span></Link>
          <div className="step-kicker">Student setup · 1 of 1</div>
          <h1>Make Campus Link yours.</h1>
          <p>Tell us where you study so we can show you vendors who are actually relevant to your campus.</p>
          <div className="trust-stack">
            <div><GraduationCap size={18} /><span>Campus-specific discovery</span></div>
            <div><BadgeCheck size={18} /><span>Verified student accounts</span></div>
            <div><ShieldCheck size={18} /><span>Private verification documents</span></div>
          </div>
        </aside>

        <section className="onboarding-panel">
          <div className="panel-heading">
            <p className="eyebrow">Complete your student profile</p>
            <h2>Welcome{profile?.first_name ? `, ${profile.first_name}` : ''}.</h2>
            <p>This takes about two minutes. Your verification document is never shown publicly.</p>
          </div>

          {params.error ? <div className="auth-error">{params.error}</div> : null}
          {params.school_request === 'sent' ? <div className="auth-success">School request sent. We’ll review it before adding it to Campus Link.</div> : null}

          <form action={completeStudentOnboarding} className="onboarding-form">
            <div className="field-block">
              <label htmlFor="institution_id">Your school</label>
              <select id="institution_id" name="institution_id" required defaultValue={profile?.institution_id || ''}>
                <option value="" disabled>Select your institution</option>
                {(institutions || []).map((school) => (
                  <option key={school.id} value={school.id}>{school.name}{school.city ? ` — ${school.city}` : ''}</option>
                ))}
              </select>
              <small>Only approved institutions appear here.</small>
            </div>

            <div className="form-grid-2">
              <div className="field-block">
                <label htmlFor="course_of_study">Course of study</label>
                <input id="course_of_study" name="course_of_study" required maxLength={120} defaultValue={profile?.course_of_study || ''} placeholder="Computer Science" />
              </div>
              <div className="field-block">
                <label htmlFor="study_level">Level</label>
                <select id="study_level" name="study_level" required defaultValue={profile?.study_level || ''}>
                  <option value="" disabled>Select level</option>
                  <option>100 Level</option><option>200 Level</option><option>300 Level</option><option>400 Level</option><option>500 Level</option><option>Postgraduate</option><option>Other</option>
                </select>
              </div>
            </div>

            <div className="form-grid-2">
              <div className="field-block">
                <label htmlFor="phone">Phone number</label>
                <input id="phone" name="phone" type="tel" required maxLength={30} defaultValue={profile?.phone || ''} placeholder="0800 000 0000" />
              </div>
              <div className="field-block">
                <label htmlFor="matric_number">Matric number <span>optional</span></label>
                <input id="matric_number" name="matric_number" maxLength={80} placeholder="Your matric/student number" />
              </div>
            </div>

            <fieldset className="verification-choice">
              <legend>How would you like to verify?</legend>
              <label><input type="radio" name="verification_method" value="student_id" defaultChecked /> <span><strong>Student ID or school evidence</strong><small>Upload a student ID, admission letter or school portal evidence.</small></span></label>
              <label><input type="radio" name="verification_method" value="school_email" /> <span><strong>School email</strong><small>Use your institution email where your school provides one.</small></span></label>
            </fieldset>

            <div className="field-block">
              <label htmlFor="school_email">School email <span>optional</span></label>
              <input id="school_email" name="school_email" type="email" maxLength={254} defaultValue={profile?.school_email || ''} placeholder="name@school.edu.ng" />
            </div>

            <div className="upload-box">
              <div><ShieldCheck size={22} /><strong>Verification document</strong></div>
              <p>PDF, JPG, PNG or WEBP. Maximum 5MB. Stored privately.</p>
              <input name="verification_document" type="file" accept=".pdf,image/jpeg,image/png,image/webp" />
            </div>

            <button className="btn btn-primary onboarding-submit" type="submit">Save & submit verification</button>
          </form>

          <details className="school-request">
            <summary><Building2 size={17} /> My school is not listed</summary>
            <form action={requestSchool} className="school-request-form">
              <input name="school_name" placeholder="Full school name" required maxLength={180} />
              <div className="form-grid-2"><input name="city" placeholder="City" maxLength={100} /><input name="state" placeholder="State" maxLength={100} /></div>
              <input name="website" type="url" placeholder="School website (optional)" maxLength={240} />
              <button className="btn btn-ghost" type="submit">Request school</button>
            </form>
          </details>
        </section>
      </section>
    </main>
  )
}
