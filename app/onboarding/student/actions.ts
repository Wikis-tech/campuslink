'use server'

import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'

const ALLOWED_DOCUMENT_TYPES = new Set(['image/jpeg', 'image/png', 'image/webp', 'application/pdf'])
const MAX_DOCUMENT_BYTES = 5 * 1024 * 1024

function clean(value: FormDataEntryValue | null, max = 160) {
  return String(value || '').trim().slice(0, max)
}

function safeFileName(name: string) {
  const ext = name.includes('.') ? `.${name.split('.').pop()?.toLowerCase()}` : ''
  return `${crypto.randomUUID()}${ext}`
}

export async function completeStudentOnboarding(formData: FormData) {
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const institutionId = clean(formData.get('institution_id'), 80)
  const phone = clean(formData.get('phone'), 30)
  const course = clean(formData.get('course_of_study'), 120)
  const level = clean(formData.get('study_level'), 40)
  const schoolEmail = clean(formData.get('school_email'), 254).toLowerCase()
  const matricNumber = clean(formData.get('matric_number'), 80)
  const verificationMethod = clean(formData.get('verification_method'), 30)
  const document = formData.get('verification_document')

  if (!institutionId || !phone || !course || !level) {
    redirect('/onboarding/student?error=Please%20complete%20all%20required%20fields')
  }

  const { data: institution } = await supabase
    .from('institutions')
    .select('id,email_domain,is_active')
    .eq('id', institutionId)
    .eq('is_active', true)
    .maybeSingle()

  if (!institution) {
    redirect('/onboarding/student?error=Please%20select%20a%20valid%20school')
  }

  if (verificationMethod === 'school_email') {
    if (!schoolEmail || !schoolEmail.includes('@')) {
      redirect('/onboarding/student?error=Enter%20a%20valid%20school%20email')
    }
    if (institution.email_domain && !schoolEmail.endsWith(`@${institution.email_domain}`)) {
      redirect('/onboarding/student?error=That%20email%20does%20not%20match%20the%20selected%20school')
    }
  }

  let storagePath: string | null = null
  if (document instanceof File && document.size > 0) {
    if (document.size > MAX_DOCUMENT_BYTES || !ALLOWED_DOCUMENT_TYPES.has(document.type)) {
      redirect('/onboarding/student?error=Verification%20document%20must%20be%20PDF%2C%20JPG%2C%20PNG%20or%20WEBP%20and%20under%205MB')
    }

    storagePath = `${userId}/student/${safeFileName(document.name)}`
    const { error: uploadError } = await supabase.storage
      .from('verification-documents')
      .upload(storagePath, document, { contentType: document.type, upsert: false })

    if (uploadError) {
      redirect('/onboarding/student?error=We%20could%20not%20upload%20your%20verification%20document')
    }
  }

  const now = new Date().toISOString()
  const { error: profileError } = await supabase
    .from('profiles')
    .update({
      institution_id: institutionId,
      phone,
      course_of_study: course,
      study_level: level,
      school_email: schoolEmail || null,
      onboarding_completed_at: now,
    })
    .eq('id', userId)
    .eq('account_type', 'student')

  if (profileError) {
    if (storagePath) await supabase.storage.from('verification-documents').remove([storagePath])
    redirect('/onboarding/student?error=We%20could%20not%20save%20your%20profile')
  }

  const method = verificationMethod === 'school_email' ? 'school_email' : document instanceof File && document.size > 0 ? 'student_id' : 'manual'
  const { error: verificationError } = await supabase
    .from('student_verifications')
    .upsert({
      student_id: userId,
      matric_number: matricNumber || null,
      school_email: schoolEmail || null,
      verification_method: method,
      status: 'pending',
      submitted_at: now,
      reviewed_by: null,
      reviewed_at: null,
      review_note: null,
    }, { onConflict: 'student_id' })

  if (verificationError) {
    redirect('/onboarding/student?error=Your%20profile%20was%20saved%20but%20verification%20submission%20failed')
  }

  if (storagePath) {
    const { error: documentError } = await supabase.from('student_documents').insert({
      student_id: userId,
      document_type: verificationMethod === 'school_email' ? 'other' : 'student_id',
      storage_path: storagePath,
      status: 'pending',
    })

    if (documentError) {
      await supabase.storage.from('verification-documents').remove([storagePath])
      redirect('/onboarding/student?error=Verification%20record%20could%20not%20be%20created')
    }
  }

  redirect('/student?onboarding=complete')
}

export async function requestSchool(formData: FormData) {
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const schoolName = clean(formData.get('school_name'), 180)
  const city = clean(formData.get('city'), 100)
  const state = clean(formData.get('state'), 100)
  const website = clean(formData.get('website'), 240)

  if (schoolName.length < 4) redirect('/onboarding/student?error=Enter%20the%20full%20name%20of%20your%20school')

  await supabase.from('school_requests').insert({
    requested_by: userId,
    school_name: schoolName,
    city: city || null,
    state: state || null,
    website: website || null,
  })

  redirect('/onboarding/student?school_request=sent')
}
