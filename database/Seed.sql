-- ============================================================
-- CampusLink - Seed Data
-- Run AFTER schema.sql
-- ============================================================


SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- CATEGORIES
-- ============================================================
INSERT INTO `categories`
    (`id`, `name`, `slug`, `icon`, `description`, `sort_order`, `is_active`)
VALUES
    (1,  'Beauty & Grooming',   'beauty',      '💄', 'Haircuts, braiding, makeup, skincare, barbing and grooming services.',     1, 1),
    (2,  'Tech & Gadgets',      'tech',        '💻', 'Phone repairs, laptop services, gadget sales, and tech support.',          2, 1),
    (3,  'Repairs & Fixes',     'repairs',     '🔧', 'Appliance repairs, plumbing, carpentry, and general handyman services.',   3, 1),
    (4,  'Academic Help',       'academic',    '📚', 'Assignment help, research assistance, project writing, and editing.',      4, 1),
    (5,  'Fashion & Clothing',  'fashion',     '👗', 'Tailoring, fashion design, alterations, clothing sales.',                  5, 1),
    (6,  'Printing & Branding', 'printing',    '🖨️', 'Printing, photocopying, lamination, branded merchandise.',                6, 1),
    (7,  'Food & Snacks',       'food',        '🍔', 'Food vendors, snacks, meals, drinks, and catering services.',             7, 1),
    (8,  'Photography',         'photography', '📷', 'Portrait photography, events, passport photos, videography.',              8, 1),
    (9,  'Tutoring',            'tutoring',    '🎓', 'Subject tutoring, exam prep, coaching, and academic mentoring.',           9, 1),
    (10, 'Laundry & Cleaning',  'laundry',     '👕', 'Laundry, ironing, dry cleaning, and room cleaning services.',            10, 1),
    (11, 'Logistics & Delivery','logistics',   '🚚', 'Parcel delivery, errands, procurement, and transport within campus.',     11, 1),
    (12, 'Health & Wellness',   'health',      '💊', 'Fitness coaching, wellness services, first aid, and health products.',    12, 1);


-- ============================================================
-- PLANS
-- ============================================================
INSERT INTO `plans`
    (`id`, `vendor_type`, `plan_type`, `label`, `amount`, `amount_naira`, `duration_days`, `features`, `is_active`)
VALUES
    -- Student Plans
    (1, 'student', 'basic',    'Student Basic',
     200000, 2000.00, 180,
     '{"listing":true,"verified_badge":true,"whatsapp_button":true,"call_button":true,"reviews":true,"featured":false,"priority_listing":false,"photo_gallery":false,"max_photos":1}',
     1),
    (2, 'student', 'premium',  'Student Premium',
     500000, 5000.00, 180,
     '{"listing":true,"verified_badge":true,"whatsapp_button":true,"call_button":true,"reviews":true,"featured":false,"priority_listing":true,"photo_gallery":true,"max_photos":5}',
     1),
    (3, 'student', 'featured', 'Student Featured',
     1000000, 10000.00, 180,
     '{"listing":true,"verified_badge":true,"whatsapp_button":true,"call_button":true,"reviews":true,"featured":true,"priority_listing":true,"photo_gallery":true,"max_photos":10}',
     1),

    -- Community Plans
    (4, 'community', 'basic',    'Community Basic',
     400000, 4000.00, 180,
     '{"listing":true,"verified_badge":true,"whatsapp_button":true,"call_button":true,"reviews":true,"featured":false,"priority_listing":false,"photo_gallery":false,"max_photos":1}',
     1),
    (5, 'community', 'premium',  'Community Premium',
     700000, 7000.00, 180,
     '{"listing":true,"verified_badge":true,"whatsapp_button":true,"call_button":true,"reviews":true,"featured":false,"priority_listing":true,"photo_gallery":true,"max_photos":5}',
     1),
    (6, 'community', 'featured', 'Community Featured',
     1200000, 12000.00, 180,
     '{"listing":true,"verified_badge":true,"whatsapp_button":true,"call_button":true,"reviews":true,"featured":true,"priority_listing":true,"photo_gallery":true,"max_photos":10}',
     1);


-- ============================================================
-- ADMIN USERS
-- Default superadmin password: Admin@CampusLink2025
-- CHANGE IMMEDIATELY after first login
-- Password hash is bcrypt cost 12
-- ============================================================
INSERT INTO `admin_users`
    (`id`, `full_name`, `email`, `password`, `role`, `is_active`)
VALUES
    (1,
     'CampusLink SuperAdmin',
     'admin@campuslink.com',
     '$2y$12$YourGeneratedHashHereChangeThis',
     -- Generate this hash by running: password_hash('Admin@CampusLink2025', PASSWORD_BCRYPT, ['cost'=>12])
     'superadmin',
     1);


-- ============================================================
-- STATIC PAGES (Legal & Info pages default content)
-- ============================================================
INSERT INTO `static_pages`
    (`slug`, `title`, `content`, `meta_desc`, `is_active`)
VALUES
(
    'about',
    'About CampusLink',
    '<h1>About CampusLink</h1>
<p>CampusLink is a secure, lightweight digital campus service directory designed to connect students and campus community members with verified vendors operating within the university environment.</p>
<h2>Our Mission</h2>
<p>Our mission is to make it easy and safe for students to discover trusted campus service providers, while giving verified vendors a professional platform to reach their target audience.</p>
<h2>What We Do</h2>
<p>CampusLink operates strictly as a <strong>directory platform</strong>. We list verified vendors, display their contact information, and allow users to contact them directly via phone or WhatsApp. We do not provide services ourselves, do not process transactions between users and vendors, and do not facilitate direct messaging between parties within the platform.</p>
<h2>Vendor Verification</h2>
<p>Every vendor undergoes a manual identity and document verification process before being listed. Student vendors must provide valid student ID cards, selfies with their ID, and proof of active service. Community vendors must provide government-issued ID and relevant business documents.</p>
<h2>Safety Commitment</h2>
<p>We are committed to maintaining a safe and trustworthy directory. Users can submit complaints against vendors, and our admin team investigates all reports. Vendors who violate our policies face suspension or permanent banning.</p>',
    'Learn about CampusLink — the trusted campus service directory.',
    1
),
(
    'how-it-works',
    'How CampusLink Works',
    '<h1>How CampusLink Works</h1>
<p>Getting connected with campus vendors through CampusLink is simple, safe, and straightforward.</p>
<ol>
<li><strong>Browse Vendors:</strong> Search and filter verified vendors by category, name, or service type.</li>
<li><strong>Contact Directly:</strong> Use the WhatsApp or Call button on the vendor profile to reach them. No in-app messaging.</li>
<li><strong>Complete Business Offline:</strong> Negotiate and complete your transaction directly with the vendor.</li>
<li><strong>Leave a Review:</strong> Share your honest experience to help other students.</li>
<li><strong>Report Issues:</strong> Submit a formal complaint with evidence if you experience problems.</li>
</ol>
<div class="disclaimer-box"><strong>Disclaimer:</strong> CampusLink is a directory platform only. All transactions are completed outside this platform.</div>',
    'Learn how to use CampusLink to find and contact campus vendors.',
    1
),
(
    'general-terms',
    'General Terms and Conditions',
    '<h1>General Terms and Conditions</h1>
<p><strong>Effective Date:</strong> January 1, 2025 | <strong>Version:</strong> 1.0</p>

<h2>1. Introduction</h2>
<p>These General Terms and Conditions govern your use of the CampusLink platform. By accessing or using CampusLink, you agree to be bound by these terms. If you do not agree, you must not use this platform.</p>

<h2>2. Platform Nature — Independent Vendor Clause</h2>
<p>CampusLink is a <strong>digital directory platform only</strong>. All vendors listed on CampusLink are independent service providers. CampusLink does not employ, supervise, or control any vendor. CampusLink does not provide services, manufacture products, or participate in transactions between users and vendors. Any contract for services is solely between the user and the vendor.</p>

<h2>3. Limitation of Liability</h2>
<p>To the maximum extent permitted by applicable Nigerian law, CampusLink and its operators shall not be liable for any direct, indirect, incidental, special, or consequential damages arising from: (a) the use or inability to use the platform; (b) any transaction or interaction between users and vendors; (c) any vendor misrepresentation, fraud, or failure to deliver services; (d) unauthorized access to your account. Your sole remedy for dissatisfaction with the platform is to stop using it.</p>

<h2>4. Indemnification</h2>
<p>You agree to indemnify, defend, and hold harmless CampusLink, its operators, affiliates, and staff from and against any claims, liabilities, damages, losses, and expenses arising from your use of the platform, your violation of these terms, or your interaction with any vendor or user.</p>

<h2>5. Age Restriction</h2>
<p>You must be at least 18 years of age to register as a vendor on CampusLink. Users under 18 may browse the platform but must not register without parental consent. Vendors who are found to be under 18 at the time of registration will have their accounts terminated immediately.</p>

<h2>6. University Compliance Clause</h2>
<p>CampusLink operates in cooperation with the guidelines of the affiliated university. Vendors and users must comply with all university rules, regulations, and codes of conduct. CampusLink reserves the right to suspend or terminate accounts at the request of university authorities.</p>

<h2>7. Force Majeure</h2>
<p>CampusLink shall not be held liable for any failure or delay in performance due to circumstances beyond our reasonable control, including but not limited to natural disasters, internet outages, government actions, pandemics, or university closures.</p>

<h2>8. Intellectual Property</h2>
<p>All content on CampusLink, including but not limited to the logo, design, text, and software, is the intellectual property of CampusLink. You may not reproduce, distribute, or create derivative works without prior written consent. Vendors retain ownership of their uploaded content but grant CampusLink a non-exclusive license to display it on the platform.</p>

<h2>9. Severability</h2>
<p>If any provision of these terms is found to be invalid or unenforceable, the remaining provisions shall continue in full force and effect.</p>

<h2>10. Modification of Terms</h2>
<p>CampusLink reserves the right to modify these terms at any time. Users will be notified of significant changes via email or in-app notification. Continued use of the platform after changes constitutes acceptance of the updated terms.</p>

<h2>11. Governing Law</h2>
<p>These terms shall be governed by and construed in accordance with the laws of the Federal Republic of Nigeria. Any disputes shall be subject to the jurisdiction of competent Nigerian courts.</p>

<h2>12. Dispute Resolution</h2>
<p>In the event of a dispute, parties agree to first attempt resolution through CampusLink mediation. If mediation fails, disputes shall be resolved by arbitration under Nigerian law, or through competent courts in Nigeria as a last resort.</p>',
    'CampusLink General Terms and Conditions governing platform use.',
    1
),
(
    'user-terms',
    'User Terms and Conditions',
    '<h1>User Terms and Conditions</h1>
<p><strong>Effective Date:</strong> January 1, 2025 | <strong>Version:</strong> 1.0</p>

<h2>1. User Eligibility</h2>
<p>To register as a user on CampusLink, you must be a student or member of the campus community affiliated with the registered university. You must provide accurate and truthful registration information.</p>

<h2>2. Permitted Use</h2>
<p>As a user, you may: browse vendor listings, save vendors, submit reviews after genuine transactions, submit complaints with evidence, and contact vendors using the provided contact buttons. You may not: submit false reviews, harass vendors, attempt to exploit the platform, or use the platform for any unlawful purpose.</p>

<h2>3. Reviews and Ratings</h2>
<p>Reviews must be honest and based on genuine experience with the vendor. You are limited to one review per vendor. Fake, defamatory, or malicious reviews are prohibited and may result in account suspension. All reviews are subject to admin moderation before publication.</p>

<h2>4. Complaints</h2>
<p>You may submit formal complaints against vendors who have wronged you. False or malicious complaints submitted to harm vendors will result in account suspension. All complaints must be supported by honest descriptions and, where available, evidence.</p>

<h2>5. Account Responsibility</h2>
<p>You are responsible for maintaining the confidentiality of your account credentials. You are liable for all activities that occur under your account. Notify us immediately at support@campuslink.com if you suspect unauthorized use.</p>

<h2>6. Prohibited Conduct</h2>
<p>The following are strictly prohibited: impersonating others, creating multiple accounts, attempting to hack or disrupt the platform, using the platform for spam or commercial solicitation, and sharing your account credentials with others.</p>

<h2>7. No Guarantee of Services</h2>
<p>CampusLink does not guarantee the quality, legality, or availability of any service listed on the platform. Users engage vendors entirely at their own risk. CampusLink bears no responsibility for the outcome of any transaction between users and vendors.</p>

<h2>8. Account Termination</h2>
<p>CampusLink reserves the right to suspend or permanently ban accounts that violate these terms, engage in fraud, or cause harm to other users or vendors.</p>',
    'Terms governing student and user accounts on CampusLink.',
    1
),
(
    'vendor-terms',
    'Vendor Terms and Conditions',
    '<h1>Vendor Terms and Conditions</h1>
<p><strong>Effective Date:</strong> January 1, 2025 | <strong>Version:</strong> 1.0</p>

<h2>1. Vendor Eligibility</h2>
<p>To register as a vendor on CampusLink, you must: (a) be at least 18 years of age; (b) provide accurate and verifiable identity documents; (c) be an active student (for student vendors) or a legitimate business operator (for community vendors); (d) offer genuine services that comply with university and Nigerian laws.</p>

<h2>2. Subscription and Payments</h2>
<p>Vendor listings are maintained on a per-semester subscription basis. Subscription periods begin immediately upon successful payment verification. No refunds are issued once a subscription has been activated except in accordance with the Refund Policy. Subscriptions must be renewed before or immediately after expiry to maintain listing visibility.</p>

<h2>3. Vendor Obligations</h2>
<p>As a vendor, you agree to: (a) provide accurate business information; (b) deliver services as described; (c) respond professionally to user inquiries; (d) not engage in fraud, deception, or harassment of users; (e) notify CampusLink of any significant changes to your business information; (f) comply with all applicable Nigerian laws and university regulations.</p>

<h2>4. Prohibited Activities</h2>
<p>Vendors are strictly prohibited from: (a) misrepresenting qualifications or services; (b) charging prices significantly different from those listed; (c) harassing, threatening, or intimidating users; (d) engaging in illegal activities; (e) operating multiple vendor accounts; (f) providing services outside the scope of the registered category without updating profile.</p>

<h2>5. Independent Vendor Status</h2>
<p>Vendors are independent service providers and are not employees, agents, or partners of CampusLink. CampusLink is not responsible for the quality, safety, legality, or delivery of any vendor service. Vendors are solely responsible for their business operations, tax obligations, and compliance with all applicable laws.</p>

<h2>6. Review and Complaint Policy</h2>
<p>Vendors accept that users may leave honest reviews and file complaints. Vendors may reply to approved reviews but may not attempt to coerce users into removing or modifying reviews. Abuse of the review or complaint system will result in suspension.</p>

<h2>7. Suspension and Banning</h2>
<p>Vendors who accumulate three or more verified complaints may be subject to suspension review. CampusLink reserves the right to suspend or permanently ban vendors who violate these terms, engage in fraud, or harm users. Suspended vendors will be notified by email with the reason for suspension.</p>

<h2>8. Plan Changes</h2>
<p>Upgrade requests are subject to admin approval and require additional payment. Downgrade requests are scheduled to take effect after the current subscription period expires. Plan changes are not eligible for refunds of any pre-paid subscription amounts.</p>

<h2>9. Data and Privacy</h2>
<p>Vendors consent to their business information being publicly displayed on CampusLink for the purpose of connecting with customers. Identity documents submitted during registration are stored securely and are accessible only to authorized admin staff for verification purposes.</p>',
    'Terms and conditions governing vendor accounts on CampusLink.',
    1
),
(
    'privacy-policy',
    'Privacy Policy',
    '<h1>Privacy Policy</h1>
<p><strong>Effective Date:</strong> January 1, 2025 | <strong>Version:</strong> 1.0</p>

<h2>1. Information We Collect</h2>
<p>We collect the following information: (a) <strong>Registration data</strong>: name, email addresses, phone number, level, department, and password; (b) <strong>Vendor data</strong>: business information, identity documents, and subscription details; (c) <strong>Usage data</strong>: pages visited, search queries, and interaction logs; (d) <strong>Technical data</strong>: IP address, browser type, device information, and session data.</p>

<h2>2. How We Use Your Information</h2>
<p>We use your information to: (a) create and manage your account; (b) verify vendor identities; (c) process subscription payments; (d) send transactional emails and notifications; (e) investigate complaints and enforce platform policies; (f) improve platform functionality and user experience.</p>

<h2>3. Data Sharing</h2>
<p>We do not sell your personal data. We may share data with: (a) Paystack for payment processing; (b) SMS providers for OTP delivery; (c) University authorities when legally required; (d) Law enforcement when required by Nigerian law.</p>

<h2>4. Data Security</h2>
<p>We implement industry-standard security measures including: password hashing, encrypted sessions, CSRF protection, rate limiting, and secure file storage. However, no system is 100% secure and we cannot guarantee absolute data security.</p>

<h2>5. Data Retention</h2>
<p>Account data is retained for the duration of your account plus 2 years after deletion. Payment records are retained for 7 years as required by Nigerian financial regulations. Login logs are retained for 90 days. Please see our full Data Retention Policy for details.</p>

<h2>6. Your Rights</h2>
<p>Under applicable law, you have the right to: access your personal data, request corrections, request deletion of non-essential data, and withdraw consent where applicable. Contact us at privacy@campuslink.com to exercise these rights.</p>

<h2>7. Cookies</h2>
<p>CampusLink uses session cookies necessary for platform functionality. We do not use advertising or tracking cookies. You may disable cookies in your browser settings, but this may affect platform functionality.</p>

<h2>8. Changes to This Policy</h2>
<p>We may update this privacy policy periodically. Significant changes will be communicated via email. Continued use of the platform after changes constitutes acceptance.</p>',
    'CampusLink Privacy Policy — how we collect, use, and protect your data.',
    1
),
(
    'refund-policy',
    'Refund Policy',
    '<h1>Refund Policy</h1>
<p><strong>Effective Date:</strong> January 1, 2025</p>

<h2>1. General Policy</h2>
<p>CampusLink subscription fees are generally <strong>non-refundable</strong> once payment has been verified and the subscription has been activated. This policy exists because vendor listings go live immediately upon payment verification, and the value of the service begins immediately.</p>

<h2>2. Exceptions — When Refunds May Be Considered</h2>
<p>Refunds may be considered under the following limited circumstances:</p>
<ul>
<li>Duplicate payment: If a vendor is charged twice for the same subscription period due to a technical error.</li>
<li>Payment credited but subscription not activated: If payment was verified but the vendor account was not activated due to a system error.</li>
<li>Vendor application rejected after payment: If a vendor paid but their registration was subsequently rejected by admin, a partial refund may be issued at CampusLink discretion.</li>
</ul>

<h2>3. How to Request a Refund</h2>
<p>To request a refund, email support@campuslink.com with: your full name, business name, Paystack payment reference, and a clear description of the issue. Requests must be submitted within 7 days of the payment date.</p>

<h2>4. Processing Time</h2>
<p>Approved refunds are processed within 7-14 business days. Refunds are returned to the original payment method. CampusLink is not responsible for delays caused by the payment processor.</p>

<h2>5. No Refund for Early Cancellation</h2>
<p>No refund is issued for early cancellation of a subscription. If you choose to stop using CampusLink before your subscription expires, the remaining subscription period is forfeited.</p>

<h2>6. Plan Downgrades</h2>
<p>Plan downgrades are not eligible for refunds of price differences. Downgrades take effect at the start of the next subscription period.</p>',
    'CampusLink Refund Policy for vendor subscriptions.',
    1
),
(
    'suspension-policy',
    'Suspension Policy',
    '<h1>Suspension and Banning Policy</h1>
<p><strong>Effective Date:</strong> January 1, 2025</p>

<h2>1. Grounds for Suspension</h2>
<p>A vendor account may be suspended for the following reasons:</p>
<ul>
<li>Accumulation of three or more verified user complaints</li>
<li>Verified fraud or deceptive practices</li>
<li>Violation of vendor terms and conditions</li>
<li>Harassment or misconduct towards users</li>
<li>Providing services outside the registered category without updating profile</li>
<li>Non-compliance with university regulations</li>
<li>Submitting false or misleading registration documents</li>
</ul>

<h2>2. Suspension Process</h2>
<p>When a suspension is triggered: (a) The admin team reviews the complaint or violation evidence; (b) The vendor is notified by email of the investigation; (c) The vendor has 48 hours to respond with their account of events; (d) Admin makes a final decision based on evidence; (e) The vendor is notified of the outcome.</p>

<h2>3. Temporary Suspension</h2>
<p>Temporary suspensions last between 7 and 30 days depending on the severity of the violation. During suspension, the vendor listing is hidden from public view. The vendor may log in to their dashboard to view the suspension reason and appeal.</p>

<h2>4. Permanent Banning</h2>
<p>Permanent banning applies in cases of: confirmed fraud, multiple repeat violations, threats or violence against users, illegal activities, and severe misrepresentation. Permanently banned vendors cannot create new accounts. Their associated email, phone number, and IP address may be blacklisted.</p>

<h2>5. Appeals</h2>
<p>Suspended vendors may appeal within 14 days of suspension by emailing appeals@campuslink.com. Appeals are reviewed by senior admin and a response is provided within 7 business days. Appeal decisions are final.</p>

<h2>6. Subscription and Refunds During Suspension</h2>
<p>Suspended vendors are not entitled to refunds for the remaining subscription period. If a suspension is lifted, the remaining subscription period is reinstated from the date of reinstatement.</p>',
    'CampusLink Vendor Suspension and Banning Policy.',
    1
),
(
    'complaint-resolution',
    'Complaint Resolution Process',
    '<h1>Complaint Resolution Process</h1>
<p><strong>Effective Date:</strong> January 1, 2025</p>

<h2>1. How to File a Complaint</h2>
<p>Users can file a complaint against a vendor by: (a) visiting the vendor profile page and clicking "Report a Problem"; (b) or by navigating to User Dashboard > My Complaints > File New Complaint. You must provide a complaint category, detailed description, and optional supporting evidence (photo, PDF, or screenshot).</p>

<h2>2. Complaint Categories</h2>
<p>Available complaint categories include: Fraud/Scam, Poor Service Quality, No Show/Abandoned Order, Overcharging, Fake or Misleading Listing, Harassment or Misconduct, Impersonation, and Other.</p>

<h2>3. Investigation Process</h2>
<p>Once a complaint is submitted: (a) The vendor is notified and given 48 hours to respond; (b) Our admin team reviews all evidence from both parties; (c) The admin may request additional information; (d) A decision is made within 7 business days; (e) Both parties are notified of the outcome.</p>

<h2>4. Possible Outcomes</h2>
<ul>
<li><strong>Dismissed:</strong> Complaint found to be unfounded or lacking evidence.</li>
<li><strong>Warning issued:</strong> Vendor receives an official warning on their record.</li>
<li><strong>Suspension:</strong> Vendor is temporarily suspended pending investigation.</li>
<li><strong>Permanent ban:</strong> Vendor is permanently removed from the platform.</li>
</ul>

<h2>5. Three-Strike Rule</h2>
<p>Three verified complaints against a single vendor automatically trigger a formal suspension review. This review is conducted by senior admin regardless of the complaint categories.</p>

<h2>6. False Complaints</h2>
<p>Filing false or malicious complaints is a violation of user terms. Users found to be submitting false complaints will have their accounts suspended or permanently banned.</p>

<h2>7. Tracking Your Complaint</h2>
<p>You can track the status of your complaint under User Dashboard > My Complaints. Each complaint is assigned a unique ticket ID (e.g., CL-AB12CD34) which you can use to reference your complaint in communications with our team.</p>',
    'CampusLink complaint filing and resolution process.',
    1
),
(
    'data-retention',
    'Data Retention Policy',
    '<h1>Data Retention Policy</h1>
<p><strong>Effective Date:</strong> January 1, 2025</p>

<h2>1. Purpose</h2>
<p>This policy describes how long CampusLink retains different types of personal and business data, and how that data is disposed of when no longer needed.</p>

<h2>2. User Account Data</h2>
<p>Active user accounts are retained for the life of the account. Deleted user accounts are retained in anonymized form for 2 years for legal and audit purposes, after which all identifying data is permanently deleted.</p>

<h2>3. Vendor Data</h2>
<p>Active vendor accounts are retained for the life of the subscription. Inactive vendor accounts with no active subscription are retained for 6 months before being archived. Archived vendor data is fully deleted after 2 years. Identity documents (ID cards, CAC certificates) are retained for 3 years after account deactivation for compliance purposes.</p>

<h2>4. Payment Records</h2>
<p>All payment records and transaction logs are retained for 7 years as required by Nigerian financial regulations and the Financial Reporting Council of Nigeria (FRCN) guidelines.</p>

<h2>5. Review and Complaint Data</h2>
<p>Approved reviews are retained indefinitely as part of the vendor profile. Deleted or rejected reviews are retained in anonymized form for 1 year for audit purposes. Complaint records are retained for 5 years regardless of outcome, as they may be required for legal proceedings.</p>

<h2>6. Login and Audit Logs</h2>
<p>Login logs are retained for 90 days and then automatically purged. Audit logs for significant actions (payments, approvals, bans) are retained for 3 years.</p>

<h2>7. Terms Acceptance Records</h2>
<p>Records of terms and policy acceptance are retained permanently as legal proof of consent.</p>

<h2>8. Data Deletion Requests</h2>
<p>You may request deletion of your personal data by emailing privacy@campuslink.com. Deletion requests are processed within 30 days. Note that some data (payment records, legal compliance data) cannot be deleted due to regulatory requirements.</p>',
    'CampusLink Data Retention Policy — how long we keep your data.',
    1
);


-- ============================================================
-- GENERATE ADMIN PASSWORD HASH
-- Run this PHP snippet once to generate the correct hash:
-- echo password_hash('Admin@CampusLink2025', PASSWORD_BCRYPT, ['cost' => 12]);
-- Then UPDATE admin_users SET password='[generated_hash]' WHERE id=1;
-- ============================================================
-- UPDATE `admin_users`
-- SET `password` = '$2y$12$REPLACE_WITH_GENERATED_HASH'
-- WHERE `id` = 1;


SET FOREIGN_KEY_CHECKS = 1;
COMMIT;