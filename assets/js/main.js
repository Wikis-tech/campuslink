'use strict';

// Get the base path for routing (handles subdirectory installations)
function getBasePath() {
  const path = window.location.pathname;
  if (path.includes('/campuslink')) return '/campuslink';
  if (path.includes('/browse') || path.includes('/categories') || path.includes('/login')) {
    const parts = path.split('/');
    for (let i = 0; i < parts.length; i++) {
      if (['browse', 'categories', 'login', 'register', 'how-it-works', 'vendor-register', 'pricing', 'terms', 'privacy', 'complaint', 'vendor-profile', 'forgot-password', 'reset-password', 'verify-email', 'logout'].includes(parts[i])) {
        return parts.slice(0, i).join('/') || '';
      }
    }
  }
  return '';
}

const BASE_PATH = getBasePath();

document.addEventListener('DOMContentLoaded', () => {
  lucide.createIcons();
  initHeader();
  initMobileNav();
  initRevealAnimations();
  initCounters();
  initCategories();
  initVendors();
  initSearch();
});

function initHeader() {
  const header = document.getElementById('siteHeader');
  if (!header) return;
  const onScroll = () => { header.classList.toggle('scrolled', window.scrollY > 30); };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
}

function initMobileNav() {
  const toggle = document.getElementById('navToggle');
  const nav = document.getElementById('mainNav');
  if (!toggle || !nav) return;
  toggle.addEventListener('click', () => {
    nav.classList.toggle('open');
    toggle.classList.toggle('open');
  });
  document.addEventListener('click', (e) => {
    if (!nav.contains(e.target) && !toggle.contains(e.target)) {
      nav.classList.remove('open');
      toggle.classList.remove('open');
    }
  });
}

function initRevealAnimations() {
  const els = document.querySelectorAll('.reveal');
  if (!els.length) return;
  const obs = new IntersectionObserver((entries) => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); obs.unobserve(e.target); } });
  }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
  els.forEach(el => obs.observe(el));
}

function initCounters() {
  const stats = document.querySelectorAll('.stat-item');
  if (!stats.length) return;
  const obs = new IntersectionObserver((entries) => {
    entries.forEach(e => { if (e.isIntersecting) { animateCounters(); obs.disconnect(); } });
  }, { threshold: 0.5 });
  if (stats[0]) obs.observe(stats[0]);
}

function animateCounters() {
  const items = document.querySelectorAll('.stat-item');
  items.forEach((item, i) => {
    const valEl = item.querySelector('.stat-val');
    if (!valEl) return;
    const target = parseFloat(item.dataset.count);
    const suffix = item.dataset.suffix || '';
    const isDecimal = item.dataset.decimal === 'true';
    const duration = 1600;
    const delay = i * 120;
    setTimeout(() => {
      let start = null;
      const step = (ts) => {
        if (!start) start = ts;
        const progress = Math.min((ts - start) / duration, 1);
        const ease = 1 - Math.pow(1 - progress, 3);
        const current = isDecimal ? (ease * target).toFixed(1) : Math.floor(ease * target).toLocaleString();
        valEl.textContent = current + suffix;
        if (progress < 1) requestAnimationFrame(step);
      };
      requestAnimationFrame(step);
    }, delay);
  });
}

const CATEGORIES = [
  { id: 'beauty', name: 'Beauty & Grooming', icon: 'scissors', count: 42 },
  { id: 'tech', name: 'Tech & Gadgets', icon: 'smartphone', count: 38 },
  { id: 'academic', name: 'Academic Support', icon: 'book-open', count: 55 },
  { id: 'food', name: 'Food & Catering', icon: 'utensils', count: 61 },
  { id: 'fashion', name: 'Fashion & Tailoring', icon: 'shirt', count: 34 },
  { id: 'printing', name: 'Printing & Stationery', icon: 'printer', count: 28 },
  { id: 'repairs', name: 'Repairs & Maintenance', icon: 'wrench', count: 22 },
  { id: 'photography', name: 'Photography', icon: 'camera', count: 19 },
  { id: 'tutoring', name: 'Tutoring', icon: 'graduation-cap', count: 47 },
  { id: 'laundry', name: 'Laundry Services', icon: 'washing-machine', count: 15 },
  { id: 'transport', name: 'Transport & Logistics', icon: 'car', count: 12 },
  { id: 'events', name: 'Events & Décor', icon: 'party-popper', count: 18 },
];

function initCategories() {
  const grid = document.getElementById('categoriesGrid');
  if (!grid) return;
  grid.innerHTML = CATEGORIES.map(cat => `
    <a href="${BASE_PATH}/browse?cat=${cat.id}" class="cat-card reveal">
      <div class="cat-icon"><i data-lucide="${cat.icon}"></i></div>
      <div class="cat-name">${cat.name}</div>
      <div class="cat-count">${cat.count} vendor${cat.count !== 1 ? 's' : ''}</div>
    </a>
  `).join('');
  lucide.createIcons();
  initRevealAnimations();
}

async function initVendors() {
  const grid = document.getElementById('vendorsGrid');
  if (!grid) return;
  try {
    const response = await fetch(`${BASE_PATH}/api/get-vendors.php?sort=featured&per=6`);
    const data = await response.json();
    if (data.success && data.vendors) {
      grid.innerHTML = data.vendors.map(v => renderVendorCard(v)).join('');
    } else {
      grid.innerHTML = '<p>No vendors found.</p>';
    }
  } catch (error) {
    console.error('Error loading vendors:', error);
    grid.innerHTML = '<p>Error loading vendors.</p>';
  }
  lucide.createIcons();
  initRevealAnimations();
}

function renderVendorCard(v) {
  const stars = '★'.repeat(Math.floor(v.rating)) + (v.rating % 1 ? '½' : '');
  return `
    <div class="vendor-card reveal">
      <div class="vendor-card-img">
        <img src="${v.img}" alt="${v.name}" loading="lazy" />
        ${v.verified ? `<div class="vendor-badge"><i data-lucide="shield-check"></i> Verified</div>` : ''}
        ${v.featured ? `<div class="plan-badge-featured">Featured</div>` : ''}
        <div class="vendor-logo"><img src="${v.logo}" alt="${v.name} logo" loading="lazy" /></div>
      </div>
      <div class="vendor-card-body">
        <div class="vendor-card-header">
          <div>
            <div class="vendor-name">${v.name}</div>
            <span class="vendor-cat">${v.category}</span>
          </div>
          <div class="vendor-rating">
            <span class="stars">${stars}</span>
            <span class="rating-val">${v.rating}</span>
          </div>
        </div>
        <p class="vendor-desc">${v.desc}</p>
        <div class="vendor-meta">
          <div class="vendor-meta-item"><i data-lucide="map-pin"></i> ${v.location}</div>
          <div class="vendor-meta-item"><i data-lucide="tag"></i> ${v.price}</div>
        </div>
        <div class="vendor-actions">
          <a href="tel:${v.phone}" class="btn btn-sm btn-call"><i data-lucide="phone"></i> Call</a>
          <a href="https://wa.me/${v.whatsapp.replace(/\D/g,'')}" target="_blank" rel="noopener" class="btn btn-sm btn-whatsapp"><i data-lucide="message-circle"></i> WhatsApp</a>
          <a href="${BASE_PATH}/vendor-profile?id=${v.id}" class="btn btn-sm btn-outline">View</a>
        </div>
      </div>
    </div>
  `;
}

function initSearch() {
  const input = document.getElementById('searchInput');
  if (input) { input.addEventListener('keydown', (e) => { if (e.key === 'Enter') doSearch(); }); }
}

function doSearch() {
  const query = document.getElementById('searchInput')?.value?.trim() || '';
  const cat = document.getElementById('searchCat')?.value || '';
  const params = new URLSearchParams();
  if (query) params.set('q', query);
  if (cat) params.set('cat', cat);
  window.location.href = `${BASE_PATH}/browse?${params.toString()}`;
}

function showToast(message, type = 'info', duration = 4000) {
  let container = document.querySelector('.toast-container');
  if (!container) { container = document.createElement('div'); container.className = 'toast-container'; document.body.appendChild(container); }
  const icons = { success: 'check-circle', error: 'x-circle', warning: 'alert-triangle', info: 'info' };
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `<i data-lucide="${icons[type] || 'info'}"></i><span>${message}</span>`;
  container.appendChild(toast);
  lucide.createIcons({ nodes: [toast] });
  setTimeout(() => { toast.style.animation = 'slideInRight 0.3s ease reverse'; setTimeout(() => toast.remove(), 300); }, duration);
}

function openModal(id) { const o = document.getElementById(id); if (o) o.classList.add('open'); document.body.style.overflow = 'hidden'; }
function closeModal(id) { const o = document.getElementById(id); if (o) o.classList.remove('open'); document.body.style.overflow = ''; }
function getCSRF() { const m = document.querySelector('meta[name="csrf-token"]'); return m ? m.content : ''; }

function validateField(input) {
  const errorEl = input.parentElement.querySelector('.form-error');
  const value = input.value.trim();
  let valid = true, message = '';
  if (input.required && !value) { valid = false; message = 'This field is required.'; }
  else if (input.type === 'email' && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) { valid = false; message = 'Enter a valid email address.'; }
  else if (input.type === 'tel' && value && !/^\+?[\d\s\-]{10,15}$/.test(value)) { valid = false; message = 'Enter a valid phone number.'; }
  else if (input.minLength > 0 && value.length < input.minLength) { valid = false; message = `Minimum ${input.minLength} characters required.`; }
  input.classList.toggle('error', !valid);
  if (errorEl) { errorEl.textContent = message; errorEl.classList.toggle('show', !valid); }
  return valid;
}

function validateForm(formEl) {
  const inputs = formEl.querySelectorAll('input[required], select[required], textarea[required]');
  let allValid = true;
  inputs.forEach(input => { if (!validateField(input)) allValid = false; });
  return allValid;
}

function toggleSaveVendor(vendorId, btn) {
  const saved = JSON.parse(localStorage.getItem('savedVendors') || '[]');
  const idx = saved.indexOf(vendorId);
  if (idx > -1) { saved.splice(idx, 1); showToast('Vendor removed from saved list.', 'info'); btn.classList.remove('saved'); }
  else { saved.push(vendorId); showToast('Vendor saved!', 'success'); btn.classList.add('saved'); }
  localStorage.setItem('savedVendors', JSON.stringify(saved));
}

window.doSearch = doSearch;
window.showToast = showToast;
window.openModal = openModal;
window.closeModal = closeModal;
window.toggleSaveVendor = toggleSaveVendor;

// Advanced animations and interactions
function initParallax() {
  const parallaxElements = document.querySelectorAll('.parallax-bg');
  if (!parallaxElements.length) return;

  const handleScroll = () => {
    const scrolled = window.pageYOffset;
    parallaxElements.forEach(el => {
      const rate = el.dataset.parallax || 0.5;
      el.style.transform = `translateY(${scrolled * rate}px)`;
    });
  };

  window.addEventListener('scroll', handleScroll, { passive: true });
  handleScroll();
}

function initMicroInteractions() {
  // Button hover effects
  document.querySelectorAll('.btn').forEach(btn => {
    btn.addEventListener('mouseenter', () => {
      btn.style.transform = 'translateY(-2px) scale(1.02)';
    });
    btn.addEventListener('mouseleave', () => {
      btn.style.transform = '';
    });
  });

  // Card hover effects
  document.querySelectorAll('.card-hover').forEach(card => {
    card.addEventListener('mouseenter', () => {
      card.style.transform = 'translateY(-8px)';
      card.style.boxShadow = '0 20px 40px rgba(0,0,0,0.15)';
    });
    card.addEventListener('mouseleave', () => {
      card.style.transform = '';
      card.style.boxShadow = '';
    });
  });

  // Image zoom on hover
  document.querySelectorAll('.img-interactive').forEach(img => {
    img.addEventListener('mouseenter', () => {
      img.style.transform = 'scale(1.05)';
    });
    img.addEventListener('mouseleave', () => {
      img.style.transform = 'scale(1)';
    });
  });
}

function initScrollAnimations() {
  const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        // Stagger children
        const children = entry.target.querySelectorAll('.stagger-item');
        children.forEach((child, index) => {
          setTimeout(() => {
            child.classList.add('visible');
          }, index * 100);
        });
      }
    });
  }, observerOptions);

  document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
}

function initSmoothScrolling() {
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
      }
    });
  });
}

function initFormEnhancements() {
  // Real-time validation
  document.querySelectorAll('input, textarea, select').forEach(field => {
    field.addEventListener('blur', () => validateField(field));
    field.addEventListener('input', () => {
      if (field.classList.contains('error')) {
        validateField(field);
      }
    });
  });

  // Form submission with loading states
  document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', (e) => {
      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i data-lucide="loader-2"></i> Processing...';
        lucide.createIcons({ nodes: [submitBtn] });
      }
    });
  });
}

function initAccessibility() {
  // Keyboard navigation
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      // Close modals
      document.querySelectorAll('.modal.open').forEach(modal => {
        modal.classList.remove('open');
        document.body.style.overflow = '';
      });
    }
  });

  // Focus management
  document.querySelectorAll('[data-modal]').forEach(trigger => {
    trigger.addEventListener('click', () => {
      const modalId = trigger.dataset.modal;
      const modal = document.getElementById(modalId);
      if (modal) {
        const focusableElements = modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
        if (focusableElements.length) {
          focusableElements[0].focus();
        }
      }
    });
  });
}

function initPerformanceOptimizations() {
  // Lazy load images
  const imageObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const img = entry.target;
        if (img.dataset.src) {
          img.src = img.dataset.src;
          img.classList.remove('lazy');
          imageObserver.unobserve(img);
        }
      }
    });
  });

  document.querySelectorAll('img[data-src]').forEach(img => imageObserver.observe(img));

  // Debounce scroll events
  let scrollTimeout;
  const debouncedScroll = () => {
    clearTimeout(scrollTimeout);
    scrollTimeout = setTimeout(() => {
      // Handle scroll-based animations
      initParallax();
    }, 16);
  };

  window.addEventListener('scroll', debouncedScroll, { passive: true });
}

// Initialize all advanced features
document.addEventListener('DOMContentLoaded', () => {
  initParallax();
  initMicroInteractions();
  initScrollAnimations();
  initSmoothScrolling();
  initFormEnhancements();
  initAccessibility();
  initPerformanceOptimizations();
});