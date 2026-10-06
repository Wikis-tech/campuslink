import Link from 'next/link'
import { BellRing, Check, Mail } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import { markAllNotificationsRead, markNotificationRead } from '@/app/actions/notifications'
import styles from './dashboard-notifications.module.css'
import { presentBrandCopy } from '@/lib/brand-copy'

export async function DashboardNotifications() {
  const supabase = await createClient()
  const { data: userData } = await supabase.auth.getUser()
  if (!userData.user) return null

  const { data: notifications } = await supabase.from('notifications')
    .select('id,title,body,type,read_at,created_at,action_url')
    .eq('user_id', userData.user.id)
    .order('created_at', { ascending: false })
    .limit(6)

  const rows = notifications || []
  if (!rows.length) return null
  const unread = rows.filter((row) => !row.read_at)

  return (
    <section className={styles.wrap} aria-label="Kampivo notifications">
      <div className={styles.head}>
        <div className={styles.title}><span className={styles.icon}><BellRing size={17}/></span><div><small>Kampivo updates</small><strong>{unread.length ? `${unread.length} new notification${unread.length === 1 ? '' : 's'}` : 'Recent notifications'}</strong></div></div>
        {unread.length ? <form action={markAllNotificationsRead}><button className={styles.markAll} type="submit"><Check size={14}/> Mark all read</button></form> : null}
      </div>
      <div className={styles.list}>
        {rows.slice(0,4).map((item) => (
          <article key={item.id} className={item.read_at ? styles.item : `${styles.item} ${styles.unread}`}>
            <span className={styles.mail}><Mail size={15}/></span>
            <div className={styles.copy}>
              <strong>{presentBrandCopy(item.title)}</strong>
              <p>{presentBrandCopy(item.body)}</p>
              <small>{new Date(item.created_at).toLocaleString()}</small>
            </div>
            <div className={styles.actions}>
              {item.action_url ? <Link href={item.action_url} className={styles.open}>Open</Link> : null}
              {!item.read_at ? <form action={markNotificationRead}><input type="hidden" name="notification_id" value={item.id}/><button type="submit" className={styles.read}>Read</button></form> : null}
            </div>
          </article>
        ))}
      </div>
    </section>
  )
}
