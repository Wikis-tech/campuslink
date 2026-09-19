import { ImageResponse } from 'next/og'

export const size = {
  width: 180,
  height: 180,
}

export const contentType = 'image/png'

export default function AppleIcon() {
  return new ImageResponse(
    (
      <div
        style={{
          width: '100%',
          height: '100%',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          background: 'linear-gradient(145deg, #0B3D91, #082D6A)',
          borderRadius: 38,
          position: 'relative',
        }}
      >
        <div
          style={{
            width: 92,
            height: 92,
            display: 'flex',
            position: 'relative',
          }}
        >
          <div
            style={{
              position: 'absolute',
              left: 4,
              top: 4,
              width: 76,
              height: 22,
              background: '#ffffff',
              borderRadius: 4,
            }}
          />
          <div
            style={{
              position: 'absolute',
              left: 4,
              top: 4,
              width: 22,
              height: 80,
              background: '#ffffff',
              borderRadius: 4,
            }}
          />
          <div
            style={{
              position: 'absolute',
              left: 4,
              top: 38,
              width: 58,
              height: 22,
              background: '#ffffff',
              borderRadius: 4,
            }}
          />
          <div
            style={{
              position: 'absolute',
              right: 0,
              bottom: 0,
              width: 34,
              height: 34,
              background: '#1EA952',
              borderRadius: 999,
            }}
          />
        </div>
      </div>
    ),
    size,
  )
}
