<?php defined('CAMPUSLINK') or die();
$pageTitle = 'My Profile';
$nameParts = explode(' ', $user['full_name'], 2);
$firstName = $nameParts[0] ?? '';
$lastName  = $nameParts[1] ?? '';

function lucide_icon(string $path, int $size = 20, string $color = 'currentColor', string $extra_style = ''): string {
    return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'"
                 viewBox="0 0 24 24" fill="none" stroke="'.$color.'"
                 stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                 style="display:inline-block;vertical-align:middle;flex-shrink:0;'.$extra_style.'">'.$path.'</svg>';
}
?>
<style>
.profile-grid{display:grid;grid-template-columns:1fr 2fr;gap:1.25rem;}
@media(max-width:700px){.profile-grid{grid-template-columns:1fr;}}
.profile-card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;}
.profile-card-head{padding:0.85rem 1.1rem;border-bottom:1px solid #e2e8f0;}
.profile-card-head h3{font-size:0.88rem;font-weight:800;color:#1e293b;margin:0;
                       display:flex;align-items:center;gap:0.4rem;}
.profile-card-body{padding:1.25rem;}
.profile-avatar{width:80px;height:80px;border-radius:50%;
background:linear-gradient(135deg,#1a56db,#0e9f6e);
display:flex;align-items:center;justify-content:center;
color:#fff;font-weight:900;font-size:1.8rem;margin:0 auto 1rem;}
.profile-name{text-align:center;font-size:1rem;font-weight:800;
color:#1e293b;margin-bottom:0.2rem;}
.profile-email{text-align:center;font-size:0.78rem;color:#64748b;margin-bottom:1rem;}
.profile-badge{display:inline-flex;align-items:center;gap:0.3rem;
padding:3px 10px;border-radius:20px;font-size:0.72rem;font-weight:700;
background:#dcfce7;color:#166534;}
.pf-row{display:flex;gap:0.5rem;padding:0.6rem 0;border-bottom:1px solid #f1f5f9;
font-size:0.82rem;align-items:center;}
.pf-row:last-child{border-bottom:none;}
.pf-label{width:80px;flex-shrink:0;color:#64748b;font-weight:600;}
.pf-val{color:#1e293b;flex:1;word-break:break-word;}
.form-group{margin-bottom:1rem;}
.form-group label{display:block;font-size:0.78rem;font-weight:700;
color:#374151;margin-bottom:0.35rem;}
.form-group input{width:100%;padding:0.65rem 0.9rem;border:1.5px solid #e2e8f0;
border-radius:9px;font-size:0.875rem;outline:none;
transition:border 0.2s;box-sizing:border-box;}
.form-group input:focus{border-color:#1a56db;box-shadow:0 0 0 3px rgba(26,86,219,0.08);}
.form-group input:disabled{background:#f8fafc;color:#94a3b8;}
.form-group .hint{font-size:0.7rem;color:#94a3b8;margin-top:0.25rem;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;}
@media(max-width:400px){.form-row{grid-template-columns:1fr;}}
.save-btn{width:100%;padding:0.75rem;background:linear-gradient(135deg,#1a56db,#0e9f6e);
color:#fff;border:none;border-radius:10px;font-weight:800;font-size:0.9rem;
cursor:pointer;transition:opacity 0.2s;display:flex;align-items:center;
justify-content:center;gap:0.45rem;}
.save-btn:hover{opacity:0.88;}
</style>

<div style="margin-bottom:1.25rem;display:flex;align-items:center;gap:0.45rem;">
    <!-- 👤 → User -->
    <?= lucide_icon('<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>', 22, '#1e293b') ?>
    <div>
        <h1 style="font-size:1.1rem;font-weight:900;color:#1e293b;margin:0 0 0.25rem;">
            My Profile
        </h1>
        <p style="font-size:0.82rem;color:#64748b;margin:0;">
            Manage your account information
        </p>
    </div>
</div>

<div class="profile-grid">
    <!-- Left: profile summary -->
    <div>
        <div class="profile-card">
            <div class="profile-card-head"><h3>Account Summary</h3></div>
            <div class="profile-card-body">
                <div class="profile-avatar">
                    <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                </div>
                <div class="profile-name"><?= e($user['full_name']) ?></div>
                <div class="profile-email">
                    <?= e($user['personal_email'] ?? $user['school_email'] ?? '') ?>
                </div>
                <div style="text-align:center;margin-bottom:1rem;">
                    <!-- ✓ Verified → ShieldCheck -->
                    <span class="profile-badge">
                        <?= lucide_icon('<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>', 13, '#166534') ?>
                        Verified Student
                    </span>
                </div>

                <!-- 🎓 → GraduationCap -->
                <div class="pf-row">
                    <span class="pf-label" style="display:flex;align-items:center;gap:0.3rem;">
                        <?= lucide_icon('<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>', 13, '#64748b') ?>
                        Matric
                    </span>
                    <span class="pf-val"><?= e($user['matric_number'] ?? 'Not set') ?></span>
                </div>
                <!-- 🏫 → Building -->
                <div class="pf-row">
                    <span class="pf-label" style="display:flex;align-items:center;gap:0.3rem;">
                        <?= lucide_icon('<rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><line x1="9" y1="22" x2="9" y2="12"/><line x1="15" y1="22" x2="15" y2="12"/><line x1="4" y1="7" x2="20" y2="7"/><line x1="4" y1="12" x2="20" y2="12"/>', 13, '#64748b') ?>
                        School
                    </span>
                    <span class="pf-val"><?= e($user['school_email'] ?? '') ?></span>
                </div>
                <!-- 📱 → Smartphone -->
                <div class="pf-row">
                    <span class="pf-label" style="display:flex;align-items:center;gap:0.3rem;">
                        <?= lucide_icon('<rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/>', 13, '#64748b') ?>
                        Phone
                    </span>
                    <span class="pf-val"><?= e($user['phone'] ?? 'Not set') ?></span>
                </div>
                <!-- 📅 → Calendar -->
                <div class="pf-row">
                    <span class="pf-label" style="display:flex;align-items:center;gap:0.3rem;">
                        <?= lucide_icon('<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>', 13, '#64748b') ?>
                        Joined
                    </span>
                    <span class="pf-val">
                        <?= date('d M Y', strtotime($user['created_at'])) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: edit form -->
    <div>
        <div class="profile-card">
            <div class="profile-card-head">
                <!-- ✏️ → PenLine -->
                <h3>
                    <?= lucide_icon('<path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/>', 15, 'currentColor') ?>
                    Edit Profile
                </h3>
            </div>
            <div class="profile-card-body">
                <form method="POST" action="<?= SITE_URL ?>/user/profile">
                    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

                    <div class="form-row">
                        <div class="form-group">
                            <label>First Name *</label>
                            <input type="text" name="first_name"
                                   value="<?= e($firstName) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Last Name *</label>
                            <input type="text" name="last_name"
                                   value="<?= e($lastName) ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Phone Number *</label>
                        <input type="tel" name="phone"
                               value="<?= e($user['phone'] ?? '') ?>"
                               placeholder="08012345678" required>
                    </div>

                    <div class="form-group">
                        <label>Gmail Address</label>
                        <input type="email"
                               value="<?= e($user['personal_email'] ?? '') ?>"
                               disabled>
                        <div class="hint">
                            Contact support to change your email address
                        </div>
                    </div>

                    <div class="form-group">
                        <label>School Email</label>
                        <input type="email"
                               value="<?= e($user['school_email'] ?? '') ?>"
                               disabled>
                        <div class="hint">School email cannot be changed</div>
                    </div>

                    <div class="form-group">
                        <label>Matric Number</label>
                        <input type="text"
                               value="<?= e($user['matric_number'] ?? '') ?>"