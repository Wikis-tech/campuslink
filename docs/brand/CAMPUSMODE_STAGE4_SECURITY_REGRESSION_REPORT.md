# CampusMode — Stage 4 Security and Regression Report

Date: 26 November 2026
Repository: Wikis-tech/campuslink
Work branch: `campuslink-ui-v3-experiment`
App: Next.js + Supabase + Vercel + Paystack
Project: Supabase `ylxddmmlhigfbjbcohwo`
Status: **IN PROGRESS — NOT APPROVED FOR LAUNCH**

## Scope and change discipline
- Reviewed the connected live Supabase Security/Performance Advisors; live database RLS/storage policies; selected function definitions; GitHub source for public/private routing, authentication, payment processing, document storage and Analytics; Vercel deployments, recent error clusters and unauthenticated public route responses.
- No destructive SQL, changes to production tables/data/policies, live payment transactions, account creation or unsolicited emails were performed.
- All code edits target the existing V3 development branch. Production release is not authorised by this report.

## Completed targeted fix
- **Paystack checkout TEST guard moved forward** in `app/api/paystack/checkout/route.ts`. The `assertPaystackTestMode()` check now runs before creating a provider-side customer; previously a live key could create a customer before transaction initialization was blocked. Commit `391e98101d6ee72105745e08bd7324b040955498`.
- Preview deployment of fix: READY, `https://campuslink-72k7ts1aq-campus-link.vercel.app` (not a production promotion).

## Student/Vendor account recovery implementation

Added `/forgot-password` and `/reset-password` pages, corresponding server actions and a visible Forgot Password link on the Student/Vendor login page. Recovery requests use Supabase `resetPasswordForEmail` with current-site callback. Unknown-address messaging remains generic; password entry requires 12–128 characters and confirmation; the page signs out after a successful password change. Admin profiles are redirected to the separate Admin setup flow. Recovery pages have noindex/no-store headers.

**Requires acceptance:** Auth provider Reset Password email must use the correct recovery link; clicking it must establish a Supabase recovery session in `/auth/confirm` so `/reset-password` can update a password, without leaking account existence. Test on mobile and with the recipient opening the email in another browser. Prefer checking `{{ .TokenHash }}` token-based SSR link construction if the current `{{ .ConfirmationURL }}` flow does not establish the session.

## Evidence-backed checks
- Vercel last 7 days: no recorded runtime error clusters; this is not a proof that journeys work.
- Fresh Supabase Security Advisor: 26 `authenticated_security_definer_function_executable` warnings. The inspected functions commonly check `auth.uid()`, AAL2, admin roles or school-specific membership, but complete role-adversarial tests remain pending. Do not mass-revoke EXECUTE or convert SECURITY DEFINER functions without tracing callers.
- Supabase Security Advisor: 3 `rls_enabled_no_policy` informational notices for `admin_communication_deliveries`, `admin_communications`, `admin_login_attempts`. These are intentionally server-controlled in the current design; unplanned broad policies would be dangerous.
- Performance Advisor: 4 unindexed foreign keys; 2 auth RLS initplan advisories; numerous unused indexes from low usage. Prioritise after correctness and throughput measurements; do not delete indexes indiscriminately.
- Buckets: `verification-documents` private (5 MiB, JPG/PNG/WebP/PDF); `vendor-media` public images; `branding-assets` public images.
- `storage.objects` policies for verification documents scope owner by first path folder equal to `auth.uid()`; global verification admins can read, School Admins may read student documents only for assigned institution. No anonymous verification-document policy was observed.
- `public.student_documents`, `student_verifications`, `vendor_documents` have RLS owner policies, review-admin policies; school-scoped Student document read is present.
- Admin access helper checks real Supabase user, active Admin membership and Supabase AAL2 MFA; School Admin helper checks active membership for target institution.
- Paystack webhook verifies HMAC-SHA512 signature before writes, stores idempotency event fingerprint, checks Paystack transaction via provider Verify and validates amount, currency/reference/plan before granting entitlements (source review, not live charge test).
- Public/unauthenticated Preview: `/`, `/login`, `/register`, `/student`, `/vendor-v2`, `/control-center`, `/admin-login-campus`, `/api/health`, manifest, sitemap, robots and offline route returned HTTP 200. Internal `/admin-v2` returned 404; GET on Paystack checkout/webhook and analytics endpoints returned 405. `/media/product/invalid` returned 404. Tested responses carried HSTS, CSP and X-Frame-Options DENY.
- A 200 for a gated URL can be a sign-in page/streamed NotFound. No logged-in E2E test has been executed by this audit. Preview can carry X-Robots-Tag noindex by hosting configuration; production homepage does not carry that header. Do not equate preview crawl settings with live SEO rules.
- Existing `/api/health` service identifier `campuslink` is internal, not a visible product name.

## Security concern to review before final approval
- The `resolve_current_account_type()` SECURITY DEFINER function reads **user-controlled raw_user_meta_data** to infer `account_type='admin'` for an account with `campuslink_admin_account=true`. It does NOT grant `admin_memberships` or AAL2 and therefore does not by itself establish Admin privilege; nonetheless, user-controlled metadata must not define admin identity. Audit this mapping and consider checking trusted Admin invitation/app metadata or membership before ever writing an Admin profile. Do not change it blindly without onboarding regression tests.
- Student/Vendor forgot-password and reset-password pages are implemented but **live recovery email/session tests are still outstanding** before Stage 5.
- Confirm leaked-password protection and exposed password policy in Supabase Auth settings.
- Confirm support email, privacy/legal claims, email templates and Admin invite flows after owner-side edits.

## Required authenticated test matrix (PENDING, DO NOT MARK PASS)
1. STUDENT: signup -> confirm OTP -> wrong/expired OTP/resend -> login -> select school -> upload verification -> pending/rejected/approved state -> Verified Profile -> Discover/filter -> Saved -> contact analytics -> reviews/safety -> logout/re-login.
2. VENDOR: fresh signup -> email confirmation -> onboarding/ID upload -> School A pending/approved -> School B not auto-approved -> products/services/portfolio/uploads -> active/suspended visibility -> analytics -> reviews -> account settings -> logout.
3. ADMIN: dedicated login -> reject normal Student/Vendor on Admin path -> enrol and verify MFA -> idle timeout -> Super Admin roles -> School Admin institution A allowed and B denied (UI + REST/RPC + storage) -> approvals -> communications -> safety/audit -> invitation expiry and revocation.
4. PRIVATE FILES: unauthenticated and cross-account cannot download verification PDF/JPG even with guessed storage keys; owner can view own; School Admin can view only assigned Student; user cannot overwrite someone else's file or forge content type.
5. PAYSTACK **TEST ONLY**: correct plan/amount/currency -> rejected tampered amount -> declined/pending -> duplicate webhook -> forged signature -> replay -> subscription reconciliation -> cancellation/renewal -> entitlements correctly reflected. Never use real funds in audit.
6. DEVICES: Chrome, Firefox, Edge and a mobile browser; mobile/tablet/desktop; light/dark; screen reader labels/keyboard; PWA install/update/offline; poor connection and graceful error state.
7. RESILIENCE: concurrent updates, duplicate signup, role-change session refresh, rate limiting, idempotency, stale cache, document upload errors and provider downtime.
8. TECHNICAL: `npm run check`, `npm run build`, dependency audit, authenticated Playwright E2E and security checks, then release-candidate production-mode rebuild and verification.

## Source docs/remediation references
- Security Advisor: https://supabase.com/docs/guides/database/database-linter?lint=0029_authenticated_security_definer_function_executable
- Unindexed FK: https://supabase.com/docs/guides/database/database-linter?lint=0001_unindexed_foreign_keys
- RLS perf: https://supabase.com/docs/guides/database/database-linter?lint=0003_auth_rls_initplan
- Supabase RLS: https://supabase.com/docs/guides/database/postgres/row-level-security

## Additional data-state summary (read-only)

- 14 Supabase auth users and 14 profiles; no missing profiles.
- 4 Student, 6 Vendor, 4 Admin profiles.
- 2 pending payments, 1 active subscription, 1 approved vendor-campus relationship.
- 2 active global Admin memberships, 1 active School Admin assignment.
- These are counts, not full workflow acceptance. Pending payments require inspecting test-state/reconciliation before treating as failures.

## Release decision
**NO-GO for Stage 5.** Targeted automated and unauthenticated checks passed, but real multi-role authenticated tests, adversarial role/RPC/storage tests, password recovery, Paystack TEST lifecycle and cross-device UI acceptance have not yet been performed. No blanket “zero bugs” guarantee can be made.


## Additional independent Stage 4 checks — 26 November 2026

- Re-ran Supabase Security Advisor: 26 externally surfaced authenticated SECURITY DEFINER RPC warnings, three INFO server-only RLS tables, and leaked-password protection disabled; Supabase Health Advisor reports no notices.
- Read-only SQL verification: **41/41 public application tables have RLS enabled**. The three policy-free internal tables have service_role SQL grants but no anon/authenticated SQL grants. Do **not** create ordinary public SELECT policies to dismiss INFO.
- Storage bucket readback: verification-documents is private, with owner-prefixed policies and institution-scoped School Admin access. Public vendor images and branding assets are separate buckets.
- Reviewed exact function source: private.has_admin_role and private.has_institution_admin_role require AAL2 MFA and active role/school membership. Inspected admin_assign_global_role_by_email, admin_review_student, admin_review_vendor_campus, admin_set_vendor_marketplace_status, vendor_respond_to_review, student_submit_vendor_review and five self-service RPC functions. These contain role/ownership checks but must be verified using separate authenticated accounts rather than SQL running as the owner.
- **Code fix:** auth callback previously accepted any return path starting with a single slash. Stage 4 commit `fbd5a6e7c73daff00f11e9847b7ddc4e7e877e60` now resolves the redirect destination using the URL parser and only accepts a matching application origin, with `/dashboard` as fallback. Vercel Preview **READY** for this commit; genuine confirmation/recovery link click-through still pending.
- Inspected verification document upload code: declared MIME, file magic, 5 MiB limit and owner-prefixed storage paths are enforced. File contents are not proven malware-free merely from magic-byte inspection.
- Latest Performance Advisor: 4 unindexed foreign keys INFO, 2 auth RLS initplan WARN, 39 unused-index INFO and 41 multiple-permissive-policy WARN findings. These do not justify wholesale policy/index changes; review explain plans before optimization.
- GET smoke-tested 24 paths on the Stage 4 checkout-guard preview across homepage/auth, Student, Vendor, Admin, PWA and health, all responded as expected at HTTP status level (23 HTTP 200 responses, implementation-only `/admin-v2` HTTP 404). Many 200s on protected routes are login/denied shells, not functional evidence.
- Current real-user acceptance still needed for the new password recovery UI, School A versus B role isolation, billable TEST plan, idle MFA and browser/PWA checks.

These findings supplement, not replace, the existing NO-GO assessment.
