# Kampivo - Handoff: Start Here

**Handoff date:** 2026-10-16  
**Product:** Kampivo (formerly CampusLink)  
**Repository:** `Wikis-tech/campuslink`  
**Active development branch:** `campuslink-ui-v3-experiment`  
**Last functional-code commit before this handoff note:** `30144f211cddcbae2f821f3cd6fd4cf0e07dde73` - **Refresh PWA icon after branding changes**

## Read this before changing anything

Kampivo is a late-stage private-beta / production-hardening campus marketplace and trust platform. It is **not** a fresh MVP and should not be rebuilt from scratch.

The current architecture deliberately separates three products:

- **Student:** campus marketplace/discovery, saved vendors, reviews, contact evidence, profile, safety/reporting.
- **Vendor:** business command centre - products, services, portfolio, business profile, availability, reviews, analytics, growth and billing.
- **Admin:** operations, school governance, Student/Vendor verification, Trust & Safety, communications, categories, audit and role-scoped administration.

The platform currently does **not** process Student-to-Vendor marketplace payments. Students discover and contact vendors directly (for example WhatsApp/phone). Paystack is for Vendor subscriptions/premium features only.

## Do not restart or re-architect casually

Do **not**:

- return to the legacy PHP application on `master`;
- restart the project as a new app;
- create a V4 merely to continue normal development;
- rebuild Supabase;
- rename database tables/columns, Paystack metadata keys, cache prefixes or internal identifiers only for cosmetic branding;
- make payment buy verification/trust;
- make a Vendor approved at one institution automatically approved at all institutions;
- broadly expose Admin/server-only tables just to silence an advisor warning;
- blindly revoke every SECURITY DEFINER RPC - inspect the authorization inside each function first;
- enable live Paystack before the full TEST lifecycle is accepted.

## Current source of truth

### GitHub
Use:

`campuslink-ui-v3-experiment`

Important historical branches:

- `master` - legacy PHP; reference only.
- `campuslink-v2-supabase` - earlier Next.js/Supabase rebuild.
- `campuslink-ui-v2-snapshot` - V2 UI snapshot.
- `kampivo-stage-c` - Stage C rebrand snapshot.
- `campuslink-ui-v3-experiment` - current application.

### Supabase
Project ref: `ylxddmmlhigfbjbcohwo`  
Project name: **Campus Link** (internal name intentionally preserved)  
Region: `eu-central-1`  
PostgreSQL: 17  
Current status at handoff: **ACTIVE_HEALTHY**

### Vercel
Team ID: `team_Jw148wtJolDGr0zuxfdIKm7e`  
Project ID: `prj_ToOEnU8m1JLE2xlGf7RUMlcdwpuW`  
Project name: `campuslink`

At handoff:
- latest Preview on the active branch: functional commit `30144f...`, READY;
- Production is behind Preview and was last observed on commit `d354e02...` (**Expose Kampivo legal pages in sitemap**).

Do not assume Preview has already been promoted.

### Resend
Verified sending domain currently: `campuslink.name.ng`  
Transactional template: **Kampivo Admin Alert**  
Template alias: `kampivo-admin-alert`

Visible brand is Kampivo, but the sender domain remains CampusLink until Stage D.

## Product principles that must survive every change

1. **Identity before visibility.**
2. **Campus approval before discovery.**
3. **Approval is school-specific.**
4. **Student privacy by default.**
5. **Payment never buys trust.**
6. **Least privilege everywhere.**
7. **Every sensitive Admin action should be auditable.**
8. **Free must remain useful; Pro should help Vendors grow.**
9. **Students should not be charged to discover or contact Vendors in the current model.**
10. **Real-user friction takes priority over adding large new features before launch.**

## Current phase

The canonical product roadmap reached:

- Phase 1 Foundation & Authentication - complete foundation.
- Phase 2 Onboarding & Verification - functional.
- Phase 3 Student Marketplace & Discovery - built.
- Phase 4 Admin & School Management - built.
- Phase 5A Monetisation foundation - built.
- Phase 5B Free/Pro enforcement - built.
- Phase 5C Paystack architecture - built; final TEST acceptance before Live.
- Phase 5D Billing management - largely built; final acceptance.
- Phase 5E Vendor Business Centre & Analytics - major build complete.
- Phase 5F Campus Intelligence & Availability - built.
- Phase 5G Trust Profiles, Reviews & Safety - implemented.
- Phase 5H Smart Search & Discovery - implemented and migrated live.
- Phase 6 Security & Abuse Hardening - substantially implemented.
- Phase 7 PWA + SEO + Production Readiness - reached Phase 7F-era hardening.

The most accurate present description is:

> **Late-stage private beta / production hardening + tester-feedback correction + Kampivo rebrand completion.**

## Rebrand status

Kampivo was introduced as a staged rebrand.

**Stage C** = user-visible rebrand. It changed public, Student, Vendor, Admin, PWA, notification and email presentation while deliberately keeping infrastructure identifiers stable.

**Stage D** = external infrastructure migration. Still pending until the Kampivo domain is acquired/cleared. It should migrate:
- Vercel primary domain;
- Supabase Auth site/redirect URLs;
- Admin invite/reset/confirmation links;
- Resend verified sender domain;
- support/privacy sender aliases;
- canonical/SEO/PWA URLs;
- old CampusLink domain to redirects while preserving old links.

Do not rename internal DB/table/metadata identifiers merely because Stage D begins.

## Most recent functional work

1. Rebranded Stage C surfaces to Kampivo.
2. Corrected legacy notification presentation.
3. Rebranded Resend template to Kampivo.
4. Added dismissible notification bell (unread/read/dismissed state).
5. Added Privacy, Terms and Trust & Compliance pages.
6. Added 18+ launch acknowledgement and Terms/Privacy acknowledgement at registration.
7. Added internal compliance documents: retention schedule, DSAR procedure, breach plan, processor register and DPIA.
8. Fixed Admin Branding so uploaded website logo/PWA app icon are actually consumed by major UI/PWA surfaces.
9. Branding assets now use dynamic endpoints such as `/brand/logo` and `/brand/app-icon/192`.

### Current age rule
The current code is **18+** for launch. A future 15-17 Student flow is possible, but it must include a proper parent/legal-guardian consent and safeguarding workflow rather than simply changing 18 to 15. Vendors/Admins should remain adults.

## Current security release gates

Supabase currently reports:
- 3 INFO items: RLS enabled but no browser policies on server-only Admin tables (`admin_communications`, `admin_communication_deliveries`, `admin_login_attempts`). These have been checked as intentionally not granted to normal browser roles.
- 26 WARNINGS for authenticated-callable SECURITY DEFINER RPCs. Several have internal role/account/school authorization checks, but a final adversarial function-by-function execution test is still required.
- Leaked-password protection is disabled and should be enabled before broad launch if supported by the current Supabase plan/configuration.

Remaining tests should include:
- Student calling Admin RPC -> denied.
- Vendor calling Admin RPC -> denied.
- School Admin A acting on School B -> denied.
- revoked/demoted Admin with an already-open session -> action denied.
- Student/Vendor cannot access Admin/private verification records.
- file/storage BOLA/IDOR tests.
- Paystack TEST edge cases, duplicate/delayed webhook and wrong reference/amount/currency scenarios.

## Current live data snapshot (2026-10-16)

Approximate live counts:
- institutions: 4
- profiles: 14
- admin memberships: 4
- vendor profiles: 1
- vendor-institution relationships: 1
- vendor services: 2
- vendor products: 2
- vendor portfolio items: 2
- reviews: 1
- saved vendors: 0
- contact events: 51
- complaints: 0
- Student verification rows: 3
- notifications: 9
- subscriptions: 3
- payments: 3
- audit logs: 42
- Admin communications: 3
- Admin communication deliveries: 9

Do not treat `contact_events=51` as 51 unique Students; earlier testing showed repeated events can come from the same Student. Vendor-facing metrics were adjusted to favour meaningful unique/lifetime signals.

## Important UX decisions

### Student
- Verified Students should **not** keep seeing Verification as an unfinished task.
- Unverified/pending Students can see Profile & Verification.
- Verified Students use **Profile** and a normal Student experience.

### Vendor
- Business Health is **not a trust score**.
- Business Health measures storefront/business completeness such as logo, cover, description, contact, listings, pricing and portfolio proof.
- Identity approval and campus approval are shown separately as **Trust & eligibility**.
- Vendor dashboard metrics should not inflate repeated raw contact events.

### Admin
- School role model was simplified toward one practical **School Admin** role per school for operations.
- Global Admins remain role-scoped.
- Admin identities/login are separate from Student/Vendor login.
- Admin MFA/AAL2, inactivity timeout and audit controls must remain.

## Design direction

The user explicitly dislikes generic "AI-generated SaaS" dashboards.

Use:
- simple, distinctive, human product UI;
- Sora headings;
- DM Sans body/interface;
- Lucide icons;
- mobile-first layouts;
- useful—not excessive—motion;
- restrained gradients/glass;
- strong dark mode;
- unique lines/shapes/visual details only when they improve the product;
- Kampivo orange brand accent with the established navy/deep-blue foundations.

Useful design references and research are already in:
- `docs/design/CAMPUSLINK_UIUX_REFERENCE.md`
- `docs/design/DASHBOARD_RESEARCH_20_PLATFORMS.md`

## Legal/compliance baseline already added

Public routes:
- `/privacy`
- `/terms`
- `/compliance`

Internal docs:
- `docs/compliance/KAMPIVO_DATA_RETENTION_SCHEDULE.md`
- `docs/compliance/KAMPIVO_DATA_SUBJECT_RIGHTS_PROCEDURE.md`
- `docs/compliance/KAMPIVO_BREACH_RESPONSE_PLAN.md`
- `docs/compliance/KAMPIVO_PROCESSOR_REGISTER.md`
- `docs/compliance/KAMPIVO_DPIA_VERIFICATION_SYSTEM.md`

Do not claim CAC registration, NDPC registration, Startup Label, trademark registration or official school endorsement before they are actually obtained.

## Immediate continuation order

1. Do not add large features.
2. Run fresh Student/Vendor/Admin human acceptance tests.
3. Run adversarial cross-role/cross-school RPC/storage tests.
4. Run complete Paystack TEST lifecycle.
5. Enable leaked-password protection if available and rerun advisors.
6. Acquire/clear `kampivo.name.ng` and complete Stage D.
7. Verify fresh Kampivo transactional email after sender-domain migration.
8. Freeze a release candidate.
9. Test that exact release candidate on desktop/mobile/PWA and multiple browsers.
10. Promote only the tested artifact to Production.
11. Monitor logs, auth, email and payment/webhook behaviour after launch.

## Existing engineering documents

Read the detailed engineering records in `docs/` before changing sensitive systems:
- `PHASE5C_PAYSTACK_SETUP.md`
- `PHASE5E_TEST_CHECKLIST.md`
- `PHASE5F_TEST_CHECKLIST.md`
- `PHASE5G_TEST_CHECKLIST.md`
- `PHASE5H_TEST_CHECKLIST.md`
- `PHASE6_SECURITY_ACCEPTANCE.md`
- `PHASE6_SECURITY_AUDIT.md`
- `PHASE6_TEST_CHECKLIST.md`
- `PHASE7A_PWA_FOUNDATION.md`
- `PHASE7C_PWA_UX_MOBILE_PERFORMANCE.md`
- `PHASE7D_TECHNICAL_SEO_CHECKLIST.md`
- `PHASE7E_PRIVACY_AWARE_SEO.md`
- `PHASE7F_PRODUCTION_HARDENING.md`
- `PHASE7_RELEASE_AUDIT.md`

## Handoff rule for a new AI/developer

Before making changes, verify the live state of:
1. GitHub branch head.
2. Vercel Preview and Production commits.
3. Supabase migrations + Security Advisor.
4. Resend domain/template.
5. Paystack environment mode.

Never assume an old chat commit is still current.

The Word/PDF master handoff produced on 2026-10-16 contains the full history, decisions, architecture, testing matrix and continuation runbook.
