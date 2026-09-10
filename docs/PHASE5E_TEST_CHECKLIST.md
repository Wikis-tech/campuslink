# Phase 5E — Vendor Analytics & Business Health test checklist

Run `supabase/migrations/202609100006_phase5e_vendor_analytics.sql` before testing analytics.

## Vendor dashboard
- Sign in as a Vendor and open `/vendor-v2`.
- Confirm the dashboard is task-first: priorities, 7-day performance, storefront, business health, and trust/safety.
- Confirm Products, Services, Portfolio, Analytics, Growth and Billing navigation works.
- Confirm Business Health reflects real profile completeness rather than verification/payment status alone.
- Check desktop, tablet and mobile layouts, plus Light/Dark/System themes.

## Analytics
- Open `/vendor-v2/analytics`.
- Free Vendor: only 7D should be available.
- Pro Monthly: 7D and 30D should be available.
- Pro Annual: 7D, 30D and 90D should be available.
- Empty analytics must show a useful empty state, not a broken chart.

## Event tracking
Use a Student account from an approved campus:
- Open the Vendor profile; Profile Views should increase after aggregation.
- Open a Vendor product; listing Views should increase.
- Click WhatsApp from the profile/product; Contact Intent should increase.
- Click Call; Contact Intent should increase.
- Save the Vendor; Saves should increase once.
- Remove the save; an unsave event should be recorded without exposing the Student identity to the Vendor.
- Submit a first verified review; Reviews Received should increment. Editing the same review should not count as a new review.

## Privacy and abuse
- Vendor must not be able to select raw `vendor_analytics_events` rows.
- Vendor should only see aggregate analytics belonging to their own Vendor ID.
- Student must not be able to record analytics for a Vendor outside their approved campus.
- Refreshing a profile repeatedly should be rate-limited by the database event guard.
- Direct calls with an invalid product/service ID must be rejected.

## Definition of done
Phase 5E is complete when the dashboard UX is approved, migration 006 succeeds, events aggregate correctly, Free/Pro time ranges are enforced, and raw Student identity data is not visible to Vendors.
