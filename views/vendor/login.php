<?php defined('CAMPUSLINK') or die(); ?>

<style>
/* ── Page wrapper ───────────────────────────────────── */
.vl-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #064e3b 0%, #065f46 40%, #0e9f6e 100%);
    padding: 1.5rem 1rem;
    position: relative;
    overflow: hidden;
}

/* Animated background blobs */
.vl-blob {
    position: absolute;
    border-radius: 50%;
    opacity: 0.08;
    animation: blobFloat 8s ease-in-out infinite;
}
.vl-blob-1 {
    width: 400px; height: 400px;
    background: #fff;
    top: -100px; left: -100px;
    animation-delay: 0s;
}
.vl-blob-2 {
    width: 300px; height: 300px;
    background: #34d399;
    bottom: -80px; right: -80px;
    animation-delay: 3s;
}
.vl-blob-3 {
    width: 200px; height: 200px;
    background: #6ee7b7;
    top: 50%; left: 60%;
    animation-delay: 1.5s;
}
@keyframes blobFloat {
    0%, 100% { transform: translate(0, 0) scale(1); }
    33%       { transform: translate(15px, -20px) scale(1.05); }
    66%       { transform: translate(-10px, 10px) scale(0.97); }
}

/* ── Card ───────────────────────────────────────────── */
.vl-card {
    background: #fff;
    border-radius: 24px;
    width: 100%;
    max-width: 420px;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(0,0,0,0.25);
    position: relative;
    z-index: 1;
    animation: cardSlideUp 0.5s cubic-bezier(0.16,1,0.3,1) both;
}
@keyframes cardSlideUp {
    from { opacity: 0; transform: translateY(30px) scale(0.97); }
    to   { opacity: 1; transform: translateY(0)    scale(1);    }
}

/* ── Header ─────────────────────────────────────────── */
.vl-header {
    background: linear-gradient(135deg, #064e3b, #0e9f6e);
    padding: 2.25rem 2rem 1.75rem;
    text-align: center;
    position: relative;
}
.vl-icon {
    width: 64px; height: 64px;
    background: rgba(255,255,255,0.15);
    border-radius: 18px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1rem;
    border: 2px solid rgba(255,255,255,0.2);
    animation: iconPop 0.6s cubic-bezier(0.34,1.56,0.64,1) 0.2s both;
    color: #fff;
}
.vl-icon svg { width: 32px; height: 32px; }
@keyframes iconPop {
    from { opacity: 0; transform: scale(0.5); }
    to   { opacity: 1; transform: scale(1);   }
}
.vl-logo {
    font-size: 1rem;
    font-weight: 900;
    color: rgba(255,255,255,0.9);
    margin-bottom: 0.3rem;
    letter-spacing: 0.02em;
}
.vl-logo span { color: #6ee7b7; }
.vl-title {
    font-size: 1.35rem;
    font-weight: 900;
    color: #fff;
    margin: 0 0 0.3rem;
    letter-spacing: -0.02em;
}
.vl-subtitle {
    font-size: 0.82rem;
    color: rgba(255,255,255,0.75);
    margin: 0;
}

/* ── Body ───────────────────────────────────────────── */
.vl-body { padding: 1.75rem 2rem; }
@media(max-width:480px) { .vl-body { padding: 1.25rem 1.25rem; } }

/* Flash messages */
.vl-flash-error {
    background: #fef2f2;
    border: 1.5px solid #fecaca;
    color: #dc2626;
    padding: 0.75rem 1rem;
    border-radius: 10px;
    font-size: 0.82rem;
    font-weight: 600;
    margin-bottom: 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    animation: shakeIn 0.4s ease both;
}
.vl-flash-success {
    background: #f0fdf4;
    border: 1.5px solid #bbf7d0;
    color: #166534;
    padding: 0.75rem 1rem;
    border-radius: 10px;
    font-size: 0.82rem;
    font-weight: 600;
    margin-bottom: 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
@keyframes shakeIn {
    0%  { transform: translateX(-8px); opacity: 0; }
    40% { transform: translateX(6px);  }
    70% { transform: translateX(-4px); }
    100%{ transform: translateX(0);    opacity: 1; }
}

/* Form fields */
.vl-group {
    margin-bottom: 1.1rem;
    animation: fadeUp 0.4s ease both;
}
.vl-group:nth-child(2) { animation-delay: 0.05s; }
.vl-group:nth-child(3) { animation-delay: 0.10s; }
.vl-group:nth-child(4) { animation-delay: 0.15s; }
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0);    }
}
.vl-group label {
    display: block;
    font-size: 0.76rem;
    font-weight: 700;
    color: #374151;
    margin-bottom: 0.35rem;
    letter-spacing: 0.02em;
}
.vl-input-wrap { position: relative; }
.vl-input-icon {
    position: absolute;
    left: 0.9rem;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    display: flex;
    align-items: center;
    color: #94a3b8;
}
.vl-input-icon svg { width: 16px; height: 16px; }
.vl-input {
    width: 100%;
    padding: 0.75rem 0.9rem 0.75rem 2.5rem;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.9rem;
    outline: none;
    transition: border 0.2s, box-shadow 0.2s, background 0.2s;
    box-sizing: border-box;
    background: #f8fafc;
    color: #1e293b;
    font-family: inherit;
}
.vl-input:focus {
    border-color: #0e9f6e;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(14,159,110,0.1);
}
.vl-pw-toggle {
    position: absolute;
    right: 0.85rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #94a3b8;
    padding: 0;
    transition: color 0.2s;
    display: flex;
    align-items: center;
}
.vl-pw-toggle svg { width: 16px; height: 16px; }
.vl-pw-toggle:hover { color: #0e9f6e; }

.vl-forgot {
    display: block;
    text-align: right;
    font-size: 0.75rem;
    color: #0e9f6e;
    font-weight: 700;
    text-decoration: none;
    margin-top: 0.4rem;
    transition: opacity 0.2s;
}
.vl-forgot:hover { opacity: 0.75; }

/* Submit button */
.vl-btn {
    width: 100%;
    padding: 0.85rem;
    background: linear-gradient(135deg, #065f46, #0e9f6e);
    color: #fff;
    border: none;
    border-radius: 11px;
    font-size: 0.95rem;
    font-weight: 800;
    cursor: pointer;
    margin-top: 0.75rem;
    transition: opacity 0.2s, transform 0.15s, box-shadow 0.2s;
    box-shadow: 0 4px 16px rgba(14,159,110,0.35);
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}
.vl-btn::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.15), transparent);
    opacity: 0;
    transition: opacity 0.2s;
}
.vl-btn:hover {
    opacity: 0.92;
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(14,159,110,0.4);
}
.vl-btn:hover::after { opacity: 1; }
.vl-btn:active { transform: translateY(0); }

/* Loading state */
.vl-btn.loading {
    pointer-events: none;
    opacity: 0.8;
}
.vl-btn.loading::before {
    content: '';
    display: inline-block;
    width: 14px; height: 14px;
    border: 2px solid rgba(255,255,255,0.4);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
    margin-right: 0.5rem;
    vertical-align: middle;
}
@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Divider */
.vl-divider {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin: 1.25rem 0;
    color: #94a3b8;
    font-size: 0.75rem;
}
.vl-divider::before,
.vl-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #e2e8f0;
}

/* Vendor login alt */
.vl-alt-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    padding: 0.7rem;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.85rem;
    font-weight: 700;
    color: #374151;
    text-decoration: none;
    transition: all 0.2s;
    background: #f8fafc;
}
.vl-alt-btn:hover {
    border-color: #1a56db;
    color: #1a56db;
    background: #eff6ff;
}
.vl-alt-btn svg { width: 16px; height: 16px; }

/* ── Footer ─────────────────────────────────────────── */
.vl-footer {
    padding: 1rem 2rem 1.5rem;
    text-align: center;
    font-size: 0.8rem;
    color: #64748b;
    border-top: 1px solid #f1f5f9;
}
.vl-footer a {
    color: #0e9f6e;
    font-weight: 700;
    text-decoration: none;
    transition: opacity 0.2s;
}
.vl-footer a:hover { opacity: 0.75; }
.vl-footer-links {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.vl-footer-dot { color: #cbd5e1; }
</style>

<div class="vl-page">
    <!-- Animated blobs -->
    <div class="vl-blob vl-blob-1"></div>
    <div class="vl-blob vl-blob-2"></div>
    <div class="vl-blob vl-blob-3"></div>

    <div class="vl-card">

        <!-- Header -->
        <div class="vl-header">
            <div class="vl-icon">
                <i data-lucide="store"></i>
            </div>
            <div class="vl-logo">Campus<span>Link</span></div>
            <h1 class="vl-title">Vendor Sign In</h1>
            <p class="vl-subtitle"><?= e(SCHOOL_NAME) ?> · Business Dashboard</p>
        </div>

        <!-- Body -->
        <div class="vl-body">

            <!-- Flash messages -->
            <?php
            $flashes = Session::getAllFlash();
            $flashIcons = [
                'success' => 'check-circle',
                'error'   => 'alert-triangle',
                'warning' => 'alert-triangle',
                'info'    => 'info',
            ];
            foreach ($flashes as $type => $messages):
                if (!is_array($messages)) $messages = [$messages];
                foreach ($messages as $message):
                    if (empty($message)) continue;
                    $isError = ($type === 'error');
            ?>
            <div class="<?= $isError ? 'vl-flash-error' : 'vl-flash-success' ?>">
                <i data-lucide="<?= $flashIcons[$type] ?? 'info' ?>" style="width:16px;height:16px;flex-shrink:0;"></i>
                <?= e($message) ?>
            </div>
            <?php endforeach; ?>
            <?php endforeach; ?>

            <form method="POST"
                  action="<?= SITE_URL ?>/vendor/login"
                  id="loginForm"
                  novalidate>
                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

                <!-- Email -->
                <div class="vl-group">
                    <label for="email">Email Address</label>
                    <div class="vl-input-wrap">
                        <span class="vl-input-icon">
                            <i data-lucide="mail"></i>
                        </span>
                        <input type="email"
                               id="email"
                               name="email"
                               class="vl-input"
                               placeholder="your@email.com"
                               value="<?= e($_SESSION['form_old']['email'] ?? '') ?>"
                               required
                               autocomplete="email">
                    </div>
                </div>

                <!-- Password -->
                <div class="vl-group">
                    <label for="password">Password</label>
                    <div class="vl-input-wrap">
                        <span class="vl-input-icon">
                            <i data-lucide="lock"></i>
                        </span>
                        <input type="password"
                               id="password"
                               name="password"
                               class="vl-input"
                               placeholder="Your password"
                               required
                               autocomplete="current-password">
                        <button type="button"
                                class="vl-pw-toggle"
                                onclick="togglePw()"
                                id="pwToggle"
                                aria-label="Show password">
                            <i data-lucide="eye" id="pwToggleIcon"></i>
                        </button>
                    </div>
                    <a href="<?= SITE_URL ?>/forgot-password" class="vl-forgot">
                        Forgot password?
                    </a>
                </div>

                <button type="submit" class="vl-btn" id="submitBtn">
                    <i data-lucide="log-in" style="width:18px;height:18px;"></i>
                    Sign In to Dashboard
                </button>
            </form>

            <div class="vl-divider">or</div>

            <a href="<?= SITE_URL ?>/login" class="vl-alt-btn">
                <i data-lucide="graduation-cap"></i> Student Login
            </a>

        </div>

        <!-- Footer -->
        <div class="vl-footer">
            <div class="vl-footer-links">
                <span>Not a vendor yet?</span>
                <a href="<?= SITE_URL ?>/vendor/register">Register free →</a>
                <span class="vl-footer-dot">·</span>
                <a href="<?= SITE_URL ?>">Back to Home</a>
            </div>
        </div>

    </div>
</div>

<script>
function togglePw() {
    var input = document.getElementById('password');
    var icon  = document.getElementById('pwToggleIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.setAttribute('data-lucide', 'eye-off');
    } else {
        input.type = 'password';
        icon.setAttribute('data-lucide', 'eye');
    }
    if (window.lucide) lucide.createIcons();
}

document.getElementById('loginForm').addEventListener('submit', function() {
    var btn = document.getElementById('submitBtn');
    btn.classList.add('loading');
    btn.innerHTML = ' Signing in...';
});
</script>