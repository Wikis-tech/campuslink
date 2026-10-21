import { NextResponse, type NextRequest } from 'next/server'
import { createClient } from '@/lib/supabase/server'

export const dynamic = 'force-dynamic'

export async function GET(_request: NextRequest, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  const supabase = await createClient()

  const { data: product } = await supabase
    .from('vendor_products')
    .select('id,storage_path,cover_image_url')
    .eq('id', id)
    .maybeSingle()

  if (!product) return new NextResponse(null, { status: 404 })

  let sourceUrl: string | null = null

  if (product.storage_path) {
    sourceUrl = supabase.storage.from('vendor-media').getPublicUrl(product.storage_path).data.publicUrl
  } else if (product.cover_image_url) {
    try {
      const parsed = new URL(product.cover_image_url)
      if (parsed.protocol === 'https:' && parsed.hostname === 'ylxddmmlhigfbjbcohwo.supabase.co') {
        sourceUrl = parsed.toString()
      }
    } catch {
      sourceUrl = null
    }
  }

  if (!sourceUrl) return new NextResponse(null, { status: 404 })

  const upstream = await fetch(sourceUrl, { cache: 'no-store' })
  if (!upstream.ok) return new NextResponse(null, { status: 404 })

  const contentType = upstream.headers.get('content-type') || 'image/jpeg'
  if (!contentType.startsWith('image/')) return new NextResponse(null, { status: 415 })

  return new NextResponse(upstream.body, {
    status: 200,
    headers: {
      'Content-Type': contentType,
      'Cache-Control': 'private, max-age=300',
      'X-Content-Type-Options': 'nosniff',
    },
  })
}
