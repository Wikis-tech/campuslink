'use server'

import { revalidatePath } from 'next/cache'
import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'
import { canManageSchools, requireAdminContext } from './lib'

function text(formData: FormData, key: string, max = 500) {
  return String(formData.get(key) || '').trim().slice(0, max)
}

function slugify(value: string) {
  return value
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
    .slice(0, 120)
}

function message(path: string, type: 'success' | 'error', value: string): never {
  redirect(`${path}?${type}=${encodeURIComponent(value)}`)
}

export async function createInstitution(formData: FormData) {
  const supabase = await createClient()
  const name = text(formData, 'name', 180)
  const slug = slugify(text(formData, 'slug', 140) || name)
  const city = text(formData, 'city', 100)
  const state = text(formData, 'state', 100)
  const country = text(formData, 'country', 100) || 'Nigeria'
  const emailDomain = text(formData, 'email_domain', 180).toLowerCase().replace(/^@/, '')
  const mode = text(formData, 'verification_mode', 40) || 'hybrid'
  const instructions = text(formData, 'verification_instructions', 1200)
  const domains = text(formData, 'allowed_student_email_domains', 800)
    .split(',')
    .map((item) => item.trim().toLowerCase().replace(/^@/, ''))
    .filter(Boolean)

  if (name.length < 4 || !slug) message('/control-center/schools', 'error', 'Enter a valid school name.')

  const { data: newInstitutionId, error } = await supabase.rpc('admin_create_institution', {
    school_name: name,
    school_slug: slug,
    school_city: city || null,
    school_state: state || null,
    school_country: country,
    school_email_domain: emailDomain || null,
    school_verification_mode: mode,
    school_email_domains: domains,
    school_instructions: instructions || null,
  })

  if (error) message('/control-center/schools', 'error', error.message)
  revalidatePath('/control-center')
  revalidatePath('/control-center/schools')
  revalidatePath('/onboarding/student')
  if (newInstitutionId) redirect(`/control-center/schools/${newInstitutionId}?setup=created`)
  message('/control-center/schools', 'success', `${name} was added and is now available for onboarding.`)
}

export async function updateInstitutionSetup(formData: FormData) {
  const context = await requireAdminContext()
  if (!canManageSchools(context.globalRole)) message('/control-center', 'error', 'You do not have permission to change school setup.')

  const id = text(formData, 'institution_id', 80)
  const name = text(formData, 'name', 180)
  const city = text(formData, 'city', 100)
  const state = text(formData, 'state', 100)
  const country = text(formData, 'country', 100) || 'Nigeria'
  const emailDomain = text(formData, 'email_domain', 180).toLowerCase().replace(/^@/, '')
  const mode = text(formData, 'verification_mode', 40) || 'hybrid'
  const instructions = text(formData, 'verification_instructions', 1200)
  const domains = text(formData, 'allowed_student_email_domains', 800)
    .split(',')
    .map((item) => item.trim().toLowerCase().replace(/^@/, ''))
    .filter(Boolean)

  if (!id || name.length < 4) message('/control-center/schools', 'error', 'Enter a valid school name.')
  if (!['hybrid','institution_email','manual'].includes(mode)) message('/control-center/schools/' + id, 'error', 'Choose a valid verification mode.')

  const supabase = await createClient()
  const { error } = await supabase.from('institutions').update({
    name,
    city: city || null,
    state: state || null,
    country,
    email_domain: emailDomain || null,
    verification_mode: mode,
    allowed_student_email_domains: domains,
    verification_instructions: instructions || null,
  }).eq('id', id)

  if (error) message('/control-center/schools/' + id, 'error', error.message)
  revalidatePath('/control-center')
  revalidatePath('/control-center/schools')
  revalidatePath('/control-center/schools/' + id)
  revalidatePath('/onboarding/student')
  message('/control-center/schools/' + id, 'success', 'School setup updated.')
}

export async function setInstitutionActive(formData: FormData) {
  const supabase = await createClient()
  const id = text(formData, 'institution_id', 80)
  const active = text(formData, 'active', 10) === 'true'
  const { error } = await supabase.rpc('admin_set_institution_active', { target_id: id, active })
  if (error) message('/control-center/schools', 'error', error.message)
  revalidatePath('/control-center/schools')
  revalidatePath('/onboarding/student')
  message('/control-center/schools', 'success', active ? 'School restored.' : 'School archived. Existing records were preserved.')
}

export async function reviewStudent(formData: FormData) {
  const supabase = await createClient()
  const studentId = text(formData, 'student_id', 80)
  const decision = text(formData, 'decision', 20)
  const note = text(formData, 'note', 800)
  const { error } = await supabase.rpc('admin_review_student', { target_student: studentId, decision, note: note || null })
  if (error) message('/control-center/students', 'error', error.message)
  revalidatePath('/control-center/students')
  revalidatePath('/student')
  message('/control-center/students', 'success', decision === 'approve' ? 'Student verified.' : 'Student verification rejected.')
}

export async function reviewVendorIdentity(formData: FormData) {
  const supabase = await createClient()
  const vendorId = text(formData, 'vendor_id', 80)
  const decision = text(formData, 'decision', 20)
  const note = text(formData, 'note', 800)
  const { error } = await supabase.rpc('admin_review_vendor_identity', { target_vendor: vendorId, decision, note: note || null })
  if (error) message('/control-center/vendors', 'error', error.message)
  revalidatePath('/control-center/vendors')
  revalidatePath('/vendor-v2')
  message('/control-center/vendors', 'success', `Vendor identity ${decision === 'approve' ? 'approved' : decision + 'ed'}.`)
}

export async function reviewVendorCampus(formData: FormData) {
  const supabase = await createClient()
  const vendorId = text(formData, 'vendor_id', 80)
  const institutionId = text(formData, 'institution_id', 80)
  const decision = text(formData, 'decision', 20)
  const note = text(formData, 'note', 800)
  const { error } = await supabase.rpc('admin_review_vendor_campus', {
    target_vendor: vendorId,
    target_institution: institutionId,
    decision,
    note: note || null,
  })
  if (error) message('/control-center/vendors', 'error', error.message)
  revalidatePath('/control-center/vendors')
  revalidatePath('/student')
  revalidatePath('/student/discover')
  message('/control-center/vendors', 'success', `Campus access ${decision === 'approve' ? 'approved' : decision + 'ed'}.`)
}

export async function updateComplaint(formData: FormData) {
  const supabase = await createClient()
  const complaintId = text(formData, 'complaint_id', 80)
  const nextStatus = text(formData, 'status', 30)
  const { error } = await supabase.rpc('admin_update_complaint', { complaint_id: complaintId, next_status: nextStatus })
  if (error) message('/control-center/reports', 'error', error.message)
  revalidatePath('/control-center/reports')
  message('/control-center/reports', 'success', `Report moved to ${nextStatus}.`)
}

export async function assignSchoolAdmin(formData: FormData) {
  const supabase = await createClient()
  const returnToRaw = text(formData, 'return_to', 240)
  const returnTo = returnToRaw.startsWith('/control-center') ? returnToRaw : '/control-center/admins'
  const email = text(formData, 'email', 254).toLowerCase()
  const institutionId = text(formData, 'institution_id', 80)
  const role = text(formData, 'role', 40)
  if (!email.includes('@')) message(returnTo, 'error', 'Enter a valid Kampivo account email.')
  if (role !== 'school_admin') message(returnTo, 'error', 'School-scoped accounts use the School Admin role.')

  const { error } = await supabase.rpc('admin_assign_school_admin_by_email', {
    target_email: email,
    target_institution: institutionId,
    assignment_role: role,
  })
  if (error) message(returnTo, 'error', error.message)
  revalidatePath('/control-center/admins')
  revalidatePath(returnTo.split('?')[0])
  message(returnTo, 'success', 'School admin assignment saved.')
}

export async function assignGlobalAdmin(formData: FormData) {
  const supabase = await createClient()
  const email = text(formData, 'email', 254).toLowerCase()
  const role = text(formData, 'role', 50)
  if (!email.includes('@')) message('/control-center/admins', 'error', 'Enter a valid Kampivo account email.')

  const { error } = await supabase.rpc('admin_assign_global_role_by_email', { target_email: email, target_role: role })
  if (error) message('/control-center/admins', 'error', error.message)
  revalidatePath('/control-center/admins')
  message('/control-center/admins', 'success', 'Global admin role saved.')
}

export async function saveCategory(formData: FormData) {
  const supabase = await createClient()
  const id = text(formData, 'category_id', 80)
  const name = text(formData, 'name', 120)
  const slug = slugify(text(formData, 'slug', 140) || name)
  const description = text(formData, 'description', 500)
  const icon = text(formData, 'icon', 80)
  const active = text(formData, 'active', 10) !== 'false'
  if (name.length < 2 || !slug) message('/control-center/categories', 'error', 'Enter a valid category name.')

  const { error } = await supabase.rpc('admin_upsert_category', {
    category_id: id || null,
    category_name: name,
    category_slug: slug,
    category_description: description || null,
    category_icon: icon || null,
    category_active: active,
  })
  if (error) message('/control-center/categories', 'error', error.message)
  revalidatePath('/control-center/categories')
  revalidatePath('/student/discover')
  message('/control-center/categories', 'success', id ? 'Category updated.' : 'Category created.')
}

export async function moderateReview(formData: FormData) {
  const supabase = await createClient()
  const reviewId = text(formData, 'review_id', 80)
  const nextStatus = text(formData, 'status', 20)
  const { error } = await supabase.rpc('admin_moderate_review', { review_id: reviewId, next_status: nextStatus })
  if (error) message('/control-center/reviews', 'error', error.message)
  revalidatePath('/control-center/reviews')
  revalidatePath('/student/discover')
  message('/control-center/reviews', 'success', `Review marked ${nextStatus}.`)
}

export async function processSchoolRequest(formData: FormData) {
  const supabase = await createClient()
  const requestId = text(formData, 'request_id', 80)
  const decision = text(formData, 'decision', 20)
  const { data: createdSchoolId, error } = await supabase.rpc('admin_process_school_request', { request_id: requestId, decision })
  if (error) message('/control-center/schools', 'error', error.message)
  revalidatePath('/control-center')
  revalidatePath('/control-center/schools')
  revalidatePath('/onboarding/student')
  if (decision === 'approve' && createdSchoolId) redirect('/control-center/schools/' + createdSchoolId + '?setup=created')
  message('/control-center/schools', 'success', decision === 'approve' ? 'School request approved and added to onboarding.' : 'School request rejected.')
}
