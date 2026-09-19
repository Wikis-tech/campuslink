# Campus Link Phase 6 — Security & Abuse Acceptance

Phase 6 is a defense-in-depth release gate. A passing UI does not override a failed database, auth, storage, payment, or authorization test.

## Admin control plane

- `/admin` returns the normal Campus Link not-found/error experience.
- Deprecated `/admin-login` does not expose an Admin form.
- Authorized Admin entry is `/admin-login-campus`.
- Student and Vendor credentials are rejected by the Admin entry.
- Admin credentials entered on the normal user login are rejected and signed out.
- Five failed Admin sign-in attempts from the same email/IP key within 15 minutes are throttled.
- Password login alone never renders `/admin-v2`.
- First Admin login requires TOTP enrollment.
- Subsequent Admin login requires the current 6-digit authenticator code.
- Database Admin authorization requires AAL2, not only the UI.
- Idle Admin sessions sign out after 20 minutes.
- School Admins see only assigned schools.
- Super Admin-only actions remain unavailable to every narrower role.

## Student abuse tests

- A Student cannot access `/admin-v2`.
- A Student cannot call Admin RPCs successfully.
- A verified Student cannot change `institution_id` directly.
- A verified Student cannot silently move to another campus through onboarding.
- A Student cannot set `student_verification_status`.
- A Student cannot review a Vendor before verification.
- A Student cannot mark a review as contact-verified without a recorded Campus Link contact.
- Review edit throttling works.
- Report severity is server-derived.
- Cross-campus Vendor discovery is denied.

## Vendor abuse tests

- A Vendor cannot access `/admin-v2`.
- A Vendor cannot set `verification_status`.
- A Vendor cannot set `marketplace_status`, `suspended_until`, safety review fields, risk counters, ratings, review counts, or contact counters.
- A Vendor cannot approve its own campus relationship.
- A Vendor can still edit business-owned fields: name, description, WhatsApp, website, location, logo, cover and Vendor type.
- Product, portfolio and verification uploads reject mismatched file signatures even when the extension/MIME header is forged.
- Storage objects remain owner-path scoped.

## Payments

- Student checkout initialization returns 403.
- Unsupported plan slugs return 400.
- The browser never supplies the payable amount used by the server.
- Existing active Pro subscriptions cannot create a parallel checkout.
- Checkout attempts are rate-limited.
- Cross-origin checkout requests are rejected.
- Oversized/non-JSON checkout requests are rejected.
- Unsigned/fake Paystack webhooks return 401.
- Oversized webhook bodies return 413.
- Signed `charge.success` still calls Paystack Verify.
- Reference, amount and currency must match the server-created payment record.
- Duplicate webhook bodies are idempotent.
- Callback page alone never grants Pro.
- Payment never grants identity verification or campus approval.

## Database and storage

- RLS remains enabled on all user-facing tables.
- No `anon` or `authenticated` role has TRUNCATE/TRIGGER/REFERENCES on protected operational tables.
- Admin membership writes are RPC/server controlled.
- Payment ledgers are not browser writable.
- Verification documents remain private and owner/Admin scoped.
- Vendor public media stays owner-write scoped.
- Security-definer RPCs are reviewed for explicit caller/role checks and fixed search_path.
- Leaked-password protection is enabled in Supabase Auth before launch.

## Web/PWA

- CSP, HSTS, frame denial, nosniff, referrer policy and permissions policy are present.
- Authenticated pages are never cached into a future service worker/offline cache.
- Service worker, when Phase 8/PWA is implemented, caches only the public shell/static assets and explicitly bypasses Auth/Admin/API/private dashboard routes.
- PWA uses the same HTTPS origin, cookies, Supabase RLS and Admin MFA as the web app.

## Release rule

Do not mark Phase 6 complete or promote a security build to production until:
1. the latest `campuslink-ui-v3-experiment` commit builds READY,
2. this checklist passes on Preview,
3. Supabase security advisors have no unexplained launch-blocking warning,
4. Paystack remains TEST-only until its abuse cases pass,
5. the production promotion is made from the exact tested commit.
