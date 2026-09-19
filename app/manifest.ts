import type { MetadataRoute } from 'next'

export default function manifest(): MetadataRoute.Manifest {
  return {
    id: '/',
    name: 'Campus Link',
    short_name: 'CampusLink',
    description: 'Discover trusted campus vendors, products and services around your school.',
    start_url: '/',
    scope: '/',
    display: 'standalone',
    orientation: 'any',
    background_color: '#f7faff',
    theme_color: '#0B3D91',
    lang: 'en',
    dir: 'ltr',
    categories: ['education', 'business', 'shopping', 'lifestyle'],
    icons: [
      {
        src: '/pwa-icon.svg',
        sizes: 'any',
        type: 'image/svg+xml',
        purpose: 'any',
      },
      {
        src: '/pwa-icon-maskable.svg',
        sizes: 'any',
        type: 'image/svg+xml',
        purpose: 'maskable',
      },
    ],
  }
}
