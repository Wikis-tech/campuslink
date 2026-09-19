# Phase 7A — PWA Foundation

Branch: `campuslink-ui-v3-experiment`

## Implemented

- Next.js App Router web app manifest at `/manifest.webmanifest`
- Campus Link standalone app identity
- Campus navy/green PWA icons
- Maskable icon for Android adaptive icon surfaces
- Generated Apple home-screen icon
- iOS web-app metadata
- Light/dark browser theme colors
- `viewport-fit=cover` for notched mobile devices
- Android/Chromium install prompt using `beforeinstallprompt`
- iPhone/iPad Add to Home Screen guidance
- Install prompt suppressed for Admin routes
- Install prompt suppressed when already running in standalone mode
- Seven-day dismissal memory to avoid nagging users
- Responsive/light/dark/reduced-motion install UI

## Security boundary

Phase 7A deliberately does **not** add a service worker or offline cache. That belongs to Phase 7B, where caching rules will be security-first.

Admin and authenticated private content must never be cached offline.

## Acceptance checklist

### Manifest
- [ ] Open `/manifest.webmanifest` and confirm valid JSON.
- [ ] Name is `Campus Link`.
- [ ] Display is `standalone`.
- [ ] Theme color is Campus Link navy.
- [ ] Both normal and maskable icons load.

### Android / Chromium
- [ ] Chrome recognizes the site as installable when platform criteria are met.
- [ ] Campus Link install prompt appears without covering mobile bottom navigation.
- [ ] Install opens a native browser install dialog.
- [ ] Installed app launches without browser chrome.
- [ ] Home-screen icon uses Campus Link branding.
- [ ] App title displays as CampusLink/Campus Link appropriately.
- [ ] Status/theme surface uses Campus Link navy.

### iPhone / iPad
- [ ] Safari shows the Campus Link Add to Home Screen helper.
- [ ] Helper tells the user to use Share → Add to Home Screen.
- [ ] Added app uses the generated Apple icon.
- [ ] Standalone app title is Campus Link.
- [ ] Status bar uses the configured web-app style.
- [ ] Prompt disappears when app is already running standalone.

### General
- [ ] Install UI does not appear on Admin routes.
- [ ] Dismissing the install UI does not show it repeatedly for seven days.
- [ ] Light mode is readable.
- [ ] Dark mode is readable.
- [ ] Reduced-motion preference removes prompt animation.
- [ ] Existing Student/Vendor/Admin navigation remains unchanged.

## Phase 7B gate

Before adding offline behavior:
- define cache allowlist, not a broad cache-all rule
- never cache `/admin*`
- never cache `/admin-login-campus*`
- never cache authenticated API responses
- never cache Paystack routes/callbacks
- never cache verification documents
- never cache Supabase auth tokens
- authenticated HTML should prefer network/no-store behavior
- sign-out must clear any sensitive client-side state/cache
