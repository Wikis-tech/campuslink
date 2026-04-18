<?php defined('CAMPUSLINK') or die(); ?>

<?php
// Helper to render a lucide-style SVG icon inline
if (!function_exists('lucide_icon')) {
function lucide_icon(string $path, int $size = 40, string $color = 'currentColor', string $extra_style = ''): string {
    return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'"
                 viewBox="0 0 24 24" fill="none" stroke="'.$color.'"
                 stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                 style="'.$extra_style.'">'.$path.'</svg>';
}
}

?>

<div class="auth-container" style="padding:3rem 1rem;">
    <div style="max-width:780px;width:100%;margin:0 auto;">

        <div style="text-align:center;margin-bottom:2.5rem;">
            <!-- Store icon -->
            <div style="font-size:3rem;margin-bottom:1rem;display:flex;justify-content:center;">
                <?= lucide_icon(
                    '<path d="M3 9l1-5h16l1 5"/><path d="M3 9a2 2 0 0 0 2 2 2 2 0 0 0 2-2 2 2 0 0 0 2 2 2 2 0 0 0 2-2 2 2 0 0 0 2 2 2 2 0 0 0 2-2"/><path d="M5 11v9h14v-9"/>',
                    48, 'var(--primary)'
                ) ?>
            </div>
            <h1 style="font-size:2rem;font-weight:900;color:var(--text-primary);
                       margin-bottom:0.75rem;letter-spacing:-0.02em;">
                List Your Business on CampusLink
            </h1>
            <p style="color:var(--text-secondary);font-size:var(--font-size-lg);
                      max-width:520px;margin:0 auto;line-height:1.6;">
                Join <?= e(SCHOOL_NAME) ?>'s trusted campus directory.
                Choose the account type that best describes you.
            </p>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;
                    margin-bottom:2.5rem;">

            <!-- Student Vendor -->
            <a href="<?= SITE_URL ?>/vendor/register?type=student"
               style="display:block;padding:2rem;border:2px solid var(--divider);
                      border-radius:var(--radius-xl);text-decoration:none;
                      background:var(--card-bg);transition:all 0.2s;text-align:center;"
               onmouseover="this.style.borderColor='var(--primary)';this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 40px rgba(0,0,0,0.12)'"
               onmouseout="this.style.borderColor='var(--divider)';this.style.transform='none';this.style.boxShadow='none'">

                <!-- GraduationCap icon -->
                <div style="margin-bottom:1rem;display:flex;justify-content:center;">
                    <?= lucide_icon(
                        '<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>',
                        48, 'var(--primary)'
                    ) ?>
                </div>

                <div style="font-size:1.1rem;font-weight:800;color:var(--text-primary);
                            margin-bottom:0.5rem;">Student Vendor</div>
                <p style="font-size:var(--font-size-sm);color:var(--text-secondary);
                           line-height:1.6;margin-bottom:1.25rem;">
                    Currently enrolled at <?= e(SCHOOL_NAME) ?>.
                    Offering services to fellow students.
                </p>

                <div style="display:flex;flex-direction:column;gap:0.4rem;
                            text-align:left;margin-bottom:1.5rem;">
                    <?php foreach ([
                        'School email required',
                        'Matric number verified',
                        'From ₦2,000/semester',
                        'Basic, Premium & Featured plans',
                    ] as $f): ?>
                    <div style="font-size:var(--font-size-xs);color:var(--text-secondary);
                                display:flex;align-items:center;gap:0.4rem;">
                        <!-- Check icon -->
                        <?= lucide_icon(
                            '<polyline points="20 6 9 17 4 12"/>',
                            14, '#1ea952'
                        ) ?>
                        <?= e($f) ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="btn btn-primary btn-full" style="display:flex;align-items:center;justify-content:center;gap:0.4rem;">
                    Get Started
                    <!-- ArrowRight icon -->
                    <?= lucide_icon('<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>', 16, 'currentColor') ?>
                </div>
            </a>

            <!-- Community Vendor -->
            <a href="<?= SITE_URL ?>/vendor/register?type=community"
               style="display:block;padding:2rem;border:2px solid var(--divider);
                      border-radius:var(--radius-xl);text-decoration:none;
                      background:var(--card-bg);transition:all 0.2s;text-align:center;"
               onmouseover="this.style.borderColor='var(--primary)';this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 40px rgba(0,0,0,0.12)'"
               onmouseout="this.style.borderColor='var(--divider)';this.style.transform='none';this.style.boxShadow='none'">

                <!-- Building2 icon -->
                <div style="margin-bottom:1rem;display:flex;justify-content:center;">
                    <?= lucide_icon(
                        '<path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><line x1="10" y1="6" x2="10" y2="6"/><line x1="14" y1="6" x2="14" y2="6"/><line x1="10" y1="10" x2="10" y2="10"/><line x1="14" y1="10" x2="14" y2="10"/><line x1="10" y1="14" x2="10" y2="14"/><line x1="14" y1="14" x2="14" y2="14"/>',
                        48, 'var(--primary)'
                    ) ?>
                </div>

                <div style="font-size:1.1rem;font-weight:800;color:var(--text-primary);
                            margin-bottom:0.5rem;">Community Vendor</div>
                <p style="font-size:var(--font-size-sm);color:var(--text-secondary);
                           line-height:1.6;margin-bottom:1.25rem;">
                    External business or non-student serving the campus community.
                </p>

                <div style="display:flex;flex-direction:column;gap:0.4rem;
                            text-align:left;margin-bottom:1.5rem;">
                    <?php foreach ([
                        'Personal or business email',
                        'NIN or CAC verification',
                        'From ₦4,000/semester',
                        'Basic, Premium & Featured plans',
                    ] as $f): ?>
                    <div style="font-size:var(--font-size-xs);color:var(--text-secondary);
                                display:flex;align-items:center;gap:0.4rem;">
                        <!-- Check icon -->
                        <?= lucide_icon(
                            '<polyline points="20 6 9 17 4 12"/>',
                            14, '#1ea952'
                        ) ?>
                        <?= e($f) ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="btn btn-outline-primary btn-full" style="display:flex;align-items:center;justify-content:center;gap:0.4rem;">
                    Get Started
                    <!-- ArrowRight icon -->
                    <?= lucide_icon('<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>', 16, 'currentColor') ?>
                </div>
            </a>

        </div>

        <div style="text-align:center;">
            <p style="color:var(--text-muted);font-size:var(--font-size-sm);">
                Already registered?
                <a href="<?= SITE_URL ?>/vendor/login"
                   style="color:var(--primary);font-weight:700;">
                    Sign in to your vendor account â†’
                </a>
            </p>
        </div>

        <div class="disclaimer-box" style="margin-top:2rem;">
            <!-- Warning/AlertTriangle icon replacing âš ï¸ -->
            <span class="disclaimer-icon" style="display:flex;align-items:flex-start;padding-top:2px;">
                <?= lucide_icon(
                    '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
                    20, 'var(--warning-dark)'
                ) ?>
            </span>
            <div class="disclaimer-text">
                <strong>Important:</strong> CampusLink is a listing platform only.
                We do not process payments between students and vendors or guarantee
                service quality. All transactions are independent.
                <a href="<?= SITE_URL ?>/vendor-terms" target="_blank"
                   style="color:var(--warning-dark);font-weight:700;">
                    Read Vendor Terms and Guidelines
                </a>
            </div>
        </div>

    </div>
</div>
