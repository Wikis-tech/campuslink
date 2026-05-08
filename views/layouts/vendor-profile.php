<?php
/**
 * CampusLink - Vendor Profile Full Page Layout
 */
defined('CAMPUSLINK') or die('Direct access not permitted.');

$pageTitle = $pageTitle ?? SITE_NAME . ' Vendor Profile';
$pageDesc  = $pageDesc  ?? SITE_DESCRIPTION;
$canonical = $canonical  ?? SITE_URL . '/' . ltrim($_SERVER['REQUEST_URI'] ?? '', '/');
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($pageDesc) ?>" />
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Public_Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
                    "tertiary-fixed": "#66ff8e",
                    "surface-bright": "#f8f9fa",
                    "error": "#ba1a1a",
                    "primary-fixed": "#dae2ff",
                    "secondary": "#006d30",
                    "on-secondary-fixed-variant": "#005323",
                    "secondary-fixed": "#7efc9a",
                    "surface-dim": "#d9dadb",
                    "surface-container-highest": "#e1e3e4",
                    "on-primary-fixed": "#001947",
                    "on-tertiary": "#ffffff",
                    "on-tertiary-fixed": "#002109",
                    "primary": "#002869",
                    "inverse-primary": "#b1c5ff",
                    "on-primary": "#ffffff",
                    "tertiary-fixed-dim": "#3de273",
                    "on-secondary-container": "#007232",
                    "outline-variant": "#c4c6d3",
                    "on-primary-container": "#8dadff",
                    "surface-container-high": "#e7e8e9",
                    "on-secondary-fixed": "#00210a",
                    "on-primary-fixed-variant": "#144296",
                    "primary-fixed-dim": "#b1c5ff",
                    "inverse-on-surface": "#f0f1f2",
                    "on-secondary": "#ffffff",
                    "surface": "#f8f9fa",
                    "on-tertiary-fixed-variant": "#005322",
                    "on-error": "#ffffff",
                    "on-surface-variant": "#434652",
                    "on-error-container": "#93000a",
                    "tertiary-container": "#004d1f",
                    "surface-variant": "#e1e3e4",
                    "surface-container-lowest": "#ffffff",
                    "on-tertiary-container": "#08c95d",
                    "on-background": "#191c1d",
                    "surface-container-low": "#f3f4f5",
                    "on-surface": "#191c1d",
                    "secondary-container": "#7bf997",
                    "outline": "#747783",
                    "surface-container": "#edeeef",
                    "tertiary": "#003413",
                    "secondary-fixed-dim": "#60df80",
                    "background": "#f8f9fa",
                    "primary-container": "#0b3d91",
                    "inverse-surface": "#2e3132",
                    "surface-tint": "#345baf",
                    "error-container": "#ffdad6"
            },
            borderRadius: {
                    DEFAULT: '0.25rem',
                    lg: '0.5rem',
                    xl: '0.75rem',
                    full: '9999px'
            },
            fontFamily: {
                    headline: ['Manrope'],
                    body: ['Public Sans'],
                    label: ['Public Sans']
            }
          },
        },
      }
    </script>
<style>
        .glass-header {
            background: rgba(248, 249, 250, 0.7);
            backdrop-filter: blur(20px);
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .filled-icon {
            font-variation-settings: 'FILL' 1;
        }
    </style>
</head>
<body class="bg-surface font-body text-on-surface">
<?= $content ?? '' ?>
</body>
</html>
