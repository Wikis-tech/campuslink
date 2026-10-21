# Kampivo Vendor Workspace — Clean UI/UX Direction

Status: Proposed visual source of truth for the Vendor redesign.
Branch: campuslink-ui-v3-experiment
Scope: UI/UX only. Preserve current routes, data model, RLS, approvals, billing, analytics, trust/safety and business logic.

## 1. Design Goal

The Vendor area should feel like a calm campus-business command centre, not a generic SaaS admin dashboard.

A Vendor should understand within five seconds:
1. Is my business live?
2. What should I do next?
3. How are Students responding?
4. Where do I manage what Students see?

The interface should reuse the clean visual language already approved for the Student marketplace:
- light neutral background
- navy primary
- orange Kampivo action accent
- white surfaces
- rounded but restrained cards
- strong typography
- clear empty states
- minimal decoration
- dark mode with deep slate/navy, never pure black

## 2. Rules Borrowed From the Reviewed UI/UX References

- Accessibility first: readable contrast, visible focus states, descriptive labels.
- Touch targets: minimum 44×44px for buttons and mobile controls.
- Avoid body text below 12px.
- Mobile-first layout; no horizontal page overflow.
- Use semantic tokens rather than page-specific random colours.
- Motion only when it communicates state; respect reduced motion.
- Forms need visible labels, inline helper text and errors close to fields.
- Mobile primary navigation should remain at 4–5 destinations.
- Preserve layout space for media to avoid cumulative layout shift.
- Use SVG/Lucide icons, not emoji as interface icons.
- Avoid a wall of cards with equal visual priority.

## 3. Vendor Navigation Architecture

### Desktop sidebar

PRIMARY
- Overview
- Listings
  - Products
  - Services
  - Portfolio
- Orders/Leads: not shown because Kampivo does not process Student→Vendor transactions.
- Reviews

BUSINESS
- Business profile
- Availability

INSIGHTS
- Analytics
- Growth

ACCOUNT
- Billing
- Appearance
- Sign out

The existing routes remain unchanged. Navigation may visually group routes without changing URLs.

### Mobile bottom navigation

Keep only:
- Home
- Listings
- Reviews
- Analytics
- More

"More" opens a bottom sheet containing:
- Business profile
- Availability
- Growth
- Billing
- Appearance
- Sign out

Do not horizontally scroll ten navigation items.

## 4. Vendor Overview — Approved Layout Direction

### A. Top bar

Left:
- Kampivo logo
- compact page label: Vendor workspace

Right:
- notification bell
- theme toggle
- avatar/account menu

On desktop the sidebar remains available.
On mobile the top bar becomes the global header.

### B. Welcome row

Eyebrow:
YOUR BUSINESS

Heading:
Good afternoon, Wisdom.

Supporting text:
Way5Atelier is live at University of Africa Toru-Orua.

Right side:
[ View storefront ]  [ + Add listing ]

Do not use a huge hero card.

### C. Marketplace status strip

One compact status component, not four equally large cards.

Example:
● Live on campus   Identity ✓   Campus ✓   Free/Pro

If action is required:
● Setup incomplete — 2 actions needed
[Continue setup]

### D. Today's action

One prominent card only.

Title:
Your next best move

Examples:
- Add a cover image
- Add pricing to 2 products
- Add your first portfolio item
- Nothing urgent today

Primary CTA goes to the exact relevant page.

### E. Performance

Four compact KPIs:
- Profile views
- Student contacts
- Saves
- Reviews

Default range: Last 7 days

No vanity metrics without context.
Use a small trend line only when there is enough historical data.

### F. Storefront

Section:
Your storefront

Three concise rows/cards:
Products   2 / 5   Manage
Services   2 / 5   Manage
Portfolio  2 / 6   Manage

Include thumbnails where useful.
Avoid repeating long explanations.

### G. Business health

Move this below core operating information.

Present:
85% complete
"Strong storefront"

Then show maximum 3 recommended improvements.

Always keep:
"Business health is not a trust or verification score."

### H. Student response

Use actual lifetime signals:
- Unique Students who contacted
- Saves
- Rating
- Published reviews

Never use raw repeated contact-event count as unique demand.

### I. Trust & eligibility

A small final section:
Identity approved ✓
Campus approved ✓
Marketplace good standing ✓

Paid plan must never be visually mixed with verification.

## 5. Listings Hub

Create a visual landing state when Vendor clicks "Listings".

This does not require a new backend route immediately; the mobile/desktop navigation can expose Products, Services and Portfolio as a grouped menu.

Visual pattern:
Products     2 active     Manage
Services     2 active     Manage
Portfolio    2 items      Manage

Each route keeps its current CRUD logic.

## 6. Products Page

Header:
Products
"Show Students what you sell."

Top-right:
+ Add product

Main layout:
- responsive product grid
- image first
- name
- clear price
- Live/Paused badge
- Edit/Pause/Remove actions

Add-product form:
- desktop: right drawer or compact side panel
- mobile: full-screen sheet/page section
- visible labels
- image preview before submit
- inline validation
- plan-limit state shown near Add button

Do not place a giant form next to a giant grid on small screens.

## 7. Services Page

Same visual system as Products.

Card:
Service name
Starting price / Ask Vendor
availability indicator if relevant
Live/Paused
Manage

Keep Products and Services visually related so Vendors learn one pattern once.

## 8. Portfolio

Use image-led cards.
Portfolio is proof of work, not inventory.

Card:
image
title
short description
visibility state
remove/edit

Empty state:
"Show Students work you are proud of."

## 9. Reviews

Top:
Rating
Published reviews
Response rate if meaningful

Below:
Review feed

Each review:
Student display identity per privacy rules
rating
date
review body
Vendor response state

CTA:
Respond

Do not mix review moderation tools reserved for Admins into Vendor UI.

## 10. Business Profile

Split into two clear sections:

Brand
- logo
- cover
- live preview

Business details
- name
- WhatsApp
- description
- location/service area
- email
- website

Keep the public storefront preview visible on desktop.
On mobile use "Preview storefront".

## 11. Availability

Simplify into three sections:

Current status
- Follow schedule
- Open
- Busy
- Closed
- Back later
- Exam mode

Where you serve
- campus location chips/checkboxes

Weekly hours
- seven compact day rows

Campus calendar becomes secondary contextual information below these controls.

## 12. Analytics

Start with four meaningful questions:
- Are Students seeing me?
- Are they opening my profile?
- Are they contacting me?
- Which listings perform best?

Dashboard order:
1. KPI strip
2. Views/contact trend
3. Best-performing Products/Services
4. discovery/search terms if reliable
5. campus/location insights when enough data exists

Avoid charts when data volume is too low.

## 13. Growth

Growth should explain how to improve performance first and sell Pro second.

Sections:
- recommended actions
- current Free limits
- what Pro unlocks
- compare plans
- upgrade CTA

Never imply Pro improves trust, verification or ranking unfairly.

## 14. Billing

Keep current Paystack security model.

Visual order:
1. Current plan
2. Renewal/payment status
3. Manage subscription
4. Billing history
5. Payment protection note

Avoid overwhelming users with provider implementation language unless relevant.

## 15. Dark Mode

Light:
- background #F7F8FA family
- surface #FFFFFF
- ink #172033
- navy #082D6A
- blue #0B3D91
- orange #FF7A00
- subtle borders #E7EBF0

Dark:
- background around #08121F
- surface around #0F1C2B
- raised surface around #13243A
- text #F4F7FB
- muted #9AABC0
- border #26364A

Orange remains an action accent, not a large background colour.

## 16. Responsive Behaviour

Desktop:
- fixed compact sidebar
- content max-width around 1180–1240px
- two-column overview where useful

Tablet:
- narrower sidebar or compact rail where appropriate
- cards collapse to two columns

Mobile:
- no desktop sidebar
- 5-item bottom navigation
- single-column content
- drawers/sheets for secondary controls
- no horizontally scrolling primary navigation
- cards at full width
- minimum 44px controls
- safe-area padding for installed PWA

## 17. Loading / Empty / Error States

Every Vendor page should have:
- skeleton/loading state
- useful zero-data state
- inline failure message
- retry where applicable
- preserved form values when possible

Never show a blank panel because data is empty.

## 18. What Must Not Change During the Redesign

Do not change:
- Supabase table/schema names
- RLS rules merely for UI convenience
- Vendor approval model
- campus-specific approval
- Student↔Vendor payment model
- Paystack metadata
- billing lifecycle
- route URLs unless separately planned
- product/service/portfolio limits
- analytics calculation meaning
- trust/safety separation
- verified/campus status semantics

## 19. Redesign Order

1. Vendor shared shell/navigation
2. Vendor Overview
3. Products
4. Services
5. Portfolio
6. Business profile
7. Availability
8. Reviews
9. Analytics
10. Growth
11. Billing
12. Full responsive/dark/PWA regression

The Overview becomes the visual source of truth before secondary pages are restyled.
