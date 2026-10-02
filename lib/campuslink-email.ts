import 'server-only'

type CampusLinkEmail = {
  to: string
  recipientName?: string | null
  subject: string
  title: string
  body: string
  ctaLabel?: string | null
  ctaUrl?: string | null
  idempotencyKey: string
}

export type CampusLinkEmailResult =
  | { ok: true; id: string | null }
  | { ok: false; reason: 'not_configured' | 'send_failed'; error: string }

const FROM = 'CampusLink Alerts <notifications@campuslink.name.ng>'

function escapeHtml(value: string) {
  return value.replace(/[&<>"']/g, (char) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
  }[char] || char))
}

function absoluteUrl(value?: string | null) {
  if (!value) return null
  if (/^https?:\/\//i.test(value)) return value
  const base = (process.env.NEXT_PUBLIC_APP_URL || 'https://campuslink.name.ng').replace(/\/$/, '')
  return `${base}${value.startsWith('/') ? value : `/${value}`}`
}

export function renderCampusLinkEmail(input: Omit<CampusLinkEmail, 'to' | 'idempotencyKey'>) {
  const name = escapeHtml(input.recipientName?.trim() || 'there')
  const title = escapeHtml(input.title)
  const body = escapeHtml(input.body).replace(/\n/g, '<br/>')
  const ctaUrl = absoluteUrl(input.ctaUrl)
  const cta = ctaUrl && input.ctaLabel
    ? `<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin-top:24px;"><tr><td bgcolor="#0B3D91" style="background-color:#0B3D91;border-radius:8px;"><a href="${escapeHtml(ctaUrl)}" style="display:inline-block;padding-top:13px;padding-right:20px;padding-bottom:13px;padding-left:20px;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:20px;font-weight:700;color:#ffffff;text-decoration:none;">${escapeHtml(input.ctaLabel)}</a></td></tr></table>`
    : ''

  const html = `<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>${title}</title>
</head>
<body style="margin:0;padding:0;background-color:#f5f7fb;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f5f7fb" style="width:100%;background-color:#f5f7fb;">
<tr><td align="center" style="padding-top:32px;padding-right:16px;padding-bottom:32px;padding-left:16px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#ffffff" style="width:100%;max-width:600px;background-color:#ffffff;border:1px solid #e3e8f0;border-radius:12px;">
<tr><td style="padding-top:28px;padding-right:32px;padding-bottom:20px;padding-left:32px;border-bottom:1px solid #edf1f5;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
<td valign="middle"><img src="https://campuslink.name.ng/brand/logo" width="142" height="36" border="0" alt="CampusLink" style="display:block;width:142px;height:36px;object-fit:contain;"/></td>
<td align="right" valign="middle"><span style="font-family:Arial,Helvetica,sans-serif;font-size:11px;line-height:16px;font-weight:700;letter-spacing:0.08em;color:#1EA952;text-transform:uppercase;">CampusLink Alert</span></td>
</tr></table>
</td></tr>
<tr><td style="padding-top:32px;padding-right:32px;padding-bottom:32px;padding-left:32px;">
<p style="margin-top:0;margin-right:0;margin-bottom:14px;margin-left:0;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:24px;color:#526072;">Hi ${name},</p>
<h1 style="margin-top:0;margin-right:0;margin-bottom:14px;margin-left:0;font-family:Arial,Helvetica,sans-serif;font-size:26px;line-height:34px;color:#082D6A;font-weight:800;">${title}</h1>
<p style="margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:25px;color:#344054;">${body}</p>
${cta}
</td></tr>
<tr><td bgcolor="#f8fafc" style="padding-top:20px;padding-right:32px;padding-bottom:22px;padding-left:32px;background-color:#f8fafc;border-top:1px solid #edf1f5;">
<p style="margin-top:0;margin-right:0;margin-bottom:6px;margin-left:0;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:19px;color:#667085;">This message was sent by CampusLink to help you complete or manage your account.</p>
<p style="margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:19px;color:#98a2b3;">CampusLink · Trusted campus commerce</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>`

  const text = [
    `Hi ${input.recipientName?.trim() || 'there'},`,
    '',
    input.title,
    input.body,
    ctaUrl && input.ctaLabel ? `${input.ctaLabel}: ${ctaUrl}` : '',
    '',
    'CampusLink · Trusted campus commerce',
  ].filter(Boolean).join('\n')

  return { html, text }
}

export async function sendCampusLinkEmail(input: CampusLinkEmail): Promise<CampusLinkEmailResult> {
  const apiKey = process.env.RESEND_API_KEY
  if (!apiKey) {
    return { ok: false, reason: 'not_configured', error: 'RESEND_API_KEY is not configured on the server.' }
  }

  try {
    const templateId = process.env.RESEND_ALERT_TEMPLATE_ID || 'campuslink-admin-alert-v1'
    const response = await fetch('https://api.resend.com/emails', {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${apiKey}`,
        'Content-Type': 'application/json',
        'Idempotency-Key': input.idempotencyKey.slice(0, 256),
      },
      body: JSON.stringify({
        from: FROM,
        to: [input.to],
        template: {
          id: templateId,
          variables: {
            RECIPIENT_NAME: input.recipientName?.trim() || 'there',
            ALERT_SUBJECT: input.subject,
            ALERT_TITLE: input.title,
            ALERT_BODY: input.body,
            CTA_LABEL: input.ctaLabel || 'Open CampusLink',
            CTA_URL: absoluteUrl(input.ctaUrl) || 'https://campuslink.name.ng',
          },
        },
        tags: [
          { name: 'product', value: 'campuslink' },
          { name: 'channel', value: 'admin-alert' },
        ],
      }),
      cache: 'no-store',
    })

    const payload = await response.json().catch(() => ({})) as { id?: string; message?: string; error?: { message?: string } }
    if (!response.ok) {
      return { ok: false, reason: 'send_failed', error: payload.error?.message || payload.message || `Resend returned HTTP ${response.status}` }
    }

    return { ok: true, id: payload.id || null }
  } catch (error) {
    return { ok: false, reason: 'send_failed', error: error instanceof Error ? error.message : 'Unknown email delivery error' }
  }
}
