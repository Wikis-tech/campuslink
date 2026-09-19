const JPEG = [0xff, 0xd8, 0xff]
const PNG = [0x89, 0x50, 0x4e, 0x47, 0x0d, 0x0a, 0x1a, 0x0a]
const PDF = [0x25, 0x50, 0x44, 0x46, 0x2d]

function startsWith(bytes: Uint8Array, signature: number[]) {
  return signature.every((value, index) => bytes[index] === value)
}

export async function sniffUploadedFile(file: File): Promise<'jpg' | 'png' | 'webp' | 'pdf' | null> {
  const bytes = new Uint8Array(await file.slice(0, 16).arrayBuffer())
  if (startsWith(bytes, JPEG)) return 'jpg'
  if (startsWith(bytes, PNG)) return 'png'
  if (
    bytes.length >= 12 &&
    String.fromCharCode(...bytes.slice(0, 4)) === 'RIFF' &&
    String.fromCharCode(...bytes.slice(8, 12)) === 'WEBP'
  ) return 'webp'
  if (startsWith(bytes, PDF)) return 'pdf'
  return null
}

export async function assertSafeImageUpload(file: File) {
  const detected = await sniffUploadedFile(file)
  const expected = file.type === 'image/jpeg' ? 'jpg'
    : file.type === 'image/png' ? 'png'
      : file.type === 'image/webp' ? 'webp'
        : null
  if (!detected || !expected || detected !== expected) {
    throw new Error('The uploaded file content does not match a supported JPG, PNG or WEBP image.')
  }
  return detected
}

export async function assertSafeVerificationUpload(file: File) {
  const detected = await sniffUploadedFile(file)
  const expected = file.type === 'image/jpeg' ? 'jpg'
    : file.type === 'image/png' ? 'png'
      : file.type === 'image/webp' ? 'webp'
        : file.type === 'application/pdf' ? 'pdf'
          : null
  if (!detected || !expected || detected !== expected) {
    throw new Error('The uploaded file content does not match its declared PDF or image type.')
  }
  return detected
}
