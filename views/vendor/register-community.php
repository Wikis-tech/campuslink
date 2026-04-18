<?php defined('CAMPUSLINK') or die(); ?>

<style>
.vreg-page{min-height:100vh;background:linear-gradient(135deg,#064e3b 0%,#065f46 50%,#0e9f6e 100%);padding:2rem 1rem;}
.vreg-card{background:#fff;border-radius:20px;max-width:700px;margin:0 auto;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.2);}
.vreg-header{background:linear-gradient(135deg,#064e3b,#0e9f6e);padding:1.75rem 2rem;color:#fff;}
.vreg-header h1{font-size:1.3rem;font-weight:900;margin:0 0 0.25rem;display:flex;align-items:center;gap:0.5rem;}
.vreg-header p{font-size:0.82rem;opacity:0.85;margin:0;}
.vreg-steps{display:flex;gap:0.4rem;margin-top:1.25rem;}
.vreg-step{flex:1;height:4px;border-radius:2px;background:rgba(255,255,255,0.25);}
.vreg-step.done{background:#34d399;}
.vreg-step.active{background:#fff;}
.vreg-body{padding:1.75rem 2rem;}
@media(max-width:600px){.vreg-body{padding:1.25rem 1rem;}}
.vsection{margin-bottom:1.5rem;padding-bottom:1.5rem;border-bottom:1px solid #f1f5f9;}
.vsection:last-child{border-bottom:none;margin-bottom:0;}
.vsection-title{font-size:0.78rem;font-weight:800;text-transform:uppercase;letter-spacing:0.07em;color:#0e9f6e;margin-bottom:1rem;display:flex;align-items:center;gap:0.4rem;}
.vsection-title i{width:15px;height:15px;flex-shrink:0;}
.vrow{display:grid;grid-template-columns:1fr 1fr;gap:0.85rem;}
@media(max-width:480px){.vrow{grid-template-columns:1fr;}}
.vfg{margin-bottom:0.85rem;}
.vfg label{display:block;font-size:0.75rem;font-weight:700;color:#374151;margin-bottom:0.3rem;}
.vfg input,.vfg select,.vfg textarea{width:100%;padding:0.65rem 0.9rem;border:1.5px solid #e2e8f0;border-radius:9px;font-size:0.875rem;outline:none;transition:border 0.2s,box-shadow 0.2s;box-sizing:border-box;font-family:inherit;background:#fff;color:#1e293b;}
.vfg input:focus,.vfg select:focus,.vfg textarea:focus{border-color:#0e9f6e;box-shadow:0 0 0 3px rgba(14,159,110,0.08);}
.vfg textarea{resize:vertical;min-height:100px;}
.vfg .hint{font-size:0.7rem;color:#94a3b8;margin-top:0.25rem;}
.vfg .req{color:#dc2626;}
.pw-wrap{position:relative;}
.pw-wrap input{padding-right:2.5rem;}
.pw-eye{position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;padding:0;display:flex;align-items:center;justify-content:center;}
.pw-eye i{width:16px;height:16px;}

/* ID verification box */
.id-box{background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:12px;padding:1.25rem;margin-bottom:0.5rem;}
.id-box-title{font-size:0.8rem;font-weight:800;color:#065f46;margin-bottom:1rem;display:flex;align-items:center;gap:0.4rem;}
.id-box-title i{width:16px;height:16px;flex-shrink:0;}

/* Plan cards */
.plan-cards{display:grid;grid-template-columns:1fr 1fr 1fr;gap:0.75rem;margin-top:0.5rem;}
@media(max-width:500px){.plan-cards{grid-template-columns:1fr;}}
.plan-card{border:2px solid #e2e8f0;border-radius:12px;padding:1rem;cursor:pointer;transition:all 0.2s;text-align:center;position:relative;}
.plan-card:hover{border-color:#0e9f6e;background:#f0fdf4;}
.plan-card.selected{border-color:#0e9f6e;background:#f0fdf4;box-shadow:0 0 0 3px rgba(14,159,110,0.12);}
.plan-card.popular{border-color:#f59e0b;}
.plan-card.popular.selected{border-color:#0e9f6e;}
.plan-card .popular-tag{position:absolute;top:-10px;left:50%;transform:translateX(-50%);background:#f59e0b;color:#fff;font-size:0.62rem;font-weight:800;padding:2px 10px;border-radius:20px;white-space:nowrap;}
.plan-name{font-size:0.85rem;font-weight:800;color:#1e293b;margin-bottom:0.3rem;display:flex;align-items:center;justify-content:center;gap:0.3rem;}
.plan-name i{width:14px;height:14px;color:#f59e0b;fill:#f59e0b;}
.plan-price{font-size:1.2rem;font-weight:900;color:#0e9f6e;line-height:1;}
.plan-price small{font-size:0.65rem;font-weight:600;color:#64748b;display:block;margin-top:0.15rem;}
.plan-features{margin-top:0.6rem;text-align:left;}
.plan-features li{font-size:0.7rem;color:#374151;list-style:none;padding:0;display:flex;align-items:center;gap:0.35rem;margin-bottom:0.2rem;}
.plan-features li i{width:12px;height:12px;color:#0e9f6e;flex-shrink:0;}

/* File upload */
.file-drop{border:2px dashed #e2e8f0;border-radius:10px;padding:1.25rem;text-align:center;cursor:pointer;transition:border 0.2s,background 0.2s;}
.file-drop:hover{border-color:#0e9f6e;background:#f0fdf4;}
.file-drop input{display:none;}
.file-drop .fd-icon{display:flex;justify-content:center;margin-bottom:0.35rem;color:#94a3b8;}
.file-drop .fd-icon i{width:32px;height:32px;}
.file-drop .fd-text{font-size:0.78rem;font-weight:700;color:#374151;}
.file-drop .fd-hint{font-size:0.68rem;color:#94a3b8;margin-top:0.2rem;}
.file-name{font-size:0.75rem;color:#0e9f6e;font-weight:700;margin-top:0.4rem;display:none;align-items:center;justify-content:center;gap:0.3rem;}
.file-name i{width:13px;height:13px;}

/* Terms */
.term-check{display:flex;gap:0.6rem;align-items:flex-start;margin-bottom:0.6rem;}
.term-check input[type=checkbox]{margin-top:3px;flex-shrink:0;width:15px;height:15px;}
.term-check label{font-size:0.78rem;color:#374151;line-height:1.5;}
.term-check label a{color:#0e9f6e;font-weight:700;text-decoration:none;}

/* Notice box */
.review-notice{background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:0.85rem 1rem;margin-bottom:1rem;font-size:0.75rem;color:#065f46;display:flex;align-items:flex-start;gap:0.5rem;}
.review-notice i{width:15px;height:15px;flex-shrink:0;margin-top:1px;}

/* Submit */
.vreg-submit{width:100%;padding:0.9rem;background:linear-gradient(135deg,#065f46,#0e9f6e);color:#fff;border:none;border-radius:11px;font-size:0.95rem;font-weight:800;cursor:pointer;transition:opacity 0.2s,transform 0.15s;margin-top:0.5rem;display:flex;align-items:center;justify-content:center;gap:0.5rem;}
.vreg-submit i{width:18px;height:18px;}
.vreg-submit:hover{opacity:0.9;transform:translateY(-1px);}

/* Back link */
.back-link{display:inline-flex;align-items:center;gap:0.3rem;color:rgba(255,255,255,0.7);text-decoration:none;font-size:0.75rem;opacity:0.85;margin-bottom:0.4rem;}
.back-link i{width:13px;height:13px;}

.flash-error{background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:0.75rem 1rem;border-radius:9px;font-size:0.82rem;margin-bottom:1rem;font-weight:600;}
.flash-success{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:0.75rem 1rem;border-radius:9px;font-size:0.82rem;margin-bottom:1rem;font-weight:600;}

.vreg-footer{padding:1rem 2rem;background:#f8fafc;border-top:1px solid #e2e8f0;text-align:center;font-size:0.78rem;color:#64748b;}
.vreg-footer a{color:#0e9f6e;font-weight:700;text-decoration:none;}
</style>

<div class="vreg-page">
<div class="vreg-card">

    <!-- Header -->
    <div class="vreg-header">
        <div>
            <a href="<?= SITE_URL ?>/vendor/register" class="back-link">
                <i data-lucide="arrow-left"></i> Change Type
            </a>
        </div>
        <h1>
            <i data-lucide="building-2" style="width:22px;height:22px;"></i>
            Community Vendor Registration
        </h1>
        <p><?= e(SCHOOL_NAME) ?> &middot; Campus Business Directory</p>
        <div class="vreg-steps">
            <div class="vreg-step done"></div>
            <div class="vreg-step active"></div>
            <div class="vreg-step"></div>
            <div class="vreg-step"></div>
        </div>
    </div>

    <div class="vreg-body">
        <?php require VIEWS_PATH . '/partials/flash.php'; ?>

        <form method="POST"
              action="<?= SITE_URL ?>/vendor/register?type=community"
              enctype="multipart/form-data"
              novalidate>
            <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
            <input type="hidden" name="vendor_type" value="community">

            <!-- Personal Info -->
            <div class="vsection">
                <div class="vsection-title">
                    <i data-lucide="user"></i> Personal Information
                </div>
                <div class="vrow">
                    <div class="vfg">
                        <label>Full Name <span class="req">*</span></label>
                        <input type="text" name="full_name"
                               placeholder="Owner or representative name"
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
                <div class="vfg">
                    <label>Email Address <span class="req">*</span></label>
                    <input type="email" name="working_email"
                           placeholder="business@email.com"
                           value="<?= e($_SESSION['form_old']['working_email'] ?? '') ?>"
                           required>
                    <div class="hint">This will be your login email and notification address</div>
                </div>
            </div>

            <!-- Business Info -->
            <div class="vsection">
                <div class="vsection-title">
                    <i data-lucide="building-2"></i> Business Information
                </div>
                <div class="vfg">
                    <label>Business Name <span class="req">*</span></label>
                    <input type="text" name="business_name"
                           placeholder="Registered or trading name"
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
                        <label>Years in Operation</label>
                        <input type="number" name="years_operation"
                               value="<?= e($_SESSION['form_old']['years_operation'] ?? '0') ?>"
                               min="0" max="100" placeholder="0">
                    </div>
                </div>
                <div class="vfg">
                    <label>Business Address <span class="req">*</span></label>
                    <input type="text" name="business_address"
                           placeholder="Physical address of your business"
                           value="<?= e($_SESSION['form_old']['business_address'] ?? '') ?>"
                           required>
                </div>
                <div class="vfg">
                    <label>Business Description <span class="req">*</span></label>
                    <textarea name="description"
                              placeholder="Describe your products or services in detail (minimum 50 characters)..."
                              required><?= e($_SESSION['form_old']['description'] ?? '') ?></textarea>
                </div>
                <div class="vrow">
                    <div class="vfg">
                        <label>Price Range</label>
                        <input type="text" name="price_range"
                               placeholder="e.g. &#8358;1,000 – &#8358;20,000"
                               value="<?= e($_SESSION['form_old']['price_range'] ?? '') ?>">
                    </div>
                    <div class="vfg">
                        <label>WhatsApp Number</label>
                        <input type="tel" name="whatsapp_number"
                               placeholder="08012345678"
                               value="<?= e($_SESSION['form_old']['whatsapp_number'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Identity Verification -->
            <div class="vsection">
                <div class="vsection-title">
                    <i data-lucide="lock"></i> Identity Verification
                </div>
                <div class="id-box">
                    <div class="id-box-title">
                        <i data-lucide="credit-card"></i> Government-Issued ID Required
                    </div>
                    <div class="vrow">
                        <div class="vfg" style="margin-bottom:0;">
                            <label>ID Type <span class="req">*</span></label>
                            <select name="id_type" required>
                                <option value="">Select ID type</option>
                                <option value="nin">NIN — National ID Number</option>
                                <option value="cac">CAC — Business Registration</option>
                                <option value="drivers_license">Driver's License</option>
                                <option value="voters_card">Voter's Card</option>
                                <option value="intl_passport">International Passport</option>
                            </select>
                        </div>
                        <div class="vfg" style="margin-bottom:0;">
                            <label>ID Number <span class="req">*</span></label>
                            <input type="text" name="id_number"
                                   placeholder="Enter your ID number"
                                   required>
                        </div>
                    </div>
                </div>
                <div class="vfg" style="margin-top:0.85rem;">
                    <label>Upload ID Document <span class="req">*</span></label>
                    <div class="file-drop" onclick="document.getElementById('id_doc').click()">
                        <input type="file" id="id_doc" name="id_document"
                               accept="image/jpeg,image/png,application/pdf"
                               onchange="showFileName(this,'id-name')" required>
                        <div class="fd-icon"><i data-lucide="file-text"></i></div>
                        <div class="fd-text">Click to upload your ID document</div>
                        <div class="fd-hint">JPG, PNG or PDF &middot; Max 3MB &middot; Kept strictly confidential</div>
                        <div class="file-name" id="id-name"></div>
                    </div>
                </div>
            </div>

            <!-- Business Logo -->
            <div class="vsection">
                <div class="vsection-title">
                    <i data-lucide="image"></i> Business Logo <span style="font-weight:500;text-transform:none;letter-spacing:0;color:#94a3b8;">(Optional)</span>
                </div>
                <div class="file-drop" onclick="document.getElementById('logo').click()">
                    <input type="file" id="logo" name="logo"
                           accept="image/jpeg,image/png,image/webp"
                           onchange="showFileName(this,'logo-name')">
                    <div class="fd-icon"><i data-lucide="image"></i></div>
                    <div class="fd-text">Click to upload your business logo</div>
                    <div class="fd-hint">JPG, PNG or WebP &middot; Max 2MB &middot; Optional but recommended</div>
                    <div class="file-name" id="logo-name"></div>
                </div>
            </div>

            <!-- Plan Selection -->
            <div class="vsection">
                <div class="vsection-title">
                    <i data-lucide="credit-card"></i> Choose Your Plan <span class="req">*</span>
                </div>
                <div class="plan-cards">
                    <?php
                    $planDefs = [
                        'basic'    => [
                            'label'    => 'Basic',
                            'star'     => false,
                            'price'    => 4000,
                            'features' => [
                                'Directory listing',
                                'WhatsApp & Call button',
                                'Student reviews',
                                'Category search',
                            ],
                        ],
                        'premium'  => [
                            'label'    => 'Premium',
                            'star'     => true,
                            'price'    => 7000,
                            'features' => [
                                'Everything in Basic',
                                'Priority in search results',
                                'Premium badge on profile',
                                'Featured in category pages',
                            ],
                        ],
                        'featured' => [
                            'label'    => 'Featured',
                            'star'     => false,
                            'price'    => 12000,
                            'features' => [
                                'Everything in Premium',
                                'Homepage featured section',
                                'Top of all search results',
                                'Featured badge on profile',
                            ],
                        ],
                    ];
                    foreach ($communityPlans as $plan):
                        $type  = $plan['plan_type'];
                        $def   = $planDefs[$type] ?? ['label'=>ucfirst($type),'star'=>false,'price'=>$plan['amount']/100,'features'=>[]];
                        $first = ($type === 'basic');
                    ?>
                    <div class="plan-card <?= $type === 'premium' ? 'popular' : '' ?> <?= $first ? 'selected' : '' ?>"
                         data-plan="<?= e($type) ?>"
                         onclick="selectPlan('<?= e($type) ?>')">
                        <?php if ($type === 'premium'): ?>
                            <div class="popular-tag">POPULAR</div>
                        <?php endif; ?>
                        <div class="plan-name">
                            <?= e($def['label']) ?>
                            <?php if ($def['star']): ?>
                                <i data-lucide="star"></i>
                            <?php endif; ?>
                        </div>
                        <div class="plan-price">
                            &#8358;<?= number_format($def['price']) ?>
                            <small>per semester (180 days)</small>
                        </div>
                        <ul class="plan-features">
                            <?php foreach ($def['features'] as $f): ?>
                            <li><i data-lucide="check"></i><?= e($f) ?></li>
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
                    <i data-lucide="key-round"></i> Account Security
                </div>
                <div class="vrow">
                    <div class="vfg">
                        <label>Password <span class="req">*</span></label>
                        <div class="pw-wrap">
                            <input type="password" name="password" id="pw1"
                                   placeholder="Minimum 8 characters" required>
                            <button type="button" class="pw-eye" onclick="togglePw('pw1',this)" aria-label="Show password">
                                <i data-lucide="eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="vfg">
                        <label>Confirm Password <span class="req">*</span></label>
                        <div class="pw-wrap">
                            <input type="password" name="password_confirmation" id="pw2"
                                   placeholder="Repeat your password" required>
                            <button type="button" class="pw-eye" onclick="togglePw('pw2',this)" aria-label="Show password">
                                <i data-lucide="eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Terms -->
            <div class="vsection">
                <div class="vsection-title">
                    <i data-lucide="clipboard-list"></i> Terms &amp; Agreements
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

            <div class="review-notice">
                <i data-lucide="clock"></i>
                <span>
                    Your application will be reviewed by our admin team within
                    <strong>24–48 hours</strong>.
                    You will receive an email notification once approved. Payment is made after approval.
                </span>
            </div>

            <button type="submit" class="vreg-submit">
                <i data-lucide="building-2"></i>
                Submit Business Vendor Application
                <i data-lucide="arrow-right"></i>
            </button>
        </form>
    </div>

    <div class="vreg-footer">
        Already registered?
        <a href="<?= SITE_URL ?>/vendor/login">Sign in to your dashboard</a>
        &nbsp;&middot;&nbsp;
        <a href="<?= SITE_URL ?>/vendor/register">Change vendor type</a>
    </div>

</div>
</div>

<script>
function selectPlan(type) {
    document.querySelectorAll('.plan-card').forEach(function(c) {
        c.classList.remove('selected');
    });
    document.querySelector('[data-plan="' + type + '"]').classList.add('selected');
    document.getElementById('selectedPlan').value = type;
}

function togglePw(id, btn) {
    var input = document.getElementById(id);
    var isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    var icon = btn.querySelector('i');
    icon.setAttribute('data-lucide', isHidden ? 'eye-off' : 'eye');
    lucide.createIcons();
}

function showFileName(input, nameId) {
    var el = document.getElementById(nameId);
    if (input.files && input.files[0]) {
        el.innerHTML = '<i data-lucide="check-circle"></i> ' + input.files[0].name;
        el.style.display = 'flex';
        lucide.createIcons();
    }
}

// Initialise all icons in this partial
lucide.createIcons();
</script>