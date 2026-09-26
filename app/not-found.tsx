import Link from 'next/link'

export default function NotFound() {
  return (
    <main style={{ minHeight: '100dvh', display: 'grid', placeItems: 'center', padding: '24px', background: '#071421', color: '#f8fafc' }}>
      <section style={{ width: 'min(560px, 100%)', textAlign: 'center' }}>
        <p style={{ margin: 0, opacity: 0.7 }}>Campus Link</p>
        <h1 style={{ fontSize: 'clamp(2rem, 8vw, 4rem)', margin: '10px 0' }}>Page not found</h1>
        <p style={{ lineHeight: 1.6, opacity: 0.78 }}>The page you requested is unavailable or you do not have access to it.</p>
        <Link href="/" style={{ display: 'inline-block', marginTop: 18, padding: '12px 18px', borderRadius: 12, background: '#1EA952', color: '#fff', textDecoration: 'none', fontWeight: 700 }}>
          Go to Campus Link
        </Link>
      </section>
    </main>
  )
}
