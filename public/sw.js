/* Campus Link Phase 7B secure service worker
 * Security model:
 * - Never cache authenticated HTML, API responses, payment/auth/admin routes or cross-origin data.
 * - Only cache versioned Next.js static assets plus a generic offline page.
 * - Logout purges all Campus Link caches.
 */
const VERSION = 'phase7b-v1'
const STATIC_CACHE = `campuslink-static-${VERSION}`
const OFFLINE_CACHE = `campuslink-offline-${VERSION}`
const OFFLINE_URL = '/offline'

const PROTECTED_PREFIXES = [
  '/admin',
  '/admin-v2',
  '/admin-login-campus',
  '/student',
  '/vendor-v2',
  '/dashboard',
  '/auth',
  '/api',
  '/onboarding',
  '/verify',
  '/billing',
  '/paystack',
]

function isProtectedPath(pathname) {
  return PROTECTED_PREFIXES.some((prefix) => pathname === prefix || pathname.startsWith(prefix + '/'))
}

function isSafeStaticAsset(url) {
  return url.origin === self.location.origin && url.pathname.startsWith('/_next/static/')
}

async function purgeCampusLinkCaches() {
  const keys = await caches.keys()
  await Promise.all(keys.filter((key) => key.startsWith('campuslink-')).map((key) => caches.delete(key)))
}

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(OFFLINE_CACHE)
      .then((cache) => cache.addAll([OFFLINE_URL, '/default-icon.svg']))
      .then(() => self.skipWaiting())
  )
})

self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    const keep = new Set([STATIC_CACHE, OFFLINE_CACHE])
    const keys = await caches.keys()
    await Promise.all(
      keys
        .filter((key) => key.startsWith('campuslink-') && !keep.has(key))
        .map((key) => caches.delete(key))
    )
    await self.clients.claim()
  })())
})

self.addEventListener('message', (event) => {
  if (!event.data || typeof event.data !== 'object') return

  if (event.data.type === 'SKIP_WAITING') {
    self.skipWaiting()
    return
  }

  if (event.data.type === 'PURGE_PRIVATE_DATA') {
    event.waitUntil(purgeCampusLinkCaches().then(async () => {
      const cache = await caches.open(OFFLINE_CACHE)
      await cache.addAll([OFFLINE_URL, '/default-icon.svg'])
    }))
  }
})

self.addEventListener('fetch', (event) => {
  const request = event.request
  const url = new URL(request.url)

  if (url.protocol !== 'http:' && url.protocol !== 'https:') return
  if (url.origin !== self.location.origin) return

  // Sign-out must always hit the server. After a successful response, clear every
  // Campus Link cache so no app-shell data survives a logout.
  if (request.method !== 'GET') {
    if (request.method === 'POST' && url.pathname === '/auth/signout') {
      event.respondWith((async () => {
        const response = await fetch(request)
        if (response.ok || (response.status >= 300 && response.status < 400)) {
          await purgeCampusLinkCaches()
          const offlineCache = await caches.open(OFFLINE_CACHE)
          await offlineCache.addAll([OFFLINE_URL, '/default-icon.svg'])
        }
        return response
      })())
    }
    return
  }

  // Absolutely no service-worker caching for protected application areas.
  if (isProtectedPath(url.pathname)) {
    if (request.mode === 'navigate') {
      event.respondWith(
        fetch(request).catch(async () => {
          const fallback = await caches.match(OFFLINE_URL)
          return fallback || new Response('Secure connection required.', {
            status: 503,
            headers: { 'Content-Type': 'text/plain; charset=utf-8' },
          })
        })
      )
    }
    return
  }

  // Never cache documents. Public and private pages stay network-authoritative.
  if (request.mode === 'navigate' || request.destination === 'document') {
    event.respondWith(
      fetch(request).catch(async () => {
        const fallback = await caches.match(OFFLINE_URL)
        return fallback || new Response('Campus Link is offline.', {
          status: 503,
          headers: { 'Content-Type': 'text/plain; charset=utf-8' },
        })
      })
    )
    return
  }

  // Cache only immutable/versioned Next.js build assets. This deliberately
  // excludes API routes, optimized remote images, Supabase media and user data.
  if (isSafeStaticAsset(url)) {
    event.respondWith((async () => {
      const cache = await caches.open(STATIC_CACHE)
      const cached = await cache.match(request)
      if (cached) return cached

      const response = await fetch(request)
      if (response.ok && (response.type === 'basic' || response.type === 'cors')) {
        await cache.put(request, response.clone())
      }
      return response
    })())
  }
})
