import { notFound } from 'next/navigation'
import { Image as ImageIcon, MonitorSmartphone, Palette, ShieldCheck } from 'lucide-react'
import { createAdminClient } from '@/lib/supabase/admin'
import { requireAdminContext } from '../lib'
import { updatePlatformBranding } from './actions'

export default async function BrandingPage({
  searchParams,
}: {
  searchParams: Promise<{ saved?: string; error?: string }>
}) {
  const context = await requireAdminContext()
  if (context.globalRole !== 'super_admin') notFound()

  const { saved, error } = await searchParams
  const admin = createAdminClient()
  const { data: branding } = await admin
    .from('platform_branding')
    .select('website_logo_url,favicon_url,app_icon_url,updated_at')
    .eq('id', 1)
    .maybeSingle()

  return (
    <div className="admin-branding-page">
      <header className="admin-section-head">
        <div>
          <span className="admin-eyebrow">Super Admin only</span>
          <h1>Branding &amp; App Identity</h1>
          <p>Update the Campus Link website logo, browser favicon and installable PWA icon from one secure place.</p>
        </div>
        <Palette size={24} />
      </header>

      {saved ? <div className="admin-inline-success">Branding updated successfully. Browsers and installed PWAs may need a refresh before cached icons change.</div> : null}
      {error ? <div className="admin-inline-error">{error}</div> : null}

      <section className="admin-branding-preview-grid">
        <article className="admin-brand-preview">
          <div className="admin-brand-preview-icon"><ImageIcon /></div>
          <span>Website logo</span>
          <div className="admin-brand-image-preview logo-preview"><img src="/brand/logo" alt="Current Campus Link website logo" /></div>
          <small>Used across login, registration and shared Campus Link branding.</small>
        </article>
        <article className="admin-brand-preview">
          <div className="admin-brand-preview-icon"><ShieldCheck /></div>
          <span>Browser favicon</span>
          <div className="admin-brand-image-preview square-preview"><img src="/brand/favicon" alt="Current Campus Link favicon" /></div>
          <small>PNG recommended. Shown in browser tabs and bookmarks.</small>
        </article>
        <article className="admin-brand-preview">
          <div className="admin-brand-preview-icon"><MonitorSmartphone /></div>
          <span>PWA app icon</span>
          <div className="admin-brand-image-preview square-preview"><img src="/brand/app-icon/192" alt="Current Campus Link PWA icon" /></div>
          <small>Upload a square 512×512 PNG for the best Android and iPhone result.</small>
        </article>
      </section>

      <form action={updatePlatformBranding} className="admin-branding-form">
        <label>
          <span>Website logo</span>
          <small>JPG, PNG or WEBP · max 3 MB</small>
          <input type="file" name="website_logo" accept="image/jpeg,image/png,image/webp" />
        </label>

        <label>
          <span>Browser favicon</span>
          <small>PNG · max 1 MB · square image recommended</small>
          <input type="file" name="favicon" accept="image/png" />
        </label>

        <label>
          <span>PWA app icon</span>
          <small>PNG · max 5 MB · use a square 512×512 image</small>
          <input type="file" name="app_icon" accept="image/png" />
        </label>

        <div className="admin-branding-security-note">
          <ShieldCheck size={18} />
          <div>
            <strong>Protected Super Admin action</strong>
            <p>Uploads are checked by file signature and saved through the server-only Supabase client. Normal Students, Vendors and scoped Admins cannot modify these assets.</p>
          </div>
        </div>

        <button type="submit" className="admin-primary-button">Save branding</button>
      </form>

      <p className="admin-branding-updated">Last updated: {branding?.updated_at ? new Date(branding.updated_at).toLocaleString() : 'Using Campus Link defaults'}</p>
    </div>
  )
}
