'use server'

import { revalidatePath } from 'next/cache'
import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'

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

  if (name.length < 4 || !slug) message('/admin-v2/schools', 'error', 'Enter a valid school name.')

  const { error } = await supabase.rpc('admin_create_institution', {
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

  if (error) message('/admin-v2/schools', 'error', error.message)
  revalidatePath('/admin-v2')
  revalidatePath('/admin-v2/schools')
  revalidatePath('/onboarding/student')
  message('/admin-v2/schools', 'success', `${name} was added and is now available for onboarding.`)
}

export async function setInstitutionActive(formData: FormData) {
  const supabase = await createClient()
  const id = text(formData, 'institution_id', 80)
  const active = text(formData, 'active', 10) === 'true'
  const { error } = await supabase.rpc('admin_set_institution_active', { target_id: id, active })
  if (error) message('/admin-v2/schools', 'error', error.message)
  revalidatePath('/admin-v2/schools')
  revalidatePath('/onboarding/student')
  message('/admin-v2/schools', 'success', active ? 'School restored.' : 'School archived. Existing records were preserved.')
}

export async function reviewStudent(formData: FormData) {
  const supabase = await createClient()
  const studentId = text(formData, 'student_id', 80)
  const decision = text(formData, 'decision', 20)
  const note = text(formData, 'note', 800)
  const { error } = await supabase.rpc('admin_review_student', { target_student: studentId, decision, note: note || null })
  if (error) message('/admin-v2/students', 'error', error.message)
  revalidatePath('/admin-v2/students')
  revalidatePath('/student')
  message('/admin-v2/students', 'success', decision === 'approve' ? 'Student verified.' : 'Student verification rejected.')
}

export async function reviewVendorIdentity(formData: FormData) {
  const supabase = await createClient()
  const vendorId = text(formData, 'vendor_id', 80)
  const decision = text(formData, 'decision', 20)
  const note = text(formData, 'note', 800)
  const { error } = await supabase.rpc('admin_review_vendor_identity', { target_vendor: vendorId, decision, note: note || null })
  if (error) message('/admin-v2/vendors', 'error', error.message)
  revalidatePath('/admin-v2/vendors')
  revalidatePath('/vendor-v2')
  message('/admin-v2/vendors', 'success', `Vendor identity ${decision === 'approve' ? 'approved' : decision + 'ed'}.`)
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
  if (error) message('/admin-v2/vendors', 'error', error.message)
  revalidatePath('/admin-v2/vendors')
  revalidatePath('/student')
  revalidatePath('/student/discover')
  message('/admin-v2/vendors', 'success', `Campus access ${decision === 'approve' ? 'approved' : decision + 'ed'}.`)
}

export async function updateComplaint(formData: FormData) {
  const supabase = await createClient()
  const complaintId = text(formData, 'complaint_id', 80)
  const nextStatus = text(formData, 'status', 30)
  const { error } = await supabase.rpc('admin_update_complaint', { complaint_id: complaintId, next_status: nextStatus })
  if (error) message('/admin-v2/reports', 'error', error.message)
  revalidatePath('/admin-v2/reports')
  message('/admin-v2/reports', 'success', `Report moved to ${nextStatus}.`)
}

export async function assignSchoolAdmin(formData: FormData) {
  const supabase = await createClient()
  const email = text(formData, 'email', 254).toLowerCase()
  const institutionId = text(formData, 'institution_id', 80)
  const role = text(formData, 'role', 40)
  if (!email.includes('@')) message('/admin-v2/admins', 'error', 'Enter a valid Campus Link account email.')

  const { error } = await supabase.rpc('admin_assign_school_admin_by_email', {
    target_email: email,
    target_institution: institutionId,
    assignment_role: role,
  })
  if (error) message('/admin-v2/admins', 'error', error.message)
  revalidatePath('/admin-v2/admins')
  message('/admin-v2/admins', 'success', 'School admin assignment saved.')
}

export async function assignGlobalAdmin(formData: FormData) {
  const supabase = await createClient()
  const email = text(formData, 'email', 254).toLowerCase()
  const role = text(formData, 'role', 50)
  if (!email.includes('@')) message('/admin-v2/admins', 'error', 'Enter a valid Campus Link account email.')

  const { error } = await supabase.rpc('admin_assign_global_role_by_email', { target_email: email, target_role: role })
  if (error) message('/admin-v2/admins', 'error', error.message)
  revalidatePath('/admin-v2/admins')
  message('/admin-v2/admins', 'success', 'Global admin role saved.')
}

export async function saveCategory(formData: FormData) {
  const supabase = await createClient()
  const id = text(formData, 'category_id', 80)
  const name = text(formData, 'name', 120)
  const slug = slugify(text(formData, 'slug', 140) || name)
  const description = text(formData, 'description', 500)
  const icon = text(formData, 'icon', 80)
  const active = text(formData, 'active', 10) !== 'false'
  if (name.length < 2 || !slug) message('/admin-v2/categories', 'error', 'Enter a valid category name.')

  const { error } = await supabase.rpc('admin_upsert_category', {
    category_id: id || null,
    category_name: name,
    category_slug: slug,
    category_description: description || null,
    category_icon: icon || null,
    category_active: active,
  })
  if (error) message('/admin-v2/categories', 'error', error.message)
  revalidatePath('/admin-v2/categories')
  revalidatePath('/student/discover')
  message('/admin-v2/categories', 'success', id ? 'Category updated.' : 'Category created.')
}

export async function moderateReview(formData: FormData) {
  const supabase = await createClient()
  const reviewId = text(formData, 'review_id', 80)
  const nextStatus = text(formData, 'status', 20)
  const { error } = await supabase.rpc('admin_moderate_review', { review_id: reviewId, next_status: nextStatus })
  if (error) message('/admin-v2/reviews', 'error', error.message)
  revalidatePath('/admin-v2/reviews')
  revalidatePath('/student/discover')
  message('/admin-v2/reviews', 'success', `Review marked ${nextStatus}.`)
}

export async function processSchoolRequest(formData: FormData) {
  const supabase = await createClient()
  const requestId = text(formData, 'request_id', 80)
  const decision = text(formData, 'decision', 20)
  const { error } = await supabase.rpc('admin_process_school_request', { request_id: requestId, decision })
  if (error) message('/admin-v2/schools', 'error', error.message)
  revalidatePath('/admin-v2/schools')
  revalidatePath('/onboarding/student')
  message('/admin-v2/schools', 'success', decision === 'approve' ? 'School request approved and added to onboarding.' : 'School request rejected.')
}
