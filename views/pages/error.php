<?php defined('CAMPUSLINK') or die(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error — CampusLink</title>
    <link rel="icon" type="image/png" href="<?= SITE_URL ?>/assets/img/favicon.png">
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.568.0/dist/lucide.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0b3d91 0%, #1e5bb8 50%, #1a4fa8 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            color: #1f2937;
        }

        .error-container {
            background: #ffffff;
            border-radius: 16px;
            padding: 3rem 2rem;
            max-width: 520px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(11, 61, 145, 0.15);
        }

        .error-icon {
            display: flex;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .error-icon svg {
            width: 72px;
            height: 72px;
            stroke: #0b3d91;
            stroke-width: 1.5;
            fill: none;
        }

        .error-code {
            font-size: 5rem;
            font-weight: 900;
            color: #0b3d91;
            line-height: 1;
            margin-bottom: 0.5rem;
            letter-spacing: -0.04em;
        }

        .error-title {
            font-size: 1.8rem;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 0.75rem;
        }

        .error-message {
            color: #6b7280;
            font-size: 1rem;
            line-height: 1.7;
            margin-bottom: 2rem;
        }

        .error-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.875rem 1.5rem;
            border-radius: 8px;
            border: none;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0b3d91 0%, #1e5bb8 100%);
            color: #ffffff;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #082d6a 0%, #1a4fa8 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(11, 61, 145, 0.25);
        }

        .btn-outline {
            background: transparent;
            color: #0b3d91;
            border: 2px solid #0b3d91;
        }

        .btn-outline:hover {
            background: rgba(11, 61, 145, 0.06);
            border-color: #082d6a;
        }

        .error-footer {
            font-size: 0.85rem;
            color: #9ca3af;
            margin-top: 1.5rem;
        }

        .error-footer a {
            color: #0b3d91;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .error-footer a:hover {
            color: #1e5bb8;
        }

        @media (max-width: 600px) {
            .error-container {
                padding: 2rem 1.5rem;
            }

            .error-code {
                font-size: 3rem;
            }

            .error-title {
                font-size: 1.4rem;
            }

            .error-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
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

        <div class="error-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <?php if ($iconName === 'file-question'): ?>
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M12 17h.01M12 13h.01"/>
                <?php elseif ($iconName === 'lock'): ?>
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                <?php elseif ($iconName === 'search-x'): ?>
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/><path d="M8 11h6M11 8v6"/>
                <?php elseif ($iconName === 'server-crash'): ?>
                    <polyline points="4 14 10 14 10 20 4 20 4 14"/><rect x="14" y="14" width="6" height="6"/><path d="M6 4h12v7H6z"/><path d="M9 4v0M15 4v0"/>
                <?php endif; ?>
            </svg>
        </div>

        <div class="error-code"><?= $code ?></div>
        <div class="error-title"><?= $title ?></div>
        <div class="error-message">
            <?= isset($customMessage) ? e($customMessage) : $message ?>
        </div>

        <div class="error-actions">
            <a href="<?= SITE_URL ?>" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                Go Home
            </a>
            <a href="<?= SITE_URL ?>/browse" class="btn btn-outline">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                Browse Vendors
            </a>
            <button onclick="history.back()" class="btn btn-outline">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
                </svg>
                Go Back
            </button>
        </div>

        <?php if ($code === 500): ?>
        <div class="error-footer">
            If this keeps happening,
            <a href="<?= SITE_URL ?>">contact our support team</a>.
        </div>
        <?php endif; ?>
    </div>
</body>
</html>