import Link from 'next/link'
import { BadgeCheck } from 'lucide-react'
import '../legal.css'

const supportEmail = 'support@campuslink.name.ng'

export const metadata = {
  title: 'Trust & Compliance | Kampivo',
  description: 'Kampivo trust, safety, privacy and compliance commitments.',
}

export default function CompliancePage() {
  return (
    <main className="legal-page">
      <nav className="legal-nav"><div className="legal-nav-inner"><Link className="legal-brand" href="/">Kampiv<strong>o</strong></Link><div className="legal-nav-links"><Link href="/privacy">Privacy</Link><Link href="/terms">Terms</Link><Link href="/register">Create account</Link></div></div></nav>
      <header className="legal-hero"><span className="legal-kicker"><BadgeCheck size={14}/> Trust & compliance</span><h1>Trust should be verifiable.</h1><p>This page explains what Kampivo currently does, what is still in progress, and which claims we will only display after the relevant authority or partner has actually granted them.</p><div className="legal-meta"><span>Last reviewed: 7 October 2026</span><span>Private beta</span><span>Nigeria</span></div></header>
      <div className="legal-layout">
        <aside className="legal-toc"><strong>On this page</strong><a href="#status">Current status</a><a href="#privacy">Privacy programme</a><a href="#trust">Marketplace trust</a><a href="#payments">Payments</a><a href="#complaints">Complaints</a><a href="#claims">Badges & claims</a><a href="#contact">Contact</a></aside>
        <article className="legal-content">
          <section className="legal-section" id="status"><h2>1. Current compliance status</h2><div className="compliance-grid">
            <div className="compliance-card"><span className="status-chip status-live">Implemented</span><strong>Private verification-document storage</strong><span>Verification evidence is not publicly exposed and access is role-scoped.</span></div>
            <div className="compliance-card"><span className="status-chip status-live">Implemented</span><strong>Privacy and Terms notices</strong><span>Kampivo publishes clear privacy, marketplace and account rules for the current service.</span></div>
            <div className="compliance-card"><span className="status-chip status-progress">In progress</span><strong>NDPC registration/classification</strong><span>Kampivo is assessing the appropriate registration category and does not claim NDPC registration until approved.</span></div>
            <div className="compliance-card"><span className="status-chip status-planned">Planned</span><strong>CAC incorporation</strong><span>Registered-company details will be displayed after incorporation. Kampivo does not currently present itself as CAC-registered.</span></div>
            <div className="compliance-card"><span className="status-chip status-progress">In progress</span><strong>Data Protection Impact Assessment</strong><span>The verification/document workflow is under a documented privacy-risk assessment and mitigation process.</span></div>
            <div className="compliance-card"><span className="status-chip status-live">Implemented</span><strong>18+ launch policy</strong><span>The current launch is restricted to adults while a separate minor/guardian process has not been implemented.</span></div>
          </div></section>

          <section className="legal-section" id="privacy"><h2>2. Privacy programme</h2><p>Kampivo's privacy programme is designed around the Nigeria Data Protection Act 2023 and guidance of the Nigeria Data Protection Commission. The programme includes data minimisation, purpose limitation, access controls, a retention schedule, data-subject request handling, incident response, processor records and a DPIA for verification evidence.</p><p>Data subjects can request access, rectification, deletion, restriction, objection or other applicable rights by contacting <a href={`mailto:${supportEmail}?subject=Kampivo%20Privacy%20Request`}>{supportEmail}</a>.</p><div className="legal-contact"><Link href="/privacy">Read the Privacy Policy</Link><a className="secondary" href="https://ndpc.gov.ng/" target="_blank" rel="noreferrer">Official NDPC website</a></div></section>

          <section className="legal-section" id="trust"><h2>3. Marketplace trust model</h2><ul><li><strong>Identity verification:</strong> Vendor evidence is reviewed before an identity status is approved.</li><li><strong>Campus-specific approval:</strong> A Vendor must be approved for the institution it serves; approval is not automatically global.</li><li><strong>Trust is not for sale:</strong> paid plans affect business tools, not verification or campus approval.</li><li><strong>Review evidence:</strong> reviews and contact signals are used to improve trust and reduce obvious abuse.</li><li><strong>Safety reporting:</strong> Students can report Vendor conduct and authorised Admins can investigate and restrict marketplace visibility.</li></ul><p>Verification reduces uncertainty but is not a guarantee of future conduct or service quality. Users should still exercise reasonable care in direct transactions.</p></section>

          <section className="legal-section" id="payments"><h2>4. Payments</h2><p>Kampivo does not currently process Student-to-Vendor marketplace payments. Students and Vendors agree and complete those transactions directly. Optional Vendor subscription billing is handled by Paystack; Kampivo does not store full card details.</p><p>Payment for a subscription never purchases a trust badge, identity approval or campus approval.</p></section>

          <section className="legal-section" id="complaints"><h2>5. Complaints and redress</h2><p>Kampivo provides in-platform reporting for marketplace safety issues and an email route for account, billing, privacy and consumer complaints. We aim to make complaint handling reasonably accessible and to keep a documented moderation trail.</p><div className="legal-contact"><a href={`mailto:${supportEmail}?subject=Kampivo%20Complaint`}>Submit a complaint</a><a className="secondary" href="https://fccpc.gov.ng/" target="_blank" rel="noreferrer">Official FCCPC website</a></div></section>

          <section className="legal-section" id="claims"><h2>6. Regulatory badges and partnership claims</h2><p>Kampivo will only display a regulatory, registration, certification, Startup Label, trademark or school-partnership badge after it has actually been granted or agreed and can be verified. A domain name, pending application or internal policy is not presented as government approval.</p><p>When registrations are completed, this page can be updated with the relevant legal name, registration/reference number and official verification link where public verification is available.</p></section>

          <section className="legal-section" id="contact"><h2>7. Contact</h2><p>General support, privacy and compliance enquiries: <a href={`mailto:${supportEmail}`}>{supportEmail}</a>.</p><div className="legal-contact"><Link href="/terms">Terms of Use</Link><Link className="secondary" href="/privacy">Privacy Policy</Link></div></section>
        </article>
      </div>
      <footer className="legal-footer"><div className="legal-footer-inner"><span>© 2026 Kampivo. Your campus. Trusted.</span><div className="legal-footer-links"><Link href="/privacy">Privacy</Link><Link href="/terms">Terms</Link><Link href="/compliance">Compliance</Link></div></div></footer>
    </main>
  )
}
