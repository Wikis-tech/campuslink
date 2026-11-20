# CampusMode Asset Manifest

Status: APPROVED STAGE 1 MANIFEST

## Approved master artwork supplied by the brand owner
The approved CampusMode logo family contains:
- Full-colour CampusMode lockup: blue Campus + green Mode
- Full-colour lockup on transparent background
- White/reversed lockup
- Black monochrome lockup
- CampusMode switch symbol
- Brand presentation/mockup sheet

The switch is always shown in the ON position: circular control on the right.

## Canonical visual rules
- Primary lockup: Campus Blue + Mode Green
- Reversed lockup: white on dark backgrounds
- Monochrome lockup: black or white only
- Do not recolour the switch randomly
- Do not separate Campus and Mode in the canonical wordmark
- Do not recreate the master wordmark with substitute fonts
- Do not add gradients, outlines, shadows or effects to the master logo

## Repository asset paths
Stage 1 provides scalable technical brand primitives in:
- /public/brand/campusmode/symbol.svg
- /public/brand/campusmode/app-icon.svg
- /public/brand/campusmode/favicon.svg
- /public/brand/campusmode/og-template.svg

The current runtime still uses Kampivo assets until Stage 2. This is intentional.

## PWA/export rules
The CampusMode symbol/app-icon source is scalable.
Stage 2 should render/export:
- 180x180 Apple touch
- 192x192 PWA
- 512x512 PWA/maskable
from the approved CampusMode app-icon source.

## Full logo runtime asset
The approved full lockup supplied by the owner must be uploaded/committed as a production asset during Stage 2 and connected through the existing dynamic brand routes/Admin Branding system.

Do not replace the approved logo artwork with a newly typed approximation.

## Social/OG
The repository OG template is a layout source, not a final campaign image.
Final Open Graph output is generated in Stage 3 after CampusMode metadata is implemented.

## Legacy assets
Existing files such as:
- kampivo-logo.svg
- kampivo-app-icon.svg
- legacy CampusLink assets
remain until the visible rebrand is implemented and tested.

They must not be deleted during Stage 1.
