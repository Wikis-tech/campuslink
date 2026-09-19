# Phase 6 — Security & Abuse Testing Audit

Date: 2026-09-19
Branch: `campuslink-ui-v3-experiment`

## Scope

Phase 6 covers the Campus Link web application and backend security boundaries: authentication, administrator access, MFA, authorization/RLS, privileged RPCs, school isolation, billing/Paystack, upload/storage safety, abuse throttles, HTTP security headers, secrets, session handling and dependency vulnerabilities.

The installable PWA/service-worker layer is not yet present in this repository. PWA-specific cache/offline testing is therefore a release gate for the later PWA implementation, not something that can be truthfully marked complete now.

## Implemented hardening

### Administrator control plane
- Public `/admin` probe returns Not Found.
- Deprecated `/admin-login` returns Not Found and its old password-only server action is inert.
- Intended administrator entry is `/admin-login-campus`.
- Active administrator identities are rejected by the normal Student/Vendor login flow.
- Admin control-plane access requires active global/school membership plus Supabase MFA AAL2.
- TOTP enrollment/challenge supports authenticator applications such as Google Authenticator and Microsoft Authenticator.
- MFA requirement is enforced both in Next.js server routing and PostgreSQL authorization helpers.
- Idle Admin sessions sign out after 20 minutes.
- Admin identities are blocked from Student and Vendor workspaces.
- Brute-force throttle uses separate hashed email and IP fingerprints: 5 account failures / 15 minutes and 25 source-IP failures / 15 minutes.
- Global roles and school-scoped roles remain separate. Super Admin alone can grant global roles.

### Database authorization
- RLS remains enabled on sensitive tables.
- MFA-aware role helpers enforce AAL2 for Admin access.
- School Admin access is scoped to assigned institutions.
- Sensitive Student review/report/contact writes remain validated RPC-only.
- Browser writes to billing/entitlement/raw analytics tables are removed or constrained.
- Raw analytics and promotion tables were reduced to least-required grants.
- Verification documents remain private.
- Public Vendor media is image-only and ownership-scoped for writes.

### Payments
- Checkout requires authenticated Vendor identity.
- Plan names are server allowlisted and prices/plans are loaded server-side.
- Checkout requests enforce same origin, JSON content type and small request size.
- Checkout initialization is rate-limited per Vendor.
- Paystack secret stays server-only.
- Webhook signature uses HMAC SHA-512 and timing-safe comparison.
- Webhook request size is capped.
- Webhook events are fingerprinted/idempotent.
- Successful payment is independently verified with Paystack.
- Reference, amount, currency, Campus Link subscription and plan are all checked before Pro activation.
- Browser callback cannot grant Pro by itself.
- Billing entitlement refresh RPC is not executable by authenticated browser users.

### Uploads
- JPG, PNG, WEBP and verification PDF uploads are checked by file signature, not only filename/MIME claims.
- Storage bucket MIME and size limits are configured.
- Verification documents are private.
- Upload ownership is constrained to the current user's storage folder.

### HTTP/browser boundary
- CSP, HSTS, X-Content-Type-Options, frame denial, referrer policy and Permissions-Policy are configured.
- Admin timeout, normal sign-out, checkout and analytics action endpoints use same-origin defenses where appropriate.
- Analytics route now enforces JSON, body-size and UUID validation.
- Framework security versions were patched to Next.js 16.3.3 and React/React DOM 19.1.9.

## Verified adversarial checks

### Admin authorization matrix
Database tests were executed as the real PostgreSQL `authenticated` role with representative JWT claims.

| Identity | AAL | Admin membership visible | Audit logs visible | Billing control-plane visible |
|---|---|---:|---:|---:|
| ordinary Student | aal1 | 0 | 0 | 0 |
| ordinary Vendor | aal1 | 0 | 0 | owner-only |
| active Admin | aal1 | 0 | 0 | 0 |
| active Admin | aal2 | authorized | authorized | authorized by role |

Additional helper test:
- Student aal1 -> Admin gate false
- Student aal2 -> Admin gate false
- Vendor aal1 -> Admin gate false
- Vendor aal2 -> Admin gate false
- Admin aal1 -> Admin gate false
- Admin aal2 -> Admin gate true

This confirms MFA is a database authorization boundary rather than only a front-end prompt.

## Residual / release-gate work

1. **Supabase leaked-password protection:** Supabase Security Advisor reports that compromised-password protection is disabled. Enable it in Supabase Auth settings before public launch.
2. **Lockfile:** package versions are exactly pinned but this repository currently has no package lockfile. Generate and commit `package-lock.json` (or the chosen package manager lockfile) from a network-enabled trusted development environment.
3. **CSP:** current Next.js-compatible CSP still permits inline scripts/styles. A nonce/hash-based CSP is a desirable later hardening task but should be migrated and regression-tested deliberately.
4. **SECURITY DEFINER advisor warnings:** intentional public RPCs remain flagged by the generic linter. They have fixed search paths, no anon execute grants, and actor/role/MFA gates. Re-audit whenever any RPC changes.
5. **PWA:** when the service worker is implemented, never cache Admin routes, authenticated HTML/API responses, auth tokens, Paystack routes/callbacks, or private verification documents.
6. **Credential rotation:** any credential ever exposed outside intended secret storage should be rotated. Current V3 source contains placeholders only; real server keys must remain in Vercel/Supabase secret configuration.
7. **Human acceptance test:** run the Phase 6 checklist on the latest Preview before production promotion.

## Security principle

Campus Link does not treat an obscure Admin URL as an authorization control. Route concealment reduces automated probing, while actual protection is provided by authentication, TOTP MFA, role/school authorization, RLS, protected RPCs, least-privilege grants, session controls and auditability.
