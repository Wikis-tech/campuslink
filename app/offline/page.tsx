import Link from 'next/link'
import { CloudOff, RefreshCw, ShieldCheck } from 'lucide-react'
import { Brand } from '@/components/Brand'

export const metadata = {
  title: 'Offline',
  robots: { index: false, follow: false },
}

export default function OfflinePage() {
  return (
    <main className="cl-offline-page">
      <section className="cl-offline-card">
        <Brand />
        <div className="cl-offline-icon" aria-hidden="true"><CloudOff /></div>
        <span className="cl-offline-kicker"><ShieldCheck size={15} /> Secure offline mode</span>
        <h1>Campus Link needs a connection for live account data.</h1>
        <p>
          For your privacy, Student, Vendor and Admin pages are not stored for offline viewing.
          Reconnect to continue with marketplace data, reviews, billing, verification and account actions.
        </p>
        <div className="cl-offline-actions">
          <Link href="/app" className="btn btn-primary"><RefreshCw size={16} /> Try again</Link>
          <Link href="/login" className="btn btn-outline-primary">Go to sign in</Link>
        </div>
        <small>No private Campus Link information has been loaded from an offline cache.</small>
      </section>
    </main>
  )
}
