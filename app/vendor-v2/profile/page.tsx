import { redirect } from 'next/navigation'
import { ImagePlus, Store, UploadCloud } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { VendorWorkspaceSidebar } from '@/components/vendor-workspace-sidebar'
import { updateVendorProfile } from './actions'

export default async function VendorProfileSettingsPage({
  searchParams,
}: {
  searchParams: Promise<{ saved?: string; error?: string }>
}) {
  const params = await searchParams
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')

  const [{ data: profile }, { data: vendor }] = await Promise.all([
    supabase.from('profiles').select('account_type').eq('id', userId).maybeSingle(),
    supabase.from('vendor_profiles').select('business_name,description,whatsapp_number,business_email,website_url,location_text,logo_url,cover_url').eq('id', userId).maybeSingle(),
  ])

  if (profile?.account_type !== 'vendor') redirect('/dashboard')
  if (!vendor) redirect('/onboarding/vendor')

  return (
    <main className="v5e-vendor-page">
      <VendorWorkspaceSidebar/>
      <section className="v5e-vendor-main vendor-profile-settings">
        <header className="v5e-vendor-header">
          <div>
            <small className="v5e-eyebrow">Business profile</small>
            <h1>Shape what Students see.</h1>
            <p>Your logo, cover image and business details appear on your public Campus Link profile.</p>
          </div>
        </header>

        {params.saved === '1' ? <div className="notice success">Business profile updated.</div> : null}
        {params.error ? <div className="notice error">{params.error}</div> : null}

        <form action={updateVendorProfile} className="vendor-profile-form">
          <section className="v5e-card vendor-profile-media-card">
            <div className="v5e-card-head">
              <div><span className="v5e-section-label">Brand media</span><h2>Logo & cover image</h2></div>
            </div>

            <div className="vendor-media-preview">
              <div className="vendor-cover-preview">
                {vendor.cover_url ? <img src={vendor.cover_url} alt="Current business cover"/> : <div><ImagePlus size={28}/><span>No cover image yet</span></div>}
              </div>
              <div className="vendor-logo-preview">
                {vendor.logo_url ? <img src={vendor.logo_url} alt="Current business logo"/> : <Store size={24}/>}
              </div>
            </div>

            <div className="vendor-profile-upload-grid">
              <label className="vendor-upload-field">
                <span><Store size={17}/> Business logo</span>
                <small>Square images work best. JPG, PNG or WEBP · max 2MB.</small>
                <input type="file" name="logo" accept="image/jpeg,image/png,image/webp"/>
              </label>
              <label className="vendor-upload-field">
                <span><UploadCloud size={17}/> Cover image</span>
                <small>Use a wide brand photo for the large profile banner. Around 1600×600 works well. JPG, PNG or WEBP · max 4MB.</small>
                <input type="file" name="cover" accept="image/jpeg,image/png,image/webp"/>
              </label>
            </div>
          </section>

          <section className="v5e-card vendor-profile-details-card">
            <div className="v5e-card-head"><div><span className="v5e-section-label">Public details</span><h2>Business information</h2></div></div>
            <div className="vendor-profile-fields">
              <label>Business name<input name="business_name" required maxLength={120} defaultValue={vendor.business_name || ''}/></label>
              <label>WhatsApp number<input name="whatsapp_number" required maxLength={30} defaultValue={vendor.whatsapp_number || ''}/></label>
              <label className="vendor-profile-full">Description<textarea name="description" required minLength={20} maxLength={1200} rows={5} defaultValue={vendor.description || ''}/></label>
              <label>Location / service area<input name="location_text" required maxLength={180} defaultValue={vendor.location_text || ''}/></label>
              <label>Business email<input name="business_email" type="email" maxLength={254} defaultValue={vendor.business_email || ''}/></label>
              <label className="vendor-profile-full">Website<input name="website_url" type="url" maxLength={240} defaultValue={vendor.website_url || ''} placeholder="https://example.com"/></label>
            </div>
            <div className="vendor-profile-save-row">
              <p>Changing these details does not buy verification or bypass campus approval.</p>
              <button className="btn btn-primary" type="submit">Save business profile</button>
            </div>
          </section>
        </form>
      </section>
    </main>
  )
}
