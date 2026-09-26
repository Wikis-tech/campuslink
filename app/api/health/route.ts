import { NextResponse } from 'next/server'
import { createAdminClient } from '@/lib/supabase/admin'

export const dynamic = 'force-dynamic'
export const runtime = 'nodejs'

export async function GET() {
  try {
    const admin = createAdminClient()
    const { error } = await admin.from('institutions').select('id').limit(1)

    if (error) {
      return NextResponse.json(
        { ok: false, service: 'campuslink' },
        {
          status: 503,
          headers: {
            'Cache-Control': 'no-store, max-age=0',
            'X-Robots-Tag': 'noindex, nofollow, noarchive, nosnippet',
          },
        },
      )
    }

    return NextResponse.json(
      { ok: true, service: 'campuslink' },
      {
        status: 200,
        headers: {
          'Cache-Control': 'no-store, max-age=0',
          'X-Robots-Tag': 'noindex, nofollow, noarchive, nosnippet',
        },
      },
    )
  } catch {
    return NextResponse.json(
      { ok: false, service: 'campuslink' },
      {
        status: 503,
        headers: {
          'Cache-Control': 'no-store, max-age=0',
          'X-Robots-Tag': 'noindex, nofollow, noarchive, nosnippet',
        },
      },
    )
  }
}
