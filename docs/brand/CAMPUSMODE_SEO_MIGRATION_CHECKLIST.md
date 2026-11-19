# CampusMode SEO and Domain Migration Checklist

## Before the new domain exists
- Keep canonical URLs on the current live domain.
- Rebrand titles/descriptions to CampusMode.
- Do not point sitemap/canonicals to an unavailable domain.
- Capture current indexed URLs and important landing pages.
- Preserve existing route paths wherever possible.

## When the new domain is obtained
1. Add/verify domain in Vercel.
2. Test HTTPS.
3. Update application base URL.
4. Update canonical host.
5. Update Open Graph absolute URLs.
6. Generate sitemap on new host.
7. Verify robots.
8. Add new Search Console property.
9. Submit new sitemap.
10. Redirect old URLs 1:1 using permanent redirects.
11. Avoid redirect chains.
12. Keep old domain active.
13. Verify auth/recovery/invite links.
14. Verify email links.
15. Check PWA manifest/start URL.
16. Check social previews.
17. Crawl important routes after launch.
18. Monitor indexation and 404s.

## Route preservation
Preferred:
old-domain/student -> new-domain/student
old-domain/vendor-v2 -> new-domain/vendor-v2
old-domain/privacy -> new-domain/privacy

Avoid:
old URL -> homepage -> new page

## SEO rebrand wording
Brand title should use CampusMode consistently.

Recommended descriptor:
CampusMode - Campus life, made easy.

Marketplace description:
Find trusted products, services and businesses around your campus.

Do not claim "official university platform" without an actual university agreement.
