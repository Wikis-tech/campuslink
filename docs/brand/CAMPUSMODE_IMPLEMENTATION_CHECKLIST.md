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

**Stage 2 implementation status: COMPLETE.**

Automated validation covers production build, public/auth/legal/PWA route responses, brand assets, manifest, and runtime-error monitoring. Authenticated Student/Vendor/Admin visual acceptance remains part of the human testing pass and Stage 4 full regression.

## Stage 3 - SEO/comms
- [ ] metadata title
- [ ] metadata description
- [ ] applicationName
- [ ] Open Graph
- [ ] Twitter/X cards
- [ ] schema.org names
- [ ] sitemap content
- [ ] robots content
- [ ] current-domain canonicals preserved
- [ ] verification email copy
- [ ] Admin alert template
- [ ] invitation copy
- [ ] password recovery copy
- [ ] legal pages
- [ ] support/privacy wording

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
