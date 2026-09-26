# Phase 7E — Privacy-Aware SEO

Status: implemented on the `campuslink-ui-v3-experiment` branch.

## Goal

Allow Google and social-preview crawlers to discover only information that Campus Link intentionally makes public, while keeping authenticated, administrative, billing, verification and moderation information out of search indexes and public previews.

## Controls implemented

- Canonical host is normalized to `https://campuslink.name.ng`.
- `www.campuslink.name.ng` permanently redirects to the canonical non-www host.
- Sitemap uses the same canonical origin.
- Vendor share pages require all of the following before they are public:
  - approved identity verification,
  - an approved campus relationship,
  - an eligible marketplace safety state.
- Under-review, actively suspended, unapproved or campus-unapproved vendors return unavailable/not-found instead of a public SEO page.
- Public vendor previews do not expose exact free-text location fields.
- Public descriptions/listing names are sanitized to remove accidental email addresses, phone numbers and pasted URLs.
- Vendor contact actions, private reviews, verification evidence, billing data, internal safety signals and Admin notes remain behind authenticated application routes.
- Public vendor pages explicitly allow indexing only after the safety/approval gate succeeds.
- Sensitive API routes emit `X-Robots-Tag: noindex, nofollow, noarchive, nosnippet`.
- Student, Vendor, Admin, auth, onboarding and verification areas remain excluded from indexing and use no-store headers where appropriate.
- Public Vendor share pages use `no-store` so a newly suspended/under-review Vendor is not left publicly visible from an old CDN page cache.

## Acceptance checks

1. `https://www.campuslink.name.ng/... ` redirects to the same path on `https://campuslink.name.ng/...`.
2. `/robots.txt` references the canonical sitemap.
3. `/sitemap.xml` contains the homepage and only currently approved/safe/campus-approved public Vendor share URLs.
4. A valid Vendor share page has canonical metadata pointing to the non-www URL.
5. A Vendor with no approved campus relationship is not publicly shareable/indexable.
6. A Vendor moved to Under Review or active Suspension stops resolving publicly.
7. A public Vendor preview does not expose raw phone/email/URL content embedded in free-text descriptions.
8. Student/Vendor/Admin/auth routes continue returning noindex headers and remain absent from the sitemap.
9. API responses are not eligible for search indexing.
10. Authenticated marketplace UX, PWA routes, payments and Admin access continue to behave exactly as before.

## Privacy boundary

Campus Link public SEO is intentionally narrower than the authenticated marketplace. Search engines may see approved public business identity and safe listing previews. They must not receive private account, verification, payment, investigation, moderation or direct-contact data.
