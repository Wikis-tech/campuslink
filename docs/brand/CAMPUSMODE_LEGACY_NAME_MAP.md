# CampusMode Legacy Name Map

This file prevents accidental destructive renaming during the CampusMode migration.

## Brand history

### CampusLink
Original working/product name.
Still appears in historical infrastructure, repository naming, database identifiers, domain/email infrastructure and legacy records.

### Kampivo
Intermediate visible rebrand.
Used across the modern application before CampusMode was selected.

### CampusMode
Current approved final product identity.

Canonical public spelling:
**CampusMode**

Tagline:
**Campus life, made easy.**

## Rename now - visible identity

Replace visible user-facing uses of CampusLink/Kampivo with CampusMode in:
- landing/public copy
- Student UI
- Vendor UI
- Admin UI
- login/register/onboarding
- PWA
- email display copy/templates
- legal/compliance pages
- page titles/descriptions
- Open Graph/social previews
- notifications
- help/support copy
- UI screenshots/reference docs

## Preserve unless separately migrated

Do not rename solely for appearance:
- repository `Wikis-tech/campuslink`
- Vercel project internal name/ID
- Supabase project internal name/ref
- historical migrations
- table names
- schema names
- database field names
- existing Paystack metadata keys
- stable idempotency keys
- audit-log historical values
- legacy cache identifiers if a rename risks invalidating clients
- internal function/component names where there is no user impact

## Stage 5 - domain-dependent

Change only after the final CampusMode domain is obtained:
- NEXT_PUBLIC_APP_URL
- Supabase Site URL
- allowed auth redirect URLs
- confirmation links
- recovery links
- Admin invitation URLs
- email CTA URLs
- Resend sender domain
- support/privacy email domains
- canonical URLs
- sitemap host
- robots host references
- OG absolute URLs
- Paystack domain/callback settings where required
- old-domain redirects

## Brand architecture mapping

- Kampivo -> CampusMode
- Kampivo Business -> CampusMode Business
- Kampivo Pro -> CampusMode Pro
- Kampivo Verified -> CampusMode Verified
- Kampivo Safety -> CampusMode Safety
- Kampivo Alerts -> CampusMode Alerts
- Kampivo Discover -> CampusMode Discover
- Kampivo for Schools -> CampusMode for Schools

## Technical review rule

Every old-name occurrence found in source code must be classified before editing:

- PUBLIC_VISIBLE
- PROVIDER_VISIBLE
- SEO_VISIBLE
- DOMAIN_DEPENDENT
- INTERNAL_SAFE_TO_KEEP
- MIGRATION_SENSITIVE
- HISTORICAL_RECORD

Never use blind global replacement across the repository.
