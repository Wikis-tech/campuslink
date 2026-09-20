'use server'

import { revalidatePath } from 'next/cache'
import { redirect } from 'next/navigation'
import { createAdminClient } from '@/lib/supabase/admin'
import { assertSafeImageUpload } from '@/lib/file-validation'
import { requireAdminContext } from '../lib'

const LOGO_MAX = 3 * 1024 * 1024
const FAVICON_MAX = 1 * 1024 * 1024
const APP_ICON_MAX = 5 * 1024 * 1024

function fail(message: string): never {
  redirect('/admin-v2/branding?error=' + encodeURIComponent(message))
}

async function requireSuperAdmin() {
  const context = await requireAdminContext()
  if (context.globalRole !== 'super_admin') fail('Only the Super Admin can change Campus Link branding.')
  return context
}

function ownedBrandingPath(publicUrl: string | null | undefined) {
  if (!publicUrl) return null
  const marker = '/storage/v1/object/public/branding-assets/'
  const index = publicUrl.indexOf(marker)
  if (index < 0) return null
  try {
    return decodeURIComponent(publicUrl.slice(index + marker.length))
  } catch {
    return null
  }
}

async function uploadBrandAsset(
  admin: ReturnType<typeof createAdminClient>,
  file: File,
  kind: 'website-logo' | 'favicon' | 'app-icon',
) {
  const max = kind === 'website-logo' ? LOGO_MAX : kind === 'favicon' ? FAVICON_MAX : APP_ICON_MAX
  if (file.size > max) {
    throw new Error(
      kind === 'website-logo'
        ? 'Website logo must be 3 MB or smaller.'
        : kind === 'favicon'
          ? 'Favicon must be 1 MB or smaller.'
          : 'App icon must be 5 MB or smaller.',
    )
  }

  const extension = await assertSafeImageUpload(file)
  if ((kind === 'favicon' || kind === 'app-icon') && extension !== 'png') {
    throw new Error(kind === 'favicon' ? 'Use a PNG image for the favicon.' : 'Use a PNG image for the PWA app icon.')
  }

  const path = `platform/${kind}-${crypto.randomUUID()}.${extension}`
  const { error } = await admin.storage.from('branding-assets').upload(path, file, {
    contentType: file.type,
    cacheControl: '300',
    upsert: false,
  })
  if (error) throw new Error(error.message || 'Brand image upload failed.')

  const { data } = admin.storage.from('branding-assets').getPublicUrl(path)
  return { path, publicUrl: data.publicUrl }
}

export async function updatePlatformBranding(formData: FormData) {
  const context = await requireSuperAdmin()
  const admin = createAdminClient()

  const websiteLogo = formData.get('website_logo')
  const favicon = formData.get('favicon')
  const appIcon = formData.get('app_icon')

  const { data: current } = await admin
    .from('platform_branding')
    .select('website_logo_url,favicon_url,app_icon_url,revision')
    .eq('id', 1)
    .maybeSingle()

  const uploaded: Array<{ path: string; publicUrl: string; field: 'website_logo_url' | 'favicon_url' | 'app_icon_url' }> = []

  try {
    if (websiteLogo instanceof File && websiteLogo.size > 0) {
      const result = await uploadBrandAsset(admin, websiteLogo, 'website-logo')
      uploaded.push({ ...result, field: 'website_logo_url' })
    }
    if (favicon instanceof File && favicon.size > 0) {
      const result = await uploadBrandAsset(admin, favicon, 'favicon')
      uploaded.push({ ...result, field: 'favicon_url' })
    }
    if (appIcon instanceof File && appIcon.size > 0) {
      const result = await uploadBrandAsset(admin, appIcon, 'app-icon')
      uploaded.push({ ...result, field: 'app_icon_url' })
    }
  } catch (error) {
    if (uploaded.length) await admin.storage.from('branding-assets').remove(uploaded.map((item) => item.path))
    fail(error instanceof Error ? error.message : 'Could not upload branding assets.')
  }

  if (!uploaded.length) fail('Choose at least one image to update.')

  const patch: Record<string, string | number | Date> = {
    updated_by: context.userId,
    updated_at: new Date(),
    revision: Number(current?.revision || 1) + 1,
  }
  for (const item of uploaded) patch[item.field] = item.publicUrl

  const { error } = await admin
    .from('platform_branding')
    .upsert({ id: 1, ...patch }, { onConflict: 'id' })

  if (error) {
    await admin.storage.from('branding-assets').remove(uploaded.map((item) => item.path))
    fail(error.message || 'Could not save Campus Link branding.')
  }

  const oldPaths = uploaded
    .map((item) => ownedBrandingPath(current?.[item.field]))
    .filter(Boolean) as string[]

  if (oldPaths.length) await admin.storage.from('branding-assets').remove(oldPaths)

  await admin.from('audit_logs').insert({
    actor_id: context.userId,
    action: 'platform_branding_updated',
    entity_type: 'platform_branding',
    entity_id: '1',
    metadata: { fields: uploaded.map((item) => item.field) },
  })

  revalidatePath('/', 'layout')
  revalidatePath('/admin-v2/branding')
  redirect('/admin-v2/branding?saved=1')
}
