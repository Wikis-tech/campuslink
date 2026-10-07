import Link from 'next/link'
import { Bell, Check, Mail, X } from 'lucide-react'
import { createClient } from '@/lib/supabase/server'
import {
  dismissAllReadNotifications,
  dismissNotification,
  markAllNotificationsRead,
  markNotificationRead,
} from '@/app/actions/notifications'
import styles from './dashboard-notifications.module.css'
import { presentBrandCopy } from '@/lib/brand-copy'

export async function DashboardNotifications() {
  const supabase = await createClient()
  const { data: userData } = await supabase.auth.getUser()
  if (!userData.user) return null

  const { data: notifications } = await supabase.from('notifications')
    .select('id,title,body,type,read_at,dismissed_at,created_at,action_url')
    .eq('user_id', userData.user.id)
    .is('dismissed_at', null)
    .order('created_at', { ascending: false })
    .limit(20)

  const rows = notifications || []
  const unread = rows.filter((row) => !row.read_at)
  const readCount = rows.length - unread.length

  return (
    <section className={styles.shell} aria-label="Kampivo notifications">
      <details className={styles.panel}>
        <summary className={styles.trigger}>
          <span className={styles.bell}>
            <Bell size={19}/>
            {unread.length ? <span className={styles.badge} aria-label={`${unread.length} unread notifications`}>{unread.length > 9 ? '9+' : unread.length}</span> : null}
          </span>
          <span className={styles.triggerCopy}>
            <strong>Notifications</strong>
            <small>{unread.length ? `${unread.length} unread` : 'You are all caught up'}</small>
          </span>
        </summary>

        <div className={styles.popover}>
          <div className={styles.head}>
            <div className={styles.title}>
              <small>Kampivo updates</small>
              <strong>{unread.length ? `${unread.length} new notification${unread.length === 1 ? '' : 's'}` : 'Recent notifications'}</strong>
            </div>
            <div className={styles.headActions}>
              {unread.length ? (
                <form action={markAllNotificationsRead}>
                  <button className={styles.textButton} type="submit"><Check size={14}/> Mark all read</button>
                </form>
              ) : null}
              {readCount ? (
                <form action={dismissAllReadNotifications}>
                  <button className={styles.textButton} type="submit"><X size={14}/> Clear read</button>
                </form>
              ) : null}
            </div>
          </div>

          {rows.length ? (
            <div className={styles.list}>
              {rows.slice(0, 6).map((item) => (
                <article key={item.id} className={item.read_at ? styles.item : `${styles.item} ${styles.unread}`}>
                  <span className={styles.mail}><Mail size={15}/></span>
                  <div className={styles.copy}>
                    <strong>{presentBrandCopy(item.title)}</strong>
                    <p>{presentBrandCopy(item.body)}</p>
                    <small>{new Date(item.created_at).toLocaleString()}</small>
                  </div>
                  <div className={styles.actions}>
                    {item.action_url ? <Link href={item.action_url} className={styles.open}>Open</Link> : null}
                    {!item.read_at ? (
                      <form action={markNotificationRead}>
                        <input type="hidden" name="notification_id" value={item.id}/>
                        <button type="submit" className={styles.read}>Read</button>
                      </form>
                    ) : null}
                    <form action={dismissNotification}>
                      <input type="hidden" name="notification_id" value={item.id}/>
                      <button type="submit" className={styles.dismiss} aria-label="Dismiss notification"><X size={14}/> Dismiss</button>
                    </form>
                  </div>
                </article>
              ))}
            </div>
          ) : (
            <div className={styles.empty}>
              <Bell size={18}/>
              <strong>No notifications</strong>
              <span>New account, verification and marketplace updates will appear here.</span>
            </div>
          )}
        </div>
      </details>
    </section>
  )
}
