/* ============================================================
   CampusLink - Vendor Dashboard JavaScript
   Reviews, complaints, profile, subscription, notifications
   ============================================================ */

'use strict';

// ============================================================
// VENDOR REVIEW REPLY FORMS
// ============================================================
function initReviewReplies() {
    // Toggle reply form
    document.querySelectorAll('.reply-toggle-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const reviewId  = btn.dataset.reviewId;
            const replyForm = document.querySelector(`.reply-form[data-review-id="${reviewId}"]`);
            if (!replyForm) return;

            const isOpen = replyForm.classList.toggle('active');
            btn.textContent = isOpen ? '✕ Cancel' : '💬 Reply';

            if (isOpen) {
                replyForm.querySelector('textarea')?.focus();
            }
        });
    });

    // Submit reply via AJAX
    document.querySelectorAll('.reply-form').forEach(form => {
        form.addEventListener('submit', async e => {
            e.preventDefault();

            const reviewId = form.dataset.reviewId;
            const textarea = form.querySelector('textarea');
            const reply    = textarea?.value.trim();
            const btn      = form.querySelector('[type="submit"]');

            if (!reply) {
                CampusLink.toast('Reply cannot be empty.', 'error');
                return;
            }

            if (reply.length > 500) {
                CampusLink.toast('Reply must be under 500 characters.', 'error');
                return;
            }

            btn.classList.add('btn-loading');
            btn.disabled = true;

            try {
                const data = await CampusLink.ajax('/vendor/reviews', 'POST', {
                    csrf_token: CampusLink.getCsrf(),
                    review_id:  reviewId,
                    reply,
                });

                if (data.success) {
                    CampusLink.toast('Reply posted successfully.', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    CampusLink.toast(data.message || 'Could not post reply.', 'error');
                }
            } catch {
                CampusLink.toast('Network error. Please try again.', 'error');
            } finally {
                btn.classList.remove('btn-loading');
                btn.disabled = false;
            }
        });
    });
}


// ============================================================
// VENDOR COMPLAINT RESPONSE FORMS
// ============================================================
function initComplaintResponses() {
    document.querySelectorAll('.complaint-response-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const id   = btn.dataset.complaintId;
            const form = document.querySelector(`.complaint-response-form[data-complaint-id="${id}"]`);
            if (!form) return;

            const isOpen = form.classList.toggle('active');
            btn.textContent = isOpen ? '✕ Cancel' : '📝 Respond';

            if (isOpen) form.querySelector('textarea')?.focus();
        });
    });
}


// ============================================================
// VENDOR PROFILE — Logo preview
// ============================================================
function initVendorLogoPreview() {
    const logoInput   = document.querySelector('input[name="logo"]');
    const previewImg  = document.querySelector('.current-logo-preview');

    if (!logoInput) return;

    logoInput.addEventListener('change', () => {
        const file = logoInput.files[0];
        if (!file || !file.type.startsWith('image/')) return;

        const reader = new FileReader();
        reader.onload = e => {
            if (previewImg) {
                previewImg.src = e.target.result;
            }
        };
        reader.readAsDataURL(file);
    });
}


// ============================================================
// SUBSCRIPTION PLAN CHANGE
// ============================================================
function initPlanChange() {
    const planCards   = document.querySelectorAll('.plan-change-card:not(.current-plan)');
    const actionInput = document.querySelector('input[name="action"]');
    const planInput   = document.querySelector('input[name="plan"]');
    const confirmBtn  = document.querySelector('.plan-change-confirm-btn');
    const confirmText = document.querySelector('.plan-change-confirm-text');

    planCards.forEach(card => {
        card.addEventListener('click', () => {
            planCards.forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');

            const plan       = card.dataset.plan;
            const action     = card.dataset.action; // 'upgrade' or 'downgrade'
            const planLabel  = card.querySelector('.plan-name')?.textContent;
            const planPrice  = card.querySelector('.plan-price')?.textContent;

            if (planInput)   planInput.value  = plan;
            if (actionInput) actionInput.value = action;

            if (confirmBtn) {
                confirmBtn.disabled     = false;
                confirmBtn.textContent  = action === 'upgrade'
                    ? `Upgrade to ${planLabel}`
                    : `Downgrade to ${planLabel}`;
            }

            if (confirmText) {
                confirmText.innerHTML = action === 'upgrade'
                    ? `You are upgrading to <strong>${planLabel}</strong> (${planPrice}/semester). Admin will activate after payment.`
                    : `You are scheduling a downgrade to <strong>${planLabel}</strong>. Takes effect after your current subscription expires.`;
                confirmText.style.display = 'block';
            }
        });
    });
}


// ============================================================
// RATING DISTRIBUTION BARS — animate on load
// ============================================================
function initRatingBars() {
    document.querySelectorAll('.rating-bar-fill').forEach(bar => {
        const width = bar.dataset.width || '0';
        // Animate after slight delay
        setTimeout(() => {
            bar.style.width = width + '%';
        }, 300);
    });
}


// ============================================================
// DASHBOARD CHARTS (using Chart.js if included)
// ============================================================
function initDashboardCharts() {
    // Revenue chart
    const revenueCanvas = document.getElementById('revenueChart');
    if (revenueCanvas && window.Chart) {
        const labels  = JSON.parse(revenueCanvas.dataset.labels  || '[]');
        const amounts = JSON.parse(revenueCanvas.dataset.amounts || '[]');

        new Chart(revenueCanvas.getContext('2d'), {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label:           'Revenue (₦)',
                    data:            amounts.map(v => v / 100),
                    borderColor:     '#0b3d91',
                    backgroundColor: 'rgba(11, 61, 145, 0.08)',
                    borderWidth:     2,
                    tension:         0.4,
                    fill:            true,
                    pointRadius:     4,
                    pointBackgroundColor: '#0b3d91',
                }],
            },
            options: {
                responsive:          true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => '₦' + ctx.parsed.y.toLocaleString('en-NG', {
                                minimumFractionDigits: 2,
                            }),
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 12 } },
                    },
                    y: {
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        ticks: {
                            font: { size: 12 },
                            callback: val => '₦' + CampusLink.formatNumber(val),
                        },
                    },
                },
            },
        });
    }

    // Rating donut chart
    const ratingCanvas = document.getElementById('ratingChart');
    if (ratingCanvas && window.Chart) {
        const dist = JSON.parse(ratingCanvas.dataset.dist || '{}');

        new Chart(ratingCanvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels:   ['5★', '4★', '3★', '2★', '1★'],
                datasets: [{
                    data:            [dist[5]||0, dist[4]||0, dist[3]||0, dist[2]||0, dist[1]||0],
                    backgroundColor: ['#1ea952', '#0b3d91', '#f59e0b', '#fb923c', '#e53e3e'],
                    borderWidth:     0,
                }],
            },
            options: {
                responsive:          true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels:   { font: { size: 12 } },
                    },
                },
                cutout: '65%',
            },
        });
    }
}


// ============================================================
// VENDOR NOTIFICATION MARK READ
// ============================================================
function initVendorNotifications() {
    document.querySelectorAll('.notification-item[data-notif-id]').forEach(item => {
        item.addEventListener('click', async () => {
            const notifId = item.dataset.notifId;
            if (!notifId || item.classList.contains('read')) return;

            try {
                await CampusLink.ajax('/notifications/mark-read', 'POST', {
                    csrf_token:      CampusLink.getCsrf(),
                    notification_id: notifId,
                });
                item.classList.remove('unread');
                item.classList.add('read');
                const dot = item.querySelector('.notification-dot');
                if (dot) dot.style.background = 'transparent';
            } catch {
                // Silent fail
            }
        });
    });
}


// ============================================================
// CHARACTER COUNTER FOR TEXTAREAS
// ============================================================
function initCharCounters() {
    document.querySelectorAll('textarea[data-max-chars]').forEach(textarea => {
        const max     = parseInt(textarea.dataset.maxChars);
        const counter = document.querySelector(
            `[data-counter-for="${textarea.id || textarea.name}"]`
        );

        if (!counter) return;

        const update = () => {
            const remaining = max - textarea.value.length;
            counter.textContent = `${textarea.value.length} / ${max}`;
            counter.className   = 'review-char-counter';

            if (remaining < 50)  counter.classList.add('warning');
            if (remaining < 20)  counter.classList.add('danger');
        };

        textarea.addEventListener('input', update);
        update();
    });
}


// ============================================================
// VENDOR PROFILE FORM — unsaved changes warning
// ============================================================
function initUnsavedChangesWarning() {
    const form = document.querySelector('.vendor-profile-form');
    if (!form) return;

    let isDirty = false;

    form.addEventListener('input', () => {
        isDirty = true;
    });

    form.addEventListener('submit', () => {
        isDirty = false;
    });

    window.addEventListener('beforeunload', e => {
        if (isDirty) {
            e.preventDefault();
            e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
            return e.returnValue;
        }
    });
}


// ============================================================
// DOM READY
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    initReviewReplies();
    initComplaintResponses();
    initVendorLogoPreview();
    initPlanChange();
    initRatingBars();
    initDashboardCharts();
    initVendorNotifications();
    initCharCounters();
    initUnsavedChangesWarning();
});