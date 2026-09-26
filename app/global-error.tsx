'use client'

export default function GlobalError({ reset }: { error: Error & { digest?: string }; reset: () => void }) {
  return (
    <html lang="en">
      <body style={{ margin: 0, minHeight: '100dvh', display: 'grid', placeItems: 'center', padding: '24px', background: '#071421', color: '#f8fafc', fontFamily: 'system-ui, sans-serif' }}>
        <main style={{ width: 'min(560px, 100%)', textAlign: 'center' }}>
          <p style={{ margin: 0, opacity: 0.7 }}>Campus Link</p>
          <h1 style={{ fontSize: 'clamp(2rem, 8vw, 3.5rem)', margin: '10px 0' }}>Something went wrong</h1>
          <p style={{ lineHeight: 1.6, opacity: 0.78 }}>Your account data has not been changed. Try the request again, or return after reconnecting if your network dropped.</p>
          <button type="button" onClick={() => reset()} style={{ marginTop: 18, padding: '12px 18px', border: 0, borderRadius: 12, background: '#1EA952', color: '#fff', fontWeight: 700, cursor: 'pointer' }}>
            Try again
          </button>
        </main>
      </body>
    </html>
  )
}
