import Link from 'next/link'
import { redirect } from 'next/navigation'
import { BadgeCheck, Building2, BriefcaseBusiness, ShieldCheck } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { completeVendorOnboarding } from './actions'

export default async function VendorOnboardingPage({
  searchParams,
}: {
  searchParams: Promise<{ error?: string }>
}) {
  const params = await searchParams
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const { data: profile } = await supabase
    .from('profiles')
    .select('account_type,first_name')
    .eq('id', userId)
    .single()

  if (profile?.account_type !== 'vendor') redirect('/onboarding/student')

  const { data: existingVendor } = await supabase
    .from('vendor_profiles')
    .select('business_name,description,whatsapp_number,business_email,website_url,location_text,vendor_type,onboarding_completed_at')
    .eq('id', userId)
    .maybeSingle()

  if (existingVendor?.onboarding_completed_at) redirect('/vendor-v2')

  const [{ data: institutions }, { data: categories }] = await Promise.all([
    supabase.from('institutions').select('id,name,city,state').eq('is_active', true).order('name'),
    supabase.from('categories').select('id,name').eq('is_active', true).order('name'),
  ])

  return (
    <main className="onboarding-shell">
      <section className="onboarding-layout">
        <aside className="onboarding-aside vendor-aside">
          <Link href="/" className="brand">Campus<span>Link</span></Link>
          <div className="step-kicker">Vendor setup · 1 of 1</div>
          <h1>Set up once. Get discovered where it matters.</h1>
          <p>Create a clear business profile, choose the campus you serve and submit evidence for review.</p>
          <div className="trust-stack">
            <div><BriefcaseBusiness size={18} /><span>Business profile & services</span></div>
            <div><Building2 size={18} /><span>Campus-by-campus approval</span></div>
            <div><ShieldCheck size={18} /><span>Private document verification</span></div>
          </div>
        </aside>

        <section className="onboarding-panel">
          <div className="panel-heading">
            <p className="eyebrow">Vendor onboarding</p>
            <h2>Let students understand your business quickly.</h2>
            <p>Only approved vendors appear in discovery. Your verification documents stay private.</p>
          </div>

          {params.error ? <div className="auth-error">{params.error}</div> : null}

          <form action={completeVendorOnboarding} className="onboarding-form">
            <div className="form-grid-2">
              <div className="field-block">
                <label htmlFor="business_name">Business name</label>
                <input id="business_name" name="business_name" required maxLength={120} defaultValue={existingVendor?.business_name || ''} placeholder="Campus Fix" />
              </div>
              <div className="field-block">
                <label htmlFor="vendor_type">Vendor type</label>
                <select id="vendor_type" name="vendor_type" required defaultValue={existingVendor?.vendor_type || ''}>
                  <option value="" disabled>Select type</option>
                  <option value="student_vendor">Student vendor</option>
                  <option value="community_vendor">Community vendor</option>
                  <option value="registered_business">Registered business</option>
                </select>
              </div>
            </div>

            <div className="field-block">
              <label htmlFor="description">Business description</label>
              <textarea id="description" name="description" required maxLength={1200} rows={5} defaultValue={existingVendor?.description || ''} placeholder="What do you offer, who do you serve, and what should a student know before contacting you?" />
              <small>Keep it useful and specific. Avoid exaggerated claims.</small>
            </div>

            <div className="form-grid-2">
              <div className="field-block">
                <label htmlFor="whatsapp_number">WhatsApp number</label>
                <input id="whatsapp_number" name="whatsapp_number" type="tel" required maxLength={30} defaultValue={existingVendor?.whatsapp_number || ''} placeholder="0800 000 0000" />
              </div>
              <div className="field-block">
                <label htmlFor="business_email">Business email <span>optional</span></label>
                <input id="business_email" name="business_email" type="email" maxLength={254} defaultValue={existingVendor?.business_email || ''} placeholder="hello@business.com" />
              </div>
            </div>

            <div className="form-grid-2">
              <div className="field-block">
                <label htmlFor="location_text">Location / service area</label>
                <input id="location_text" name="location_text" required maxLength={180} defaultValue={existingVendor?.location_text || ''} placeholder="Yaba / around UNILAG" />
              </div>
              <div className="field-block">
                <label htmlFor="website_url">Website <span>optional</span></label>
                <input id="website_url" name="website_url" type="url" maxLength={240} defaultValue={existingVendor?.website_url || ''} placeholder="https://example.com" />
              </div>
            </div>

            <div className="form-grid-2">
              <div className="field-block">
                <label htmlFor="institution_id">Primary campus served</label>
                <select id="institution_id" name="institution_id" required defaultValue="">
                  <option value="" disabled>Select campus</option>
                  {(institutions || []).map((school) => (
                    <option key={school.id} value={school.id}>{school.name}{school.city ? ` — ${school.city}` : ''}</option>
                  ))}
                </select>
              </div>
              <div className="field-block">
                <label htmlFor="category_id">Main category</label>
                <select id="category_id" name="category_id" required defaultValue="">
                  <option value="" disabled>Select category</option>
                  {(categories || []).map((category) => <option key={category.id} value={category.id}>{category.name}</option>)}
                </select>
              </div>
            </div>

            <div className="service-box">
              <div className="section-mini-title"><BadgeCheck size={18} /> Your first service</div>
              <div className="form-grid-2">
                <div className="field-block">
                  <label htmlFor="service_name">Service name</label>
                  <input id="service_name" name="service_name" required maxLength={120} placeholder="Phone screen repair" />
                </div>
                <div className="field-block">
                  <label htmlFor="price_from">Starting price <span>optional</span></label>
                  <input id="price_from" name="price_from" inputMode="decimal" maxLength={30} placeholder="5000" />
                </div>
              </div>
              <div className="field-block">
                <label htmlFor="service_description">Service note <span>optional</span></label>
                <input id="service_description" name="service_description" maxLength={500} placeholder="What is included or what should the student know?" />
              </div>
            </div>

            <div className="form-grid-2">
              <div className="field-block">
                <label htmlFor="document_type">Verification evidence</label>
                <select id="document_type" name="document_type" required defaultValue="">
                  <option value="" disabled>Select document type</option>
                  <option value="student_id">Student ID</option>
                  <option value="government_id">Government ID</option>
                  <option value="cac_document">CAC document</option>
                  <option value="proof_of_address">Proof of address</option>
                  <option value="business_certificate">Business certificate</option>
                  <option value="portfolio_evidence">Portfolio / service evidence</option>
                </select>
              </div>
              <div className="upload-box compact-upload">
                <div><ShieldCheck size={20} /><strong>Upload evidence</strong></div>
                <p>PDF, JPG, PNG or WEBP · max 5MB</p>
                <input name="verification_document" type="file" required accept=".pdf,image/jpeg,image/png,image/webp" />
              </div>
            </div>

            <div className="onboarding-note">Submitting does not instantly publish your business. A Campus Link admin reviews your identity/business evidence and your selected campus before approval.</div>
            <button className="btn btn-primary onboarding-submit" type="submit">Submit vendor profile for review</button>
          </form>
        </section>
      </section>
    </main>
  )
}
