'use client'

import { useEffect, useRef } from 'react'

type Props = {
  vendorId: string
  event: 'profile_view' | 'product_view' | 'service_view' | 'portfolio_view' | 'search_impression' | 'search_click'
  productId?: string | null
  serviceId?: string | null
}

export function VendorAnalyticsBeacon({ vendorId, event, productId, serviceId }: Props) {
  const sent = useRef(false)

  useEffect(() => {
    if (sent.current) return
    sent.current = true
    const body = JSON.stringify({ vendorId, event, productId: productId || null, serviceId: serviceId || null })
    fetch('/api/analytics/vendor-event', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body,
      keepalive: true,
      credentials: 'same-origin',
    }).catch(() => {})
  }, [vendorId, event, productId, serviceId])

  return null
}
