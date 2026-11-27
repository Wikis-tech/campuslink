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


/*
 * Stage 4 security regression invariants.
 * These checks fail on accidental guard removal; they do not replace
 * authorised multi-account browser, RPC, or Paystack sandbox tests.
 */
requireAll('app/auth/confirm/route.ts',
  ["candidate.origin === fallback.origin", "verifyOtp({ type, token_hash: tokenHash })"],
  'confirmation must validate token and restrict redirects to the current origin')
requireAll('app/reset-password/actions.ts',
  ["supabase.auth.getUser()", "admin_memberships", "institution_admin_assignments", "supabase.auth.signOut()"],
  'self-service password recovery must verify identity and exclude active Admin memberships')
requireAll('app/forgot-password/actions.ts',
  ["resetPasswordForEmail(", "redirect('/forgot-password?sent=1')"],
  'recovery requests must use Supabase and retain generic response semantics')
requireAll('app/api/paystack/checkout/route.ts',
  ["assertPaystackTestMode()", "createPaystackCustomer(", "ALLOWED_PLANS", "Sign in to continue."],
  'checkout must enforce TEST mode, recognised plans and a signed-in user')
{
  const source = read('app/api/paystack/checkout/route.ts')
  if (source.indexOf('assertPaystackTestMode()') >= source.indexOf('createPaystackCustomer({')) {
    failures.push('app/api/paystack/checkout/route.ts: TEST guard must execute before external customer creation')
  }
}
requireAll('app/api/paystack/webhook/route.ts',
  ["verifyPaystackWebhookSignature(rawBody, signature)", "paystackEventFingerprint(rawBody)", "reconcileSuccessfulPaystackPayment(reference)"],
  'webhook must validate signature, replay fingerprint and provider reconciliation')
requireAll('app/onboarding/student/actions.ts',
  ["assertSafeVerificationUpload(document)", ".from('verification-documents')", "storagePath = \`\${userId}/student/"],
  'verification uploads must retain magic-byte validation and owner-prefixed private file paths')
requireAll('app/admin-v2/lib.ts',
  ["supabase.auth.getUser()", "currentLevel !== 'aal2'", "admin_memberships", "institution_admin_assignments"],
  'Admin must be authenticated with AAL2 and an active privileged assignment')
requireAll('next.config.ts',
  ["source: '/forgot-password'", "source: '/reset-password'", "source: '/control-center", "no-store, private"],
  'recovery and private Admin surfaces must remain non-cacheable and protected')

if (failures.length) {
  console.error('Phase 7F release gate failed:')
  for (const failure of failures) console.error(' - ' + failure)
  process.exit(1)
}

console.log('Phase 7F release invariants passed.')
