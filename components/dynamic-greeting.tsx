'use client'

import { useEffect, useMemo, useState } from 'react'

type GreetingProps = {
  firstName?: string | null
  sessionSeed?: string | null
  role?: 'student' | 'vendor'
}

type CampusContextProps = {
  school?: string | null
  vendorCount?: number
  sessionSeed?: string | null
}

const studentGreetings = {
  morning: [
    'Good morning, {name}.',
    'Morning, {name}. What do you need today?',
    'Up and moving, {name}?',
    'Fresh day, {name}. Find what you need.',
    'Hey {name}, good morning.',
    'Morning, {name}. Your campus is waking up.'
  ],
  afternoon: [
    'Good afternoon, {name}.',
    'Hey {name}, what are you looking for?',
    'Afternoon, {name}. Need someone on campus?',
    'What’s up, {name}?',
    'Back again, {name}? Let’s find it.',
    'Hey {name}. Your next campus connection is close.'
  ],
  evening: [
    'Good evening, {name}.',
    'Evening, {name}. Still looking for something?',
    'Hey {name}, how’s your day going?',
    'What’s up, {name}?',
    'Evening, {name}. Find who you need.',
    'Hey {name}. Let’s make this quick.'
  ],
  night: [
    'Hey {name}, still up?',
    'Late one, {name}? We’ve got you.',
    'Good night, {name}. Need something before you log off?',
    'Still moving, {name}?',
    'Hey {name}. Find it now, sort it tomorrow.',
    'Night mode, {name}. What do you need?'
  ]
}

const vendorGreetings = {
  morning: [
    'Good morning, {name}.',
    'Morning, {name}. Ready for today’s customers?',
    'Fresh day, {name}. Let’s grow your campus reach.',
    'Hey {name}, good morning.',
    'Morning, {name}. Your storefront is ready.',
    'Up and running, {name}?'
  ],
  afternoon: [
    'Good afternoon, {name}.',
    'Hey {name}, how’s business today?',
    'Afternoon, {name}. Check what students are doing.',
    'What’s up, {name}?',
    'Back again, {name}? Let’s check your activity.',
    'Hey {name}. Keep your campus presence moving.'
  ],
  evening: [
    'Good evening, {name}.',
    'Evening, {name}. See how today went.',
    'Hey {name}, let’s check your business activity.',
    'What’s up, {name}?',
    'Evening, {name}. Your storefront is still working.',
    'Hey {name}. One quick business check-in.'
  ],
  night: [
    'Still working, {name}?',
    'Late check-in, {name}.',
    'Hey {name}, let’s wrap up the day.',
    'Night shift, {name}?',
    'Still up, {name}? Your dashboard is ready.',
    'One last look, {name}?'
  ]
}

function hash(value: string) {
  let h = 2166136261
  for (let i = 0; i < value.length; i += 1) {
    h ^= value.charCodeAt(i)
    h = Math.imul(h, 16777619)
  }
  return Math.abs(h >>> 0)
}

function periodForHour(hour: number): keyof typeof studentGreetings {
  if (hour >= 5 && hour < 12) return 'morning'
  if (hour >= 12 && hour < 17) return 'afternoon'
  if (hour >= 17 && hour < 22) return 'evening'
  return 'night'
}

function fallbackName(firstName?: string | null, role?: 'student' | 'vendor') {
  return firstName?.trim() || (role === 'vendor' ? 'there' : 'there')
}

export function DynamicGreeting({ firstName, sessionSeed, role = 'student' }: GreetingProps) {
  const name = fallbackName(firstName, role)
  const [hour, setHour] = useState<number | null>(null)

  useEffect(() => {
    const update = () => setHour(new Date().getHours())
    update()
    const timer = window.setInterval(update, 60_000)
    return () => window.clearInterval(timer)
  }, [])

  const greeting = useMemo(() => {
    if (hour === null) return `Welcome back, ${name}.`
    const period = periodForHour(hour)
    const bank = role === 'vendor' ? vendorGreetings[period] : studentGreetings[period]
    const dayKey = new Date().toLocaleDateString('en-CA')
    const seed = `${sessionSeed || dayKey}:${dayKey}:${period}:${name}:${role}`
    const chosen = bank[hash(seed) % bank.length]
    return chosen.replace('{name}', name)
  }, [hour, name, role, sessionSeed])

  return <h1 className="dynamic-greeting" suppressHydrationWarning>{greeting}</h1>
}

export function DynamicCampusContext({ school, vendorCount = 0, sessionSeed }: CampusContextProps) {
  const [mounted, setMounted] = useState(false)
  useEffect(() => setMounted(true), [])

  const context = useMemo(() => {
    if (!school) {
      return {
        label: 'Campus not set',
        title: 'Add your school when you’re ready',
        detail: 'Your dashboard still works. Adding a school makes discovery local to you.'
      }
    }
    if (!mounted) {
      return {
        label: 'Around you',
        title: school,
        detail: `${vendorCount} approved vendor${vendorCount === 1 ? '' : 's'} available for your campus.`
      }
    }

    const options = [
      'Around your campus',
      'Your local view',
      'Nearby on Campus Link',
      'Your campus area',
      'Serving your school',
      'Campus around you'
    ]
    const dayKey = new Date().toLocaleDateString('en-CA')
    const label = options[hash(`${sessionSeed || dayKey}:${dayKey}:${school}`) % options.length]
    return {
      label,
      title: school,
      detail: vendorCount
        ? `${vendorCount} approved vendor${vendorCount === 1 ? '' : 's'} currently available here.`
        : 'Approved vendors will appear here as your local network grows.'
    }
  }, [mounted, school, sessionSeed, vendorCount])

  return (
    <div className="student-campus-card dynamic-campus-context">
      <span>{context.label}</span>
      <strong>{context.title}</strong>
      <small>{context.detail}</small>
    </div>
  )
}
