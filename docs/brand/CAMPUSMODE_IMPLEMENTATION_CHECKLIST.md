# CampusMode Brand Implementation Checklist

Use this file during Stages 1-4.

## Stage 1 - Foundation
- [x] CampusMode spelling locked
- [x] Tagline locked
- [x] brand book created and retained outside runtime source
- [x] production logo master selected by brand owner
- [x] symbol master selected
- [x] scalable favicon source prepared
- [x] scalable PWA 192 source prepared
- [x] scalable PWA 512 source prepared
- [x] scalable Apple touch icon source prepared
- [x] OG/social source template prepared
- [x] colour tokens documented
- [x] typography documented
- [x] legacy name map approved

**Stage 1 status: COMPLETE.**

Runtime replacement of Kampivo assets is intentionally deferred to Stage 2. Domain-dependent identity remains intentionally deferred to Stage 5.

## Stage 2 - Product UI
- [x] landing
- [x] public header/footer
- [x] login
- [x] register
- [x] email check/verification screens
- [x] Student onboarding
- [x] Vendor onboarding
- [x] Admin login
- [x] Student Overview
- [x] Student Discover
- [x] Student Saved
- [x] Student Profile
- [x] Student Safety
- [x] Student Product/Service detail
- [x] Vendor storefront
- [x] Vendor Overview
- [x] Vendor Listings
- [x] Products
- [x] Services
- [x] Portfolio
- [x] Business Profile
- [x] Availability
- [x] Reviews
- [x] Analytics
- [x] Growth
- [x] Billing
- [x] Admin shell
- [x] Admin dashboards
- [x] Admin communications
- [x] Trust & Safety
- [x] notifications
- [x] PWA splash/install UI
- [x] favicon/app icons

**Stage 2 implementation status: COMPLETE (source and public preview corrections verified 20 November 2026; authenticated acceptance outstanding).**

### Post-implementation visual defect corrections — 20 November 2026
- [x] Replace legacy **Kampivo** wordmark in Terms, Privacy and Compliance navigation with the approved CampusMode logo asset.
- [x] Verify public preview route responses for landing, legal, Login, Register, Admin Login, email-check, offline and manifest; visible CampusMode present and no Kampivo string in responses.
- [x] Verify relevant Student and Vendor source-page files plus core Admin source-page files contain no literal Kampivo brand copy.
- [x] Preserve current support email/sender domain and internal identifiers pending Stage 5.
- [ ] Log in as real verified Student and visually accept student navigation, discovery, saved, profile and safety across desktop/mobile.
- [ ] Log in as approved Vendor and visually accept each workspace page including billing states.
- [ ] Log in as Super Admin and School Admin with MFA and confirm school-scoped admin routes and notifications.
- [ ] Verify browser cache/PWA installed client reflects new brand after refresh/reinstall.

**Caution:** Public HTTP 200 on gated routes does not demonstrate successful authenticated access; /admin-v2 returned 404 unauthenticated, which is consistent with route concealment but does not establish authorized behaviour. Do not sign off Stage 2 end-to-end acceptance until the unchecked tests pass.

Automated validation covers production build, public/auth/legal/PWA route responses, brand assets, manifest, and runtime-error monitoring. Authenticated Student/Vendor/Admin visual acceptance remains part of the human testing pass and Stage 4 full regression.

## Stage 3 - SEO/comms
- [x] metadata title
- [x] metadata description
- [x] applicationName
- [x] Open Graph
- [x] Twitter/X cards
- [x] schema.org names
- [x] sitemap content
- [x] robots content
- [x] current-domain canonicals preserved
- [ ] verification email copy
- [x] Admin alert template
- [x] invitation copy
- [ ] password recovery copy
- [x] legal pages
- [x] support/privacy wording

**Stage 3 code status: IMPLEMENTED (26 November 2026).** Supabase-hosted confirmation/recovery provider templates and end-to-end test email delivery still require owner acceptance. The unchecked verification/recovery email rows are deliberate. Full report: [CAMPUSMODE_STAGE3_IMPLEMENTATION_REPORT.md](./CAMPUSMODE_STAGE3_IMPLEMENTATION_REPORT.md).

## Stage 4 - Regression
- [ ] source scan for visible Kampivo
- [ ] source scan for visible CampusLink
- [ ] classify remaining old-name references
- [ ] Student lifecycle
- [ ] Vendor lifecycle
- [ ] Admin lifecycle
- [ ] RLS/BOLA
- [ ] privileged RPC checks
- [ ] file privacy
- [ ] notification ownership
- [ ] logout/session
- [ ] Admin MFA
- [ ] Paystack TEST
- [ ] light mode
- [ ] dark mode
- [ ] mobile
- [ ] PWA
- [ ] build
- [ ] TypeScript
- [ ] runtime error scan
- [ ] release candidate approved

## Stage 5 readiness
- [ ] CampusMode domain obtained
- [ ] DNS access available
- [ ] Resend domain verification possible
- [ ] Supabase redirect migration plan ready
- [ ] old domain retained
- [ ] SEO baseline recorded
- [ ] final migration window selected
