# Campus Link Dashboard Research — 20+ Marketplace, Seller and Student Platforms

This design research supports the Campus Link UI V3 experiment. The goal is not to copy another product. It is to understand recurring interaction patterns that make marketplaces and business dashboards clear, useful, trusted and responsive.

## Platforms reviewed

1. Shopify Admin — central sidebar, global search, real-time metrics, alerts and task-first home.
2. Etsy Shop Manager — Today’s top tasks, compact stats, recent activity, shop advisor and contextual education.
3. eBay Seller Hub — configurable overview, listings, performance, research, advertising and payments separated by clear tabs.
4. Amazon Seller Central — operational command center, account health, inventory, performance and growth recommendations.
5. Square Dashboard — simple real-time business metrics, item-level performance and reports.
6. Stripe Dashboard — at-a-glance activity, operational status, payments and issue management.
7. Fresha Partner / Marketplace Profile — strong profile imagery, services, marketplace visibility, reviews and performance in one business context.
8. DoorDash Merchant Portal / Business Manager — mobile-first bottom navigation, live actions, menu control and performance summaries.
9. Uber Eats Manager — homepage overview plus deep analytics, feedback, menu, marketing and payments as separate workspaces.
10. Airbnb Host dashboard — status-driven hosting tasks, listing quality, guest communication and performance.
11. Fiverr Freelancer dashboard — seller level, rating, response rate, to-dos, gigs and listing-level impressions/clicks/conversion.
12. Upwork dashboards — action-needed filters, project/job status, messages and saved talent brought close to active work.
13. Taskrabbit — consumer discovery starts with categories/search; provider profiles emphasize skills, reviews, availability and rate.
14. Thumbtack Pro — lead/task orientation, profile quality, reviews and service-area relevance.
15. Poshmark Seller Tools / Closet Insights — listings are visual inventory; performance is attached to listings, not only abstract charts.
16. Mercari Seller Dashboard — item-level views, clicks and likes directly beside each listing.
17. Vinted — clean listing management, profile-first trust and clear states for active/hidden/removed items.
18. Campus Market Nigeria — products and services first, categories, verified vendors and WhatsApp contact with no complex checkout.
19. Rakata — campus-filtered product/service listings, mobile bottom navigation, seller profile and campus proximity as core context.
20. iMart.ng — student/vendor role split, campus filtering, product photos, promoted visibility and verification cues.
21. EdeEki — lifestyle marketplace presentation, identity verification, campus-local discovery and strong visual listing cards.
22. UNiDAYS — consumer discovery is feed/category led rather than dashboard-metric led; brand tiles and personalized discovery dominate.

## Patterns worth adopting

### Student side: marketplace, not dashboard
- Search and categories should be above personal statistics.
- Product/service imagery should dominate discovery.
- Campus context should be visible but compact.
- Bottom navigation on mobile should expose Home, Search/Discover, Saved and Profile.
- Trust signals belong near the vendor/listing, not inside a separate generic trust card.
- Saved/recently viewed content should feel like a visual rail.
- Activity analytics should be secondary or hidden until meaningful data exists.

### Vendor side: daily command center
- First screen should answer: Am I visible? What needs attention? What are students engaging with?
- Use task/status rows before charts.
- Separate Products, Services, Portfolio, Reviews, Growth and Billing.
- Listing-level engagement is more useful than generic dashboard charts.
- Profile strength should produce actionable fixes.
- Verification and paid plan status must remain visually distinct.
- Show real marketplace state: Live, Under review, Suspended, Campus pending.

### Information architecture
- Student navigation: Home, Discover, Saved, Profile/Verification.
- Vendor navigation: Overview, Products, Services, Portfolio, Reviews, Analytics/Growth, Billing.
- Admin navigation remains operational and data dense.
- Avoid making every destination a card on the home page. Use navigation plus a small number of high-priority modules.

### Responsive patterns
- Desktop can use a persistent/sidebar or wide top shell.
- Tablet collapses secondary panels and keeps core navigation obvious.
- Mobile uses bottom navigation for students and compact tabs/menu for vendors.
- Horizontal rails are useful for categories and products, but primary actions must remain visible without horizontal scrolling.
- Controls should be touch-friendly and text must wrap without clipping.

### Motion
- Use motion to show state change: saved, activated, under review, navigation selection, upload completion.
- Prefer 120–240ms transitions for hover/press/focus feedback.
- Avoid continuous decorative motion in dense business screens.
- Respect prefers-reduced-motion.

## Campus Link V3 direction

### Student Home
1. Personalized greeting + compact campus chip.
2. Large search field.
3. Browse categories.
4. Products around your campus.
5. Services around your campus.
6. Trusted/featured vendors.
7. Saved/recent activity.
8. Verification status only when action is required.

### Vendor Home
1. Greeting + business name + marketplace status.
2. Action strip: visibility, reports/safety, plan, campus approval.
3. Quick actions: Add product, Add service, Add portfolio work.
4. Product/service performance and recent engagement.
5. Reviews / enquiries / saves.
6. Growth and billing kept secondary unless action is needed.

### Product model
Campus Link separates three concepts:
- Products — individual items a vendor offers (e.g. a shirt, perfume, laptop accessory). Students can open an item then contact the vendor.
- Services — what the vendor does (e.g. haircut, laundry, tutoring, repairs).
- Portfolio — proof/examples of completed work and visual credibility.

Campus Link does not process the student-to-vendor transaction. Product/service CTAs lead to WhatsApp or phone contact.

### Safety model
- Reports are visible to admins immediately.
- One reporter cannot inflate the escalation threshold with multiple unresolved reports against the same vendor.
- Five distinct unresolved reports within 30 days automatically place the vendor under marketplace review and hide them from student discovery while keeping vendor login available.
- Admin can clear the hold, keep under review, or suspend for a defined number of days.
- Payment/Pro status never overrides safety, verification or campus approval.
