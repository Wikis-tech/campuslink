# Phase 7 Release Audit — 7A through 7F

Branch: `campuslink-ui-v3-experiment`

## 7A — PWA foundation and managed branding

Verified in source/database:
- Dynamic web manifest.
- Standalone display mode.
- Installed app starts at `/app?source=pwa`, not the public landing page.
- PWA entry routes authenticated users through `/dashboard` and signed-out users through Student/Vendor login.
- Super Admin-managed favicon and PWA icon are live in `platform_branding`.
- Branding uploads are Super Admin only and file-signature validated.
- Verification documents remain private storage.

Status: PASS.

## 7B — Secure service worker

Verified in source:
- Protected Admin, Student, Vendor, Auth, API, Billing and Paystack path families are never cached.
- HTML documents remain network authoritative.
- Only immutable Next.js static assets and the generic offline fallback are cached.
- Logout/private-data purge clears Campus Link caches.
- Admin cannot operate offline.

Status: PASS.

## 7C — PWA UX, mobile and performance

Verified in source/build:
- Student mobile bottom navigation and account actions exist.
- Student account actions include profile/verification, saved Vendors, Safety Centre, appearance and sign out.
- Vendor mobile navigation is horizontally usable and includes a More/account sheet, appearance and sign out.
- Installed PWA redirects public marketing/Admin paths back to the Student/Vendor app gateway.
- Phase 7F CI typecheck and production build pass with the current mobile/PWA implementation.

Status: PASS in automated/source checks.
Real Android/iPhone gesture, safe-area and browser-engine acceptance remains a physical-device release check.

## 7D — Technical SEO

Verified:
- Canonical metadata.
- Sitemap generation.
- robots configuration.
- Open Graph/Twitter metadata.
- Dynamic Vendor social-preview route.
- Search Console ownership and sitemap processing were already confirmed during Phase 7D.

Status: PASS.

## 7E — Privacy-aware SEO

Verified in source:
- Canonical origin normalized to `https://campuslink.name.ng`.
- www host permanently redirects to canonical non-www host.
- Student/Vendor/Admin/Auth/PWA/API private surfaces are not indexable.
- Public Vendor SEO requires approved identity, approved campus relationship and safe marketplace state.
- Public share metadata sanitizes accidental email, phone and pasted URL content.
- Under-review/actively suspended Vendors do not receive a public share page.
- Public Vendor share responses use no-store so stale safety state is not kept as a public page cache.

Status: PASS in code/build. Promote the current Phase 7F preview before evaluating these rules on the production domain.

## 7F — Production hardening

Implemented and verified:
- V3 branch now has an active GitHub release-gate workflow.
- Release invariants run automatically.
- TypeScript typecheck passes.
- npm production dependency audit reports zero vulnerabilities at the configured high-severity gate.
- Next.js production build passes.
- Vercel Preview build reaches READY.
- Generic production-safe 404 and global error UI added.
- Vercel runtime error scan returned no errors in the checked 24-hour window.
- Supabase project is ACTIVE_HEALTHY.
- Supabase Leaked Password Protection warning is no longer present.
- Student RLS regression: no Admin/audit/safety/payment/subscription data exposed.
- Vendor RLS regression: no Admin/audit/safety or other-Vendor billing data exposed.
- Admin AAL1 regression: sensitive Admin/payment/subscription data is blocked.
- Admin AAL2 regression: authorized Admin data becomes available only after MFA.
- Admin SECURITY DEFINER RPCs were rechecked; Admin entry points delegate to private AAL2-aware role/scope helpers.
- `admin_login_attempts` has no anon/authenticated table grants; its no-policy RLS advisor notice is intentional server-only isolation.
- 30 missing foreign-key covering indexes were added; Supabase no longer reports the unindexed-foreign-key advisor.
- Resend domain is verified with sending enabled; recent Campus Link transactional verification emails show delivered status.
- Minimal `/api/health` endpoint added for production uptime checks without exposing internal data.

## Advisor items deliberately not treated as release failures

- SECURITY DEFINER advisor: still reports RPCs intentionally callable by authenticated users. Authorization remains inside the functions and private Admin helpers. Re-review whenever an RPC changes.
- Unused-index advisor: newly added FK indexes can appear unused immediately because statistics have not accumulated; do not remove them just because they are new.
- Multiple-permissive-policy advisor: requires policy-by-policy consolidation and should not be changed blindly during a final regression phase because it can alter access semantics.
- Two Auth RLS initialization-plan warnings remain performance notices, not authorization failures.

## Release acceptance still requiring real external interaction

Before public launch/live-money mode, manually confirm:
- Android Chrome installed-PWA install/update/offline/logout/back-button behavior.
- iPhone Safari Add-to-Home-Screen/install/offline/logout behavior.
- Safari and Firefox signed-in smoke tests.
- Paystack test-mode declined/abandoned/delayed/duplicate/renewal-failure cases.
- One fresh end-to-end Auth OTP email request.
- One valid upload and one renamed/mismatched file rejection.

These are acceptance tests, not missing architecture. Do not switch Paystack to live mode until the Phase 8 launch gate explicitly approves it.
