# Phase 7C — PWA UX, Mobile & Performance

## Decision

Campus Link uses a bottom-navigation-first mobile pattern for the Student and Vendor applications.

Compared options:

1. **Hamburger-only navigation** — compact but hides high-frequency actions and makes marketplace navigation slower.
2. **Top tab navigation** — familiar on desktop but competes with search/header space on smaller phones.
3. **Bottom navigation with horizontal overflow** — keeps primary actions within thumb reach, preserves every desktop workspace destination, and scales when Vendor tools exceed five items.

Campus Link uses option 3. Student navigation keeps its core destinations visible and adds an Account sheet. Vendor navigation exposes every unique workspace destination in a horizontally scrollable rail plus a More sheet.

## Standalone PWA boundary

The installed PWA is intentionally a Student/Vendor application.

- Manifest start URL remains `/app?source=pwa`.
- `/app` sends signed-out users to Student/Vendor login and signed-in users to the correct Student or Vendor workspace.
- A standalone-mode route controller redirects the installed app away from:
  - the public landing page;
  - marketing/public pages that are outside the authenticated app;
  - all Admin and Admin-login route families.
- The normal website is unchanged and can still expose the public landing site.
- Admin remains a browser-only control plane protected by the existing MFA/RBAC/RLS security architecture.

## Student mobile UX

Bottom rail includes:

- Home
- Discover
- Saved
- Safety
- Verification
- Account

The rail scrolls horizontally if needed instead of wrapping into a second row.

Account opens a mobile sheet containing profile/verification, saved vendors, Safety Centre, appearance controls and sign out.

## Vendor mobile UX

The duplicate Profile/Business Profile destination was removed.

The bottom rail now includes every unique Vendor workspace:

- Overview
- Products
- Services
- Portfolio
- Business profile
- Availability
- Reviews
- Analytics
- Growth
- Billing

The rail is horizontally scrollable and thumb-friendly. A More sheet exposes Business Profile, appearance controls and sign out.

## Performance work

- Authenticated below-the-fold sections use `content-visibility: auto` where supported.
- Intrinsic placeholder sizing reduces rendering work without changing page data or layout.
- Mobile form fields use 16px font sizing to prevent unwanted iOS zoom.
- Bottom rails use touch manipulation, momentum scrolling, scroll containment and hidden scrollbars.
- Safe-area insets are respected on notched iPhones and Android devices.
- Standalone pages use `100dvh`/safe-area-aware spacing.
- Existing Phase 7B service-worker policy remains unchanged: no caching of authenticated HTML, Admin, payment, auth or private API content.

## Regression rules

Phase 7C must not change:

- Supabase RLS or role boundaries.
- Paystack flows.
- Phase 5G trust/safety behavior.
- Phase 5H search/ranking.
- Desktop navigation.
- public website availability in normal browsers.
- secure service-worker caching rules.

## Acceptance checklist

1. Install Campus Link and launch it: it opens through `/app?source=pwa`, never the landing page.
2. While installed, attempt to navigate to `/`: the app returns to the PWA app entry.
3. While installed, attempt an Admin/Admin-login URL: the app returns to the Student/Vendor app entry and does not become an Admin shell.
4. Open the same URLs in a normal browser: the public website continues to behave normally.
5. Student mobile bottom rail shows Home, Discover, Saved, Safety and Verification; it does not wrap.
6. Student rail can scroll horizontally on narrow devices.
7. Student Account opens and offers profile, saved, safety, theme and sign out.
8. Vendor bottom rail contains all unique desktop workspace destinations.
9. Vendor rail scrolls smoothly on 360–430px phones.
10. Vendor More opens and sign out works.
11. Bottom navigation does not cover final page content.
12. iPhone safe area does not cover controls.
13. Inputs do not force iOS zoom.
14. Light and Dark themes remain readable.
15. 360px, 390px, 414px, tablet and desktop layouts remain usable.
16. Student/Vendor data, reviews, reports, billing and search still behave exactly as before.
17. Offline behavior remains Phase 7B-safe: protected pages require a network connection and private content is not cached.
