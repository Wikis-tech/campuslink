# CampusMode Release Acceptance Checklist

This checklist is the formal GO/NO-GO gate for the CampusMode release candidate.

## Brand
- [ ] No unintended visible CampusLink branding
- [ ] No unintended visible Kampivo branding
- [ ] CampusMode logo renders correctly
- [ ] Favicon/PWA icons render correctly
- [ ] Light/dark brand contrast passes review

## Student
- [ ] Fresh registration
- [ ] Email confirmation
- [ ] Login
- [ ] Onboarding
- [ ] Verification submission
- [ ] Admin approval
- [ ] Verified Profile state
- [ ] Discover/search
- [ ] Product/Service detail
- [ ] Save/unsave
- [ ] Contact tracking
- [ ] Review
- [ ] Report/Safety
- [ ] Notifications
- [ ] Logout/re-login

## Vendor
- [ ] Fresh registration
- [ ] Email confirmation
- [ ] Onboarding
- [ ] Identity review
- [ ] Campus approval
- [ ] Overview
- [ ] Products
- [ ] Services
- [ ] Portfolio
- [ ] Business Profile
- [ ] Availability
- [ ] Reviews
- [ ] Analytics
- [ ] Growth
- [ ] Billing
- [ ] Storefront visibility
- [ ] Product images
- [ ] Notifications
- [ ] Logout/re-login

## Admin
- [ ] Dedicated Admin login
- [ ] MFA
- [ ] Super Admin
- [ ] School Admin
- [ ] Cross-school isolation
- [ ] Students
- [ ] Vendors
- [ ] Approvals
- [ ] Categories
- [ ] Trust & Safety
- [ ] Communications
- [ ] Notifications
- [ ] Audit
- [ ] Invitations

## Security
- [ ] RLS enabled on exposed tables
- [ ] Student cannot perform Vendor/Admin actions
- [ ] Vendor cannot perform Admin actions
- [ ] School Admin cannot cross institutions
- [ ] privileged RPC execution tests passed
- [ ] verification documents remain private
- [ ] upload MIME/signature validation
- [ ] notification ownership isolation
- [ ] session/logout checks
- [ ] leaked-password protection decision documented
- [ ] Paystack webhook/signature/idempotency tests

## UX
- [ ] desktop
- [ ] tablet
- [ ] mobile
- [ ] PWA
- [ ] light
- [ ] dark
- [ ] empty states
- [ ] long content
- [ ] image fallbacks
- [ ] accessibility/focus/touch targets

## Technical
- [ ] Next.js build passes
- [ ] TypeScript passes
- [ ] no release-blocking runtime errors
- [ ] manifest valid
- [ ] icons valid
- [ ] sitemap valid
- [ ] robots valid

## Stage 5
- [ ] new domain verified
- [ ] Supabase Auth URLs migrated
- [ ] Resend sender domain verified
- [ ] canonical URLs migrated
- [ ] old-domain redirects verified
- [ ] new sitemap submitted
- [ ] fresh signup tested on new domain
- [ ] email/recovery/invite links tested
- [ ] final Paystack callback/webhook tested
- [ ] post-launch monitoring active

## Decision
- [ ] GO
- [ ] NO-GO

A release is GO only when no launch-blocking item remains unresolved.
