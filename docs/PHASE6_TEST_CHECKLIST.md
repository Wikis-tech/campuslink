# Phase 6 — Security Acceptance Checklist

Run on the newest `campuslink-ui-v3-experiment` Preview before production promotion.

## Admin route concealment and authorization
- [ ] Anonymous GET `/admin` shows Campus Link Not Found/error, not an Admin login.
- [ ] Anonymous GET `/admin-login` shows Not Found/error.
- [ ] `/admin-login-campus` is the intentional Admin sign-in entry.
- [ ] Signed-in Student opening `/admin` or `/admin-v2` receives Not Found/error and never gets Admin data.
- [ ] Signed-in Vendor opening `/admin` or `/admin-v2` receives Not Found/error and never gets Admin data.
- [ ] Admin account using normal `/login` is signed out/rejected and directed to the separate authorized flow without exposing role details.

## MFA / Admin session
- [ ] Correct Admin password without TOTP cannot open `/admin-v2`.
- [ ] First-time Admin can enroll TOTP with an authenticator app.
- [ ] Wrong/expired TOTP is rejected.
- [ ] Correct TOTP raises the session to AAL2 and opens the authorized Admin workspace.
- [ ] After 20 minutes idle, Admin is signed out.
- [ ] An Admin session manually visiting `/student` receives Not Found.
- [ ] An Admin session manually visiting `/vendor-v2` receives Not Found.
- [ ] Five wrong passwords for one Admin account within 15 minutes trigger account throttling.
- [ ] Credential-stuffing style failures from one source are throttled without revealing whether an email is an Admin.

## Role separation
- [ ] School Admin sees only assigned school records.
- [ ] School Verifier cannot grant global roles.
- [ ] School Support cannot approve global/vendor identity operations outside its scope.
- [ ] Operations Admin cannot create a global Admin.
- [ ] Only Super Admin can grant global roles / Super Admin.
- [ ] Password-only AAL1 Admin cannot use Admin database/RPC privileges.

## Student / Vendor isolation
- [ ] Student cannot read another Student's private verification material.
- [ ] Vendor cannot read another Vendor's private billing or verification data.
- [ ] Student cannot forge contact evidence.
- [ ] Student cannot directly write review trust metadata or Vendor responses.
- [ ] Vendor cannot alter Student review rating/comment/moderation.
- [ ] Cross-school Student cannot discover/access a Vendor not approved for that institution.
- [ ] Under-review/suspended Vendor remains absent from Student discovery regardless of Pro status.

## Payment abuse
- [ ] Calling checkout while signed out returns unauthorized.
- [ ] Student cannot initialize Vendor Pro checkout.
- [ ] Invalid/tampered plan slug is rejected.
- [ ] Repeated checkout abuse is rate-limited.
- [ ] Fake callback reference never activates Pro.
- [ ] A reference belonging to another Vendor never activates the current Vendor.
- [ ] Wrong amount/currency/plan from verification cannot activate Pro.
- [ ] Unsigned or incorrectly signed webhook is rejected.
- [ ] Oversized webhook is rejected.
- [ ] Replaying the same webhook is idempotent and does not double-credit.
- [ ] Pro status never overrides verification, campus approval or suspension.

## Upload / storage abuse
- [ ] Rename TXT/EXE to JPG and upload: rejected.
- [ ] Rename non-PDF to PDF verification evidence: rejected.
- [ ] Unsupported SVG/script file: rejected.
- [ ] Oversized Vendor media (> bucket limit): rejected.
- [ ] Verification evidence remains private and cannot be opened anonymously.
- [ ] User cannot write into another user's storage folder.

## HTTP / browser defenses
- [ ] Responses include CSP, HSTS, nosniff, frame denial and referrer/permissions headers.
- [ ] Cross-site POST to sign-out/admin-timeout/checkout/analytics is rejected where Origin is supplied.
- [ ] Analytics endpoint rejects non-JSON, oversized and malformed UUID requests.
- [ ] No server secret appears in browser source, network payloads or committed V3 files.

## Regression
- [ ] Student marketplace/search 5H still works.
- [ ] Campus Trust/reviews/safety 5G still works.
- [ ] Vendor products/services/portfolio/profile uploads still work.
- [ ] Vendor analytics still records valid Student activity.
- [ ] Paystack test checkout still works.
- [ ] Admin school/vendor/review/safety workflows still work after MFA.
- [ ] Light/Dark and mobile layouts remain intact.

## Required before production
- [ ] Enable Supabase leaked-password protection.
- [ ] Generate and commit a package-manager lockfile.
- [ ] Latest Phase 6 Preview is READY and acceptance checks pass.
- [ ] Do not promote to `campuslink.name.ng` until the above pass.

## PWA security gate (when PWA is implemented)
- [ ] Service worker does not cache `/admin*`, `/admin-login-campus*`, authenticated HTML/API requests, Paystack endpoints, auth tokens or verification documents.
- [ ] Authenticated pages use network/no-store behavior where appropriate.
- [ ] Offline cache contains public/static assets only.
- [ ] Sign-out clears sensitive client state/cache.
