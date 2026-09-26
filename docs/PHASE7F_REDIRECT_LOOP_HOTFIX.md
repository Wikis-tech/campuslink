# Phase 7F redirect-loop hotfix

After the Phase 7F production promotion, browsers returned ERR_TOO_MANY_REDIRECTS on Student and Admin routes.

Root cause: the production domain layer redirects the apex host to www, while Next.js had a host redirect from www back to the apex. That produced an infinite loop before application routing ran.

Fix:
- Remove the Next.js host redirect.
- Let Vercel be the only host-canonicalization layer.
- Use https://www.campuslink.name.ng as the SEO canonical origin so metadata, sitemap and Search Console align with the actual production host.
- Keep private-route noindex, PWA, CSP/HSTS and authentication protections unchanged.

Regression rule: never configure opposite host redirects in both the platform domain layer and application code.
