# Phase 5G — Trust Profiles, Reviews & Safety

## Required SQL
Run these migrations in order:
1. `202609170008_phase5g_trust_reviews_safety.sql`
2. `202609170009_phase5g_interaction_integrity.sql`

Stop at the first SQL error and fix that exact error before continuing.

## Student trust profile
- Open an approved Vendor profile as a Student.
- Campus Trust shows: Identity verified, Campus approved, Account in good standing, verified-contact review count, Member since.
- No numeric/mysterious public trust score is shown.
- Suspended/under-review Vendors still remain hidden by existing marketplace safety rules.

## Reviews
- Unverified Student cannot review.
- Verified Student can create one review per Vendor.
- Editing same review updates it instead of creating a duplicate.
- Repeated rapid edits within 60 seconds are blocked.
- A Student who contacted the Vendor through Campus Link receives the `Contacted through Campus Link` label.
- A Student who did not contact through Campus Link does not receive that label.
- The label must not say the purchase was verified.

## Contact-evidence integrity
- Product/Service/Vendor WhatsApp and phone actions still redirect normally.
- Contact event is recorded through `student_record_contact_event`.
- Direct Data API inserts into `contact_events` by a Student should be denied.
- Cross-campus contact evidence cannot be created.

## Vendor review responses
- `/vendor-v2/reviews` loads.
- Vendor sees only their own published reviews.
- Vendor can publish/update a response.
- Vendor cannot edit Student rating/comment/status.
- Response appears nested under the Student review on the public storefront.
- Hidden Admin-moderated response no longer appears publicly.

## Review write integrity
- Direct Student Data API writes to protected review columns are denied.
- Review submission still works through the dedicated RPC.
- `contact_verified_at` cannot be forged from the browser.
- `vendor_response` cannot be written by a Student.

## Better report categories
- Vendor report form requires one of: Fraud/scam, Harassment, Fake product, Misrepresentation, Unsafe behaviour, Spam, Prohibited item, Other.
- Severity is derived server-side; Student cannot choose it.
- Fraud/scam, harassment, unsafe behaviour and prohibited item become high severity.
- Spam becomes low severity.
- Duplicate unresolved report by same Student/Vendor is blocked.
- Direct complaint insert through Data API is denied.
- Cross-campus Vendor reports are rejected.

## Student Safety Centre
- `/student/safety` loads on desktop/mobile.
- Guidance is readable in Light/Dark mode.
- Student sees only their own reports.
- Vendor never sees Student investigation notes.

## Admin Safety Investigations
- `/admin-v2/safety` loads.
- Open/high-severity/under-review summary metrics are correct.
- Vendor risk band is clearly described as an investigation signal, not proof.
- Risk band is never shown to Students.
- Admin can open connected Vendor 360 and Student 360.
- Internal notes remain Admin-only.
- Moving case Open → Reviewing → Resolved/Closed works and is audited.
- School-scoped Admin can only access cases in assigned schools.
- School-scoped Admin can investigate but cannot change marketplace safety state if their role lacks that power.
- Authorized global Admin can set Active / Under review / Suspended.
- Suspension requires 1–365 days.
- Vendor under review/suspended disappears from Student discovery.
- Vendor can still access their own workspace unless account auth is separately disabled.

## Vendor response moderation
- Admin can hide/publish a Vendor response without changing the Student review.
- Moderation is audited.

## Existing five-report safety rule
- Five DISTINCT unresolved Students reporting same Vendor within 30 days places an active Vendor under review.
- Repeated reports by one Student do not count as five.
- Resolving cases recalculates risk count correctly.

## UI/UX
- Student Trust Profile resembles a clean marketplace evidence block, not a compliance dashboard.
- Review cards emphasize rating, review text, trust labels and Vendor response.
- Vendor review workspace is task/reputation-focused.
- Admin safety workspace is dense enough for operations but still readable.
- Desktop + 390px + 414px mobile work.
- Light/Dark modes keep readable contrast.

## Console/runtime
No new Campus Link red errors for:
- `/student/safety`
- `/student/vendors/[slug]`
- `/vendor-v2/reviews`
- `/admin-v2/safety`
- `student_submit_vendor_review`
- `student_report_vendor`
- `student_record_contact_event`
- `vendor_respond_to_review`
- `admin_update_safety_case`
- `admin_moderate_vendor_response`

Ignore only known third-party/browser-extension console noise after confirming it disappears with extensions disabled.
