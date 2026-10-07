# Kampivo Processor and Vendor Register

Owner: Privacy/compliance lead
Review: before onboarding a new processor and at least every 6 months.

| Provider | Role/purpose | Data categories | Known service region / transfer note | Contract/DPA review |
| --- | --- | --- | --- | --- |
| Supabase | Database, Auth, private object storage | Accounts, profiles, verification records/evidence, marketplace and Admin data | Project region: EU Central; cross-border transfer assessment required | Verify current DPA/subprocessor terms before public launch |
| Vercel | Hosting, CDN/runtime | Web requests, application traffic, server processing | Global delivery; current functions may run outside Nigeria | Verify current DPA/subprocessor terms before public launch |
| Resend | Transactional/admin email | Recipient email/name, message content, delivery events | Current sender domain region EU West | Verify current DPA/subprocessor terms before public launch |
| Paystack | Vendor subscription payments | Billing email, payment/subscription references/status | Payment provider; do not send verification documents | Verify merchant terms/privacy and live-business KYC before enabling live charging |
| ImprovMX | Inbound email forwarding | Email content/addresses for configured aliases | External email processor | Confirm privacy/DPA terms and access controls |

## Vendor onboarding checklist

- Purpose and minimum data shared
- Security and access controls
- Processing locations / cross-border transfer basis
- Retention/deletion capability
- Subprocessors
- Breach notification commitment
- Data subject request assistance
- Contract/DPA/terms captured
- Exit/deletion procedure
