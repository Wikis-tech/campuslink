'use client'

import { useState } from 'react'
import { ArrowRight, LoaderCircle, LockKeyhole } from 'lucide-react'

export function PaystackCheckoutButton({ planSlug, label }: { planSlug: 'pro-monthly' | 'pro-annual'; label: string }) {
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)

  async function startCheckout() {
    if (loading) return
    setLoading(true)
    setError(null)
    try {
      const response = await fetch('/api/paystack/checkout', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ planSlug }),
      })
      const body = await response.json().catch(() => ({})) as { authorizationUrl?: string; error?: string }
      if (!response.ok || !body.authorizationUrl) throw new Error(body.error || 'Checkout could not be started.')
      window.location.assign(body.authorizationUrl)
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Checkout could not be started.')
      setLoading(false)
    }
  }

  return (
    <div className="phase5c-checkout-wrap">
      <button className="phase5c-checkout-button" type="button" onClick={startCheckout} disabled={loading}>
        {loading ? <LoaderCircle className="phase5c-spin" size={17}/> : <LockKeyhole size={17}/>} {loading ? 'Opening secure checkout…' : label} {!loading ? <ArrowRight size={16}/> : null}
      </button>
      {error ? <p className="phase5c-checkout-error" role="alert">{error}</p> : <small className="phase5c-checkout-note">Secure Paystack checkout · Campus Link never stores your card details.</small>}
    </div>
  )
}
