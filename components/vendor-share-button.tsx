'use client'

import { Share2 } from 'lucide-react'
import { useState } from 'react'

export function VendorShareButton({ slug, businessName }: { slug: string; businessName: string }) {
  const [copied, setCopied] = useState(false)

  const share = async () => {
    const url = new URL(`/share/vendor/${encodeURIComponent(slug)}`, window.location.origin).toString()
    const data = {
      title: `${businessName} on Campus Link`,
      text: `Check out ${businessName}, a verified Campus Link vendor.`,
      url,
    }

    try {
      if (navigator.share) {
        await navigator.share(data)
        return
      }
      await navigator.clipboard.writeText(url)
      setCopied(true)
      window.setTimeout(() => setCopied(false), 1800)
    } catch (error) {
      // AbortError means the native share sheet was closed intentionally.
      if (error instanceof DOMException && error.name === 'AbortError') return
      try {
        await navigator.clipboard.writeText(url)
        setCopied(true)
        window.setTimeout(() => setCopied(false), 1800)
      } catch {
        window.prompt('Copy this Campus Link vendor link:', url)
      }
    }
  }

  return (
    <button className="btn btn-ghost" type="button" onClick={share} aria-label={`Share ${businessName}`}>
      <Share2 size={17} />
      {copied ? 'Link copied' : 'Share vendor'}
    </button>
  )
}
