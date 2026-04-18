/* ============================================================
   CampusLink - Main JavaScript
   Global utilities, header, mobile nav, toasts, notifications
   ============================================================ */

'use strict';

// ============================================================
// GLOBAL CAMPUSLINK NAMESPACE
// ============================================================
const CampusLink = {

    // ============================================================
    // INIT — runs on DOMContentLoaded
    // ============================================================
    init() {
        this.initHeader();
        this.initMobileNav();
        this.initToasts();
        this.initNotificationBell();
        this.initDashboardSidebar();
        this.initFlashMessages();
        this.initConfirmDialogs();
        this.initLazyImages();
        this.initScrollAnimations();
        this.initStarDisplays();
        this.initTimeAgo();
        this.initSaveButtons();
        this.bindGlobalAjaxErrors();
    },

    // ============================================================
    // HEADER — scroll shadow + active nav link
    // ============================================================
    initHeader() {
        const header = document.querySelector('.site-header');
        if (!header) return;

        const onScroll = () => {
            header.classList.toggle('scrolled', window.scrollY > 20);
        };

        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();

        // Mark active nav link
        const currentPath = window.location.pathname.replace(/\/$/, '');
        document.querySelectorAll('.nav-link, .mobile-nav-link').forEach(link => {
            const href = link.getAttribute('href');
            if (!href) return;
            const linkPath = href.replace(/\/$/, '');
            if (currentPath === linkPath || (linkPath !== '' && currentPath.startsWith(linkPath))) {
                link.classList.add('active');
            }
        });
    },

    // ============================================================
    // MOBILE NAV — drawer open/close
    // ============================================================
    initMobileNav() {
        const menuBtn     = document.querySelector('.mobile-menu-btn');
        const mobileNav   = document.querySelector('.mobile-nav');
        const overlay     = document.querySelector('.mobile-nav-overlay');
        const closeBtn    = document.querySelector('.mobile-nav-close');

        if (!menuBtn || !mobileNav) return;

        const open = () => {
            menuBtn.classList.add('active');
            mobileNav.classList.add('active');
            if (overlay) overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            menuBtn.setAttribute('aria-expanded', 'true');
        };

        const close = () => {
            menuBtn.classList.remove('active');
            mobileNav.classList.remove('active');
            if (overlay) overlay.classList.remove('active');
            document.body.style.overflow = '';
            menuBtn.setAttribute('aria-expanded', 'false');
        };

        menuBtn.addEventListener('click', () => {
            mobileNav.classList.contains('active') ? close() : open();
        });

        if (closeBtn)  closeBtn.addEventListener('click', close);
        if (overlay)   overlay.addEventListener('click', close);

        // Close on Escape
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && mobileNav.classList.contains('active')) close();
        });
    },

    // ============================================================
    // DASHBOARD SIDEBAR — mobile toggle
    // ============================================================
    initDashboardSidebar() {
        const toggleBtn = document.querySelector('.mobile-sidebar-btn');
        const sidebar   = document.querySelector('.dashboard-sidebar');
        if (!toggleBtn || !sidebar) return;

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });

        // Close when clicking outside
        document.addEventListener('click', e => {
            if (
                sidebar.classList.contains('open') &&
                !sidebar.contains(e.target) &&
                !toggleBtn.contains(e.target)
            ) {
                sidebar.classList.remove('open');
            }
        });
    },

    // ============================================================
    // URL HELPERS
    // ============================================================
    resolveUrl(url) {
        if (!url) return '';
        if (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('//')) {
            return url;
        }
        if (url.startsWith('/')) {
            return (window.CAMPUSLINK_ROOT || '') + url;
        }
        return url;
    },

    // ============================================================
    // TOAST NOTIFICATIONS
    // ============================================================
    toastQueue: [],

    initToasts() {
        // Create container if missing
        if (!document.querySelector('.toast-container')) {
            const container = document.createElement('div');
            container.className = 'toast-container';
            container.setAttribute('aria-live', 'polite');
            document.body.appendChild(container);
        }
    },

    toast(message, type = 'info', duration = 4000) {
        const container = document.querySelector('.toast-container');
        if (!container) return;

        const icons = {
            success: '<i data-feather="check-circle" class="toast-icon"></i>',
            error:   '<i data-feather="x-circle" class="toast-icon"></i>',
            warning: '<i data-feather="alert-triangle" class="toast-icon"></i>',
            info:    '<i data-feather="info" class="toast-icon"></i>',
        };

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <span class="toast-icon">${icons[type] || icons.info}</span>
            <span class="toast-message">${this.escapeHtml(message)}</span>
            <button class="toast-close" aria-label="Close">&times;</button>
        `;

        container.appendChild(toast);

        if (window.lucide) lucide.createIcons();

        // Auto remove
        const timer = setTimeout(() => this.removeToast(toast), duration);

        toast.querySelector('.toast-close').addEventListener('click', () => {
            clearTimeout(timer);
            this.removeToast(toast);
        });

        return toast;
    },

    removeToast(toast) {
        toast.style.opacity    = '0';
        toast.style.transform  = 'translateX(20px)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    },

    // ============================================================
    // FLASH MESSAGES — auto-dismiss
    // ============================================================
    initFlashMessages() {
        document.querySelectorAll('.alert[data-auto-dismiss]').forEach(alert => {
            const delay = parseInt(alert.dataset.autoDismiss) || 5000;
            setTimeout(() => {
                alert.style.opacity    = '0';
                alert.style.transform  = 'translateY(-8px)';
                alert.style.transition = 'all 0.4s ease';
                setTimeout(() => alert.remove(), 400);
            }, delay);
        });

        // Manual close buttons
        document.querySelectorAll('.alert .alert-close').forEach(btn => {
            btn.addEventListener('click', () => {
                const alert = btn.closest('.alert');
                alert.style.opacity   = '0';
                alert.style.transform = 'translateY(-8px)';
                alert.style.transition = 'all 0.3s ease';
                setTimeout(() => alert.remove(), 300);
            });
        });
    },

    // ============================================================
    // NOTIFICATION BELL
    // ============================================================
    initNotificationBell() {
        const bell      = document.querySelector('.notif-bell-btn');
        const dropdown  = document.querySelector('.notif-dropdown');
        const countEl   = document.querySelector('.notif-count-badge');

        if (!bell) return;

        // Toggle dropdown
        bell.addEventListener('click', e => {
            e.stopPropagation();
            const isOpen = dropdown?.classList.toggle('active');
            bell.setAttribute('aria-expanded', String(!!isOpen));

            // Mark all read when opening
            if (isOpen) {
                this.markAllNotifsRead();
                if (countEl) {
                    countEl.style.display = 'none';
                }
            }
        });

        // Close when clicking outside
        document.addEventListener('click', e => {
            if (dropdown && !dropdown.contains(e.target) && !bell.contains(e.target)) {
                dropdown.classList.remove('active');
                bell.setAttribute('aria-expanded', 'false');
            }
        });

        // Poll unread count every 60 seconds
        this.pollNotifCount(countEl);
        setInterval(() => this.pollNotifCount(countEl), 60000);
    },

    pollNotifCount(countEl) {
        if (!countEl) return;

        this.ajax('/notifications/count', 'GET')
            .then(data => {
                if (data.success && data.count > 0) {
                    countEl.textContent = data.count > 99 ? '99+' : data.count;
                    countEl.style.display = 'flex';
                } else {
                    countEl.style.display = 'none';
                }
            })
            .catch(() => {}); // Silently fail
    },

    markAllNotifsRead() {
        const csrfToken = this.getCsrf();
        if (!csrfToken) return;

        this.ajax('/notifications/mark-all-read', 'POST', { csrf_token: csrfToken })
            .catch(() => {});
    },

    // ============================================================
    // CONFIRM DIALOGS
    // ============================================================
    initConfirmDialogs() {
        document.addEventListener('click', e => {
            const btn = e.target.closest('[data-confirm]');
            if (!btn) return;

            const message = btn.dataset.confirm || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    },

    // ============================================================
    // LAZY LOAD IMAGES
    // ============================================================
    initLazyImages() {
        if (!('IntersectionObserver' in window)) return;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                const img = entry.target;
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                }
                observer.unobserve(img);
            });
        }, { rootMargin: '200px' });

        document.querySelectorAll('img[data-src]').forEach(img => observer.observe(img));
    },

    // ============================================================
    // SCROLL ANIMATIONS
    // ============================================================
    initScrollAnimations() {
        if (!('IntersectionObserver' in window)) return;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

        document.querySelectorAll(
            '.category-card, .vendor-card, .trust-card, .step-card, .stat-card, .browse-vendor-card'
        ).forEach(el => {
            el.style.opacity = '0';
            observer.observe(el);
        });
    },

    // ============================================================
    // STAR RATING DISPLAYS
    // ============================================================
    initStarDisplays() {
        document.querySelectorAll('[data-rating]').forEach(el => {
            const rating = parseFloat(el.dataset.rating) || 0;
            el.innerHTML = this.buildStars(rating);
        });
    },

    buildStars(rating, max = 5) {
        let html = '';
        for (let i = 1; i <= max; i++) {
            if (rating >= i) {
                html += '<span class="star full"><i data-feather="star" class="star-icon"></i></span>';
            } else if (rating >= i - 0.5) {
                html += '<span class="star half"><i data-feather="star" class="star-icon"></i></span>';
            } else {
                html += '<span class="star empty"><i data-feather="star" class="star-icon"></i></span>';
            }
        }
        if (window.lucide) lucide.createIcons();
        return html;
    },

    // ============================================================
    // TIME AGO
    // ============================================================
    initTimeAgo() {
        document.querySelectorAll('[data-time]').forEach(el => {
            const timestamp = el.dataset.time;
            if (!timestamp) return;
            el.textContent = this.timeAgo(new Date(timestamp));
            el.title = new Date(timestamp).toLocaleString();
        });
    },

    timeAgo(date) {
        const seconds = Math.floor((Date.now() - date.getTime()) / 1000);
        const intervals = [
            { label: 'year',   seconds: 31536000 },
            { label: 'month',  seconds: 2592000  },
            { label: 'week',   seconds: 604800   },
            { label: 'day',    seconds: 86400    },
            { label: 'hour',   seconds: 3600     },
            { label: 'minute', seconds: 60       },
        ];

        for (const { label, seconds: s } of intervals) {
            const count = Math.floor(seconds / s);
            if (count >= 1) {
                return `${count} ${label}${count !== 1 ? 's' : ''} ago`;
            }
        }
        return 'Just now';
    },

    // ============================================================
    // SAVE VENDOR TOGGLE (generic)
    // ============================================================
    initSaveButtons() {
        document.addEventListener('click', async e => {
            const btn = e.target.closest('.browse-save-btn, .vendor-action-btn[data-save]');
            if (!btn) return;

            const vendorId = btn.dataset.vendorId;
            if (!vendorId) return;

            // Require login
            if (!btn.dataset.userId) {
                window.location.href = this.resolveUrl('/login?redirect=' + encodeURIComponent(window.location.pathname));
                return;
            }

            btn.disabled = true;
            const csrf   = this.getCsrf();

            try {
                const data = await this.ajax('/saved-vendors/toggle', 'POST', {
                    vendor_id:  vendorId,
                    csrf_token: csrf,
                });

                if (data.success) {
                    const saved = data.saved;
                    btn.classList.toggle('saved', saved);

                    if (btn.classList.contains('browse-save-btn')) {
                        btn.innerHTML = saved ? '<i data-feather="heart" class="save-icon filled"></i>' : '<i data-feather="heart" class="save-icon"></i>';
                        btn.title       = saved ? 'Remove from saved' : 'Save vendor';
                    } else {
                        const span = btn.querySelector('span') || btn;
                        span.innerHTML = saved ? '<i data-feather="heart" class="save-icon"></i> Saved' : '<i data-feather="heart" class="save-icon"></i> Save';
                    }

                    if (window.lucide) lucide.createIcons();

                    this.toast(data.message, 'success');
                } else {
                    this.toast(data.message || 'Could not update saved status.', 'error');
                }
            } catch {
                this.toast('Network error. Please try again.', 'error');
            } finally {
                btn.disabled = false;
            }
        });
    },

    // ============================================================
    // GLOBAL AJAX HELPER
    // ============================================================
    async ajax(url, method = 'GET', data = null) {
        const options = {
            method,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        };

        if (data && method !== 'GET') {
            options.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(data);
        }

        const response = await fetch(this.resolveUrl(url), options);

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        return response.json();
    },

    // ============================================================
    // BIND GLOBAL AJAX ERRORS
    // ============================================================
    bindGlobalAjaxErrors() {
        // Override fetch to handle 401/403 globally
        const originalFetch = window.fetch;
        window.fetch = async (...args) => {
            const response = await originalFetch(...args);

            if (response.status === 401) {
                window.location.href = this.resolveUrl('/login?redirect=' + encodeURIComponent(window.location.pathname));
            }

            return response;
        };
    },

    // ============================================================
    // UTILITIES
    // ============================================================
    getCsrf() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) return meta.content;

        const input = document.querySelector('input[name="csrf_token"]');
        return input ? input.value : '';
    },

    escapeHtml(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    },

    formatCurrency(kobo) {
        return '₦' + (kobo / 100).toLocaleString('en-NG', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    },

    formatNumber(num) {
        if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
        if (num >= 1000)    return (num / 1000).toFixed(1) + 'K';
        return String(num);
    },

    debounce(fn, delay = 300) {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => fn(...args), delay);
        };
    },

    throttle(fn, limit = 200) {
        let inThrottle;
        return (...args) => {
            if (!inThrottle) {
                fn(...args);
                inThrottle = true;
                setTimeout(() => { inThrottle = false; }, limit);
            }
        };
    },

    copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text)
                .then(() => this.toast('Copied to clipboard!', 'success'))
                .catch(() => this.fallbackCopy(text));
        } else {
            this.fallbackCopy(text);
        }
    },

    fallbackCopy(text) {
        const el = document.createElement('textarea');
        el.value = text;
        el.style.position = 'absolute';
        el.style.left = '-9999px';
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        this.toast('Copied!', 'success');
    },

    scrollTo(selector, offset = 80) {
        const el = document.querySelector(selector);
        if (!el) return;
        const top = el.getBoundingClientRect().top + window.scrollY - offset;
        window.scrollTo({ top, behavior: 'smooth' });
    },
};


// ============================================================
// COUNTER ANIMATION (for stats sections)
// ============================================================
function animateCounters() {
    const counters = document.querySelectorAll('[data-count-to]');
    if (!counters.length) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            const el       = entry.target;
            const target   = parseInt(el.dataset.countTo);
            const duration = parseInt(el.dataset.countDuration) || 1500;
            const prefix   = el.dataset.countPrefix || '';
            const suffix   = el.dataset.countSuffix || '';
            const start    = Date.now();

            const update = () => {
                const elapsed  = Date.now() - start;
                const progress = Math.min(elapsed / duration, 1);
                const ease     = 1 - Math.pow(1 - progress, 3); // cubic ease out
                const current  = Math.round(target * ease);

                el.textContent = prefix + CampusLink.formatNumber(current) + suffix;

                if (progress < 1) requestAnimationFrame(update);
                else el.textContent = prefix + CampusLink.formatNumber(target) + suffix;
            };

            requestAnimationFrame(update);
            observer.unobserve(el);
        });
    }, { threshold: 0.5 });

    counters.forEach(el => observer.observe(el));
}


// ============================================================
// COPY TICKET / REFERENCE BUTTONS
// ============================================================
function initCopyButtons() {
    document.querySelectorAll('[data-copy]').forEach(btn => {
        btn.addEventListener('click', () => {
            CampusLink.copyToClipboard(btn.dataset.copy);
        });
    });
}


// ============================================================
// DROPDOWN MENUS (generic)
// ============================================================
function initDropdowns() {
    document.addEventListener('click', e => {
        // Open/close
        const trigger = e.target.closest('[data-dropdown-trigger]');
        if (trigger) {
            const targetId = trigger.dataset.dropdownTrigger;
            const menu     = document.getElementById(targetId);
            if (!menu) return;

            const isOpen = menu.classList.toggle('active');
            trigger.setAttribute('aria-expanded', String(isOpen));
            e.stopPropagation();
            return;
        }

        // Close all open dropdowns on outside click
        document.querySelectorAll('.dropdown-menu.active').forEach(menu => {
            menu.classList.remove('active');
        });
        document.querySelectorAll('[data-dropdown-trigger]').forEach(t => {
            t.setAttribute('aria-expanded', 'false');
        });
    });

    // Close on Escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.dropdown-menu.active').forEach(menu => {
                menu.classList.remove('active');
            });
        }
    });
}


// ============================================================
// TABS (generic tab switcher)
// ============================================================
function initTabs() {
    document.querySelectorAll('[data-tab-group]').forEach(group => {
        const buttons = group.querySelectorAll('[data-tab-target]');
        const panels  = group.querySelectorAll('[data-tab-panel]');

        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                const target = btn.dataset.tabTarget;

                buttons.forEach(b => b.classList.remove('active'));
                panels.forEach(p => p.classList.remove('active'));

                btn.classList.add('active');
                const panel = group.querySelector(`[data-tab-panel="${target}"]`);
                if (panel) panel.classList.add('active');
            });
        });
    });

    // Vendor profile specific tabs
    document.querySelectorAll('.vendor-tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.dataset.tab;

            document.querySelectorAll('.vendor-tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.vendor-tab-content').forEach(p => p.classList.remove('active'));

            btn.classList.add('active');
            const panel = document.querySelector(`.vendor-tab-content[data-tab="${target}"]`);
            if (panel) panel.classList.add('active');
        });
    });
}


// ============================================================
// DOM READY
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    CampusLink.init();
    animateCounters();
    initCopyButtons();
    initDropdowns();
    initTabs();
});

// Expose globally
window.CampusLink = CampusLink