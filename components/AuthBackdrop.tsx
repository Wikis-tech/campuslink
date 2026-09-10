export function AuthBackdrop() {
  return (
    <div className="auth-backdrop" aria-hidden="true">
      <div className="auth-backdrop-slide auth-backdrop-slide-one" />
      <div className="auth-backdrop-slide auth-backdrop-slide-two" />
      <div className="auth-backdrop-slide auth-backdrop-slide-three" />
      <div className="auth-backdrop-overlay" />
      <div className="auth-backdrop-noise" />
    </div>
  )
}
