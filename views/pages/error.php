<?php defined('CAMPUSLINK') or die(); ?>

<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

<div style="min-height:80vh;display:flex;align-items:center;justify-content:center;
            padding:3rem 1rem;text-align:center;">

    <div style="max-width:520px;width:100%;">

        <?php
        $codes = [
            400 => ['file-question',  'Bad Request',    'The server could not understand your request.'],
            403 => ['lock',           'Access Denied',  "You don't have permission to access this page."],
            404 => ['search-x',       'Page Not Found', "The page you're looking for doesn't exist or has been moved."],
            500 => ['server-crash',   'Server Error',   'Something went wrong on our end. Please try again shortly.'],
        ];
        $code = $errorCode ?? 404;
        [$iconName, $title, $message] = $codes[$code] ?? $codes[404];
        ?>

        <div style="display:flex;justify-content:center;margin-bottom:1.5rem;">
            <i data-lucide="<?= $iconName ?>"
               style="width:72px;height:72px;stroke:#cbd5e1;stroke-width:1.2;fill:none;"></i>
        </div>

        <div style="font-size:5rem;font-weight:900;color:#e2e8f0;
                    line-height:1;margin-bottom:0.5rem;letter-spacing:-0.04em;">
            <?= $code ?>
        </div>

        <h1 style="font-size:1.8rem;font-weight:800;color:var(--text-primary);
                   margin-bottom:0.75rem;">
            <?= $title ?>
        </h1>

        <p style="color:var(--text-secondary);font-size:var(--font-size-lg);
                  line-height:1.7;margin-bottom:2rem;">
            <?= isset($customMessage) ? e($customMessage) : $message ?>
        </p>

        <div style="display:flex;gap:0.75rem;justify-content:center;flex-wrap:wrap;">
            <a href="<?= SITE_URL ?>" class="btn btn-primary"
               style="display:inline-flex;align-items:center;gap:0.4rem;">
                <i data-lucide="home" style="width:15px;height:15px;"></i> Go Home
            </a>
            <a href="<?= SITE_URL ?>/browse" class="btn btn-outline-primary"
               style="display:inline-flex;align-items:center;gap:0.4rem;">
                <i data-lucide="search" style="width:15px;height:15px;"></i> Browse Vendors
            </a>
            <button onclick="history.back()" class="btn btn-outline-primary"
                    style="display:inline-flex;align-items:center;gap:0.4rem;">
                <i data-lucide="arrow-left" style="width:15px;height:15px;"></i> Go Back
            </button>
        </div>

        <?php if ($code === 500): ?>
        <p style="font-size:var(--font-size-xs);color:var(--text-muted);margin-top:1.5rem;">
            If this keeps happening,
            <a href="<?= SITE_URL ?>/contact">contact our support team</a>.
        </p>
        <?php endif; ?>

    </div>
</div>

<script>lucide.createIcons();</script>