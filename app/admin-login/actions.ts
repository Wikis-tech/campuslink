'use server'

import { notFound } from 'next/navigation'

// The former /admin-login entry point is intentionally dead.
// Keeping this exported action inert prevents stale clients or copied action
// identifiers from invoking the deprecated password-only administrator flow.
export async function adminLogin() {
  notFound()
}
