import { redirect } from 'next/navigation'
import { CalendarDays, Clock3, MapPin, RadioTower, School } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { VendorWorkspaceSidebar } from '@/components/vendor-workspace-sidebar'
import { formatClock, getVendorAvailability } from '@/lib/phase5f'
import { saveAvailability, saveBusinessHours, saveServiceAreas } from './actions'

const dayNames = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday']

export default async function VendorAvailabilityPage({ searchParams }: { searchParams: Promise<{ ok?: string; error?: string }> }) {
  const params = await searchParams
  const supabase = await createClient()
  const { data: claimsData } = await supabase.auth.getClaims()
  const userId = claimsData?.claims?.sub
  if (!userId) redirect('/login')
  const { data: profile } = await supabase.from('profiles').select('account_type').eq('id', userId).maybeSingle()
  if (profile?.account_type !== 'vendor') redirect('/dashboard')

  const { data: vendor } = await supabase.from('vendor_profiles').select('business_name').eq('id', userId).maybeSingle()
  const { data: campus } = await supabase.from('vendor_institutions').select('institution_id,status').eq('vendor_id', userId).eq('is_primary', true).maybeSingle()
  const institutionId = campus?.institution_id || null

  const [{ data: locations }, { data: selectedAreas }, { data: hours }, { data: availability }, { data: events }, { data: institution }] = await Promise.all([
    institutionId ? supabase.from('campus_locations').select('id,name,location_type,description').eq('institution_id', institutionId).eq('is_active', true).order('sort_order').order('name') : Promise.resolve({ data: [] as any[] }),
    supabase.from('vendor_service_areas').select('campus_location_id').eq('vendor_id', userId),
    supabase.from('vendor_business_hours').select('day_of_week,is_closed,opens_at,closes_at').eq('vendor_id', userId).order('day_of_week'),
    supabase.from('vendor_availability').select('manual_status,status_message,back_at,exam_mode_until').eq('vendor_id', userId).maybeSingle(),
    institutionId ? supabase.from('campus_calendar_events').select('id,event_type,title,starts_on,ends_on').eq('institution_id', institutionId).eq('is_published', true).gte('starts_on', new Date(Date.now() - 7*86400000).toISOString().slice(0,10)).order('starts_on').limit(6) : Promise.resolve({ data: [] as any[] }),
    institutionId ? supabase.from('institutions').select('name').eq('id', institutionId).maybeSingle() : Promise.resolve({ data: null as any }),
  ])

  const selectedSet = new Set((selectedAreas || []).map((row:any) => row.campus_location_id))
  const hourMap = new Map((hours || []).map((row:any) => [Number(row.day_of_week), row]))
  const liveState = getVendorAvailability(availability, hours || [])

  return <main className="v5e-vendor-page">
    <VendorWorkspaceSidebar/>
    <section className="v5e-vendor-main phase5f-workspace">
      <header className="v5e-vendor-header phase5f-header"><div><small className="v5e-eyebrow">Campus intelligence</small><h1>Availability & service areas</h1><p>Tell students where you operate and whether you are available right now. Your schedule never changes your verification or safety status.</p></div></header>
      {params.ok ? <div className="notice success">{params.ok}</div> : null}
      {params.error ? <div className="notice error">{params.error}</div> : null}

      <section className="phase5f-summary-grid">
        <article className="v5e-card"><span className="v5e-section-label">Current status</span><h2 className={`phase5f-live ${liveState.code}`}>{liveState.label}</h2><p>{liveState.detail || 'Campus Link is using your saved schedule.'}</p></article>
        <article className="v5e-card"><span className="v5e-section-label">Primary campus</span><h2>{institution?.name || 'Campus not set'}</h2><p>{campus?.status === 'approved' ? 'Approved campus' : `Campus status: ${campus?.status || 'not set'}`}</p></article>
        <article className="v5e-card"><span className="v5e-section-label">Service areas</span><h2>{selectedSet.size}</h2><p>{selectedSet.size ? 'Campus locations selected' : 'Choose where students can find you'}</p></article>
      </section>

      <section className="phase5f-grid">
        <article className="v5e-card phase5f-panel">
          <div className="v5e-card-head"><div><span className="v5e-section-label"><RadioTower size={14}/> Live availability</span><h2>What should students see now?</h2></div></div>
          <form action={saveAvailability} className="phase5f-form">
            <label>Status<select name="manual_status" defaultValue={availability?.manual_status || 'schedule'}><option value="schedule">Follow business hours</option><option value="open">Open now</option><option value="busy">Busy</option><option value="closed">Closed</option><option value="back_later">Back later</option><option value="exam_mode">Exam mode</option></select></label>
            <label>Status message<input name="status_message" maxLength={160} defaultValue={availability?.status_message || ''} placeholder="Optional, e.g. Deliveries only this afternoon"/></label>
            <div className="phase5f-form-row"><label>Back at<input name="back_at" type="datetime-local"/></label><label>Exam mode until<input name="exam_mode_until" type="date" defaultValue={availability?.exam_mode_until || ''}/></label></div>
            <small>“Back at” is used only for Back later. “Exam mode until” is used only for Exam mode. Expired overrides automatically fall back to your weekly schedule.</small>
            <button className="btn btn-primary" type="submit">Save availability</button>
          </form>
        </article>

        <article className="v5e-card phase5f-panel">
          <div className="v5e-card-head"><div><span className="v5e-section-label"><MapPin size={14}/> Campus service areas</span><h2>Where do you serve students?</h2></div></div>
          <form action={saveServiceAreas} className="phase5f-form">
            {!locations?.length ? <div className="phase5f-empty"><School size={20}/><strong>No campus locations yet</strong><p>Your campus admin can add gates, hostels, faculties and landmarks. Until then, your business remains discoverable at campus level.</p></div> : <div className="phase5f-location-list">{locations.map((location:any) => <label key={location.id} className="phase5f-check"><input type="checkbox" name="location_id" value={location.id} defaultChecked={selectedSet.has(location.id)}/><span><strong>{location.name}</strong><small>{location.location_type.replaceAll('_',' ')}{location.description ? ` · ${location.description}` : ''}</small></span></label>)}</div>}
            <button className="btn btn-primary" type="submit" disabled={!locations?.length}>Save service areas</button>
          </form>
        </article>
      </section>

      <article className="v5e-card phase5f-panel">
        <div className="v5e-card-head"><div><span className="v5e-section-label"><Clock3 size={14}/> Weekly hours</span><h2>Set a normal week</h2></div></div>
        <form action={saveBusinessHours} className="phase5f-form">
          <div className="phase5f-hours">{dayNames.map((day, index) => { const row:any = hourMap.get(index); return <div className="phase5f-hour-row" key={day}><strong>{day}</strong><label className="phase5f-closed"><input type="checkbox" name={`closed_${index}`} defaultChecked={row?.is_closed ?? false}/> Closed</label><input type="time" name={`opens_${index}`} defaultValue={row?.opens_at?.slice(0,5) || '09:00'}/><span>to</span><input type="time" name={`closes_${index}`} defaultValue={row?.closes_at?.slice(0,5) || '18:00'}/><small>{row && !row.is_closed ? `${formatClock(row.opens_at)}–${formatClock(row.closes_at)}` : ''}</small></div> })}</div>
          <button className="btn btn-primary" type="submit">Save weekly hours</button>
        </form>
      </article>

      <article className="v5e-card phase5f-panel">
        <div className="v5e-card-head"><div><span className="v5e-section-label"><CalendarDays size={14}/> Campus calendar</span><h2>Dates that may affect demand</h2></div></div>
        {!events?.length ? <div className="phase5f-empty"><CalendarDays size={20}/><strong>No published campus dates yet</strong><p>Campus admins can add resumption, exams, matriculation, convocation, semester breaks and events.</p></div> : <div className="phase5f-event-list">{events.map((event:any) => <div key={event.id}><span>{event.event_type.replaceAll('_',' ')}</span><strong>{event.title}</strong><small>{event.starts_on}{event.ends_on ? ` → ${event.ends_on}` : ''}</small></div>)}</div>}
      </article>
    </section>
  </main>
}
