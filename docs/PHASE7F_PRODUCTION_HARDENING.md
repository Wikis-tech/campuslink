# Phase 7F — Production Hardening

This is the release gate for the Phase 7 PWA/SEO work.

## Automated controls

- Current V3 branch now runs CI on every push and pull request.
- Phase 7F source invariants validate the PWA boundary, private-route SEO controls, CSP/HSTS, Paystack webhook security, upload sniffing, Admin AAL2 MFA and public Vendor approval rules.
- TypeScript compilation is required.
- Production dependencies are audited at HIGH severity or above.
- A full Next.js production build is required.
- Generic production-safe 404 and global error experiences are present and do not disclose internal implementation details.

## Platform checks performed

- Vercel deployment build status checked.
- Vercel production runtime errors checked.
- Supabase project health checked.
- Supabase Security Advisor rerun.
- Supabase Performance Advisor rerun.
- Missing foreign-key covering indexes identified and added.
- Resend sending domain verified and sending capability confirmed.

## Important advisor interpretation

`admin_login_attempts` intentionally has RLS enabled with no browser policy. It is server-only by design.

Supabase continues to warn that authenticated users can invoke several SECURITY DEFINER RPCs. Those RPCs are intentional application entry points. Admin RPCs delegate authorization to private helpers that require both active Admin membership and AAL2 MFA; Student/Vendor RPCs validate the caller and ownership in the function body. These warnings must continue to be reviewed whenever an RPC changes.

## Manual release acceptance still required

Automated inspection cannot replace real-device/browser testing. Before Phase 8 launch, run:
- Student, Vendor and Admin signed-in journeys with dedicated test accounts.
- Android installed-PWA install/update/offline/logout test.
- iPhone Safari/Add-to-Home-Screen test.
- Safari, Firefox and Edge smoke test.
- Paystack test-mode successful, declined, abandoned, duplicate, delayed and failed-renewal cases.
- One end-to-end Supabase Auth email/OTP delivery check.
- One upload rejection test using a renamed non-image file and one valid image/PDF upload.

Do not switch Paystack to live mode as part of Phase 7F.
