# Phase 5H — Smart Search & Discovery Acceptance Checklist

Use the latest `campuslink-ui-v3-experiment` preview deployment.

## Search relevance
- Search an exact Product name. The exact item should rank first or near first.
- Search an exact Service name. The service should rank first or near first.
- Search an exact Vendor name. The Vendor storefront should rank prominently.
- Search a category name. Relevant listings in that category should surface.
- Search a misspelling such as `clth` for `Cloth`. Close matches should still appear.
- Search a multi-word phrase such as `phone repair`. Relevant keyword matches should appear.
- Search text found only in a listing description. The relevant listing should still be discoverable.

## Campus and safety rules
- A Student must only receive Vendors approved for their institution.
- Under-review or actively suspended Vendors must not appear.
- Expired suspensions may become discoverable again according to the existing safety rules.
- Pro subscription status must not increase relevance score or bypass safety/campus eligibility.

## Filters
- Category filter should narrow Products and Services correctly.
- Campus area filter should narrow Vendors serving that area.
- 4.0+ and 4.5+ rating filters should work.
- Available now should only return Vendors currently considered open by Phase 5F.
- Type filter should support Everything, Products, Services and Vendors.
- Sort should support Best match, Top rated and Newest.

## Discovery UX
- Empty search should show campus recommendations instead of a blank result page.
- Search results should display the match reason where useful, such as Exact match, Strong title match, Vendor match, Relevant keywords or Similar match.
- Results should keep Product, Service and Vendor presentation visually distinct.
- Zero-result state should suggest useful categories and recovery actions.
- Student header global search should route into the same smart Discover experience.
- Search/filter submissions should use the shared Campus Link circular loading feedback.

## Mobile and themes
- Test at approximately 390px and 414px widths.
- Search fields and filters must remain usable without broken overflow.
- Product/Service cards and Vendor cards must remain readable.
- Verify Light and Dark mode.

## Regression
- Product detail pages still open.
- Service detail pages still open.
- Vendor storefronts still open.
- Save Vendor still works.
- WhatsApp/phone contact still works.
- Campus Trust and Phase 5G review/safety behaviour remain intact.
