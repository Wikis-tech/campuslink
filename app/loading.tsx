export default function Loading() {
  return (
    <div className="campuslink-loading-overlay campuslink-route-loading" role="status" aria-live="polite" aria-label="Loading page">
      <div className="campuslink-loading-card">
        <div className="campuslink-loading-spinner" aria-hidden="true" />
        <div>
          <strong>CampusLink</strong>
          <span>Loading…</span>
        </div>
      </div>
    </div>
  )
}
