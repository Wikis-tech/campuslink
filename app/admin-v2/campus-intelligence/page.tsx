import { CalendarDays, MapPinned, Plus, School, ToggleLeft, Trash2 } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { eventTypeLabel } from '@/lib/phase5f'
import { requireAdminContext } from '../lib'
import { createCampusEvent, createCampusLocation, deleteCampusEvent, toggleCampusLocation } from './actions'

export default async function CampusIntelligenceAdminPage({ searchParams }: { searchParams: Promise<{ campus?: string; ok?: string; error?: string }> }) {
  const params = await searchParams
  const context = await requireAdminContext()
  const supabase = await createClient()

  let institutionQuery = supabase.from('institutions').select('id,name,city,state,is_active').order('name')
  if (!context.isGlobalAdmin) institutionQuery = institutionQuery.in('id', context.schoolAssignments.map((item) => item.institution_id))
  const { data: institutions } = await institutionQuery
  const selectedCampusId = params.campus && (institutions || []).some((item:any) => item.id === params.campus) ? params.campus : institutions?.[0]?.id
  const selectedCampus = (institutions || []).find((item:any) => item.id === selectedCampusId)

  const [{ data: locations }, { data: events }] = selectedCampusId ? await Promise.all([
    supabase.from('campus_locations').select('id,name,slug,location_type,description,is_active,sort_order').eq('institution_id', selectedCampusId).order('sort_order').order('name'),
    supabase.from('campus_calendar_events').select('id,event_type,title,description,starts_on,ends_on,is_published').eq('institution_id', selectedCampusId).order('starts_on', { ascending: false }).limit(40),
  ]) : [{ data: [] as any[] }, { data: [] as any[] }]

  return <div className="admin-page phase5f-admin-page">
    <header className="admin-page-head"><div><span className="admin-kicker">Phase 5F · Campus intelligence</span><h1>Campus locations & calendar</h1><p>Model the places students actually use, then publish campus dates that can influence vendor availability and marketplace demand.</p></div></header>
    {params.ok ? <div className="notice success">{params.ok}</div> : null}
    {params.error ? <div className="notice error">{params.error}</div> : null}

    <form method="get" className="admin-card phase5f-campus-switcher"><label>Campus<select name="campus" defaultValue={selectedCampusId || ''}>{(institutions || []).map((item:any) => <option key={item.id} value={item.id}>{item.name}</option>)}</select></label><button className="admin-btn" type="submit">Open campus</button></form>

    {!selectedCampusId ? <div className="admin-card admin-empty"><School size={28}/><h2>No campus assignment</h2><p>This admin account does not currently have a campus it can manage.</p></div> : <>
      <section className="admin-grid-two phase5f-admin-grid">
        <article className="admin-card">
          <div className="admin-card-head"><div><span className="admin-kicker"><MapPinned size={14}/> Campus map vocabulary</span><h2>{selectedCampus?.name}</h2></div></div>
          <form action={createCampusLocation} className="admin-form phase5f-admin-form"><input type="hidden" name="institution_id" value={selectedCampusId}/><label>Location name<input name="name" required minLength={2} placeholder="Main Gate"/></label><label>Type<select name="location_type" defaultValue="landmark"><option value="gate">Gate</option><option value="hostel">Hostel</option><option value="faculty">Faculty</option><option value="student_centre">Student centre</option><option value="library">Library</option><option value="landmark">Landmark</option><option value="off_campus">Off-campus area</option><option value="other">Other</option></select></label><label>Description<input name="description" maxLength={240} placeholder="Optional context for students and vendors"/></label><button className="admin-btn primary" type="submit"><Plus size={15}/> Add location</button></form>
          <div className="phase5f-admin-list">{(locations || []).map((location:any) => <div key={location.id} className={location.is_active ? '' : 'muted'}><div><strong>{location.name}</strong><small>{location.location_type.replaceAll('_',' ')}{location.description ? ` · ${location.description}` : ''}</small></div><form action={toggleCampusLocation}><input type="hidden" name="id" value={location.id}/><input type="hidden" name="institution_id" value={selectedCampusId}/><input type="hidden" name="is_active" value={String(location.is_active)}/><button type="submit" className="admin-icon-btn" title={location.is_active ? 'Hide location' : 'Restore location'}><ToggleLeft size={18}/></button></form></div>)}{!locations?.length ? <div className="admin-empty-inline">Add gates, hostels, faculties, centres, libraries and nearby off-campus areas.</div> : null}</div>
        </article>

        <article className="admin-card">
          <div className="admin-card-head"><div><span className="admin-kicker"><CalendarDays size={14}/> Campus calendar</span><h2>Important dates</h2></div></div>
          <form action={createCampusEvent} className="admin-form phase5f-admin-form"><input type="hidden" name="institution_id" value={selectedCampusId}/><label>Type<select name="event_type" defaultValue="event"><option value="resumption">Resumption</option><option value="exam">Exams</option><option value="matriculation">Matriculation</option><option value="convocation">Convocation</option><option value="semester_break">Semester break</option><option value="event">Campus event</option><option value="other">Other</option></select></label><label>Title<input name="title" required placeholder="First semester examinations"/></label><div className="admin-form-row"><label>Starts<input name="starts_on" type="date" required/></label><label>Ends<input name="ends_on" type="date"/></label></div><label>Description<textarea name="description" maxLength={500} placeholder="Optional context for vendors and students"/></label><button className="admin-btn primary" type="submit"><Plus size={15}/> Publish date</button></form>
          <div className="phase5f-admin-list">{(events || []).map((event:any) => <div key={event.id}><div><span>{eventTypeLabel(event.event_type)}</span><strong>{event.title}</strong><small>{event.starts_on}{event.ends_on ? ` → ${event.ends_on}` : ''}</small></div><form action={deleteCampusEvent}><input type="hidden" name="id" value={event.id}/><input type="hidden" name="institution_id" value={selectedCampusId}/><button type="submit" className="admin-icon-btn danger" title="Remove campus date"><Trash2 size={16}/></button></form></div>)}{!events?.length ? <div className="admin-empty-inline">Publish resumption, exams, matriculation, convocation, semester breaks and other campus dates.</div> : null}</div>
        </article>
      </section>
    </>}
  </div>
}
