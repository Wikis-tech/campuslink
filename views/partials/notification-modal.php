<!-- Notification Modal System -->
<style>
/* Notification Modal Styles */
.notification-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    display: none;
    z-index: 10000;
    animation: modalFadeIn 0.2s ease-out;
}

.notification-modal.show {
    display: flex;
    align-items: center;
    justify-content: center;
}

.notification-modal-content {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    overflow: hidden;
    animation: modalSlideIn 0.3s ease-out;
    position: relative;
}

.notification-modal-header {
    padding: 1.5rem 1.5rem 1rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.notification-modal-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.notification-modal-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.notification-modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #64748b;
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 6px;
    transition: all 0.15s;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
}

.notification-modal-close:hover {
    background: #f1f5f9;
    color: #374151;
}

.notification-modal-body {
    padding: 1.5rem;
    max-height: 60vh;
    overflow-y: auto;
}

.notification-modal-message {
    font-size: 0.95rem;
    line-height: 1.6;
    color: #374151;
    margin: 0;
}

.notification-modal-meta {
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.8rem;
    color: #64748b;
}

.notification-modal-actions {
    margin-top: 1.5rem;
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
}

.notification-modal-btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.notification-modal-btn-primary {
    background: #1a56db;
    color: #fff;
}

.notification-modal-btn-primary:hover {
    background: #1d4ed8;
}

.notification-modal-btn-secondary {
    background: #f1f5f9;
    color: #374151;
}

.notification-modal-btn-secondary:hover {
    background: #e2e8f0;
}

/* Icon backgrounds */
.ni-info { background: #eff6ff; }
.ni-success { background: #f0fdf4; }
.ni-warning { background: #fffbeb; }
.ni-error { background: #fef2f2; }
.ni-system { background: #f5f3ff; }

/* Animations */
@keyframes modalFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: scale(0.9) translateY(-20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

/* Responsive */
@media (max-width: 640px) {
    .notification-modal-content {
        width: 95%;
        margin: 1rem;
    }

    .notification-modal-header {
        padding: 1.25rem 1.25rem 0.75rem;
    }

    .notification-modal-body {
        padding: 1.25rem;
    }

    .notification-modal-actions {
        flex-direction: column;
    }

    .notification-modal-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<!-- Notification Modal HTML -->
<div id="notificationModal" class="notification-modal">
    <div class="notification-modal-content">
        <div class="notification-modal-header">
            <h3 class="notification-modal-title">
                <span class="notification-modal-icon" id="modalIcon"></span>
                <span id="modalTitle">Notification</span>
            </h3>
            <button class="notification-modal-close" id="modalClose">&times;</button>
        </div>
        <div class="notification-modal-body">
            <p class="notification-modal-message" id="modalMessage"></p>
            <div class="notification-modal-meta">
                <span id="modalTime"></span>
                <span id="modalType"></span>
            </div>
            <div class="notification-modal-actions">
                <button class="notification-modal-btn notification-modal-btn-secondary" id="modalMarkRead">
                    <i data-lucide="check" style="width:14px;height:14px;"></i>
                    Mark as Read
                </button>
                <a href="#" class="notification-modal-btn notification-modal-btn-primary" id="modalViewLink">
                    <i data-lucide="external-link" style="width:14px;height:14px;"></i>
                    View Details
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Notification Modal System
class NotificationModal {
    constructor() {
        this.modal = document.getElementById('notificationModal');
        this.closeBtn = document.getElementById('modalClose');
        this.markReadBtn = document.getElementById('modalMarkRead');
        this.viewLink = document.getElementById('modalViewLink');
        this.currentNotification = null;

        this.init();
    }

    init() {
        // Close modal when clicking outside or on close button
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal || e.target === this.closeBtn) {
                this.close();
            }
        });

        // Mark as read functionality
        this.markReadBtn.addEventListener('click', () => {
            if (this.currentNotification) {
                this.markAsRead(this.currentNotification.id);
            }
        });

        // Keyboard support
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal.classList.contains('show')) {
                this.close();
            }
        });
    }

    show(notification) {
        this.currentNotification = notification;

        // Set modal content
        document.getElementById('modalTitle').textContent = notification.title;
        document.getElementById('modalMessage').textContent = notification.message;
        document.getElementById('modalTime').textContent = this.formatTime(notification.created_at);
        document.getElementById('modalType').textContent = notification.type.charAt(0).toUpperCase() + notification.type.slice(1);

        // Set icon
        const iconElement = document.getElementById('modalIcon');
        const iconMap = {
            'payment': 'credit-card',
            'review': 'star',
            'complaint': 'clipboard',
            'approval': 'check-circle',
            'reminder': 'clock',
            'warning': 'alert-triangle',
            'error': 'x-circle',
            'success': 'check-circle',
            'system': 'settings',
            'info': 'info'
        };

        const iconName = iconMap[notification.type] || 'info';
        const iconClass = this.getIconClass(notification.type);

        iconElement.innerHTML = `<i data-lucide="${iconName}"></i>`;
        iconElement.className = `notification-modal-icon ${iconClass}`;

        // Set view link
        this.viewLink.href = notification.link || '#';
        if (!notification.link) {
            this.viewLink.style.display = 'none';
        } else {
            this.viewLink.style.display = 'inline-flex';
        }

        // Show mark as read button only if unread
        if (notification.is_read) {
            this.markReadBtn.style.display = 'none';
        } else {
            this.markReadBtn.style.display = 'inline-flex';
        }

        // Show modal
        this.modal.classList.add('show');
        document.body.style.overflow = 'hidden';

        // Re-initialize Lucide icons
        if (typeof lucide !== 'undefined' && lucide.createIcons) {
            lucide.createIcons();
        }
    }

    close() {
        this.modal.classList.remove('show');
        document.body.style.overflow = '';
        this.currentNotification = null;
    }

    formatTime(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now - date;
        const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

        if (diffDays === 0) {
            return 'Today';
        } else if (diffDays === 1) {
            return 'Yesterday';
        } else if (diffDays < 7) {
            return `${diffDays} days ago`;
        } else {
            return date.toLocaleDateString();
        }
    }

    getIconClass(type) {
        const classMap = {
            'payment': 'ni-success',
            'review': 'ni-info',
            'complaint': 'ni-warning',
            'approval': 'ni-success',
            'reminder': 'ni-warning',
            'warning': 'ni-warning',
            'error': 'ni-error',
            'success': 'ni-success',
            'system': 'ni-system',
            'info': 'ni-info'
        };
        return classMap[type] || 'ni-info';
    }

    async markAsRead(notificationId) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ||
                            document.querySelector('input[name="csrf_token"]')?.value;

            const response = await fetch('<?= SITE_URL ?>/notifications/mark-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    notification_id: notificationId,
                    csrf_token: csrfToken
                })
            });

            const data = await response.json();

            if (data.success) {
                // Update UI
                const notificationElement = document.querySelector(`[data-notif-id="${notificationId}"]`);
                if (notificationElement) {
                    notificationElement.classList.remove('unread');
                    notificationElement.classList.add('read');
                }

                // Update unread count
                const unreadElements = document.querySelectorAll('.notification-item.unread');
                const newCount = unreadElements.length - 1;
                const subtitleElement = document.querySelector('.dashboard-page-subtitle');
                if (subtitleElement) {
                    subtitleElement.textContent = `${Math.max(0, newCount)} unread`;
                }

                // Hide mark as read button
                this.markReadBtn.style.display = 'none';

                // Show success message
                this.showToast('Marked as read', 'success');
            } else {
                this.showToast('Could not mark as read', 'error');
            }
        } catch (error) {
            this.showToast('Network error', 'error');
        }
    }

    showToast(message, type = 'info') {
        // Try to use existing toast system if available
        if (typeof CampusLink !== 'undefined' && CampusLink.toast) {
            CampusLink.toast(message, type);
        } else {
            // Fallback simple alert
            alert(message);
        }
    }
}

// Initialize modal when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.notificationModal = new NotificationModal();

    // Add click handlers to notification items
    document.addEventListener('click', function(e) {
        const notificationItem = e.target.closest('.notification-item, .notif-card, tr[data-notif-id]');
        if (notificationItem && !e.target.closest('a[href]:not([href="#"])')) {
            e.preventDefault();

            // Extract notification data from the element
            let notificationData = {};

            // Handle different notification structures
            if (notificationItem.classList.contains('notification-item')) {
                // Vendor notifications
                notificationData = {
                    id: notificationItem.dataset.notifId,
                    title: notificationItem.querySelector('.notification-title')?.textContent?.trim(),
                    message: notificationItem.querySelector('.notification-message')?.textContent?.trim(),
                    type: notificationItem.dataset.type || 'info',
                    created_at: notificationItem.querySelector('[data-time]')?.dataset.time || new Date().toISOString(),
                    is_read: notificationItem.classList.contains('read'),
                    link: notificationItem.querySelector('a')?.href || notificationItem.href
                };
            } else if (notificationItem.classList.contains('notif-card')) {
                // User notifications — read from data attributes set on the card
                notificationData = {
                    id:         notificationItem.dataset.notifId || notificationItem.dataset.id,
                    title:      notificationItem.querySelector('.notif-title')?.textContent?.trim() || 'Notification',
                    message:    notificationItem.querySelector('.notif-msg')?.textContent?.trim()   || '',
                    type:       notificationItem.dataset.type    || 'info',
                    created_at: notificationItem.dataset.time    || new Date().toISOString(),
                    is_read:    notificationItem.classList.contains('read'),
                    link:       notificationItem.querySelector('a[href]:not([href="#"])')?.href || '#'
                };
            } else if (notificationItem.tagName === 'TR' && notificationItem.dataset.notifId) {
                // Admin notifications (table rows) — read from data attributes
                notificationData = {
                    id:         notificationItem.dataset.notifId,
                    title:      notificationItem.dataset.title   || notificationItem.querySelector('.an-title')?.textContent?.trim() || 'Notification',
                    message:    notificationItem.dataset.message || notificationItem.querySelector('.an-msg')?.textContent?.trim()   || '',
                    type:       notificationItem.dataset.type    || 'info',
                    created_at: notificationItem.dataset.time    || new Date().toISOString(),
                    is_read:    notificationItem.dataset.isRead  === '1',
                    link:       notificationItem.dataset.link    || '#'
                };
            }

            // Fill in missing data if available
            if (!notificationData.title) {
                notificationData.title = notificationItem.querySelector('h3, .title, strong, .an-title')?.textContent?.trim() || 'Notification';
            }
            if (!notificationData.message) {
                notificationData.message = notificationItem.querySelector('p, .message, .content, .an-msg')?.textContent?.trim() || '';
            }

            window.notificationModal.show(notificationData);
        }
    });
});
</script>