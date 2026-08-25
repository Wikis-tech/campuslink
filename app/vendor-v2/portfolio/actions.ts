'use server'

import { revalidatePath } from 'next/cache'
import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'

const ALLOWED_TYPES = new Set(['image/jpeg','image/png','image/webp'])
const MAX_FILE_SIZE = 5 * 1024 * 1024

async function requireVendor() {
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const { data: profile } = await supabase.from('profiles').select('account_type').eq('id', userId).single()
  if (!profile || profile.account_type !== 'vendor') redirect('/dashboard')
  return { supabase, userId }
}

export async function addPortfolioItem(formData: FormData) {
  const title = String(formData.get('title') || '').trim().slice(0, 100)
  const description = String(formData.get('description') || '').trim().slice(0, 500)
  const image = formData.get('image')

  if (!title || !(image instanceof File) || image.size === 0) {
    redirect('/vendor-v2/portfolio?error=Add%20a%20title%20and%20portfolio%20image')
  }
  if (!ALLOWED_TYPES.has(image.type)) {
    redirect('/vendor-v2/portfolio?error=Use%20a%20JPG,%20PNG%20or%20WEBP%20image')
  }
  if (image.size > MAX_FILE_SIZE) {
    redirect('/vendor-v2/portfolio?error=Portfolio%20images%20must%20be%205MB%20or%20smaller')
  }

  const { supabase, userId } = await requireVendor()
  const extension = image.type === 'image/png' ? 'png' : image.type === 'image/webp' ? 'webp' : 'jpg'
  const path = `${userId}/${crypto.randomUUID()}.${extension}`

  const { error: uploadError } = await supabase.storage.from('vendor-media').upload(path, image, {
    contentType: image.type,
    upsert: false,
  })
  if (uploadError) redirect('/vendor-v2/portfolio?error=We%20could%20not%20upload%20that%20image')

  const { data: publicUrlData } = supabase.storage.from('vendor-media').getPublicUrl(path)
  const { error: insertError } = await supabase.from('vendor_portfolio_items').insert({
    vendor_id: userId,
    title,
    description: description || null,
    image_url: publicUrlData.publicUrl,
    storage_path: path,
  })

  if (insertError) {
    await supabase.storage.from('vendor-media').remove([path])
    redirect('/vendor-v2/portfolio?error=We%20could%20not%20save%20the%20portfolio%20item')
  }

  revalidatePath('/vendor-v2/portfolio')
  redirect('/vendor-v2/portfolio?added=1')
}

export async function deletePortfolioItem(formData: FormData) {
  const itemId = String(formData.get('item_id') || '')
  if (!itemId) redirect('/vendor-v2/portfolio')

  const { supabase, userId } = await requireVendor()
  const { data: item } = await supabase
    .from('vendor_portfolio_items')
    .select('id,storage_path')
    .eq('id', itemId)
    .eq('vendor_id', userId)
    .maybeSingle()

  if (!item) redirect('/vendor-v2/portfolio')
  if (item.storage_path) await supabase.storage.from('vendor-media').remove([item.storage_path])
  await supabase.from('vendor_portfolio_items').delete().eq('id', itemId).eq('vendor_id', userId)

  revalidatePath('/vendor-v2/portfolio')
  redirect('/vendor-v2/portfolio?deleted=1')
}
