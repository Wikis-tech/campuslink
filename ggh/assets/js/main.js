'use strict';

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
    toggle.innerHTML = nav.classList.contains('open')
      ? '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>'
      : '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>';
  });
  document.addEventListener('click', (e) => {
    if (!nav.contains(e.target) && !toggle.contains(e.target)) nav.classList.remove('open');
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
    <a href="pages/browse.html?cat=${cat.id}" class="cat-card reveal">
      <div class="cat-icon"><i data-lucide="${cat.icon}"></i></div>
      <div class="cat-name">${cat.name}</div>
      <div class="cat-count">${cat.count} vendor${cat.count !== 1 ? 's' : ''}</div>
    </a>
  `).join('');
  lucide.createIcons();
  initRevealAnimations();
}

const VENDORS = [
  { id:1, name:'Glam Studio NG', category:'Beauty & Grooming', rating:4.9, reviews:84, desc:'Professional hairstyling, braiding, and makeup services. Campus pickup available.', location:'Block C, Female Hostel', price:'₦2,000 – ₦15,000', phone:'+2348012345678', whatsapp:'+2348012345678', verified:true, featured:true, img:'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?w=600&auto=format&fit=crop&q=60', logo:'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=100&auto=format&fit=crop&q=60' },
  { id:2, name:'TechFix Campus', category:'Tech & Gadgets', rating:4.7, reviews:56, desc:'Laptop, phone, and device repairs. Fast turnaround. Student-run, always on campus.', location:'Engineering Block, Room 12', price:'₦1,500 – ₦25,000', phone:'+2348023456789', whatsapp:'+2348023456789', verified:true, featured:false, img:'https://images.unsplash.com/photo-1518770660439-4636190af475?w=600&auto=format&fit=crop&q=60', logo:'https://images.unsplash.com/photo-1563206767-5b18f218e8de?w=100&auto=format&fit=crop&q=60' },
  { id:3, name:"Scholar's Hub", category:'Academic Support', rating:4.8, reviews:102, desc:'Assignment help, past questions, research writing, and thesis editing services.', location:'Library Annex, Table 4', price:'₦500 – ₦10,000', phone:'+2348034567890', whatsapp:'+2348034567890', verified:true, featured:true, img:'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=600&auto=format&fit=crop&q=60', logo:'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&auto=format&fit=crop&q=60' },
  { id:4, name:'Flavours Kitchen', category:'Food & Catering', rating:4.6, reviews:73, desc:'Home-cooked Nigerian meals delivered to your hostel. Order by 12PM for dinner.', location:'Catering Block', price:'₦800 – ₦3,500', phone:'+2348045678901', whatsapp:'+2348045678901', verified:true, featured:false, img:'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=600&auto=format&fit=crop&q=60', logo:'https://images.unsplash.com/photo-1581299894007-aaa50297cf16?w=100&auto=format&fit=crop&q=60' },
  { id:5, name:'StitchCraft Tailors', category:'Fashion & Tailoring', rating:4.5, reviews:38, desc:'Custom-fit clothing, repairs, and alterations. Native wear specialists on campus.', location:'Main Gate Plaza', price:'₦3,000 – ₦20,000', phone:'+2348056789012', whatsapp:'+2348056789012', verified:true, featured:false, img:'https://images.unsplash.com/photo-1558769132-cb1aea458c5e?w=600&auto=format&fit=crop&q=60', logo:'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?w=100&auto=format&fit=crop&q=60' },
  { id:6, name:'PrintZone Campus', category:'Printing & Stationery', rating:4.7, reviews:91, desc:'Fast printing, binding, lamination, and ID card printing. Open 7am–9pm daily.', location:'Student Union Building', price:'₦20 – ₦5,000', phone:'+2348067890123', whatsapp:'+2348067890123', verified:true, featured:true, img:'https://images.unsplash.com/photo-1562564055-71e051d33c19?w=600&auto=format&fit=crop&q=60', logo:'https://images.unsplash.com/photo-1572021335469-31706a17aaef?w=100&auto=format&fit=crop&q=60' },
];

function initVendors() {
  const grid = document.getElementById('vendorsGrid');
  if (!grid) return;
  grid.innerHTML = VENDORS.map(v => renderVendorCard(v)).join('');
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
          <a href="pages/vendor-profile.html?id=${v.id}" class="btn btn-sm btn-outline">View</a>
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
  window.location.href = `pages/browse.html?${params.toString()}`;
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