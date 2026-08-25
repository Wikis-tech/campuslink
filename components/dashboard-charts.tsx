'use client'

import {
  Bar,
  BarChart,
  CartesianGrid,
  Cell,
  Pie,
  PieChart,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts'

const brand = '#0b3d91'
const accent = '#1ea952'
const softBlue = '#8fb5ef'
const softGreen = '#8ed7aa'

export function StudentActivityChart({
  saved,
  reviews,
  contacts,
  reports,
}: {
  saved: number
  reviews: number
  contacts: number
  reports: number
}) {
  const data = [
    { name: 'Saved', value: saved },
    { name: 'Reviews', value: reviews },
    { name: 'Contacts', value: contacts },
    { name: 'Reports', value: reports },
  ]

  return (
    <div className="chart-shell chart-shell-student">
      <div className="chart-heading">
        <div>
          <span>Your activity</span>
          <strong>Campus Link at a glance</strong>
        </div>
        <small>Live account data</small>
      </div>
      <div className="chart-stage">
        <ResponsiveContainer width="100%" height="100%">
          <BarChart data={data} margin={{ top: 8, right: 8, left: -20, bottom: 0 }}>
            <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="rgba(11,61,145,.08)" />
            <XAxis dataKey="name" tickLine={false} axisLine={false} tick={{ fill: '#667085', fontSize: 12 }} />
            <YAxis allowDecimals={false} tickLine={false} axisLine={false} tick={{ fill: '#98a2b3', fontSize: 11 }} />
            <Tooltip cursor={{ fill: 'rgba(11,61,145,.04)' }} contentStyle={{ borderRadius: 14, border: '1px solid #e4e9f0', boxShadow: '0 12px 30px rgba(15,23,42,.08)' }} />
            <Bar dataKey="value" radius={[9, 9, 3, 3]} animationDuration={900}>
              {data.map((entry, index) => <Cell key={entry.name} fill={[brand, accent, softBlue, softGreen][index]} />)}
            </Bar>
          </BarChart>
        </ResponsiveContainer>
      </div>
    </div>
  )
}

export function AdminOverviewChart({
  students,
  vendors,
  schools,
  reports,
}: {
  students: number
  vendors: number
  schools: number
  reports: number
}) {
  const data = [
    { name: 'Students', value: students, fill: brand },
    { name: 'Vendors', value: vendors, fill: accent },
    { name: 'Schools', value: schools, fill: softBlue },
    { name: 'Reports', value: reports, fill: '#f4b740' },
  ]

  return (
    <div className="chart-shell admin-chart-shell">
      <div className="chart-heading">
        <div>
          <span>Platform footprint</span>
          <strong>Operational distribution</strong>
        </div>
        <small>Current scope</small>
      </div>
      <div className="admin-chart-grid">
        <div className="chart-stage compact">
          <ResponsiveContainer width="100%" height="100%">
            <BarChart data={data} margin={{ top: 8, right: 8, left: -22, bottom: 0 }}>
              <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="rgba(11,61,145,.08)" />
              <XAxis dataKey="name" tickLine={false} axisLine={false} tick={{ fill: '#667085', fontSize: 11 }} />
              <YAxis allowDecimals={false} tickLine={false} axisLine={false} tick={{ fill: '#98a2b3', fontSize: 10 }} />
              <Tooltip cursor={{ fill: 'rgba(11,61,145,.04)' }} contentStyle={{ borderRadius: 14, border: '1px solid #e4e9f0' }} />
              <Bar dataKey="value" radius={[8, 8, 3, 3]} animationDuration={950}>
                {data.map((entry) => <Cell key={entry.name} fill={entry.fill} />)}
              </Bar>
            </BarChart>
          </ResponsiveContainer>
        </div>
        <div className="chart-stage compact pie-stage">
          <ResponsiveContainer width="100%" height="100%">
            <PieChart>
              <Pie data={data} dataKey="value" nameKey="name" innerRadius={44} outerRadius={72} paddingAngle={4} animationDuration={950}>
                {data.map((entry) => <Cell key={entry.name} fill={entry.fill} />)}
              </Pie>
              <Tooltip contentStyle={{ borderRadius: 14, border: '1px solid #e4e9f0' }} />
            </PieChart>
          </ResponsiveContainer>
        </div>
      </div>
    </div>
  )
}
