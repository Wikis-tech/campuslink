const ZERO_UUID = '00000000-0000-0000-0000-000000000000'

function missingFunction(error: any) {
  if (!error) return false
  const code = String(error.code || '')
  const message = String(error.message || '')
  return code === 'PGRST202' || code === '42883' || /function .* does not exist|could not find the function/i.test(message)
}

export type Phase5gReadiness = {
  ready: boolean
  schemaReady: boolean
  contactIntegrityReady: boolean
  issues: string[]
}

export async function checkPhase5gReadiness(supabase: any): Promise<Phase5gReadiness> {
  const issues: string[] = []

  const [reviewCheck, complaintCheck] = await Promise.all([
    supabase.from('reviews').select('id,contact_verified_at,vendor_response,vendor_response_status').limit(1),
    supabase.from('complaints').select('id,category,severity').limit(1),
  ])

  const schemaReady = !reviewCheck.error && !complaintCheck.error
  if (!schemaReady) {
    issues.push('Phase 5G trust/review/report columns are not available in the live database.')
  }

  const { error: contactRpcError } = await supabase.rpc('student_record_contact_event', {
    target_vendor: ZERO_UUID,
    contact_channel: 'whatsapp',
  })
  const contactIntegrityReady = !missingFunction(contactRpcError)
  if (!contactIntegrityReady) {
    issues.push('Phase 5G secure contact-evidence RPC is not available in the live database.')
  }

  return {
    ready: schemaReady && contactIntegrityReady,
    schemaReady,
    contactIntegrityReady,
    issues,
  }
}
