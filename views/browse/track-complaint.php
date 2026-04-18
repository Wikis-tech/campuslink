<?php defined('CAMPUSLINK') or die(); ?>

<?php
if (!function_exists('lucide_icon')) {
function lucide_icon(string $path, int $size = 20, string $color = 'currentColor', string $extra_style = ''): string {
    return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'"
                 viewBox="0 0 24 24" fill="none" stroke="'.$color.'"
                 stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                 style="display:inline-block;vertical-align:middle;'.$extra_style.'">'.$path.'</svg>';
}
}

?>

<section style="padding:3rem 0;min-height:70vh;">
    <div class="container" style="max-width:680px;">

        <div style="margin-bottom:2rem;">
            <h1 style="font-size:1.75rem;font-weight:900;color:var(--text-primary);
                       margin-bottom:0.5rem;display:flex;align-items:center;gap:0.6rem;">
                <!-- Search icon replacing ðŸ” -->
                <?= lucide_icon('<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>', 28, 'var(--primary)') ?>
                Track Complaint
            </h1>
            <p style="color:var(--text-secondary);">
                Enter your ticket ID to check the status of your complaint.
            </p>
        </div>

        <!-- Search form -->
        <div class="dash-card" style="margin-bottom:1.5rem;">
            <div class="dash-card-body">
                <form action="<?= SITE_URL ?>/complaints/track" method="GET"
                      style="display:flex;gap:0.75rem;">
                    <input type="text"
                           name="ticket"
                           class="form-control"
                           placeholder="e.g. CL-A1B2C3D4"
                           value="<?= e($_GET['ticket'] ?? '') ?>"
                           style="font-family:monospace;letter-spacing:0.06em;flex:1;">
                    <button type="submit" class="btn btn-primary"
                            style="white-space:nowrap;display:inline-flex;align-items:center;gap:0.4rem;">
                        Track
                        <?= lucide_icon('<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>', 16, 'currentColor') ?>
                    </button>
                </form>
            </div>
        </div>

        <?php if (isset($complaint)): ?>
        <!-- Complaint Found -->
        <div class="dash-card">
            <div class="dash-card-header">
                <div class="dash-card-title">
                    <span class="dash-card-title-icon">
                        <!-- ClipboardList icon replacing ðŸ“‹ -->
                        <?= lucide_icon('<rect x="9" y="2" width="6" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><line x1="12" y1="11" x2="16" y2="11"/><line x1="12" y1="16" x2="16" y2="16"/><line x1="8" y1="11" x2="8.01" y2="11"/><line x1="8" y1="16" x2="8.01" y2="16"/>', 18, 'var(--primary)') ?>
                    </span>
                    Complaint Details
                </div>
                <span class="badge badge-status-<?= e($complaint['status']) ?>">
                    <?= ucwords(str_replace('_', ' ', $complaint['status'])) ?>
                </span>
            </div>
            <div class="dash-card-body">

                <?php
                $statusSteps = [
                    'submitted'    => ['submitted', 'under_review', 'verified', 'resolved'],
                    'under_review' => ['submitted', 'under_review', 'verified', 'resolved'],
                    'verified'     => ['submitted', 'under_review', 'verified', 'resolved'],
                    'resolved'     => ['submitted', 'under_review', 'verified', 'resolved'],
                    'dismissed'    => ['submitted', 'dismissed'],
                ];
                $steps     = $statusSteps[$complaint['status']] ?? ['submitted'];
                $statusPos = array_search($complaint['status'], $steps);

                // Lucide SVG paths for each step icon
                $stepIcons = [
                    'submitted'    => '<polyline points="20 6 9 17 4 12"/>',                                         // Check
                    'under_review' => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>', // Search
                    'verified'     => '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>', // AlertTriangle
                    'resolved'     => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>', // CheckCircle
                    'dismissed'    => '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>', // XCircle
                ];
                $stepLabels = [
                    'submitted'    => 'Submitted',
                    'under_review' => 'Under Review',
                    'verified'     => 'Verified',
                    'resolved'     => 'Resolved',
                    'dismissed'    => 'Dismissed',
                ];
                ?>
                <div style="display:flex;align-items:center;margin-bottom:2rem;
                            overflow-x:auto;padding:0.5rem 0;">
                    <?php foreach ($steps as $i => $step):
                        $isActive  = $i <= $statusPos;
                        $isCurrent = $i === $statusPos;
                        $iconPath  = $stepIcons[$step] ?? '<circle cx="12" cy="12" r="4"/>';
                        $label     = $stepLabels[$step] ?? ucfirst($step);
                    ?>
                    <div style="display:flex;flex-direction:column;align-items:center;
                                flex:1;min-width:80px;position:relative;">
                        <div style="width:36px;height:36px;border-radius:50%;
                                    display:flex;align-items:center;justify-content:center;
                                    margin-bottom:0.4rem;
                                    background:<?= $isActive ? 'var(--primary)' : 'var(--divider)' ?>;
                                    color:<?= $isActive ? '#fff' : 'var(--text-muted)' ?>;
                                    border:<?= $isCurrent ? '3px solid var(--accent-green)' : 'none' ?>;
                                    box-shadow:<?= $isCurrent ? '0 0 0 3px rgba(30,169,82,0.2)' : 'none' ?>;">
                            <?= lucide_icon($iconPath, 16, $isActive ? '#fff' : 'var(--text-muted)') ?>
                        </div>
                        <div style="font-size:0.65rem;font-weight:<?= $isCurrent ? '700' : '500' ?>;
                                    color:<?= $isActive ? 'var(--text-primary)' : 'var(--text-muted)' ?>;
                                    text-align:center;white-space:nowrap;">
                            <?= $label ?>
                        </div>
                        <?php if ($i < count($steps) - 1): ?>
                        <div style="position:absolute;top:18px;left:calc(50% + 18px);
                                    right:calc(-50% + 18px);height:2px;
                                    background:<?= $i < $statusPos ? 'var(--primary)' : 'var(--divider)' ?>;
                                    z-index:-1;">
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Details -->
                <div style="display:flex;flex-direction:column;gap:1rem;">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div>
                            <div style="font-size:var(--font-size-xs);color:var(--text-muted);
                                        margin-bottom:0.25rem;">Ticket ID</div>
                            <div style="font-family:monospace;font-weight:700;
                                        font-size:var(--font-size-sm);"
                                 data-copy="<?= e($complaint['ticket_id']) ?>">
                                <?= e($complaint['ticket_id']) ?>
                            </div>
                        </div>
                        <div>
                            <div style="font-size:var(--font-size-xs);color:var(--text-muted);
                                        margin-bottom:0.25rem;">Filed On</div>
                            <div style="font-weight:600;font-size:var(--font-size-sm);">
                                <?= date('d M Y \a\t g:ia', strtotime($complaint['created_at'])) ?>
                            </div>
                        </div>
                        <div>
                            <div style="font-size:var(--font-size-xs);color:var(--text-muted);
                                        margin-bottom:0.25rem;">Against Vendor</div>
                            <div style="font-weight:600;font-size:var(--font-size-sm);">
                                <a href="<?= SITE_URL ?>/vendor/<?= e($complaint['vendor_slug']) ?>"
                                   style="color:var(--primary);">
                                    <?= e($complaint['business_name']) ?>
                                </a>
                            </div>
                        </div>
                        <div>
                            <div style="font-size:var(--font-size-xs);color:var(--text-muted);
                                        margin-bottom:0.25rem;">Category</div>
                            <div style="font-weight:600;font-size:var(--font-size-sm);">
                                <?= ucwords(str_replace('_', ' ', $complaint['category'])) ?>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div style="font-size:var(--font-size-xs);color:var(--text-muted);
                                    margin-bottom:0.25rem;">Description</div>
                        <div style="font-size:var(--font-size-sm);color:var(--text-secondary);
                                    line-height:1.6;background:var(--bg);
                                    border-radius:var(--radius-lg);padding:0.75rem;
                                    border:1px solid var(--divider);">
                            <?= e($complaint['description']) ?>
                        </div>
                    </div>

                    <?php if (!empty($complaint['vendor_response'])): ?>
                    <div>
                        <div style="font-size:var(--font-size-xs);color:var(--text-muted);
                                    margin-bottom:0.25rem;">Vendor Response</div>
                        <div class="review-vendor-reply">
                            <?= e($complaint['vendor_response']) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($complaint['admin_note'])): ?>
                    <div class="alert alert-info">
                        <!-- Info icon replacing â„¹ï¸ -->
                        <span class="alert-icon">
                            <?= lucide_icon('<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>', 18, 'currentColor') ?>
                        </span>
                        <div>
                            <strong>Admin Note:</strong>
                            <?= e($complaint['admin_note']) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($complaint['status'] === 'resolved'): ?>
                    <div class="alert alert-success" style="background:#f0fdf4;
                         border-color:#bbf7d0;color:#166534;">
                        <!-- CheckCircle icon replacing âœ… -->
                        <span class="alert-icon">
                            <?= lucide_icon('<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>', 18, '#166534') ?>
                        </span>
                        <div>
                            <strong>Complaint Resolved</strong><br>
                            <span style="font-size:var(--font-size-xs);">
                                This complaint has been reviewed and resolved.
                                Thank you for helping maintain the quality of CampusLink.
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <?php elseif (isset($_GET['ticket']) && !empty($_GET['ticket'])): ?>
        <!-- Not found -->
        <div class="dash-card">
            <div class="dash-card-body">
                <div class="empty-state">
                    <!-- Search icon replacing ðŸ” -->
                    <div class="empty-icon">
                        <?= lucide_icon('<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>', 48, 'var(--primary)') ?>
                    </div>
                    <h3>Complaint Not Found</h3>
                    <p>
                        No complaint found with ticket ID
                        <strong><?= e($_GET['ticket']) ?></strong>.
                        Check the ticket ID and try again.
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div style="margin-top:1.5rem;text-align:center;">
            <a href="<?= SITE_URL ?>/browse"
               style="font-size:var(--font-size-sm);color:var(--text-muted);
                      display:inline-flex;align-items:center;gap:0.3rem;">
                <!-- ArrowLeft icon replacing â† -->
                <?= lucide_icon('<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>', 15, 'var(--text-muted)') ?>
                Back to Directory
            </a>
        </div>

    </div>
</section>
