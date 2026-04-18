<?php defined('CAMPUSLINK') or die(); ?>

<style>
.ve-page{min-height:100vh;display:flex;align-items:center;justify-content:center;
background:linear-gradient(135deg,#0b3d91 0%,#1a56db 60%,#0e9f6e 100%);padding:1.5rem;}
.ve-box{background:#fff;border-radius:20px;width:100%;max-width:440px;
overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.2);text-align:center;}
.ve-header{background:linear-gradient(135deg,#0b3d91,#1a56db);
padding:2rem;color:#fff;}
.ve-icon{font-size:3rem;margin-bottom:0.75rem;
animation:iconBounce 1s cubic-bezier(0.34,1.56,0.64,1) both;}
@keyframes iconBounce{
    from{opacity:0;transform:scale(0.3);}
    to{opacity:1;transform:scale(1);}
}
.ve-header h1{font-size:1.3rem;font-weight:900;margin:0 0 0.3rem;}
.ve-header p{font-size:0.82rem;opacity:0.85;margin:0;}
.ve-body{padding:2rem 1.75rem;}
.ve-email-box{background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:10px;
padding:0.85rem 1rem;margin:1rem 0;font-size:0.85rem;color:#166534;font-weight:700;}
.ve-steps{text-align:left;margin:1.25rem 0;}
.ve-step{display:flex;align-items:flex-start;gap:0.75rem;
padding:0.6rem 0;border-bottom:1px solid #f1f5f9;font-size:0.82rem;color:#374151;}
.ve-step:last-child{border-bottom:none;}
.ve-step-num{width:22px;height:22px;border-radius:50%;
background:linear-gradient(135deg,#1a56db,#0e9f6e);color:#fff;
display:flex;align-items:center;justify-content:center;
font-size:0.7rem;font-weight:900;flex-shrink:0;margin-top:1px;}
.ve-warning{background:#fffbeb;border:1px solid #fde68a;border-radius:9px;
padding:0.75rem;font-size:0.78rem;color:#92400e;margin-top:1rem;line-height:1.5;}
.ve-btn{display:block;width:100%;padding:0.75rem;margin-top:1rem;
background:linear-gradient(135deg,#1a56db,#0e9f6e);color:#fff;
border:none;border-radius:10px;font-weight:800;font-size:0.875rem;
text-decoration:none;text-align:center;cursor:pointer;transition:opacity 0.2s;}
.ve-btn:hover{opacity:0.88;}
.ve-btn-outline{background:#fff;color:#1a56db;border:1.5px solid #1a56db;margin-top:0.6rem;}
.ve-footer{padding:1rem;background:#f8fafc;border-top:1px solid #e2e8f0;
font-size:0.78rem;color:#64748b;}
.ve-footer a{color:#1a56db;font-weight:700;text-decoration:none;}

/* Invalid state */
.ve-invalid .ve-icon-wrap{color:#dc2626;}
.ve-invalid .ve-header{background:linear-gradient(135deg,#7f1d1d,#dc2626);}
</style>

<?php
$status = $status ?? 'pending';
$email  = $email  ?? Session::get('pending_verify_email', '');
?>

<div class="ve-page">
<div class="ve-box <?= $status === 'invalid' ? 've-invalid' : '' ?>">

    <?php if ($status === 'invalid'): ?>
    <!-- Invalid token -->
    <div class="ve-header">
        <div class="ve-icon">❌</div>
        <h1>Link Invalid or Expired</h1>
        <p>This verification link is no longer valid</p>
    </div>
    <div class="ve-body">
        <p style="color:#374151;font-size:0.875rem;line-height:1.6;margin-bottom:1rem;">
            This verification link has expired or has already been used.
            Verification links are valid for <strong>24 hours</strong>.
        </p>
        <a href="<?= SITE_URL ?>/register" class="ve-btn">
            🎓 Register Again
        </a>
        <a href="<?= SITE_URL ?>/login" class="ve-btn ve-btn-outline">
            Already verified? Log In
        </a>
    </div>

    <?php else: ?>
    <!-- Pending verification -->
    <div class="ve-header">
        <div class="ve-icon">📧</div>
        <h1>Check Your Email</h1>
        <p>One step left to activate your account</p>
    </div>
    <div class="ve-body">

        <?php
        $flashes = Session::getAllFlash();
        $icons = [
            'success' => '✅',
            'error'   => '❌',
            'warning' => '⚠️',
            'info'    => 'ℹ️',
        ];
        foreach ($flashes as $type => $messages):
            if (!is_array($messages)) $messages = [$messages];
            foreach ($messages as $message):
                if (empty($message)) continue;
        ?>
        <div style="padding:0.65rem 0.85rem;border-radius:9px;font-size:0.82rem;
                    font-weight:600;margin-bottom:1rem;
                    background:<?= $type==='error'?'#fef2f2':'#f0fdf4' ?>;
                    border:1px solid <?= $type==='error'?'#fecaca':'#bbf7d0' ?>;
                    color:<?= $type==='error'?'#dc2626':'#166534' ?>;">
            <?= $icons[$type] ?? 'ℹ️' ?> <?= e($message) ?>
        </div>
        <?php endforeach; ?>
        <?php endforeach; ?>

        <?php if ($email): ?>
        <div class="ve-email-box">
            📧 <?= e($email) ?>
        </div>
        <?php endif; ?>

        <p style="color:#374151;font-size:0.875rem;line-height:1.6;margin-bottom:1rem;">
            We sent a verification link to your email address.
            Click the link in that email to activate your CampusLink account.
        </p>

        <div class="ve-steps">
            <div class="ve-step">
                <div class="ve-step-num">1</div>
                <div>Open your email inbox (check spam/junk too)</div>
            </div>
            <div class="ve-step">
                <div class="ve-step-num">2</div>
                <div>Find the email from <strong>CampusLink</strong></div>
            </div>
            <div class="ve-step">
                <div class="ve-step-num">3</div>
                <div>Click the <strong>"Verify My Email Address"</strong> button</div>
            </div>
            <div class="ve-step">
                <div class="ve-step-num">4</div>
                <div>You will be redirected to login automatically</div>
            </div>
        </div>

        <div class="ve-warning">
            ⏰ The verification link expires in <strong>24 hours</strong>.
            If you do not verify within that time you will need to register again.
        </div>

        <a href="<?= SITE_URL ?>/login" class="ve-btn" style="margin-top:1.25rem;">
            Already verified? Log In →
        </a>
    </div>
    <?php endif; ?>

    <div class="ve-footer">
        Wrong email? <a href="<?= SITE_URL ?>/register">Register with correct email →</a>
    </div>

</div>
</div>
