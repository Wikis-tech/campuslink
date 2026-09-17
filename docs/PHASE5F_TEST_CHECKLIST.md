# Phase 5F — Campus Intelligence & Availability Acceptance Checklist

Run after `202609170007_phase5f_campus_intelligence_availability.sql` is applied.

## Admin — Campus Intelligence
- [ ] `/admin-v2/campus-intelligence` loads for Super Admin / Operations Admin / Content Admin.
- [ ] School Admin / School Support only see their assigned institution(s).
- [ ] School Verifier cannot mutate Campus Intelligence unless separately granted a supported role.
- [ ] Add Main Gate, Hostel, Faculty, Student Centre, Library, Landmark and Off-campus locations.
- [ ] Duplicate location slug in the same campus returns a controlled error, not a 500.
- [ ] Hide/restore a campus location.
- [ ] Hidden location disappears from Student/Vendor selection but remains visible to authorized Admin.
- [ ] Add Resumption, Exam, Matriculation, Convocation, Semester break and Event dates.
- [ ] End date before start date is rejected cleanly.
- [ ] Remove a campus date.

## Vendor — Availability & Service Areas
- [ ] Vendor sidebar has `Availability` on desktop and mobile.
- [ ] `/vendor-v2/availability` loads without 404/500.
- [ ] Approved primary campus is shown.
- [ ] Vendor can select multiple campus service areas.
- [ ] Vendor cannot save a location belonging to another institution.
- [ ] Vendor can save all seven business-hour rows.
- [ ] Open day requires both opening and closing time.
- [ ] Closed day can save without times.
- [ ] `Follow business hours` derives Open/Closed correctly in Africa/Lagos time.
- [ ] Manual `Open now`, `Closed`, `Busy` override schedule.
- [ ] `Back later` shows until the chosen future time, then automatically falls back to schedule.
- [ ] `Exam mode` shows limited availability until the chosen date, then falls back to schedule.
- [ ] Status message is shown safely and limited to 160 characters.
- [ ] Vendor still manages business while Busy/Closed/Exam mode; this does not change verification or safety state.
- [ ] Campus calendar dates appear on the Vendor availability page.

## Student — Discovery
- [ ] `/student/discover` shows Campus Area filter when the institution has locations.
- [ ] `Available now` filter only returns vendors whose effective status is open.
- [ ] Selecting Main Gate/Hostel/etc returns vendors serving that area.
- [ ] Vendors with no explicit service areas remain campus-wide, rather than disappearing unexpectedly.
- [ ] Product and Service cards show availability badges.
- [ ] Vendor cards show up to three service-area tags.
- [ ] Busy/Closed/Back later/Exam mode vendors still appear unless `Available now` is selected.
- [ ] Identity approval + campus approval + marketplace safety rules still apply before any availability filter.
- [ ] A suspended/under-review vendor never reappears merely because status is `Open now`.
- [ ] Mobile filters remain usable at 390px and 414px.
- [ ] Light and Dark mode both keep status badges, filters and forms readable.

## Security / Data Isolation
- [ ] Vendor A cannot edit Vendor B hours, status or service areas.
- [ ] Student cannot insert/update/delete availability records.
- [ ] School Admin A cannot manage Campus B locations/calendar.
- [ ] All Phase 5F public-schema tables have RLS enabled.
- [ ] `private.can_manage_campus_intelligence()` is not executable by `anon`/`public`.
- [ ] No service-role key is exposed to browser code.
- [ ] Direct URL/form tampering with another institution ID is rejected by RLS/server checks.

## Regression
- [ ] Student Home, Discover, Saved, Product, Service and Vendor profile still load.
- [ ] Vendor Overview, Products, Services, Portfolio, Analytics, Growth and Billing still load.
- [ ] Admin Overview, Marketplace, Schools, Students, Vendors, Reviews, Reports, Categories, Admins and Audit still load.
- [ ] No new red console errors from Campus Link routes/APIs.
- [ ] Latest Vercel deployment is READY and build error log is empty.
