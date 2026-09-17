export type AvailabilityRow = {
  manual_status?: string | null
  status_message?: string | null
  back_at?: string | null
  exam_mode_until?: string | null
}

export type BusinessHourRow = {
  day_of_week: number
  is_closed: boolean
  opens_at?: string | null
  closes_at?: string | null
}

export type VendorAvailabilityState = {
  code: 'open' | 'closed' | 'busy' | 'back_later' | 'exam_mode'
  label: string
  detail: string | null
  isOpen: boolean
}

const LAGOS_TZ = 'Africa/Lagos'

function lagosParts(date = new Date()) {
  const parts = new Intl.DateTimeFormat('en-GB', {
    timeZone: LAGOS_TZ,
    weekday: 'short',
    hour: '2-digit',
    minute: '2-digit',
    hourCycle: 'h23',
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  }).formatToParts(date)
  const value = (type: string) => parts.find((part) => part.type === type)?.value || ''
  const weekdayMap: Record<string, number> = { Sun: 0, Mon: 1, Tue: 2, Wed: 3, Thu: 4, Fri: 5, Sat: 6 }
  return {
    weekday: weekdayMap[value('weekday')] ?? 0,
    minutes: Number(value('hour')) * 60 + Number(value('minute')),
    date: `${value('year')}-${value('month')}-${value('day')}`,
  }
}

function timeToMinutes(value?: string | null) {
  if (!value) return null
  const [hour, minute] = value.slice(0, 5).split(':').map(Number)
  if (!Number.isFinite(hour) || !Number.isFinite(minute)) return null
  return hour * 60 + minute
}

export function formatClock(value?: string | null) {
  if (!value) return ''
  const [hourRaw, minuteRaw] = value.slice(0, 5).split(':').map(Number)
  const suffix = hourRaw >= 12 ? 'pm' : 'am'
  const hour = hourRaw % 12 || 12
  return `${hour}:${String(minuteRaw).padStart(2, '0')}${suffix}`
}

export function getVendorAvailability(
  availability: AvailabilityRow | null | undefined,
  hours: BusinessHourRow[] = [],
  now = new Date(),
): VendorAvailabilityState {
  const parts = lagosParts(now)
  const manual = availability?.manual_status || 'schedule'

  if (manual === 'open') return { code: 'open', label: 'Open now', detail: availability?.status_message || null, isOpen: true }
  if (manual === 'closed') return { code: 'closed', label: 'Closed', detail: availability?.status_message || null, isOpen: false }
  if (manual === 'busy') return { code: 'busy', label: 'Busy', detail: availability?.status_message || 'Limited availability right now', isOpen: false }

  if (manual === 'back_later') {
    const backAt = availability?.back_at ? new Date(availability.back_at) : null
    if (backAt && backAt > now) {
      return {
        code: 'back_later',
        label: 'Back later',
        detail: availability?.status_message || `Back ${new Intl.DateTimeFormat('en-NG', { timeZone: LAGOS_TZ, hour: 'numeric', minute: '2-digit' }).format(backAt)}`,
        isOpen: false,
      }
    }
  }

  if (manual === 'exam_mode' && availability?.exam_mode_until && availability.exam_mode_until >= parts.date) {
    return {
      code: 'exam_mode',
      label: 'Exam mode',
      detail: availability?.status_message || `Limited availability until ${new Intl.DateTimeFormat('en-NG', { day: 'numeric', month: 'short' }).format(new Date(`${availability.exam_mode_until}T12:00:00Z`))}`,
      isOpen: false,
    }
  }

  const today = hours.find((row) => Number(row.day_of_week) === parts.weekday)
  if (!today || today.is_closed) return { code: 'closed', label: 'Closed now', detail: today ? 'Closed today' : 'Hours not set for today', isOpen: false }

  const opens = timeToMinutes(today.opens_at)
  const closes = timeToMinutes(today.closes_at)
  if (opens === null || closes === null) return { code: 'closed', label: 'Closed now', detail: 'Hours not set', isOpen: false }

  const inRange = closes > opens
    ? parts.minutes >= opens && parts.minutes < closes
    : parts.minutes >= opens || parts.minutes < closes

  if (inRange) return { code: 'open', label: 'Open now', detail: `Until ${formatClock(today.closes_at)}`, isOpen: true }
  if (parts.minutes < opens) return { code: 'closed', label: 'Closed now', detail: `Opens ${formatClock(today.opens_at)}`, isOpen: false }
  return { code: 'closed', label: 'Closed now', detail: 'Closed for today', isOpen: false }
}

export function eventTypeLabel(type: string) {
  const labels: Record<string, string> = {
    resumption: 'Resumption', exam: 'Exams', matriculation: 'Matriculation', convocation: 'Convocation', semester_break: 'Semester break', event: 'Campus event', other: 'Campus date',
  }
  return labels[type] || 'Campus date'
}
