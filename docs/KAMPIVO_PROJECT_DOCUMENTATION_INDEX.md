# Kampivo Project Documentation Index

This index is for the next developer/AI continuing Kampivo.

## First
Read `/KAMPIVO_HANDOFF_START_HERE.md`.

## Architecture / payments
- `PHASE5C_PAYSTACK_SETUP.md`
- `PHASE5E_TEST_CHECKLIST.md`
- `PHASE5F_TEST_CHECKLIST.md`
- `PHASE5G_TEST_CHECKLIST.md`
- `PHASE5H_TEST_CHECKLIST.md`

## Security
- `PHASE6_SECURITY_ACCEPTANCE.md`
- `PHASE6_SECURITY_AUDIT.md`
- `PHASE6_TEST_CHECKLIST.md`

## PWA / SEO / release
- `PHASE7A_PWA_FOUNDATION.md`
- `PHASE7C_PWA_UX_MOBILE_PERFORMANCE.md`
- `PHASE7D_TECHNICAL_SEO_CHECKLIST.md`
- `PHASE7E_PRIVACY_AWARE_SEO.md`
- `PHASE7F_PRODUCTION_HARDENING.md`
- `PHASE7F_REDIRECT_LOOP_HOTFIX.md`
- `PHASE7_RELEASE_AUDIT.md`

## UI/UX research
- `design/CAMPUSLINK_UIUX_REFERENCE.md`
- `design/DASHBOARD_RESEARCH_20_PLATFORMS.md`

## Privacy / compliance
- `compliance/KAMPIVO_DATA_RETENTION_SCHEDULE.md`
- `compliance/KAMPIVO_DATA_SUBJECT_RIGHTS_PROCEDURE.md`
- `compliance/KAMPIVO_BREACH_RESPONSE_PLAN.md`
- `compliance/KAMPIVO_PROCESSOR_REGISTER.md`
- `compliance/KAMPIVO_DPIA_VERIFICATION_SYSTEM.md`

## Current continuation
The active branch is `campuslink-ui-v3-experiment`. The last functional code commit before the handoff documentation commit is `30144f211cddcbae2f821f3cd6fd4cf0e07dde73`.

The immediate focus is release hardening, not new feature expansion:
fresh authenticated acceptance -> adversarial role/RPC/storage tests -> Paystack TEST acceptance -> Stage D domain/email migration -> release candidate -> production.
