'use strict';
// ============================================================
// CAMPUSLINK — DASHBOARD JAVASCRIPT
// Tab switching, charts, real-time notifications
// ============================================================

const Dashboard = {
  currentTab: null,

  init() {
    this.initTabs();
    this.initSidebar();
    this.initCharts();
    this.initCounters();
    lucide.createIcons();
  },

  initTabs() {
    document.querySelectorAll('[data-tab]').forEach(link => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        this.switchTab(link.dataset.tab, link.dataset.label);
      });
    });

    // Handle URL hash on load
    const hash = window.location.hash.replace('#', '');
    if (hash && document.getElementById(`tab-${hash}`)) {
      this.switchTab(hash);
    } else {
      // Activate first tab
      const first = document.querySelector('[data-tab]');
      if (first) this.switchTab(first.dataset.tab, first.dataset.label);
    }
  },

  switchTab(tab, label = '') {
    // Deactivate all
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('[data-tab]').forEach(l => l.classList.remove('active'));

    // Activate target
    const panel = document.getElementById(`tab-${tab}`);
    const link  = document.querySelector(`[data-tab="${tab}"]`);
    if (panel) panel.classList.add('active');
    if (link)  link.classList.add('active');

    // Update topbar title
    const titleEl = document.getElementById('topbarTitle');
    if (titleEl) titleEl.textContent = label || tab.charAt(0).toUpperCase() + tab.slice(1);

    // Update URL hash
    history.replaceState(null, '', `#${tab}`);

    // Close sidebar on mobile
    if (window.innerWidth < 900) {
      document.getElementById('dashSidebar')?.classList.remove('open');
      document.getElementById('sidebarOverlay')?.classList.remove('show');
    }

    this.currentTab = tab;
    lucide.createIcons();
  },

  initSidebar() {
    const toggle  = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('dashSidebar');
    const overlay = document.getElementById('sidebarOverlay');

    toggle?.addEventListener('click', () => {
      sidebar?.classList.toggle('open');
      overlay?.classList.toggle('show');
      toggle.classList.toggle('open');
    });

    overlay?.addEventListener('click', () => {
      sidebar?.classList.remove('open');
      overlay.classList.remove('show');
      toggle?.classList.remove('open');
    });
  },

  initCounters() {
    const statEls = document.querySelectorAll('[data-count]');
    if (!statEls.length) return;

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const el      = entry.target;
          const target  = parseFloat(el.dataset.count);
          const suffix  = el.dataset.suffix || '';
          const dec     = el.dataset.decimal === 'true';
          const dur     = 1400;
          let start     = null;

          const step = (ts) => {
            if (!start) start = ts;
            const prog = Math.min((ts - start) / dur, 1);
            const ease = 1 - Math.pow(1 - prog, 3);
            const curr = dec
              ? (ease * target).toFixed(1)
              : Math.floor(ease * target).toLocaleString();
            el.textContent = curr + suffix;
            if (prog < 1) requestAnimationFrame(step);
          };
          requestAnimationFrame(step);
          observer.unobserve(el);
        }
      });
    }, { threshold: 0.5 });

    statEls.forEach(el => observer.observe(el));
  },

  initCharts() {
    // Simple CSS bar chart animation
    document.querySelectorAll('.rb-fill').forEach((bar, i) => {
      const width = bar.style.width;
      bar.style.width = '0%';
      setTimeout(() => {
        bar.style.transition = 'width 1s cubic-bezier(0.4,0,0.2,1)';
        bar.style.width = width;
      }, 300 + i * 100);
    });

    // Subscription progress bar
    document.querySelectorAll('.sub-prog-fill').forEach(bar => {
      const width = bar.style.width;
      bar.style.width = '0%';
      setTimeout(() => {
        bar.style.transition = 'width 1.2s cubic-bezier(0.4,0,0.2,1)';
        bar.style.width = width;
      }, 400);
    });
  },

  // ===== Reply system =====
  showReplyBox(id) {
    const box = document.getElementById(`reply-box-${id}`);
    if (box) {
      box.style.display = 'block';
      box.querySelector('textarea')?.focus();
    }
  },

  cancelReply(id) {
    const box = document.getElementById(`reply-box-${id}`);
    if (box) box.style.display = 'none';
  },

  async submitReply(reviewId, vendorId) {
    const box      = document.getElementById(`reply-box-${reviewId}`);
    const textarea = box?.querySelector('textarea');
    const text     = textarea?.value?.trim();

    if (!text || text.length < 5) {
      showToast('Please write a reply before submitting.', 'error');
      return;
    }

    try {
      const res  = await fetch('/api/vendor-action', {
        method:  'POST',
        headers: {
          'Content-Type':     'application/json',
          'X-CSRF-Token':     document.querySelector('meta[name="csrf-token"]')?.content || '',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
          action:    'reply_review',
          review_id: reviewId,
          vendor_id: vendorId,
          reply:     text,
        }),
      });
      const data = await res.json();
      if (data.success) {
        showToast('Reply submitted and pending admin approval.', 'success');
        box.style.display = 'none';
        if (textarea) textarea.value = '';
      } else {
        showToast(data.error || 'Failed to submit reply.', 'error');
      }
    } catch {
      showToast('Network error. Please try again.', 'error');
    }
  },

  // ===== Profile save =====
  async saveProfile(formId) {
    const form = document.getElementById(formId);
    if (!form) return;

    const data = Object.fromEntries(new FormData(form).entries());

    try {
      const res  = await fetch('/api/vendor-action', {
        method:  'POST',
        headers: {
          'Content-Type':     'application/json',
          'X-CSRF-Token':     document.querySelector('meta[name="csrf-token"]')?.content || '',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ action: 'update_profile', ...data }),
      });
      const result = await res.json();
      if (result.success) showToast('Profile updated successfully!', 'success');
      else showToast(result.error || 'Update failed.', 'error');
    } catch {
      showToast('Network error.', 'error');
    }
  },
};

// ===== Expose helpers =====
window.switchTab    = (tab, label)    => Dashboard.switchTab(tab, label);
window.toggleSidebar = ()             => {
  document.getElementById('dashSidebar')?.classList.toggle('open');
  document.getElementById('sidebarOverlay')?.classList.toggle('show');
};
window.showReplyBox  = (id)           => Dashboard.showReplyBox(id);
window.cancelReply   = (id)           => Dashboard.cancelReply(id);
window.submitReply   = (rid, vid)     => Dashboard.submitReply(rid, vid);
window.saveProfile   = (formId)       => Dashboard.saveProfile(formId);

document.addEventListener('DOMContentLoaded', () => Dashboard.init());