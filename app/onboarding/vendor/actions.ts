'use server'

import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'

const ALLOWED_DOCUMENT_TYPES = new Set(['image/jpeg', 'image/png', 'image/webp', 'application/pdf'])
const MAX_DOCUMENT_BYTES = 5 * 1024 * 1024

function clean(value: FormDataEntryValue | null, max = 180) {
  return String(value || '').trim().slice(0, max)
}

function slugify(value: string) {
  return value.toLowerCase().normalize('NFKD').replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '').slice(0, 60)
}

function safeFileName(name: string) {
  const ext = name.includes('.') ? `.${name.split('.').pop()?.toLowerCase()}` : ''
  return `${crypto.randomUUID()}${ext}`
}

export async function completeVendorOnboarding(formData: FormData) {
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const businessName = clean(formData.get('business_name'), 120)
  const description = clean(formData.get('description'), 1200)
  const whatsapp = clean(formData.get('whatsapp_number'), 30)
  const businessEmail = clean(formData.get('business_email'), 254).toLowerCase()
  const websiteUrl = clean(formData.get('website_url'), 240)
  const locationText = clean(formData.get('location_text'), 180)
  const vendorType = clean(formData.get('vendor_type'), 40)
  const institutionId = clean(formData.get('institution_id'), 80)
  const categoryId = clean(formData.get('category_id'), 80)
  const serviceName = clean(formData.get('service_name'), 120)
  const serviceDescription = clean(formData.get('service_description'), 500)
  const priceFromRaw = clean(formData.get('price_from'), 30)
  const documentType = clean(formData.get('document_type'), 50)
  const verificationDocument = formData.get('verification_document')

  if (!businessName || !description || !whatsapp || !locationText || !institutionId || !categoryId || !serviceName) {
    redirect('/onboarding/vendor?error=Please%20complete%20all%20required%20fields')
  }

  if (!['student_vendor', 'community_vendor', 'registered_business'].includes(vendorType)) {
    redirect('/onboarding/vendor?error=Choose%20a%20valid%20vendor%20type')
  }

  const [{ data: institution }, { data: category }] = await Promise.all([
    supabase.from('institutions').select('id').eq('id', institutionId).eq('is_active', true).maybeSingle(),
    supabase.from('categories').select('id').eq('id', categoryId).eq('is_active', true).maybeSingle(),
  ])

  if (!institution || !category) {
    redirect('/onboarding/vendor?error=Select%20a%20valid%20school%20and%20service%20category')
  }

  if (!(verificationDocument instanceof File) || verificationDocument.size === 0) {
    redirect('/onboarding/vendor?error=Upload%20a%20verification%20document%20before%20submitting')
  }

  if (verificationDocument.size > MAX_DOCUMENT_BYTES || !ALLOWED_DOCUMENT_TYPES.has(verificationDocument.type)) {
    redirect('/onboarding/vendor?error=Verification%20document%20must%20be%20PDF%2C%20JPG%2C%20PNG%20or%20WEBP%20and%20under%205MB')
  }

  const allowedDocTypes = vendorType === 'student_vendor'
    ? ['student_id', 'government_id', 'portfolio_evidence']
    : ['government_id', 'cac_document', 'proof_of_address', 'business_certificate', 'portfolio_evidence']

  if (!allowedDocTypes.includes(documentType)) {
    redirect('/onboarding/vendor?error=Choose%20a%20valid%20verification%20document%20type')
  }

  const now = new Date().toISOString()
  const baseSlug = slugify(businessName) || 'vendor'
  const vendorSlug = `${baseSlug}-${userId.slice(0, 8)}`

  const { error: vendorError } = await supabase.from('vendor_profiles').upsert({
    id: userId,
    business_name: businessName,
    slug: vendorSlug,
    description,
    whatsapp_number: whatsapp,
    business_email: businessEmail || null,
    website_url: websiteUrl || null,
    location_text: locationText,
    vendor_type: vendorType,
    verification_status: 'pending',
    onboarding_completed_at: now,
    verification_submitted_at: now,
  }, { onConflict: 'id' })

  if (vendorError) redirect('/onboarding/vendor?error=We%20could%20not%20save%20your%20business%20profile')

  const { error: schoolError } = await supabase.from('vendor_institutions').upsert({
    vendor_id: userId,
    institution_id: institutionId,
    status: 'pending',
    is_primary: true,
    reviewed_by: null,
    reviewed_at: null,
    review_note: null,
  }, { onConflict: 'vendor_id,institution_id' })

  if (schoolError) redirect('/onboarding/vendor?error=We%20could%20not%20save%20your%20school%20selection')

  const priceFrom = priceFromRaw ? Number(priceFromRaw.replace(/,/g, '')) : null
  if (priceFrom !== null && (!Number.isFinite(priceFrom) || priceFrom < 0 || priceFrom > 100000000)) {
    redirect('/onboarding/vendor?error=Enter%20a%20valid%20starting%20price')
  }

  const { error: serviceError } = await supabase.from('vendor_services').insert({
    vendor_id: userId,
    category_id: categoryId,
    name: serviceName,
    description: serviceDescription || null,
    price_from: priceFrom,
    is_active: true,
  })

  if (serviceError) redirect('/onboarding/vendor?error=Your%20profile%20was%20saved%20but%20the%20service%20could%20not%20be%20added')

  const storagePath = `${userId}/vendor/${safeFileName(verificationDocument.name)}`
  const { error: uploadError } = await supabase.storage
    .from('verification-documents')
    .upload(storagePath, verificationDocument, { contentType: verificationDocument.type, upsert: false })

  if (uploadError) redirect('/onboarding/vendor?error=We%20could%20not%20upload%20your%20verification%20document')

  const { error: documentError } = await supabase.from('vendor_documents').insert({
    vendor_id: userId,
    institution_id: institutionId,
    document_type: documentType,
    storage_path: storagePath,
    status: 'pending',
  })

  if (documentError) {
    await supabase.storage.from('verification-documents').remove([storagePath])
    redirect('/onboarding/vendor?error=We%20could%20not%20create%20the%20verification%20record')
  }

  redirect('/vendor-v2?onboarding=complete')
}
