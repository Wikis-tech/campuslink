'use client'

import { Area, AreaChart, CartesianGrid, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts'

type Point = {
  day: string
  impressions: number
  profileViews: number
  contacts: number
}

export function VendorAnalyticsChart({ data }: { data: Point[] }) {
  return (
    <div className="v5e-chart-stage" aria-label="Vendor performance chart">
      <ResponsiveContainer width="100%" height="100%">
        <AreaChart data={data} margin={{ top: 10, right: 8, left: -24, bottom: 0 }}>
          <defs>
            <linearGradient id="v5eBlue" x1="0" y1="0" x2="0" y2="1">
              <stop offset="5%" stopColor="#0b3d91" stopOpacity={0.22}/>
              <stop offset="95%" stopColor="#0b3d91" stopOpacity={0}/>
            </linearGradient>
            <linearGradient id="v5eGreen" x1="0" y1="0" x2="0" y2="1">
              <stop offset="5%" stopColor="#1ea952" stopOpacity={0.2}/>
              <stop offset="95%" stopColor="#1ea952" stopOpacity={0}/>
            </linearGradient>
          </defs>
          <CartesianGrid vertical={false} strokeDasharray="3 3" stroke="rgba(148,163,184,.18)"/>
          <XAxis dataKey="day" tickLine={false} axisLine={false} tick={{ fontSize: 11, fill: '#7a8798' }}/>
          <YAxis allowDecimals={false} tickLine={false} axisLine={false} tick={{ fontSize: 11, fill: '#7a8798' }}/>
          <Tooltip contentStyle={{ borderRadius: 14, border: '1px solid rgba(148,163,184,.25)', boxShadow: '0 16px 40px rgba(15,23,42,.12)' }}/>
          <Area type="monotone" dataKey="impressions" name="Search impressions" stroke="#0b3d91" fill="url(#v5eBlue)" strokeWidth={2}/>
          <Area type="monotone" dataKey="profileViews" name="Profile views" stroke="#1ea952" fill="url(#v5eGreen)" strokeWidth={2}/>
          <Area type="monotone" dataKey="contacts" name="Contacts" stroke="#7c3aed" fill="transparent" strokeWidth={2}/>
        </AreaChart>
      </ResponsiveContainer>
    </div>
  )
}
