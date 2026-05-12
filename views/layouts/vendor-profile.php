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
<meta name="csrf-token" content="<?= CSRF::token() ?>">
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Public_Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<script src="https://cdn.jsdelivr.net/npm/lucide@0.568.0/dist/lucide.min.js"></script>
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

/* Mobile Navigation Styles */
.mobile-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    background: rgba(248, 249, 250, 0.95);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(0,0,0,0.1);
}
.mobile-header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    max-width: 100%;
}
.mobile-header-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.mobile-logo {
    font-size: 1.25rem;
    font-weight: 900;
    color: #002869;
    text-decoration: none;
    letter-spacing: -0.02em;
}
.mobile-logo .logo-campus { color: #002869; }
.mobile-logo .logo-link { color: #0e9f6e; }
.mobile-header-right {
    display: flex;
    align-items: center;
}
.header-school-logo img {
    height: 32px;
    width: auto;
}

/* Mobile Menu Button */
.mobile-menu-btn {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    width: 24px;
    height: 18px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    z-index: 1001;
}
.mobile-menu-btn span {
    display: block;
    width: 100%;
    height: 2px;
    background: #002869;
    border-radius: 1px;
    transition: all 0.3s ease;
    transform-origin: center;
}
.mobile-menu-btn.active span:nth-child(1) {
    transform: rotate(45deg) translate(5px, 5px);
}
.mobile-menu-btn.active span:nth-child(2) {
    opacity: 0;
}
.mobile-menu-btn.active span:nth-child(3) {
    transform: rotate(-45deg) translate(7px, -6px);
}

/* Mobile Nav Overlay */
.mobile-nav-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 999;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}
.mobile-nav-overlay.active {
    opacity: 1;
    visibility: visible;
}

/* Mobile Nav Drawer */
.mobile-nav {
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    width: 280px;
    background: #fff;
    z-index: 1000;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
    overflow-y: auto;
    box-shadow: 2px 0 20px rgba(0,0,0,0.1);
}
.mobile-nav.active {
    transform: translateX(0);
}
.mobile-nav-close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #64748b;
    cursor: pointer;
    z-index: 1001;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background 0.2s;
}
.mobile-nav-close:hover {
    background: #f1f5f9;
}
.mobile-nav-logo {
    padding: 1.5rem 1.25rem 1rem;
    border-bottom: 1px solid #e2e8f0;
    font-size: 1.3rem;
    font-weight: 900;
    color: #002869;
    letter-spacing: -0.02em;
}
.mobile-nav-logo .logo-campus { color: #002869; }
.mobile-nav-logo .logo-link { color: #0e9f6e; }
.mobile-nav-user {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.mobile-nav-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #002869, #0e9f6e);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 800;
    font-size: 0.9rem;
    flex-shrink: 0;
}
.mobile-nav-user-info {
    flex: 1;
    min-width: 0;
}
.mobile-nav-user-name {
    font-size: 0.9rem;
    font-weight: 700;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.mobile-nav-user-role {
    font-size: 0.75rem;
    color: #64748b;
}
.mobile-nav-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1.25rem;
    color: #374151;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: background 0.2s;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    cursor: pointer;
}
.mobile-nav-link:hover,
.mobile-nav-link.active {
    background: #f8fafc;
    color: #002869;
}
.mobile-nav-link .nav-icon {
    width: 18px;
    height: 18px;
    stroke: currentColor;
    stroke-width: 2;
    fill: none;
    stroke-linecap: round;
    stroke-linejoin: round;
    vertical-align: middle;
    flex-shrink: 0;
}

/* Responsive adjustments */
@media (min-width: 1024px) {
    .mobile-header,
    .mobile-nav,
    .mobile-nav-overlay {
        display: none;
    }
}
</style>
<script>window.CAMPUSLINK_ROOT = '<?= rtrim(SITE_URL, '/') ?>';</script>
<script src="<?= SITE_URL ?>/assets/js/main.js" defer></script>
</head>
<body class="bg-surface font-body text-on-surface">
<?= $content ?? '' ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.lucide) lucide.createIcons();

    const menuBtn = document.querySelector('.mobile-menu-btn');
    const mobileNav = document.querySelector('.mobile-nav');
    const overlay = document.querySelector('.mobile-nav-overlay');
    const closeBtn = document.querySelector('.mobile-nav-close');

    const openNav = () => {
        menuBtn?.classList.add('active');
        menuBtn?.setAttribute('aria-expanded', 'true');
        mobileNav?.classList.add('active');
        overlay?.classList.add('active');
        document.body.style.overflow = 'hidden';
    };

    const closeNav = () => {
        menuBtn?.classList.remove('active');
        menuBtn?.setAttribute('aria-expanded', 'false');
        mobileNav?.classList.remove('active');
        overlay?.classList.remove('active');
        document.body.style.overflow = '';
    };

    menuBtn?.addEventListener('click', function() {
        if (mobileNav?.classList.contains('active')) {
            closeNav();
        } else {
            openNav();
        }
    });
    closeBtn?.addEventListener('click', closeNav);
    overlay?.addEventListener('click', closeNav);
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && mobileNav?.classList.contains('active')) {
            closeNav();
        }
    });
});
</script>
</body>
</html>
