import fs from 'node:fs'

const read = (path) => fs.readFileSync(new URL('../' + path, import.meta.url), 'utf8')
const failures = []

function requireText(path, text, reason) {
  const source = read(path)
  if (!source.includes(text)) failures.push(`${path}: ${reason}`)
}

function requireAll(path, items, reason) {
  const source = read(path)
  for (const item of items) {
    if (!source.includes(item)) failures.push(`${path}: ${reason} (missing ${item})`)
  }
}

requireText('app/manifest.ts', "start_url: '/app?source=pwa'", 'installed PWA must start in the authenticated app gateway')
requireText('app/manifest.ts', "display: 'standalone'", 'PWA must remain standalone')
requireAll('public/sw.js',
  ["'/admin'", "'/admin-v2'", "'/admin-login-campus'", "'/student'", "'/vendor-v2'", "'/api'", "'/paystack'"],
  'secure service worker must bypass all protected application areas')
requireText('public/sw.js', "request.mode === 'navigate' || request.destination === 'document'", 'documents must remain network authoritative')
requireText('public/sw.js', "PURGE_PRIVATE_DATA", 'service worker must support private cache purge')
requireText('components/pwa-shell-controller.tsx', "router.replace('/app?source=pwa')", 'standalone PWA must redirect public/admin paths to app gateway')
requireAll('next.config.ts',
  ["source: '/student/:path*'", "source: '/vendor-v2/:path*'", "source: '/admin-v2/:path*'", "noindex, nofollow, noarchive, nosnippet"],
  'private route families must remain excluded from indexing')
requireText('next.config.ts', "Strict-Transport-Security", 'HSTS must remain enabled')
requireText('next.config.ts', "Content-Security-Policy", 'CSP must remain enabled')
requireText('lib/seo-privacy.ts', "https://www.campuslink.name.ng", 'canonical production origin must match the Vercel primary www host')
requireText('lib/paystack.ts', "createHmac('sha512'", 'Paystack webhook signatures must remain HMAC-SHA512 verified')
requireText('lib/paystack.ts', 'timingSafeEqual', 'Paystack webhook verification must remain timing safe')
requireText('app/api/paystack/webhook/route.ts', 'reconcileSuccessfulPaystackPayment', 'signed webhook must still reconcile against Paystack/server ledger')
requireText('lib/file-validation.ts', 'sniffUploadedFile', 'uploads must remain content-signature checked')
requireText('app/admin-v2/lib.ts', "currentLevel !== 'aal2'", 'Admin control plane must continue requiring AAL2')
requireText('app/share/vendor/[slug]/page.tsx', "eq('status', 'approved')", 'public Vendor SEO must require campus approval')
requireText('app/share/vendor/[slug]/page.tsx', "robots: {", 'public Vendor SEO must explicitly define crawler policy')

if (failures.length) {
  console.error('Phase 7F release gate failed:')
  for (const failure of failures) console.error(' - ' + failure)
  process.exit(1)
}

console.log('Phase 7F release invariants passed.')
