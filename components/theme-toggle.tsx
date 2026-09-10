'use client'

import { Laptop, Moon, Sun } from 'lucide-react'
import { useEffect, useState } from 'react'

type ThemeMode = 'light' | 'dark' | 'system'

const order: ThemeMode[] = ['light', 'dark', 'system']

function applyTheme(mode: ThemeMode) {
  const root = document.documentElement
  const resolved = mode === 'system'
    ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
    : mode

  root.dataset.theme = resolved
  root.dataset.themePreference = mode
  root.style.colorScheme = resolved
}

export function ThemeToggle({ compact = false }: { compact?: boolean }) {
  const [mode, setMode] = useState<ThemeMode>('system')

  useEffect(() => {
    const saved = (localStorage.getItem('campuslink-theme') as ThemeMode | null) || 'system'
    setMode(saved)
    applyTheme(saved)

    const media = window.matchMedia('(prefers-color-scheme: dark)')
    const handleChange = () => {
      const current = (localStorage.getItem('campuslink-theme') as ThemeMode | null) || 'system'
      if (current === 'system') applyTheme('system')
    }
    media.addEventListener('change', handleChange)
    return () => media.removeEventListener('change', handleChange)
  }, [])

  const next = () => {
    const nextMode = order[(order.indexOf(mode) + 1) % order.length]
    setMode(nextMode)
    localStorage.setItem('campuslink-theme', nextMode)
    applyTheme(nextMode)
  }

  const Icon = mode === 'dark' ? Moon : mode === 'light' ? Sun : Laptop
  const label = mode === 'system' ? 'System appearance' : `${mode[0].toUpperCase()}${mode.slice(1)} mode`

  return (
    <button
      type="button"
      className={`cl-theme-toggle${compact ? ' compact' : ''}`}
      onClick={next}
      aria-label={`${label}. Activate next appearance mode.`}
      title={`${label} · click to change`}
    >
      <Icon size={16} />
      {!compact && <span>{mode}</span>}
    </button>
  )
}
