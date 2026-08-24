'use server'

import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'

export async function register(formData: FormData) {
  const firstName = String(formData.get('first_name') || '').trim()
  const lastName = String(formData.get('last_name') || '').trim()
  const email = String(formData.get('email') || '').trim().toLowerCase()
  const password = String(formData.get('password') || '')
  const accountType = String(formData.get('account_type') || 'student') === 'vendor' ? 'vendor' : 'student'

  if (!firstName || !lastName || !email || password.length < 8) {
    redirect('/register?error=Complete%20all%20fields%20and%20use%20at%20least%208%20characters%20for%20your%20password')
  }

  const supabase = await createClient()
  const { error } = await supabase.auth.signUp({
    email,
    password,
    options: {
      data: {
        first_name: firstName,
        last_name: lastName,
        account_type: accountType,
      },
      emailRedirectTo: `${process.env.NEXT_PUBLIC_APP_URL || 'http://localhost:3000'}/auth/confirm`,
    },
  })

  if (error) {
    redirect(`/register?error=${encodeURIComponent(error.message)}`)
  }

  redirect('/register/check-email')
}
