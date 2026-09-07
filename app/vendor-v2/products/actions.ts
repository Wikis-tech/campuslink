'use server'

import { randomUUID } from 'node:crypto'
import { revalidatePath } from 'next/cache'
import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'

const MAX_IMAGE_BYTES = 5 * 1024 * 1024
const ALLOWED_TYPES = new Set(['image/jpeg', 'image/png', 'image/webp'])

function text(formData: FormData, key: string, max = 1000) { return String(formData.get(key) || '').trim().slice(0, max) }
function back(type: 'success' | 'error', value: string): never { redirect(`/vendor-v2/products?${type}=${encodeURIComponent(value)}`) }
async function requireVendor() { const supabase = await createClient(); const { data: userData } = await supabase.auth.getUser(); const user = userData.user; if (!user) redirect('/login'); const { data: profile } = await supabase.from('profiles').select('account_type').eq('id', user.id).maybeSingle(); if (!profile || profile.account_type !== 'vendor') redirect('/dashboard'); return { supabase, user } }

export async function createProduct(formData: FormData) {
  const { supabase, user } = await requireVendor()
  const name = text(formData, 'name', 140), description = text(formData, 'description', 1800), categoryId = text(formData, 'category_id', 80), pricingType = text(formData, 'pricing_type', 20) || 'fixed', rawPrice = text(formData, 'price_ngn', 30), image = formData.get('image')
  const price = rawPrice ? Number(rawPrice.replace(/,/g, '')) : null
  if (name.length < 2) back('error', 'Enter a product name.')
  if (!['fixed','from','contact'].includes(pricingType)) back('error', 'Choose a valid pricing option.')
  if (pricingType !== 'contact' && (price === null || !Number.isFinite(price) || price < 0)) back('error', 'Enter a valid price or choose Contact for price.')
  const { data: entitlementRows } = await supabase.rpc('get_my_vendor_entitlements'); const entitlement = Array.isArray(entitlementRows) ? entitlementRows[0] : null; const productLimit = Number(entitlement?.entitlements?.product_limit || 5)
  const { count } = await supabase.from('vendor_products').select('id', { count:'exact', head:true }).eq('vendor_id', user.id).eq('is_active', true)
  if ((count || 0) >= productLimit) back('error', `Your current plan allows ${productLimit} active products. Pause one or upgrade your plan.`)
  let coverImageUrl:string|null=null, storagePath:string|null=null
  if (image instanceof File && image.size > 0) { if (!ALLOWED_TYPES.has(image.type)) back('error','Product image must be JPG, PNG or WEBP.'); if (image.size > MAX_IMAGE_BYTES) back('error','Product image must be 5 MB or smaller.'); const ext=image.type==='image/png'?'png':image.type==='image/webp'?'webp':'jpg'; storagePath=`${user.id}/products/${randomUUID()}.${ext}`; const uploaded=await supabase.storage.from('vendor-media').upload(storagePath,await image.arrayBuffer(),{contentType:image.type,upsert:false}); if(uploaded.error) back('error',uploaded.error.message); coverImageUrl=supabase.storage.from('vendor-media').getPublicUrl(storagePath).data.publicUrl }
  const inserted=await supabase.from('vendor_products').insert({vendor_id:user.id,category_id:categoryId||null,name,description:description||null,price_ngn:pricingType==='contact'?null:price,pricing_type:pricingType,cover_image_url:coverImageUrl,storage_path:storagePath,is_active:true})
  if(inserted.error){if(storagePath) await supabase.storage.from('vendor-media').remove([storagePath]); back('error',inserted.error.message)}
  revalidatePath('/vendor-v2/products'); revalidatePath('/vendor-v2'); revalidatePath('/student'); back('success','Product added to your Campus Link catalogue.')
}
export async function setProductActive(formData: FormData) { const {supabase,user}=await requireVendor(); const id=text(formData,'product_id',80),active=text(formData,'active',10)==='true'; const result=await supabase.from('vendor_products').update({is_active:active,updated_at:new Date().toISOString()}).eq('id',id).eq('vendor_id',user.id); if(result.error) back('error',result.error.message); revalidatePath('/vendor-v2/products'); revalidatePath('/student'); back('success',active?'Product is visible again.':'Product paused.') }
export async function deleteProduct(formData: FormData) { const {supabase,user}=await requireVendor(); const id=text(formData,'product_id',80); const {data:product}=await supabase.from('vendor_products').select('storage_path').eq('id',id).eq('vendor_id',user.id).maybeSingle(); const result=await supabase.from('vendor_products').delete().eq('id',id).eq('vendor_id',user.id); if(result.error) back('error',result.error.message); if(product?.storage_path) await supabase.storage.from('vendor-media').remove([product.storage_path]); revalidatePath('/vendor-v2/products'); revalidatePath('/student'); back('success','Product removed.') }
