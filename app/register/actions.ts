'use server'

import { redirect } from 'next/navigation'
import { createClient } from '@/lib/supabase/server'

export async function register(formData: FormData) {
  const firstName = String(formData.get('first_name') || '').trim().slice(0, 80)
  const lastName = String(formData.get('last_name') || '').trim().slice(0, 80)
  const email = String(formData.get('email') || '').trim().toLowerCase().slice(0, 254)
  const password = String(formData.get('password') || '')
  const accountType = String(formData.get('account_type') || 'student') === 'vendor' ? 'vendor' : 'student'

  if (!firstName || !lastName || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    redirect('/register?error=Enter%20your%20name%20and%20a%20valid%20email%20address')
  }

  if (password.length < 8 || password.length > 128) {
    redirect('/register?error=Use%20a%20password%20between%208%20and%20128%20characters')
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
    const normalized = error.message.toLowerCase()
    if (normalized.includes('already') || normalized.includes('registered') || normalized.includes('exists')) {
      redirect('/register?error=An%20account%20may%20already%20exist%20for%20this%20email.%20Try%20signing%20in%20instead')
    }
    if (normalized.includes('password')) {
      redirect('/register?error=That%20password%20does%20not%20meet%20the%20security%20requirements')
    }
    redirect('/register?error=We%20could%20not%20create%20your%20account.%20Please%20try%20again')
  }

  redirect('/register/check-email')
}
