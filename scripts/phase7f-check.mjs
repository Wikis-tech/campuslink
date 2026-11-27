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

// Shared vendor URLs are public SEO pages; their UI must still reflect the
// current authenticated session and must not bypass campus-specific contact.
requireAll('app/share/vendor/[slug]/page.tsx',
  ["supabase.auth.getUser()", "account_type,institution_id,onboarding_completed_at,student_verification_status",
   "institutions.some((institution) => institution.id === profile?.institution_id)",
   "Contact a vendor from another campus", "Signed in", "WhatsApp this vendor"],
  'shared vendor cards must preserve Student session awareness and campus scope')
{
  const source = read('app/share/vendor/[slug]/page.tsx')
  if (source.includes('Kampiv<strong>o</strong>')) {
    failures.push('app/share/vendor/[slug]/page.tsx: legacy Kampivo branding must not return')
  }
}
requireAll('app/student/explore-campuses/page.tsx',
  ["href={`/share/vendor/", "Cross-campus discovery & contact"],
  'cross-campus listing must continue linking to public read-only share pages')
requireAll('app/student/vendors/[slug]/contact/route.ts',
  [".eq('institution_id',profile.institution_id)", ".eq('status','approved')", "student_record_contact_event"],
  'protected contact must require campus approval and record contact evidence')

// Explicitly keep cross-campus contact server-only and restricted to verified
// Students + actively approved Vendors. Never serve the phone in public HTML.
requireAll('app/share/vendor/[slug]/contact/route.ts',
  ["supabase.auth.getUser()", "student_verification_status !== 'verified'",
   "vendor_institutions", ".eq('status', 'approved')", "institutions.is_active",
   "contact_events", "vendor_analytics_events", "https://wa.me/", "private, no-store"],
  'cross-campus WhatsApp contact must require Student verification and vendor approval')
requireAll('app/share/vendor/[slug]/page.tsx',
  ["from('vendor_portfolio_items')", ".eq('is_active', true)", "Projects &amp; Portfolio",
   "whatsappPath", "canContact"],
  'public vendor page must present approved portfolio and verified-Student contact only')

// New categories must be suggestions, not automatic taxonomy mutations.
// Keep server validation and Vendor service ownership checks intact.
requireAll('components/vendor-category-picker.tsx',
  ["other-campus-services", "name=\"other_service_details\"", "required", "maxLength={120}"],
  'Other campus services requires a bounded vendor-entered specification')
requireAll('app/onboarding/vendor/actions.ts',
  ["category.slug === 'other-campus-services'", "other_service_details: isOtherCategory ? otherServiceDetails : null"],
  'Vendor onboarding must validate and store custom service details only for Other')
requireAll('app/vendor-v2/services/actions.ts',
  ["category.slug === 'other-campus-services'", "other_service_details: isOtherCategory ? otherServiceDetails : null"],
  'Vendor workspace must enforce the same custom service validation')
requireAll('app/admin-v2/vendors/page.tsx',
  ["other_service_details", "customServiceMap", "Other service:"],
  'Admins must see vendor custom-service descriptions before approving a business')
requireAll('supabase/migrations/202611270001_vendor_other_service_details.sql',
  ["add column if not exists other_service_details", "vendor_services_other_service_details_length"],
  'Other category details must remain bounded in the database')

if (failures.length) {
  console.error('Phase 7F release gate failed:')
  for (const failure of failures) console.error(' - ' + failure)
  process.exit(1)
}

console.log('Phase 7F release invariants passed.')
