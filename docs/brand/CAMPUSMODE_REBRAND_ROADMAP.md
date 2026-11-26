# CampusMode Rebrand Roadmap

Status: ACTIVE PLANNING DOCUMENT
Current application branch: `campuslink-ui-v3-experiment`
Current public product name in code: CampusMode (Stages 1–2 implemented; Stage 3 code implemented with provider-managed Auth templates pending acceptance)
Final approved product name: CampusMode
Tagline: Campus life, made easy.
Brand book: CampusMode Brand Book 2026

## Purpose

This document defines the controlled migration from CampusLink/Kampivo to CampusMode.

The migration is deliberately split into five stages so that brand changes do not break authentication, Supabase, Vercel, Paystack, PWA, notifications, SEO, or current Student/Vendor/Admin workflows.

Stages 1-4 complete the product and technical rebrand while the existing domain remains active.
Stage 5 performs the final domain migration, SEO cutover, acceptance testing, security review and launch.

## Core rule

Do not rename invisible technical identifiers simply to make them look branded.

Examples that may safely remain until there is a real engineering reason to change them:
- Supabase project name/reference
- database table/schema names
- historical migration names
- GitHub repository name
- Vercel project ID/name
- database columns
- internal idempotency keys
- Paystack metadata keys already used in production/test flows
- cache keys that are migration-sensitive
- legacy audit-log values

Visible product identity, public metadata, communications, assets and SEO must become CampusMode.

---

# Stage 1 - Brand Foundation, Assets and Source-of-Truth

Goal: establish CampusMode as the approved brand inside the repository before changing the product UI.

### Step 1.1 - Lock naming
Canonical spelling:
- CampusMode

Do not use:
- Campus Mode as the main brand lockup
- Campusmode
- campusmode in user-facing headings
- Kampivo
- CampusLink, except in historical/legal migration notes

### Step 1.2 - Lock messaging
Primary tagline:
- Campus life, made easy.

Primary product descriptor:
- Find trusted products, services and businesses around your campus.

Student value:
- Find what you need around campus.

Vendor value:
- Put your business where Students can find it.

### Step 1.3 - Lock brand architecture
- CampusMode
- CampusMode Business
- CampusMode Pro
- CampusMode Verified
- CampusMode Safety
- CampusMode Discover
- CampusMode for Schools
- CampusMode Alerts

### Step 1.4 - Add approved assets
Create a controlled brand directory for:
- primary full-colour logo
- reversed/white logo
- black logo
- symbol/icon
- favicon
- PWA 192
- PWA 512
- Apple touch icon
- OG/social image template

Never use random exports from Downloads in production.

### Step 1.5 - Define brand tokens
Primary:
- Campus Blue: #154597
- Mode Green: #20A953
- Midnight: #08121F
- Ink: #172033
- Cloud: #F1F3F6
- Warm Paper: #F0EDE8

Typography:
- CampusMode wordmark: Neue Machina
- Tagline: Open Sans
- Product UI/body: use approved interface system; do not recreate the logo from live text

### Step 1.6 - Create repository documentation
Required:
- this roadmap
- legacy name map
- brand implementation checklist
- SEO migration checklist
- release acceptance checklist

### Stage 1 exit criteria
- Brand name, tagline and architecture documented
- Approved logo assets identified
- Colours/type documented
- No code rename started yet
- All migration rules understood

---

# Stage 2 - Visible Product Rebrand

**Status: IMPLEMENTED — automated QA passed; human authenticated acceptance checklist supplied.**

Goal: replace every user-facing Kampivo/CampusLink identity with CampusMode while leaving infrastructure unchanged.

### Step 2.1 - Public website
Update:
- landing page
- header
- footer
- CTA copy
- trust/compliance wording
- Privacy/Terms/Compliance pages
- contact/help wording
- error pages
- loading states
- empty states

### Step 2.2 - Authentication
Update:
- login
- registration
- Student/Vendor account selection
- email verification screens
- password recovery UI
- Admin login presentation
- onboarding presentation

Do not change auth route structure in this stage.

### Step 2.3 - Student application
Update:
- Student header/logo
- Overview
- Discover
- Saved
- Profile
- Safety
- Vendor storefront
- Product/Service detail
- notification wording
- PWA navigation labels where branded

Preserve all current campus isolation, verification and marketplace rules.

### Step 2.4 - Vendor application
Update:
- Vendor workspace brand
- Vendor Overview
- Listings
- Products
- Services
- Portfolio
- Business Profile
- Availability
- Reviews
- Analytics
- Growth
- Billing
- notification wording

Rename visible "Kampivo Pro" to "CampusMode Pro".
Rename visible workspace identity to "CampusMode Business" where appropriate.

### Step 2.5 - Admin application
Update:
- Admin sidebar/header
- Admin login
- communications
- approval queues
- Trust & Safety
- audit presentation
- School Admin surfaces
- system banners

Do not rename Admin roles, database role values or permission identifiers.

### Step 2.6 - PWA/app identity
Update:
- manifest display name
- short name
- launch splash
- install prompt
- app icons
- Apple touch icon
- theme metadata

### Step 2.7 - Notifications and email copy
Update visible sender/display identity:
- CampusMode Alerts
- CampusMode update
- Open CampusMode

Existing sender domain may remain on the current domain until Stage 5.

### Step 2.8 - Brand scan
Search source for visible:
- Kampivo
- CampusLink
- Campus Link
- old logos
- legacy orange-only identity
- old tagline

Classify every result:
- user-facing: must change
- internal/historical: may remain

### Stage 2 exit criteria
- No unintended visible Kampivo/CampusLink text
- Student/Vendor/Admin visually CampusMode
- PWA visually CampusMode
- Existing workflows still pass
- Existing domain still active

---

# Stage 3 - Product Metadata, SEO and Communication Rebrand

Goal: make CampusMode the discoverable public identity without changing the domain yet.

### Step 3.1 - Metadata
Update:
- page titles
- descriptions
- applicationName
- Open Graph title/description
- Twitter/X cards
- schema.org organization/product names
- social preview assets

### Step 3.2 - Canonical strategy before new domain
Keep canonical URLs on the current live domain until Stage 5.

Do NOT publish future CampusMode-domain canonicals before the domain exists and is verified.

### Step 3.3 - Sitemap/robots
Keep current host.
Update visible naming and page metadata only.

### Step 3.4 - Structured data
Update organization/product names to CampusMode.
Do not claim:
- CAC registration
- NDPC registration
- Startup Label
- university endorsement
unless actually obtained.

### Step 3.5 - Email/provider templates
Update all provider-side templates:
- verification
- Admin communications
- alerts
- invitations
- recovery/reset copy

Sender address can still use the existing domain until Stage 5, but display name/body must be CampusMode.

### Step 3.6 - Social/trust copy
Prepare:
- CampusMode social bios
- launch descriptor
- CampusMode Business descriptor
- trust language
- support/privacy naming

### Step 3.7 - SEO baseline capture
Before domain migration record:
- indexed pages
- current titles/descriptions
- sitemap URLs
- important current backlinks
- Search Console state if available
- top landing pages
- crawl status

This becomes the comparison baseline for Stage 5.

### Stage 3 exit criteria
- Search metadata says CampusMode
- provider templates say CampusMode
- canonical host remains correct for current domain
- sitemap/robots valid
- no premature new-domain references

---

# Stage 4 - Technical Rebrand Hardening and Full Regression

Goal: make the codebase, documentation and release candidate ready for a domain migration without breaking hidden implementation contracts.

### Step 4.1 - Environment/config audit
Inventory every brand-sensitive env/config value:
- NEXT_PUBLIC_APP_URL
- email sender/display names
- Supabase redirect URLs
- Paystack callback URLs
- webhook URLs
- PWA start_url/scope
- canonical base URL
- Admin invitation URL
- support/privacy addresses

Do not change domain-dependent values yet.

### Step 4.2 - Internal identifier classification
For each CampusLink/Kampivo identifier decide:
A. visible - rename now
B. internal but harmless - leave
C. domain-dependent - Stage 5
D. migration-sensitive - preserve indefinitely unless separately migrated

### Step 4.3 - Documentation update
Update GitHub documentation so CampusMode is now the current product name.

Keep a historical section documenting:
CampusLink -> Kampivo -> CampusMode

Do not rewrite migration history as if CampusMode always existed.

### Step 4.4 - Functional regression
Run complete Student:
register -> verify email -> onboarding -> Admin approval -> dashboard -> discover -> Product/Service -> save -> contact -> review -> report -> notification -> logout/login

Run complete Vendor:
register -> verify email -> onboarding -> identity/campus approval -> dashboard -> Products -> Services -> Portfolio -> Profile -> Availability -> Reviews -> Analytics -> Growth -> Billing

Run complete Admin:
Admin login -> MFA -> school scope -> Student/Vendor approvals -> categories -> Trust & Safety -> communications -> notifications -> audit -> invitations

### Step 4.5 - Security regression
Verify:
- RLS/BOLA
- Student cannot call Vendor/Admin actions
- Vendor cannot call Admin actions
- School Admin cannot cross schools
- privileged RPC authorization
- verification-document privacy
- upload validation
- notification ownership
- session/logout
- Admin MFA
- Paystack TEST security

### Step 4.6 - UI regression
Test:
- light
- dark
- desktop
- tablet
- mobile
- PWA
- Chrome
- Safari/iOS where available
- long names
- empty states
- low-data analytics
- image fallbacks

### Step 4.7 - Build and runtime
Require:
- successful Next.js build
- TypeScript pass
- no new runtime errors
- no broken routes
- no hydration regressions
- PWA manifest/icons valid

### Stage 4 exit criteria
- CampusMode rebrand complete on current domain
- Student/Vendor/Admin workflows pass
- security checks pass
- documentation updated
- zero known launch-blocking regressions
- release candidate ready for Stage 5

---

# Stage 5 - New Domain, Final SEO Cutover, Final Testing and Launch

This stage begins only when the final CampusMode domain is obtained.

Goal: migrate external infrastructure safely and launch the tested CampusMode release.

### Step 5.1 - Add new domain to Vercel
- configure DNS
- verify domain
- keep old CampusLink domain active

### Step 5.2 - Update application base URLs
Change:
- NEXT_PUBLIC_APP_URL
- canonical base
- OG URLs
- sitemap host
- robots references
- PWA URLs where applicable

### Step 5.3 - Supabase Auth migration
Update:
- Site URL
- allowed redirect URLs
- email confirmation
- password recovery
- OAuth callbacks if added
- Admin invitation destinations

Keep old domain redirects allowed during transition.

### Step 5.4 - Email domain migration
Verify new CampusMode sender domain in Resend.
Configure SPF/DKIM/return path as required.

Then move:
- CampusMode Alerts <notifications@new-domain>
- support@new-domain
- privacy@new-domain

### Step 5.5 - Paystack URL migration
Update domain-dependent:
- callback URLs
- webhook/return URLs if domain-bound
- business website/profile

Do not rename stable metadata/idempotency identifiers unnecessarily.

### Step 5.6 - SEO migration
- switch canonical URLs
- generate new sitemap
- verify robots
- add new Search Console property
- submit sitemap
- map old URLs 1:1 to new URLs
- use permanent redirects from old domain
- avoid redirect chains
- preserve route paths wherever possible
- keep old domain active for transition

### Step 5.7 - Final acceptance
Repeat full Stage 4 regression on the new domain.

Must test:
- fresh Student signup
- fresh Vendor signup
- email verification
- password reset
- Admin invite
- MFA
- Student approval
- Vendor identity/campus approval
- marketplace discovery
- Product/Service images
- notifications
- PWA install
- logout
- Paystack TEST
- one controlled live billing test only after business/payment readiness

### Step 5.8 - Launch decision
GO only if:
- no blocker bugs
- no auth redirect failures
- emails arrive
- old links redirect correctly
- SEO metadata/canonicals correct
- security gate closed
- legal/compliance pages current
- domain/email infrastructure verified

### Step 5.9 - Post-launch monitoring
Monitor:
- Vercel runtime errors
- Supabase Auth failures
- Resend delivery/bounces
- Paystack webhook/payment reconciliation
- Search Console crawl/indexing
- broken old-domain links
- user-reported onboarding failures

---

# Rebrand non-negotiables

1. Never trade security for visual consistency.
2. Never change database schema names merely for branding.
3. Never make Paystack Pro status look like trust/verification.
4. Never claim government/university status before it exists.
5. Never switch canonicals to a domain that is not live.
6. Never turn off the old domain on launch day.
7. Never perform domain migration and major feature development in the same release.
8. Preserve historical migration/audit records.
9. Every stage must build and be tested before the next stage starts.
10. Stage 5 is a release migration, not a redesign stage.
