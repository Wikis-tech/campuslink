<?php defined('CAMPUSLINK') or die(); ?>

<style>
/* Lucide icon sizing */
.lucide{width:15px;height:15px;stroke:currentColor;stroke-width:2;fill:none;stroke-linecap:round;stroke-linejoin:round;vertical-align:middle;flex-shrink:0;}
.lucide-lg{width:28px;height:28px;}
.lucide-md{width:18px;height:18px;}

.vreg-page{min-height:100vh;background:linear-gradient(135deg,#0b3d91 0%,#1a56db 50%,#0e9f6e 100%);padding:2rem 1rem;}
.vreg-card{background:#fff;border-radius:20px;max-width:680px;margin:0 auto;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.2);}
.vreg-header{background:linear-gradient(135deg,#0b3d91,#1a56db);padding:1.75rem 2rem;color:#fff;}
.vreg-header h1{font-size:1.3rem;font-weight:900;margin:0 0 0.25rem;display:flex;align-items:center;gap:0.5rem;}
.vreg-header p{font-size:0.82rem;opacity:0.85;margin:0;}
.vreg-steps{display:flex;gap:0.4rem;margin-top:1.25rem;}
.vreg-step{flex:1;height:4px;border-radius:2px;background:rgba(255,255,255,0.25);}
.vreg-step.done{background:#34d399;}
.vreg-step.active{background:#fff;}
.vreg-body{padding:1.75rem 2rem;}
@media(max-width:600px){.vreg-body{padding:1.25rem 1rem;}}
.vsection{margin-bottom:1.5rem;padding-bottom:1.5rem;border-bottom:1px solid #f1f5f9;}
.vsection:last-child{border-bottom:none;}
.vsection-title{font-size:0.78rem;font-weight:800;text-transform:uppercase;letter-spacing:0.07em;color:#1a56db;margin-bottom:1rem;display:flex;align-items:center;gap:0.4rem;}
.vrow{display:grid;grid-template-columns:1fr 1fr;gap:0.85rem;}
@media(max-width:480px){.vrow{grid-template-columns:1fr;}}
.vfg{margin-bottom:0.85rem;}
.vfg label{display:block;font-size:0.75rem;font-weight:700;color:#374151;margin-bottom:0.3rem;}
.vfg input,.vfg select,.vfg textarea{width:100%;padding:0.65rem 0.9rem;border:1.5px solid #e2e8f0;border-radius:9px;font-size:0.875rem;outline:none;transition:border 0.2s,box-shadow 0.2s;box-sizing:border-box;font-family:inherit;background:#fff;color:#1e293b;}
.vfg input:focus,.vfg select:focus,.vfg textarea:focus{border-color:#1a56db;box-shadow:0 0 0 3px rgba(26,86,219,0.08);}
.vfg textarea{resize:vertical;min-height:100px;}
.vfg .hint{font-size:0.7rem;color:#94a3b8;margin-top:0.25rem;}
.vfg .req{color:#dc2626;}
.pw-wrap{position:relative;}
.pw-wrap input{padding-right:2.5rem;}
.pw-eye{position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;padding:0;display:flex;align-items:center;}
.pw-eye:hover{color:#374151;}

/* Plan cards */
.plan-cards{display:grid;grid-template-columns:1fr 1fr 1fr;gap:0.75rem;margin-top:0.5rem;}
@media(max-width:500px){.plan-cards{grid-template-columns:1fr;}}
.plan-card{border:2px solid #e2e8f0;border-radius:12px;padding:1rem;cursor:pointer;transition:all 0.2s;text-align:center;position:relative;}
.plan-card:hover{border-color:#1a56db;background:#f8faff;}
.plan-card.selected{border-color:#1a56db;background:#eff6ff;box-shadow:0 0 0 3px rgba(26,86,219,0.12);}
.plan-card.popular{border-color:#f59e0b;}
.plan-card.popular.selected{border-color:#1a56db;}
.plan-card .popular-tag{position:absolute;top:-10px;left:50%;transform:translateX(-50%);background:#f59e0b;color:#fff;font-size:0.62rem;font-weight:800;padding:2px 10px;border-radius:20px;white-space:nowrap;}
.plan-name{font-size:0.85rem;font-weight:800;color:#1e293b;margin-bottom:0.3rem;display:flex;align-items:center;justify-content:center;gap:0.35rem;}
.plan-price{font-size:1.2rem;font-weight:900;color:#1a56db;line-height:1;}
.plan-price small{font-size:0.65rem;font-weight:600;color:#64748b;display:block;margin-top:0.15rem;}
.plan-features{margin-top:0.6rem;text-align:left;}
.plan-features li{font-size:0.7rem;color:#374151;list-style:none;padding:0;display:flex;align-items:center;gap:0.35rem;margin-bottom:0.2rem;}
.plan-features li svg{color:#0e9f6e;flex-shrink:0;}

/* File upload */
.file-drop{border:2px dashed #e2e8f0;border-radius:10px;padding:1.25rem;text-align:center;cursor:pointer;transition:border 0.2s,background 0.2s;}
.file-drop:hover{border-color:#1a56db;background:#f8faff;}
.file-drop input{display:none;}
.file-drop .fd-icon{margin-bottom:0.35rem;color:#94a3b8;display:flex;justify-content:center;}
.file-drop .fd-text{font-size:0.78rem;font-weight:700;color:#374151;}
.file-drop .fd-hint{font-size:0.68rem;color:#94a3b8;margin-top:0.2rem;}
.file-name{font-size:0.75rem;color:#0e9f6e;font-weight:700;margin-top:0.4rem;display:none;justify-content:center;align-items:center;gap:0.3rem;}

/* Terms */
.term-check{display:flex;gap:0.6rem;align-items:flex-start;margin-bottom:0.6rem;}
.term-check input[type=checkbox]{margin-top:3px;flex-shrink:0;width:15px;height:15px;}
.term-check label{font-size:0.78rem;color:#374151;line-height:1.5;}
.term-check label a{color:#1a56db;font-weight:700;text-decoration:none;}

/* Submit */
.vreg-submit{width:100%;padding:0.9rem;background:linear-gradient(135deg,#1a56db,#0e9f6e);color:#fff;border:none;border-radius:11px;font-size:0.95rem;font-weight:800;cursor:pointer;transition:opacity 0.2s,transform 0.15s;margin-top:0.5rem;display:flex;align-items:center;justify-content:center;gap:0.5rem;}
.vreg-submit:hover{opacity:0.9;transform:translateY(-1px);}

/* Flash */
.flash-error{background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:0.75rem 1rem;border-radius:9px;font-size:0.82rem;margin-bottom:1rem;font-weight:600;}
.flash-success{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:0.75rem 1rem;border-radius:9px;font-size:0.82rem;margin-bottom:1rem;font-weight:600;}

.vreg-footer{padding:1rem 2rem;background:#f8fafc;border-top:1px solid #e2e8f0;text-align:center;font-size:0.78rem;color:#64748b;}
.vreg-footer a{color:#1a56db;font-weight:700;text-decoration:none;}

/* Warning notice */
.vreg-notice{background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:0.85rem 1rem;margin-bottom:1rem;font-size:0.75rem;color:#92400e;display:flex;align-items:flex-start;gap:0.5rem;}
</style>

<!-- Lucide Icons CDN -->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

<div class="vreg-page">
<div class="vreg-card">

    <!-- Header -->
    <div class="vreg-header">
        <div style="font-size:0.75rem;opacity:0.75;margin-bottom:0.4rem;">
            <a href="<?= SITE_URL ?>/vendor/register"
               style="color:rgba(255,255,255,0.7);text-decoration:none;display:inline-flex;align-items:center;gap:0.3rem;">
                <i data-lucide="arrow-left" style="width:13px;height:13px;"></i> Change Type
            </a>
        </div>
        <h1>
            <i data-lucide="graduation-cap" class="lucide-md" style="color:#34d399;"></i>
            Student Vendor Registration
        </h1>
        <p><?= e(SCHOOL_NAME) ?> &middot; Verified Campus Directory</p>
        <div class="vreg-steps">
            <div class="vreg-step done"></div>
            <div class="vreg-step active"></div>
            <div class="vreg-step"></div>
        </div>
    </div>

    <div class="vreg-body">
        <?php require VIEWS_PATH . '/partials/flash.php'; ?>

        <form method="POST"
              action="<?= SITE_URL ?>/vendor/register?type=student"
              enctype="multipart/form-data"
              novalidate>
            <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
            <input type="hidden" name="vendor_type" value="student">

            <!-- Personal Info -->
            <div class="vsection">
                <div class="vsection-title">
                    <i data-lucide="user" class="lucide"></i> Personal Information
                </div>
                <div class="vrow">
                    <div class="vfg">
                        <label>Full Name <span class="req">*</span></label>
                        <input type="text" name="full_name"
                               placeholder="Your full name"
                               value="<?= e($_SESSION['form_old']['full_name'] ?? '') ?>"
                               required>
                    </div>
                    <div class="vfg">
                        <label>Phone Number <span class="req">*</span></label>
                        <input type="tel" name="phone"
                               placeholder="08012345678"
                               value="<?= e($_SESSION['form_old']['phone'] ?? '') ?>"
                               required>
                    </div>
                </div>
                <div class="vrow">
                    <div class="vfg">
                        <label>School Email <span class="req">*</span></label>
                        <input type="email" name="school_email"
                               placeholder="you@<?= e(SCHOOL_EMAIL_DOMAIN) ?>"
                               value="<?= e($_SESSION['form_old']['school_email'] ?? '') ?>"
                               required>
                        <div class="hint">Must be your official school email</div>
                    </div>
                    <div class="vfg">
                        <label>Matric Number <span class="req">*</span></label>
                        <input type="text" name="matric_number"
                               placeholder="UAT/2021/001"
                               value="<?= e($_SESSION['form_old']['matric_number'] ?? '') ?>"
                               required>
                    </div>
                </div>
            </div>

            <!-- Business Info -->
            <div class="vsection">
                <div class="vsection-title">
                    <i data-lucide="store" class="lucide"></i> Business Information
                </div>
                <div class="vfg">
                    <label>Business / Brand Name <span class="req">*</span></label>
                    <input type="text" name="business_name"
                           placeholder="What do you call your business?"
                           value="<?= e($_SESSION['form_old']['business_name'] ?? '') ?>"
                           required>
                </div>
                <div class="vrow">
                    <div class="vfg">
                        <label>Category <span class="req">*</span></label>
                        <select name="category_id" required>
                            <option value="">Select category</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>"
                                <?= (($_SESSION['form_old']['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="vfg">
                        <label>Price Range</label>
                        <input type="text" name="price_range"
                               placeholder="e.g. &#8358;500 &#8211; &#8358;5,000"
                               value="<?= e($_SESSION['form_old']['price_range'] ?? '') ?>">
                    </div>
                </div>
                <div class="vfg">
                    <label>Business Description <span class="req">*</span></label>
                    <textarea name="description"
                              placeholder="Describe your products or services (minimum 50 characters)..."
                              required><?= e($_SESSION['form_old']['description'] ?? '') ?></textarea>
                </div>
                <div class="vfg">
                    <label>WhatsApp Number</label>
                    <input type="tel" name="whatsapp_number"
                           placeholder="08012345678"
                           value="<?= e($_SESSION['form_old']['whatsapp_number'] ?? '') ?>">
                    <div class="hint">Students will contact you via this number</div>
                </div>
            </div>

            <!-- Logo Upload -->
            <div class="vsection">
                <div class="vsection-title">
                    <i data-lucide="image" class="lucide"></i> Business Logo
                    <span style="font-size:0.7rem;font-weight:600;color:#94a3b8;text-transform:none;letter-spacing:0;">(Optional)</span>
                </div>
                <div class="file-drop" onclick="document.getElementById('logo').click()">
                    <input type="file" id="logo" name="logo"
                           accept="image/jpeg,image/png,image/webp"
                           onchange="showFileName(this,'logo-name')">
                    <div class="fd-icon">
                        <i data-lucide="upload-cloud" style="width:32px;height:32px;"></i>
                    </div>
                    <div class="fd-text">Click to upload your logo</div>
                    <div class="fd-hint">JPG, PNG or WebP &middot; Max 2MB</div>
                    <div class="file-name" id="logo-name"></div>
                </div>
            </div>

            <!-- Plan Selection -->
            <div class="vsection">
                <div class="vsection-title">
                    <i data-lucide="credit-card" class="lucide"></i> Choose Your Plan <span class="req">*</span>
                </div>
                <div class="plan-cards">
                    <?php
                    $planDefs = [
                        'basic'    => ['label'=>'Basic',   'icon'=>'package',       'price'=>2000,  'features'=>['Directory listing','WhatsApp & Call button','Student reviews','Category search']],
                        'premium'  => ['label'=>'Premium', 'icon'=>'star',          'price'=>5000,  'features'=>['Everything in Basic','Priority in search results','Premium badge on profile','Featured in category pages']],
                        'featured' => ['label'=>'Featured','icon'=>'zap',           'price'=>10000, 'features'=>['Everything in Premium','Homepage featured section','Top of all search results','Featured badge']],
                    ];
                    foreach ($studentPlans as $plan):
                        $type    = $plan['plan_type'];
                        $def     = $planDefs[$type] ?? ['label'=>ucfirst($type),'icon'=>'box','price'=>$plan['amount']/100,'features'=>[]];
                        $isFirst = ($type === 'basic');
                    ?>
                    <div class="plan-card <?= $type==='premium'?'popular':'' ?> <?= $isFirst?'selected':'' ?>"
                         data-plan="<?= e($type) ?>"
                         onclick="selectPlan('<?= e($type) ?>')">
                        <?php if ($type === 'premium'): ?>
                        <div class="popular-tag">POPULAR</div>
                        <?php endif; ?>
                        <div class="plan-name">
                            <i data-lucide="<?= e($def['icon']) ?>" style="width:14px;height:14px;color:#1a56db;"></i>
                            <?= e($def['label']) ?>
                        </div>
                        <div class="plan-price">
                            &#8358;<?= number_format($def['price']) ?>
                            <small>per semester (180 days)</small>
                        </div>
                        <ul class="plan-features">
                            <?php foreach ($def['features'] as $f): ?>
                            <li>
                                <i data-lucide="check" style="width:11px;height:11px;color:#0e9f6e;stroke-width:3;flex-shrink:0;"></i>
                                <?= e($f) ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="plan_type" id="selectedPlan" value="basic">
            </div>

            <!-- Password -->
            <div class="vsection">
                <div class="vsection-title">
                    <i data-lucide="lock" class="lucide"></i> Account Security
                </div>
                <div class="vrow">
                    <div class="vfg">
                        <label>Password <span class="req">*</span></label>
                        <div class="pw-wrap">
                            <input type="password" name="password" id="pw1"
                                   placeholder="Minimum 8 characters" required>
                            <button type="button" class="pw-eye"
                                    onclick="togglePw('pw1',this)">
                                <i data-lucide="eye" style="width:16px;height:16px;"></i>
                            </button>
                        </div>
                    </div>
                    <div class="vfg">
                        <label>Confirm Password <span class="req">*</span></label>
                        <div class="pw-wrap">
                            <input type="password" name="password_confirmation" id="pw2"
                                   placeholder="Repeat your password" required>
                            <button type="button" class="pw-eye"
                                    onclick="togglePw('pw2',this)">
                                <i data-lucide="eye" style="width:16px;height:16px;"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Terms -->
            <div class="vsection">
                <div class="vsection-title">
                    <i data-lucide="file-text" class="lucide"></i> Terms &amp; Agreements
                </div>
                <div class="term-check">
                    <input type="checkbox" name="terms_general" id="tg" required>
                    <label for="tg">I agree to the
                        <a href="<?= SITE_URL ?>/general-terms" target="_blank">General Terms</a>
                        and
                        <a href="<?= SITE_URL ?>/vendor-terms" target="_blank">Vendor Terms</a>
                        <span class="req">*</span>
                    </label>
                </div>
                <div class="term-check">
                    <input type="checkbox" name="terms_privacy" id="tp" required>
                    <label for="tp">I agree to the
                        <a href="<?= SITE_URL ?>/privacy-policy" target="_blank">Privacy Policy</a>
                        <span class="req">*</span>
                    </label>
                </div>
                <div class="term-check">
                    <input type="checkbox" name="terms_accurate" id="ta" required>
                    <label for="ta">I confirm all information provided is accurate and truthful
                        <span class="req">*</span>
                    </label>
                </div>
            </div>

            <div class="vreg-notice">
                <i data-lucide="alert-triangle" style="width:15px;height:15px;flex-shrink:0;margin-top:1px;"></i>
                <span>Your application will be reviewed by our admin team within <strong>24&#8211;48 hours</strong>.
                You will be notified by email once approved. Payment is made after approval.</span>
            </div>

            <button type="submit" class="vreg-submit">
                <i data-lucide="send" style="width:17px;height:17px;"></i>
                Submit Student Vendor Application
            </button>
        </form>
    </div>

    <div class="vreg-footer">
        Already registered? <a href="<?= SITE_URL ?>/vendor/login">Sign in to your dashboard</a>
        &nbsp;&middot;&nbsp;
        <a href="<?= SITE_URL ?>/vendor/register">Change vendor type</a>
    </div>

</div>
</div>

<script>
// Initialise all Lucide icons
lucide.createIcons();

function selectPlan(type) {
    document.querySelectorAll('.plan-card').forEach(c => c.classList.remove('selected'));
    document.querySelector('[data-plan="'+type+'"]').classList.add('selected');
    document.getElementById('selectedPlan').value = type;
}

function togglePw(id, btn) {
    var input = document.getElementById(id);
    var isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    // Swap icon
    var icon = btn.querySelector('i[data-lucide]');
    if (icon) {
        icon.setAttribute('data-lucide', isHidden ? 'eye-off' : 'eye');
        lucide.createIcons(); // re-render swapped icon
    }
}

function showFileName(input, nameId) {
    var el = document.getElementById(nameId);
    if (input.files && input.files[0]) {
        el.innerHTML = '<i data-lucide="check-circle" style="width:13px;height:13px;color:#0e9f6e;"></i> ' + input.files[0].name;
        el.style.display = 'flex';
        lucide.createIcons();
    }
}
</script>