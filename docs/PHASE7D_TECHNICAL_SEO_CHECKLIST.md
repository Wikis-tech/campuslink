# Phase 7D — Technical SEO acceptance

Phase 7D makes Campus Link crawlable where it is intentionally public and non-indexable where the application is private.

## Public/indexable
- `/` — canonical homepage.
- `/share/vendor/[slug]` — only when the Vendor is identity-approved, campus-approved and marketplace-safe.

## Deliberately non-indexable
- Student workspace.
- Vendor workspace.
- Dashboard/PWA entry.
- Login and registration.
- Auth callbacks, onboarding and verification.
- Offline/PWA helper routes.
- Admin routes (protected by headers and authorization; the hidden Admin login route is intentionally not advertised in `robots.txt`).
- API/payment endpoints.

## Acceptance checks
1. Open `/robots.txt` and confirm a sitemap is declared.
2. Confirm `robots.txt` does not reveal the private Admin login URL.
3. Open `/sitemap.xml` and confirm the homepage is present.
4. Confirm only safe approved Vendors appear as `/share/vendor/... `.
5. Put a test Vendor Under Review and confirm it is excluded from the generated sitemap.
6. Restore that Vendor to Active and confirm it returns.
7. View source on the homepage and confirm `WebSite` and `Organization` JSON-LD exists.
8. View source on a public Vendor share page and confirm Vendor JSON-LD exists.
9. Confirm a Vendor with zero reviews does not publish fake `aggregateRating` structured data.
10. Confirm `/login`, `/register`, `/app`, `/student/*`, `/vendor-v2/*`, and Admin pages return no-index signals.
11. Confirm the homepage canonical resolves to the production Campus Link origin.
12. Confirm Vendor share pages self-canonicalize.
13. Confirm social Open Graph images still render for the homepage and Vendor share pages.
14. Confirm a 404/unavailable Vendor is not indexable.
15. Submit `/sitemap.xml` in Google Search Console after production promotion.

Phase 7D does not guarantee ranking. It gives search engines correct technical signals, canonical URLs, structured data and a privacy-safe crawl surface.
