'use server'

import { revalidatePath } from 'next/cache'
import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'

const IMAGE_TYPES = new Set(['image/jpeg', 'image/png', 'image/webp'])
const LOGO_MAX = 2 * 1024 * 1024
const COVER_MAX = 4 * 1024 * 1024

function clean(value: FormDataEntryValue | null, max: number) {
  return String(value || '').trim().slice(0, max)
}

function extFor(file: File) {
  if (file.type === 'image/png') return 'png'
  if (file.type === 'image/webp') return 'webp'
  return 'jpg'
}

function ownedStoragePath(publicUrl: string | null | undefined, userId: string) {
  if (!publicUrl) return null
  const marker = '/storage/v1/object/public/vendor-media/'
  const index = publicUrl.indexOf(marker)
  if (index < 0) return null
  try {
    const path = decodeURIComponent(publicUrl.slice(index + marker.length))
    return path.startsWith(userId + '/') ? path : null
  } catch {
    return null
  }
}

async function requireVendor() {
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const { data: profile } = await supabase.from('profiles').select('account_type').eq('id', userId).maybeSingle()
  if (profile?.account_type !== 'vendor') redirect('/dashboard')
  return { supabase, userId }
}

async function uploadVendorImage(
  supabase: Awaited<ReturnType<typeof createClient>>,
  userId: string,
  file: File,
  kind: 'logo' | 'cover',
) {
  const max = kind === 'logo' ? LOGO_MAX : COVER_MAX
  if (!IMAGE_TYPES.has(file.type)) throw new Error('Use a JPG, PNG or WEBP image.')
  if (file.size > max) throw new Error(kind === 'logo' ? 'Logo must be 2MB or smaller.' : 'Cover image must be 4MB or smaller.')

  const path = `${userId}/profile/${kind}-${crypto.randomUUID()}.${extFor(file)}`
  const { error } = await supabase.storage.from('vendor-media').upload(path, file, {
    contentType: file.type,
    upsert: false,
  })
  if (error) throw new Error(error.message || 'Image upload failed.')

  const { data } = supabase.storage.from('vendor-media').getPublicUrl(path)
  return { path, publicUrl: data.publicUrl }
}

export async function updateVendorProfile(formData: FormData) {
  const { supabase, userId } = await requireVendor()

  const businessName = clean(formData.get('business_name'), 120)
  const description = clean(formData.get('description'), 1200)
  const whatsapp = clean(formData.get('whatsapp_number'), 30)
  const location = clean(formData.get('location_text'), 180)
  const businessEmail = clean(formData.get('business_email'), 254).toLowerCase()
  const website = clean(formData.get('website_url'), 240)
  const logo = formData.get('logo')
  const cover = formData.get('cover')

  if (!businessName || description.length < 20 || !whatsapp || !location) {
    redirect('/vendor-v2/profile?error=Complete%20the%20required%20business%20details.')
  }
  if (!/^[+0-9 ()-]{7,30}$/.test(whatsapp)) {
    redirect('/vendor-v2/profile?error=Enter%20a%20valid%20WhatsApp%20number.')
  }
  if (businessEmail && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(businessEmail)) {
    redirect('/vendor-v2/profile?error=Enter%20a%20valid%20business%20email.')
  }
  if (website) {
    try {
      const u = new URL(website)
      if (!['https:', 'http:'].includes(u.protocol)) throw new Error()
    } catch {
      redirect('/vendor-v2/profile?error=Website%20must%20start%20with%20http%3A%2F%2F%20or%20https%3A%2F%2F.')
    }
  }

  const { data: current } = await supabase
    .from('vendor_profiles')
    .select('logo_url,cover_url')
    .eq('id', userId)
    .maybeSingle()

  let uploadedLogo: { path: string; publicUrl: string } | null = null
  let uploadedCover: { path: string; publicUrl: string } | null = null

  try {
    if (logo instanceof File && logo.size > 0) uploadedLogo = await uploadVendorImage(supabase, userId, logo, 'logo')
    if (cover instanceof File && cover.size > 0) uploadedCover = await uploadVendorImage(supabase, userId, cover, 'cover')
  } catch (error) {
    if (uploadedLogo) await supabase.storage.from('vendor-media').remove([uploadedLogo.path])
    if (uploadedCover) await supabase.storage.from('vendor-media').remove([uploadedCover.path])
    redirect(`/vendor-v2/profile?error=${encodeURIComponent(error instanceof Error ? error.message : 'We could not upload your image.')}`)
  }

  const { error } = await supabase
    .from('vendor_profiles')
    .update({
      business_name: businessName,
      description,
      whatsapp_number: whatsapp,
      location_text: location,
      business_email: businessEmail || null,
      website_url: website || null,
      ...(uploadedLogo ? { logo_url: uploadedLogo.publicUrl } : {}),
      ...(uploadedCover ? { cover_url: uploadedCover.publicUrl } : {}),
      updated_at: new Date().toISOString(),
    })
    .eq('id', userId)

  if (error) {
    if (uploadedLogo) await supabase.storage.from('vendor-media').remove([uploadedLogo.path])
    if (uploadedCover) await supabase.storage.from('vendor-media').remove([uploadedCover.path])
    redirect(`/vendor-v2/profile?error=${encodeURIComponent(error.message || 'We could not save your profile.')}`)
  }

  const oldLogo = uploadedLogo ? ownedStoragePath(current?.logo_url, userId) : null
  const oldCover = uploadedCover ? ownedStoragePath(current?.cover_url, userId) : null
  const oldPaths = [oldLogo, oldCover].filter(Boolean) as string[]
  if (oldPaths.length) await supabase.storage.from('vendor-media').remove(oldPaths)

  revalidatePath('/vendor-v2')
  revalidatePath('/vendor-v2/profile')
  revalidatePath('/student')
  revalidatePath('/student/discover')
  redirect('/vendor-v2/profile?saved=1')
}
