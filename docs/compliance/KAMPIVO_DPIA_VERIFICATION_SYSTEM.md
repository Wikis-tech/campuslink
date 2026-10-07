# Kampivo DPIA — Student and Vendor Verification / Document System

Status: Launch baseline DPIA; requires sign-off by privacy lead/DPO or a licensed DPCO where appropriate.
Date: 7 October 2026

## 1. Processing description

Kampivo collects evidence to verify student campus membership and Vendor identity/business legitimacy. Evidence can include Student IDs, matric/portal evidence, school email evidence, CAC/business documents and other verification files. Evidence is stored in a private Supabase Storage bucket. Authorised Admin roles review evidence and record a verification decision.

## 2. Purpose and necessity

Purpose: reduce impersonation, fraud and unsafe marketplace participation; establish campus-specific eligibility; support appeals and safety investigations.

Necessity: verification status cannot reliably be established from an ordinary account email alone. Data minimisation requires requesting the least intrusive evidence that can reasonably prove the relevant fact.

## 3. Data subjects and data

Adults (18+) at launch: Students, Vendors and authorised Admin reviewers.
Data: identity/contact details, school/business affiliation, verification documents, decision/audit metadata. Verification evidence can create elevated identity-theft and privacy risk if exposed.

## 4. Main risks and controls

| Risk | Impact | Existing / required mitigation |
| --- | --- | --- |
| Public exposure of identity documents | High | Private bucket; no public URLs; owner/Admin scoped access; test RLS/storage policies |
| School Admin sees another school's evidence | High | Institution-scoped authorisation; adversarial cross-school test before launch |
| Vendor/Student accesses another user's document | High | Owner-folder storage policies; BOLA/IDOR tests |
| Admin account compromise | High | Dedicated Admin entry, MFA/AAL2, throttling, audit logging, least privilege |
| Excessive document retention | High | 180-day post-decision deletion target unless hold; quarterly review until automated |
| Collecting evidence from minors without safeguards | High | 18+ launch rule; do not onboard known minors until lawful minor workflow exists |
| Forged/malicious uploads | Medium/High | MIME/type limits, file-size limit, content-signature validation, controlled display/download |
| Cross-border processing | Medium | Maintain processor register, review DPA/subprocessors and lawful transfer mechanism |
| Incorrect verification decision | Medium | Human review, notes/audit trail and appeal/re-review capability |
| Internal misuse | High | Role scoping, audit logs, access reviews, documented disciplinary/escalation path |

## 5. Residual risk and launch decision

Residual risk is acceptable for controlled adult private beta only if: private storage remains enforced; cross-role/cross-school access tests pass; Admin MFA remains mandatory; no service-role key is exposed; retention reviews begin; and a breach/DSAR process is operational.

Public launch requires final security regression, processor/DPA review, privacy contact ownership, and DCPMI classification/registration decision.

## 6. Review triggers

Review this DPIA when: minors are allowed; biometric data is introduced; automated verification/profiling is added; a new processor is introduced; data moves to a new country/region; the evidence set expands; a material breach occurs; or the platform changes from direct-contact marketplace to payments/escrow.
