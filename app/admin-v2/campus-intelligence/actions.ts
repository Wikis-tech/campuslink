'use server'

import { revalidatePath } from 'next/cache'
import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'
import { requireAdminContext } from '../lib'

function back(message: string, type: 'ok' | 'error' = 'ok'): never {
  redirect(`/admin-v2/campus-intelligence?${type}=${encodeURIComponent(message)}`)
}

function slugify(value: string) {
  return value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 80)
}

async function ensureInstitutionAccess(institutionId: string) {
  const context = await requireAdminContext()
  if (context.isGlobalAdmin) return context
  if (!context.schoolAssignments.some((item) => item.institution_id === institutionId && ['school_admin','school_support'].includes(item.role))) {
    back('You do not have permission to manage that campus.', 'error')
  }
  return context
}

export async function createCampusLocation(formData: FormData) {
  const institutionId = String(formData.get('institution_id') || '')
  const name = String(formData.get('name') || '').trim()
  const locationType = String(formData.get('location_type') || 'landmark')
  const description = String(formData.get('description') || '').trim().slice(0, 240) || null
  if (!institutionId || name.length < 2) back('Choose a campus and enter a valid location name.', 'error')
  const context = await ensureInstitutionAccess(institutionId)
  const allowedTypes = new Set(['gate','hostel','faculty','student_centre','library','landmark','off_campus','other'])
  if (!allowedTypes.has(locationType)) back('Invalid location type.', 'error')
  const supabase = await createClient()
  const { error } = await supabase.from('campus_locations').insert({ institution_id: institutionId, name, slug: slugify(name), location_type: locationType, description, created_by: context.userId })
  if (error) back(`Could not add location: ${error.message}`, 'error')
  revalidatePath('/admin-v2/campus-intelligence')
  revalidatePath('/vendor-v2/availability')
  back('Campus location added.')
}

export async function toggleCampusLocation(formData: FormData) {
  const id = String(formData.get('id') || '')
  const institutionId = String(formData.get('institution_id') || '')
  const isActive = String(formData.get('is_active') || '') === 'true'
  if (!id || !institutionId) back('Missing campus location.', 'error')
  await ensureInstitutionAccess(institutionId)
  const supabase = await createClient()
  const { error } = await supabase.from('campus_locations').update({ is_active: !isActive, updated_at: new Date().toISOString() }).eq('id', id).eq('institution_id', institutionId)
  if (error) back(`Could not update location: ${error.message}`, 'error')
  revalidatePath('/admin-v2/campus-intelligence')
  revalidatePath('/student/discover')
  revalidatePath('/vendor-v2/availability')
  back(isActive ? 'Campus location hidden.' : 'Campus location restored.')
}

export async function createCampusEvent(formData: FormData) {
  const institutionId = String(formData.get('institution_id') || '')
  const eventType = String(formData.get('event_type') || 'event')
  const title = String(formData.get('title') || '').trim()
  const description = String(formData.get('description') || '').trim().slice(0, 500) || null
  const startsOn = String(formData.get('starts_on') || '')
  const endsOn = String(formData.get('ends_on') || '') || null
  if (!institutionId || !title || !startsOn) back('Campus, title and start date are required.', 'error')
  if (endsOn && endsOn < startsOn) back('End date cannot be before the start date.', 'error')
  const allowedTypes = new Set(['resumption','exam','matriculation','convocation','semester_break','event','other'])
  if (!allowedTypes.has(eventType)) back('Invalid campus event type.', 'error')
  const context = await ensureInstitutionAccess(institutionId)
  const supabase = await createClient()
  const { error } = await supabase.from('campus_calendar_events').insert({ institution_id: institutionId, event_type: eventType, title, description, starts_on: startsOn, ends_on: endsOn, is_published: true, created_by: context.userId })
  if (error) back(`Could not add campus date: ${error.message}`, 'error')
  revalidatePath('/admin-v2/campus-intelligence')
  revalidatePath('/vendor-v2/availability')
  revalidatePath('/student')
  back('Campus calendar date published.')
}

export async function deleteCampusEvent(formData: FormData) {
  const id = String(formData.get('id') || '')
  const institutionId = String(formData.get('institution_id') || '')
  if (!id || !institutionId) back('Missing campus event.', 'error')
  await ensureInstitutionAccess(institutionId)
  const supabase = await createClient()
  const { error } = await supabase.from('campus_calendar_events').delete().eq('id', id).eq('institution_id', institutionId)
  if (error) back(`Could not remove campus date: ${error.message}`, 'error')
  revalidatePath('/admin-v2/campus-intelligence')
  revalidatePath('/vendor-v2/availability')
  back('Campus calendar date removed.')
}
