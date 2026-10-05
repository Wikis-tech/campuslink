'use server'

import { revalidatePath } from 'next/cache'
import { redirect } from 'next/navigation'
import { createAdminClient } from '@/lib/supabase/admin'
import { sendCampusLinkEmail } from '@/lib/campuslink-email'
import { requireAdminContext } from '../lib'

type Recipient = {
  userId: string
  name: string
  institutionId: string | null
  accountType: 'student' | 'vendor'
}

const GLOBAL_ANNOUNCERS = new Set(['super_admin','operations_admin','content_admin'])
const GLOBAL_ONBOARDING_REMINDERS = new Set(['super_admin','operations_admin'])

function clean(value: FormDataEntryValue | null, max = 1200) {
  return String(value || '').trim().slice(0, max)
}

function safeReturnPath(path: string) {
  return path.startsWith('/control-center') ? path : '/control-center/communications'
}

function back(path: string, kind: 'success' | 'error', value: string): never {
  const safePath = safeReturnPath(path)
  const join = safePath.includes('?') ? '&' : '?'
  redirect(`${safePath}${join}${kind}=${encodeURIComponent(value)}`)
}

function assignedSchoolIds(context: Awaited<ReturnType<typeof requireAdminContext>>) {
  return new Set(context.schoolAssignments.filter((item) => item.is_active).map((item) => item.institution_id))
}

async function authEmailMap() {
  const admin = createAdminClient()
  const map = new Map<string,string>()
  let page = 1
  while (page <= 10) {
    const { data, error } = await admin.auth.admin.listUsers({ page, perPage: 1000 })
    if (error) break
    for (const user of data.users || []) if (user.email) map.set(user.id, user.email)
    if ((data.users || []).length < 1000) break
    page += 1
  }
  return map
}

function reminderDefinition(type: string) {
  if (type === 'student_onboarding_incomplete') return {
    subject: 'Complete your Kampivo Student setup',
    title: 'Complete your Kampivo setup',
    body: 'You started creating your Kampivo Student account, but your campus setup is still incomplete. Complete the remaining steps so you can use the verified campus marketplace experience.',
    ctaLabel: 'Complete Student setup',
    ctaUrl: '/onboarding/student',
  }
  if (type === 'student_verification_incomplete') return {
    subject: 'Complete your CampusLink Student verification',
    title: 'Complete your Student verification',
    body: 'Your Student account setup is complete, but Kampivo still needs your verification details. Finish verification so your account can receive verified Student privileges.',
    ctaLabel: 'Complete verification',
    ctaUrl: '/onboarding/student',
  }
  if (type === 'vendor_onboarding_incomplete') return {
    subject: 'Complete your CampusLink Vendor registration',
    title: 'Finish setting up your Vendor account',
    body: 'You started a Kampivo Vendor account, but your business profile is not complete yet. Finish setup so identity review and campus approval can continue.',
    ctaLabel: 'Complete Vendor setup',
    ctaUrl: '/onboarding/vendor',
  }
  throw new Error('Unsupported reminder type')
}

async function resolveReminderRecipients(
  type: string,
  context: Awaited<ReturnType<typeof requireAdminContext>>,
  targetUserId?: string,
): Promise<Recipient[]> {
  const admin = createAdminClient()
  const schoolIds = assignedSchoolIds(context)
  const globalAllowed = GLOBAL_ONBOARDING_REMINDERS.has(context.globalRole || '')

  if (type === 'vendor_onboarding_incomplete') {
    if (!globalAllowed) throw new Error('Only Super Admin or Operations Admin can remind Vendor accounts that have not selected a school yet.')
    const { data: vendorAccounts, error } = await admin.from('profiles')
      .select('id,first_name,last_name,institution_id')
      .eq('account_type','vendor')
    if (error) throw error
    const { data: vendorProfiles, error: profileError } = await admin.from('vendor_profiles').select('id')
    if (profileError) throw profileError
    const profileIds = new Set((vendorProfiles || []).map((row) => row.id))
    return (vendorAccounts || [])
      .filter((row) => !profileIds.has(row.id))
      .filter((row) => !targetUserId || row.id === targetUserId)
      .map((row) => ({
        userId: row.id,
        name: [row.first_name,row.last_name].filter(Boolean).join(' ') || 'Vendor',
        institutionId: row.institution_id || null,
        accountType: 'vendor' as const,
      }))
  }

  if (type === 'student_onboarding_incomplete') {
    const { data, error } = await admin.from('profiles')
      .select('id,first_name,last_name,institution_id,onboarding_completed_at')
      .eq('account_type','student')
    if (error) throw error
    return (data || [])
      .filter((row) => !row.onboarding_completed_at || !row.institution_id)
      .filter((row) => globalAllowed || (row.institution_id && schoolIds.has(row.institution_id)))
      .filter((row) => !targetUserId || row.id === targetUserId)
      .map((row) => ({
        userId: row.id,
        name: [row.first_name,row.last_name].filter(Boolean).join(' ') || 'Student',
        institutionId: row.institution_id || null,
        accountType: 'student' as const,
      }))
  }

  if (type === 'student_verification_incomplete') {
    const { data: students, error } = await admin.from('profiles')
      .select('id,first_name,last_name,institution_id,onboarding_completed_at,student_verification_status')
      .eq('account_type','student')
      .not('onboarding_completed_at','is',null)
    if (error) throw error
    const verificationCandidates = (students || []).filter((row) => row.student_verification_status !== 'verified')
    const ids = verificationCandidates.map((row) => row.id)
    const { data: verificationRows, error: verificationError } = ids.length
      ? await admin.from('student_verifications').select('student_id,status').in('student_id', ids)
      : { data: [] as any[], error: null }
    if (verificationError) throw verificationError
    const verificationMap = new Map((verificationRows || []).map((row) => [row.student_id,row.status]))
    const canGlobal = ['super_admin','operations_admin','verification_admin'].includes(context.globalRole || '')
    return verificationCandidates
      .filter((row) => {
        const state = verificationMap.get(row.id)
        return !state || state === 'rejected'
      })
      .filter((row) => canGlobal || (row.institution_id && schoolIds.has(row.institution_id)))
      .filter((row) => !targetUserId || row.id === targetUserId)
      .map((row) => ({
        userId: row.id,
        name: [row.first_name,row.last_name].filter(Boolean).join(' ') || 'Student',
        institutionId: row.institution_id || null,
        accountType: 'student' as const,
      }))
  }

  throw new Error('Unsupported reminder type')
}

async function deliver(
  communicationId: string,
  recipient: Recipient,
  email: string | undefined,
  subject: string,
  title: string,
  body: string,
  ctaLabel: string | null,
  ctaUrl: string | null,
  reminderKey?: string,
  createdBy?: string,
) {
  const admin = createAdminClient()

  if (reminderKey) {
    const since = new Date(Date.now() - 24 * 60 * 60 * 1000).toISOString()
    const { data: recent } = await admin.from('admin_communication_deliveries')
      .select('id')
      .eq('user_id', recipient.userId)
      .eq('reminder_key', reminderKey)
      .gte('sent_at', since)
      .limit(1)
    if (recent?.length) {
      await admin.from('admin_communication_deliveries').insert({
        communication_id: communicationId,
        user_id: recipient.userId,
        email: email || null,
        reminder_key: reminderKey,
        email_status: 'skipped_cooldown',
        error_message: 'A reminder of this type was already sent within the last 24 hours.',
      })
      return { dashboard: 0, emailSent: 0, emailFailed: 0, skipped: 1 }
    }
  }

  const { data: notification, error: notificationError } = await admin.from('notifications').insert({
    user_id: recipient.userId,
    title,
    body,
    type: reminderKey ? 'reminder' : 'announcement',
    action_url: ctaUrl,
    source: 'admin',
    created_by: createdBy || null,
    metadata: { communication_id: communicationId, reminder_key: reminderKey || null },
  }).select('id').single()

  if (notificationError) throw notificationError

  let emailStatus: 'sent' | 'failed' | 'not_configured' = 'failed'
  let providerId: string | null = null
  let errorMessage: string | null = null

  if (!email) {
    emailStatus = 'failed'
    errorMessage = 'No account email was available.'
  } else {
    const result = await sendCampusLinkEmail({
      to: email,
      recipientName: recipient.name,
      subject,
      title,
      body,
      ctaLabel,
      ctaUrl,
      idempotencyKey: `campuslink-${communicationId}-${recipient.userId}`,
    })
    if (result.ok) {
      emailStatus = 'sent'
      providerId = result.id
    } else {
      emailStatus = result.reason === 'not_configured' ? 'not_configured' : 'failed'
      errorMessage = result.error
    }
  }

  await admin.from('admin_communication_deliveries').insert({
    communication_id: communicationId,
    user_id: recipient.userId,
    email: email || null,
    reminder_key: reminderKey || null,
    notification_id: notification.id,
    email_status: emailStatus,
    email_provider_id: providerId,
    error_message: errorMessage,
  })

  return {
    dashboard: 1,
    emailSent: emailStatus === 'sent' ? 1 : 0,
    emailFailed: emailStatus === 'sent' ? 0 : 1,
    skipped: 0,
  }
}

export async function sendOperationalReminder(formData: FormData) {
  const context = await requireAdminContext()
  const reminderType = clean(formData.get('reminder_type'), 80)
  const targetUserId = clean(formData.get('target_user_id'), 80) || undefined
  const returnTo = clean(formData.get('return_to'), 240) || '/control-center/communications'
  const definition = reminderDefinition(reminderType)

  let recipients: Recipient[]
  try {
    recipients = await resolveReminderRecipients(reminderType, context, targetUserId)
  } catch (error) {
    back(returnTo, 'error', error instanceof Error ? error.message : 'Could not resolve reminder recipients.')
  }

  if (!recipients.length) back(returnTo, 'error', 'No eligible accounts currently match this reminder.')

  const admin = createAdminClient()
  const emails = await authEmailMap()
  const { data: communication, error } = await admin.from('admin_communications').insert({
    created_by: context.userId,
    kind: 'reminder',
    audience_type: reminderType,
    institution_id: !context.isGlobalAdmin && context.schoolAssignments.length === 1 ? context.schoolAssignments[0].institution_id : null,
    subject: definition.subject,
    title: definition.title,
    body: definition.body,
    cta_label: definition.ctaLabel,
    cta_url: definition.ctaUrl,
    recipient_count: recipients.length,
  }).select('id').single()
  if (error || !communication) back(returnTo, 'error', error?.message || 'Could not create the reminder send.')

  let dashboardSent = 0
  let emailSent = 0
  let emailFailed = 0
  let skipped = 0

  for (const recipient of recipients) {
    const result = await deliver(
      communication.id,
      recipient,
      emails.get(recipient.userId),
      definition.subject,
      definition.title,
      definition.body,
      definition.ctaLabel,
      definition.ctaUrl,
      reminderType,
      context.userId,
    )
    dashboardSent += result.dashboard
    emailSent += result.emailSent
    emailFailed += result.emailFailed
    skipped += result.skipped
  }

  await admin.from('admin_communications').update({
    dashboard_sent_count: dashboardSent,
    email_sent_count: emailSent,
    email_failed_count: emailFailed,
  }).eq('id', communication.id)

  await admin.from('audit_logs').insert({
    actor_id: context.userId,
    action: 'communications.reminder_sent',
    entity_type: 'communication',
    entity_id: communication.id,
    metadata: { reminder_type: reminderType, recipients: recipients.length, dashboard_sent: dashboardSent, email_sent: emailSent, email_failed: emailFailed, skipped_cooldown: skipped },
  })

  revalidatePath('/control-center/communications')
  revalidatePath('/control-center/students')
  revalidatePath('/control-center/vendors')
  revalidatePath('/student')
  revalidatePath('/vendor-v2')

  const emailSummary = emailFailed ? `${emailSent} email sent; ${emailFailed} email delivery issue${emailFailed === 1 ? '' : 's'}.` : `${emailSent} email${emailSent === 1 ? '' : 's'} sent.`
  back(returnTo, 'success', `${dashboardSent} dashboard reminder${dashboardSent === 1 ? '' : 's'} created. ${emailSummary}${skipped ? ` ${skipped} skipped by the 24-hour cooldown.` : ''}`)
}

export async function sendAnnouncement(formData: FormData) {
  const context = await requireAdminContext()
  const audience = clean(formData.get('audience'), 60)
  const institutionId = clean(formData.get('institution_id'), 80) || null
  const subject = clean(formData.get('subject'), 160)
  const title = clean(formData.get('title'), 160)
  const body = clean(formData.get('body'), 1800)
  const ctaLabel = clean(formData.get('cta_label'), 60) || null
  let ctaUrl = clean(formData.get('cta_url'), 300) || null

  if (subject.length < 3 || title.length < 3 || body.length < 5) {
    back('/control-center/communications', 'error', 'Subject, notification title and message are required.')
  }

  if (ctaUrl) {
    const isRelative = ctaUrl.startsWith('/')
    const isCampusLinkUrl = /^https:\/\/(www\.)?campuslink\.name\.ng(?:\/|$)/i.test(ctaUrl)
    if (!isRelative && !isCampusLinkUrl) {
      back('/control-center/communications', 'error', 'Announcement links must stay inside Kampivo for account safety.')
    }
    if (isRelative && !ctaUrl.startsWith('//')) {
      ctaUrl = ctaUrl
    } else if (!isCampusLinkUrl) {
      back('/control-center/communications', 'error', 'Choose a valid Kampivo link.')
    }
  }

  const isGlobalAudience = ['all_students','all_vendors','all_users'].includes(audience)
  const isSchoolAudience = ['school_students','school_vendors','school_all'].includes(audience)
  if (!isGlobalAudience && !isSchoolAudience) back('/control-center/communications', 'error', 'Choose a valid audience.')

  const schoolIds = assignedSchoolIds(context)
  if (isGlobalAudience && !GLOBAL_ANNOUNCERS.has(context.globalRole || '')) {
    back('/control-center/communications', 'error', 'Your Admin role cannot send platform-wide announcements.')
  }
  if (isSchoolAudience) {
    if (!institutionId) back('/control-center/communications', 'error', 'Choose a school for this audience.')
    const globalCan = GLOBAL_ANNOUNCERS.has(context.globalRole || '')
    if (!globalCan && !schoolIds.has(institutionId)) {
      back('/control-center/communications', 'error', 'You can only message users in your assigned school.')
    }
  }

  const admin = createAdminClient()
  const recipients = new Map<string,Recipient>()

  if (audience === 'all_students' || audience === 'all_users') {
    const { data } = await admin.from('profiles').select('id,first_name,last_name,institution_id').eq('account_type','student')
    for (const row of data || []) recipients.set(row.id,{userId:row.id,name:[row.first_name,row.last_name].filter(Boolean).join(' ')||'Student',institutionId:row.institution_id||null,accountType:'student'})
  }
  if (audience === 'all_vendors' || audience === 'all_users') {
    const { data } = await admin.from('profiles').select('id,first_name,last_name,institution_id').eq('account_type','vendor')
    for (const row of data || []) recipients.set(row.id,{userId:row.id,name:[row.first_name,row.last_name].filter(Boolean).join(' ')||'Vendor',institutionId:row.institution_id||null,accountType:'vendor'})
  }
  if (audience === 'school_students' || audience === 'school_all') {
    const { data } = await admin.from('profiles').select('id,first_name,last_name,institution_id').eq('account_type','student').eq('institution_id',institutionId!)
    for (const row of data || []) recipients.set(row.id,{userId:row.id,name:[row.first_name,row.last_name].filter(Boolean).join(' ')||'Student',institutionId:row.institution_id||null,accountType:'student'})
  }
  if (audience === 'school_vendors' || audience === 'school_all') {
    const { data: links } = await admin.from('vendor_institutions').select('vendor_id').eq('institution_id',institutionId!).neq('status','rejected')
    const ids = Array.from(new Set((links || []).map((row) => row.vendor_id)))
    if (ids.length) {
      const { data } = await admin.from('profiles').select('id,first_name,last_name,institution_id').eq('account_type','vendor').in('id',ids)
      for (const row of data || []) recipients.set(row.id,{userId:row.id,name:[row.first_name,row.last_name].filter(Boolean).join(' ')||'Vendor',institutionId:institutionId,accountType:'vendor'})
    }
  }

  const recipientList = Array.from(recipients.values())
  if (!recipientList.length) back('/control-center/communications', 'error', 'No users currently match that audience.')

  const { data: communication, error } = await admin.from('admin_communications').insert({
    created_by: context.userId,
    kind: 'announcement',
    audience_type: audience,
    institution_id: institutionId,
    subject,
    title,
    body,
    cta_label: ctaLabel,
    cta_url: ctaUrl,
    recipient_count: recipientList.length,
  }).select('id').single()
  if (error || !communication) back('/control-center/communications', 'error', error?.message || 'Could not create the announcement.')

  const emails = await authEmailMap()
  let dashboardSent = 0
  let emailSent = 0
  let emailFailed = 0
  for (const recipient of recipientList) {
    const result = await deliver(communication.id,recipient,emails.get(recipient.userId),subject,title,body,ctaLabel,ctaUrl,undefined,context.userId)
    dashboardSent += result.dashboard
    emailSent += result.emailSent
    emailFailed += result.emailFailed
  }

  await admin.from('admin_communications').update({
    dashboard_sent_count: dashboardSent,
    email_sent_count: emailSent,
    email_failed_count: emailFailed,
  }).eq('id',communication.id)

  await admin.from('audit_logs').insert({
    actor_id: context.userId,
    action: 'communications.announcement_sent',
    entity_type: 'communication',
    entity_id: communication.id,
    metadata: { audience, institution_id: institutionId, recipients: recipientList.length, dashboard_sent: dashboardSent, email_sent: emailSent, email_failed: emailFailed },
  })

  revalidatePath('/control-center/communications')
  revalidatePath('/student')
  revalidatePath('/vendor-v2')
  back('/control-center/communications','success',`Announcement delivered to ${dashboardSent} dashboard${dashboardSent === 1 ? '' : 's'}; ${emailSent} email${emailSent === 1 ? '' : 's'} sent${emailFailed ? `, ${emailFailed} email issue${emailFailed === 1 ? '' : 's'} recorded` : ''}.`)
}
