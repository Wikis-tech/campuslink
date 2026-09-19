export function CampusLinkLoadingScreen({ label = 'Loading…' }: { label?: string }) {
  return (
    <div className="campuslink-loading-overlay campuslink-route-loading" role="status" aria-live="polite" aria-label={label}>
      <div className="campuslink-loading-spinner" aria-hidden="true" />
      <span className="campuslink-loading-sr">{label}</span>
    </div>
  )
}
