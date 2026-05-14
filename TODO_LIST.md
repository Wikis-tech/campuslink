# CampusLink Launch TODO List

This list is ordered for launch readiness. We apply the needful fixes first, then reserve later improvements for post-launch updates.

## 1. Launch-Critical Tasks (Must Fix Tonight)

1. Normalize vendor plan pricing across code and docs
   - Ensure student and community plan amounts match in `config/config.php`, `controllers/VendorController.php`, registration/payment flows, and landing page copy.
   - Verify Paystack amounts are correctly expressed in Kobo and align with displayed Naira values.

2. Make the student free plan explicit and available
   - Confirm the `basic` student plan behaves as a free/default option if that is the desired launch positioning.
   - If not yet implemented, update registration/payment logic to support a true free student entry plan.

3. Update landing page vendor pricing and CTA messaging
   - Align home page plan cards and CTAs with the priority strategy: free student access, low-cost community entry, and visibility-first packaging.

4. Fix the logged-in navigation/security behavior
   - Confirm current `index.php` logic only allows browsing while logged in and does not log out users incorrectly on `/browse`.
   - Keep the user/vendor portal isolation policy intact but allow the browser list page as the permitted public view.

5. Update vendor plan naming and tier positioning
   - Use clear labels that match the strategy: `Student Free`, `Student Boost`, `Student Featured`, `Community Basic`, `Community Premium`, `Community Featured`.
   - Ensure UI and admin views show consistent tier names.

6. Audit vendor registration forms and plan selection
   - Verify student/community registration pages allow selection of the intended plan and display plan benefits clearly.
   - Confirm the plan type is enforced safely in `RegistrationController` and `PaymentController`.

7. Fix any direct plan map inconsistencies
   - Search for hard-coded plan values like `200000`, `400000`, `1000000` and validate them against the chosen launch pricing.

## 2. High-Priority Launch Enhancements

8. Improve vendor trust indicators
   - Add or clarify badges for verified vendors, featured vendors, and premium listings in browse/vendor cards.
   - Ensure these labels appear in `views/browse/index.php`, `views/partials/vendor-card.php`, and vendor profile views.

9. Improve marketplace visibility signals
   - Show social proof items such as rating, review count, or featured status on index/browse cards.
   - Add a small “Trending” or “Popular” label where possible.

10. Strengthen WhatsApp and sharing CTAs
    - Confirm vendor cards and landing pages include share links or WhatsApp contact CTAs.
    - Ensure the user experience matches the growth strategy.

## 3. Post-Launch Update Work (After Launch)

11. Add referral / ambassador systems
    - Plan referral rewards that unlock boosts or badges.
    - Create a lightweight referral flow for vendor/user invitations.

12. Add promotional plan products
    - Boosted listings, sponsored posts, event promotion, and verification badge purchases.
    - Keep these as roadmap items and release only after launch.

13. Add vendor analytics and growth tools
    - Track enquiry counts, profile views, and active status.
    - Expand dashboards post-launch when vendor adoption stabilizes.

14. Expand future monetization and marketplace features
    - Transaction commissions, event promotion, verified badges, and sponsored content can be added later.

## 4. Notes and Observations

- Current strategy docs prioritize marketplace activity over revenue.
- For launch, keep barriers low and keep the first vendor tier as friction-free as possible.
- The platform should focus on trust, vendor density, and discovery first.

---

## Recommended First Edits

- `config/config.php`
- `controllers/VendorController.php`
- `controllers/RegistrationController.php`
- `controllers/PaymentController.php`
- `views/home/index.php`
- `views/vendor/register-student.php`
- `views/vendor/register-community.php`
- `views/browse/index.php`
- `views/partials/vendor-card.php`
- `views/vendor/subscription.php`

Keep this file as the source of truth during launch updates.
