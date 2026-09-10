# Campus Link Phase 5C — Paystack TEST setup

Phase 5C is deliberately test-only until the full checkout + webhook flow has passed validation. Payment never grants identity verification, campus approval, or public discovery.

## 1. Apply the Phase 5C database migration

Run this file in Supabase SQL Editor after the successful 5A and 5B migrations:

`supabase/migrations/202609070004_phase5c_paystack_security.sql`

Stop immediately if Supabase returns an error. Do not continue configuration until the exact SQL failure is fixed.

## 2. Create two TEST plans in Paystack

In the Paystack dashboard, stay in **Test mode** and create:

- **Campus Link Pro Monthly** — NGN 2,500 — monthly
- **Campus Link Pro Annual** — NGN 24,000 — annually

Copy the test plan codes returned by Paystack. They look like `PLN_xxxxxxxxxx`.

Then run this SQL in Supabase, replacing the placeholders with the TEST plan codes:

```sql
update public.subscription_plans
set paystack_plan_code = 'PLN_MONTHLY_TEST_CODE', updated_at = now()
where slug = 'pro-monthly' and tier = 'pro';

update public.subscription_plans
set paystack_plan_code = 'PLN_ANNUAL_TEST_CODE', updated_at = now()
where slug = 'pro-annual' and tier = 'pro';
```

Confirm:

```sql
select slug, price_ngn, billing_interval, paystack_plan_code
from public.subscription_plans
where slug in ('pro-monthly','pro-annual')
order by slug;
```

Do not put a live plan code into a test integration. Paystack plans and API keys must belong to the same environment.

## 3. Configure Vercel server environment variables

Set these in the Campus Link Vercel project. Never commit their real values to GitHub.

```text
SUPABASE_SECRET_KEY=<Supabase server secret/service-role compatible key>
PAYSTACK_SECRET_KEY=sk_test_...
PAYSTACK_PUBLIC_KEY=pk_test_...
PAYSTACK_ALLOW_LIVE=false
NEXT_PUBLIC_APP_URL=https://campuslink.name.ng
```

`PAYSTACK_SECRET_KEY` and `SUPABASE_SECRET_KEY` are server-only. Never prefix either with `NEXT_PUBLIC_`.

The application contains a safety lock that rejects a non-test Paystack secret key while `PAYSTACK_ALLOW_LIVE` is not explicitly `true`.

## 4. Configure the Paystack webhook

Set the Paystack TEST webhook URL to:

`https://campuslink.name.ng/api/paystack/webhook`

Campus Link validates `x-paystack-signature` using HMAC SHA-512 with the Paystack secret key before parsing or processing an event. There is no separate Campus Link webhook secret.

The webhook is the authority for activating Pro. The browser callback is informational only.

## 5. Security model

The payment flow is:

1. Signed-in Vendor selects Monthly or Annual Pro.
2. Campus Link server validates the Vendor and requested public plan.
3. Server creates a unique Campus Link payment reference and pending ledger row.
4. Server initializes Paystack checkout using the TEST secret key.
5. Paystack hosts the card collection UI. Campus Link never receives raw card numbers or CVV.
6. Browser returns to `/vendor-v2/billing/callback` after checkout. This page **does not activate Pro**.
7. Paystack sends a signed webhook to Campus Link.
8. For `charge.success`, Campus Link additionally calls Paystack Verify and compares reference, amount, currency and success status.
9. `subscription.create` is what changes the local subscription to `active` and refreshes Vendor entitlements.
10. Duplicate webhook bodies are idempotently recognized by a deterministic event fingerprint.

## 6. Test cases before live mode

Test all of these with Paystack TEST data:

- Monthly Pro succeeds.
- Annual Pro succeeds.
- User closes/cancels Paystack checkout.
- Invalid/failed card does not activate Pro.
- Refreshing the callback page does not create another subscription.
- Calling the callback URL manually does not activate Pro.
- A fake POST to the webhook without a valid Paystack signature returns 401.
- Replaying a valid webhook does not create duplicate payment/subscription effects.
- A Student account cannot initialize Vendor checkout.
- A Vendor cannot request an arbitrary plan slug or amount.
- An existing active Pro Vendor cannot create another simultaneous checkout.
- Verification/campus approval stays unchanged before and after payment.
- Free/Pro service and portfolio limits change only after the signed subscription lifecycle event.

## 7. Do not enable live payments yet

Keep:

`PAYSTACK_ALLOW_LIVE=false`

Phase 5C is complete only after the TEST end-to-end flow is proven. Live keys should be introduced only after Phase 5C validation and the later billing/security checks are complete.

## Paystack documentation used

- Subscriptions: https://paystack.com/docs/payments/subscriptions/
- Transaction initialize/verify API: https://paystack.com/docs/api/transaction/
- Webhooks: https://paystack.com/docs/payments/webhooks/
- Payment verification: https://paystack.com/docs/payments/verify-payments/
