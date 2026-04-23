/**
 * CampusLink — Motion & Animation Engine
 * Drop-in replacement / append for assets/js/landing.js
 *
 * ZERO functionality changes — only animation logic lives here.
 * All existing event listeners, form submissions, and routing
 * from the original landing.js remain untouched.
 */

(function () {
  'use strict';

  /* ────────────────────────────────────────────────────────────
     0. UTILITY
  ──────────────────────────────────────────────────────────── */
  const qs  = (sel, root = document) => root.querySelector(sel);
  const qsa = (sel, root = document) => [...root.querySelectorAll(sel)];
  const onLoad = (fn) => {
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn);
    else fn();
  };

  /* ────────────────────────────────────────────────────────────
     1. PAGE PROGRESS BAR
  ──────────────────────────────────────────────────────────── */
  function initProgressBar () {
    const bar = document.createElement('div');
    bar.id = 'cl-progress-bar';
    document.body.prepend(bar);

    // Animate to 80% quickly, finish on load
    bar.style.width = '0%';
    requestAnimationFrame(() => { bar.style.width = '70%'; });

    window.addEventListener('load', () => {
      bar.style.width = '100%';
      setTimeout(() => bar.classList.add('done'), 400);
      setTimeout(() => bar.remove(), 1200);
    });
  }

  /* ────────────────────────────────────────────────────────────
     2. CUSTOM CURSOR (desktop only)
  ──────────────────────────────────────────────────────────── */
  function initCursor () {
    // Only on pointer-fine devices (not touch)
    if (!window.matchMedia('(pointer: fine)').matches) return;

    const dot  = document.createElement('div'); dot.id  = 'cl-cursor';
    const ring = document.createElement('div'); ring.id = 'cl-cursor-ring';
    document.body.append(dot, ring);

    let mx = -100, my = -100;
    let rx = -100, ry = -100;

    document.addEventListener('mousemove', e => {
      mx = e.clientX; my = e.clientY;
      dot.style.left  = mx + 'px';
      dot.style.top   = my + 'px';
    });

    // Ring lags behind — smooth follow via RAF
    function followRing () {
      rx += (mx - rx) * 0.14;
      ry += (my - ry) * 0.14;
      ring.style.left = rx + 'px';
      ring.style.top  = ry + 'px';
      requestAnimationFrame(followRing);
    }
    followRing();

    // Expand on hoverable elements
    const hoverEls = 'a, button, [role="button"], .category-card, .vendor-card, .trust-card, .step-card, .plan-preview-card';
    document.addEventListener('mouseover', e => {
      if (e.target.closest(hoverEls)) document.body.classList.add('cursor-hover');
    });
    document.addEventListener('mouseout', e => {
      if (e.target.closest(hoverEls)) document.body.classList.remove('cursor-hover');
    });
  }

  /* ────────────────────────────────────────────────────────────
     3. SCROLL-AWARE HEADER GLASS EFFECT
  ──────────────────────────────────────────────────────────── */
  function initHeaderScroll () {
    const header = qs('#siteHeader');
    if (!header) return;

    let ticking = false;
    function update () {
      header.classList.toggle('scrolled', window.scrollY > 40);
      ticking = false;
    }
    window.addEventListener('scroll', () => {
      if (!ticking) { requestAnimationFrame(update); ticking = true; }
    }, { passive: true });
    update();
  }

  /* ────────────────────────────────────────────────────────────
     4. INTERSECTION OBSERVER — SCROLL REVEAL
     Watches elements with .reveal, .reveal-left, .reveal-right,
     .reveal-scale, .reveal-stagger, and .section-header
  ──────────────────────────────────────────────────────────── */
  function initScrollReveal () {
    const CLASSES = [
      '.reveal',
      '.reveal-left',
      '.reveal-right',
      '.reveal-scale',
      '.reveal-stagger',
      '.section-header',
      '.categories-section',
      '.trust-section',
      '.stats-section',
    ];

    const targets = qsa(CLASSES.join(','));
    if (!targets.length) return;

    const io = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          io.unobserve(entry.target); // fire once
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });

    targets.forEach(el => io.observe(el));
  }

  /* ────────────────────────────────────────────────────────────
     5. AUTO-INJECT REVEAL CLASSES
     Adds reveal classes to existing markup so you don't have to
     edit the PHP file — runs once on DOMContentLoaded.
  ──────────────────────────────────────────────────────────── */
  function injectRevealClasses () {
    // Section headers
    qsa('.section-header').forEach(el => el.classList.add('reveal'));

    // Category cards — staggered grid
    const catGrid = qs('.categories-grid');
    if (catGrid) catGrid.classList.add('reveal-stagger');

    // Vendor cards — staggered grid
    const vendorGrid = qs('.vendors-grid');
    if (vendorGrid) vendorGrid.classList.add('reveal-stagger');

    // Trust cards
    const trustGrid = qs('.trust-grid');
    if (trustGrid) trustGrid.classList.add('reveal-stagger');

    // Steps
    qsa('.step-card').forEach((el, i) => {
      el.classList.add('reveal');
      el.style.transitionDelay = (i * 0.10) + 's';
    });

    // Plan preview cards
    const planLeft  = qs('.vendor-cta-text');
    const planRight = qs('.vendor-cta-cards');
    if (planLeft)  planLeft.classList.add('reveal-left');
    if (planRight) planRight.classList.add('reveal-right');

    // Stats section
    const statsGrid = qs('.stats-grid');
    if (statsGrid) statsGrid.classList.add('reveal-stagger');

    // Disclaimer box
    const disclaimer = qs('.disclaimer-box');
    if (disclaimer) disclaimer.classList.add('reveal-scale');

    // "How disclaimer" text
    const howDisc = qs('.how-disclaimer');
    if (howDisc) howDisc.classList.add('reveal');

    // Footer columns
    qsa('.footer-col, .footer-brand').forEach((el, i) => {
      el.classList.add('reveal');
      el.style.transitionDelay = (i * 0.08) + 's';
    });
  }

  /* ────────────────────────────────────────────────────────────
     6. PARALLAX — HERO SECTION
  ──────────────────────────────────────────────────────────── */
  function initParallax () {
    const heroSection = qs('.hero-section');
    if (!heroSection) return;

    // Parallax on hero stats strip
    const heroStats = qs('.hero-stats');

    let ticking = false;
    function onScroll () {
      if (!ticking) {
        requestAnimationFrame(() => {
          const sy = window.scrollY;
          // Gentle upward drift for the overlay
          const overlay = qs('.hero-bg-overlay');
          if (overlay) {
            overlay.style.transform = `translateY(${sy * 0.18}px)`;
          }
          // Hero content slight counter-scroll for depth
          const heroContent = qs('.hero-content');
          if (heroContent && sy < window.innerHeight) {
            heroContent.style.transform = `translateY(${sy * 0.08}px)`;
          }
          ticking = false;
        });
        ticking = true;
      }
    }
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  /* ────────────────────────────────────────────────────────────
     7. COUNTER ANIMATION — STATISTICS
  ──────────────────────────────────────────────────────────── */
  function initCounters () {
    const counters = qsa('.counter[data-target]');
    if (!counters.length) return;

    const io = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        const el = entry.target;
        const target = parseInt(el.dataset.target, 10) || 0;
        if (target === 0) { el.textContent = '0'; io.unobserve(el); return; }

        const duration = 1800;
        const start    = performance.now();
        const startVal = 0;

        el.classList.add('ticking');

        function step (now) {
          const elapsed  = now - start;
          const progress = Math.min(elapsed / duration, 1);
          // Ease out quart
          const eased = 1 - Math.pow(1 - progress, 4);
          el.textContent = Math.round(startVal + (target - startVal) * eased).toLocaleString();
          if (progress < 1) requestAnimationFrame(step);
          else {
            el.textContent = target.toLocaleString();
            el.classList.remove('ticking');
            io.unobserve(el);
          }
        }
        requestAnimationFrame(step);
      });
    }, { threshold: 0.5 });

    counters.forEach(el => io.observe(el));
  }

  /* ────────────────────────────────────────────────────────────
     8. SMOOTH ANCHOR SCROLL
  ──────────────────────────────────────────────────────────── */
  function initSmoothScroll () {
    document.addEventListener('click', e => {
      const a = e.target.closest('a[href^="#"]');
      if (!a) return;
      const target = qs(a.getAttribute('href'));
      if (!target) return;
      e.preventDefault();
      const offset = 80; // header height
      const top = target.getBoundingClientRect().top + window.scrollY - offset;
      window.scrollTo({ top, behavior: 'smooth' });
    });
  }

  /* ────────────────────────────────────────────────────────────
     9. BUTTON RIPPLE — Mouse position aware
  ──────────────────────────────────────────────────────────── */
  function initRipple () {
    document.addEventListener('mousedown', e => {
      const btn = e.target.closest('.btn');
      if (!btn) return;
      const rect = btn.getBoundingClientRect();
      const x = ((e.clientX - rect.left) / rect.width  * 100).toFixed(1) + '%';
      const y = ((e.clientY - rect.top)  / rect.height * 100).toFixed(1) + '%';
      btn.style.setProperty('--rx', x);
      btn.style.setProperty('--ry', y);
    });
  }

  /* ────────────────────────────────────────────────────────────
     10. HERO SEARCH — Live category highlight
  ──────────────────────────────────────────────────────────── */
  function initHeroSearch () {
    const input    = qs('#heroSearchInput');
    const wrapper  = qs('.hero-search-wrapper');
    if (!input || !wrapper) return;

    input.addEventListener('focus', () => {
      wrapper.style.boxShadow = '0 0 0 3px rgba(255,255,255,0.4), 0 16px 48px rgba(0,0,0,0.2)';
    });
    input.addEventListener('blur', () => {
      wrapper.style.boxShadow = '';
    });
  }

  /* ────────────────────────────────────────────────────────────
     11. VENDOR CARD — Tilt on mouse move (subtle 3D)
  ──────────────────────────────────────────────────────────── */
  function initCardTilt () {
    // Only on desktop
    if (!window.matchMedia('(pointer: fine)').matches) return;

    qsa('.vendor-card, .trust-card, .plan-preview-card').forEach(card => {
      card.addEventListener('mousemove', e => {
        const rect = card.getBoundingClientRect();
        const x = (e.clientX - rect.left) / rect.width  - 0.5; // -0.5 to 0.5
        const y = (e.clientY - rect.top)  / rect.height - 0.5;
        const maxTilt = 5;
        card.style.transform = `
          perspective(800px)
          rotateY(${x * maxTilt}deg)
          rotateX(${-y * maxTilt}deg)
          translateY(-8px)
        `;
        card.style.transition = 'transform 0.1s linear, box-shadow 0.1s linear';
      });

      card.addEventListener('mouseleave', () => {
        card.style.transform = '';
        card.style.transition = '';
      });
    });
  }

  /* ────────────────────────────────────────────────────────────
     12. SECTION LABEL WORD REVEAL — character stagger
  ──────────────────────────────────────────────────────────── */
  function initTextReveal () {
    // Wraps each word in section titles with a span for stagger
    qsa('.section-title').forEach(el => {
      if (el.dataset.animated) return;
      el.dataset.animated = '1';
      const words = el.innerHTML.split(' ');
      el.innerHTML = words.map((w, i) =>
        `<span style="display:inline-block;opacity:0;transform:translateY(20px);
          transition:opacity .55s cubic-bezier(.16,1,.3,1) ${(i*0.07+0.1).toFixed(2)}s,
                     transform .55s cubic-bezier(.16,1,.3,1) ${(i*0.07+0.1).toFixed(2)}s">${w}</span>`
      ).join(' ');
    });

    // Observer to fire
    const io = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        qsa('span[style*="translateY"]', entry.target).forEach(span => {
          span.style.opacity    = '1';
          span.style.transform  = 'translateY(0)';
        });
        io.unobserve(entry.target);
      });
    }, { threshold: 0.3 });

    qsa('.section-title').forEach(el => io.observe(el));
  }

  /* ────────────────────────────────────────────────────────────
     13. FOOTER BOTTOM BAR — APPEAR ON NEAR-SCROLL
  ──────────────────────────────────────────────────────────── */
  function initFooterReveal () {
    const footer = qs('.site-footer');
    if (!footer) return;

    footer.style.opacity = '0';
    footer.style.transform = 'translateY(20px)';
    footer.style.transition = 'opacity .8s cubic-bezier(.16,1,.3,1), transform .8s cubic-bezier(.16,1,.3,1)';

    const io = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          footer.style.opacity   = '1';
          footer.style.transform = 'translateY(0)';
          io.unobserve(footer);
        }
      });
    }, { threshold: 0.05 });

    io.observe(footer);
  }

  /* ────────────────────────────────────────────────────────────
     14. MOBILE NAV — enhanced open/close transitions
  ──────────────────────────────────────────────────────────── */
  function enhanceMobileNav () {
    const btn     = qs('#mobileMenuBtn');
    const nav     = qs('#mobileNav');
    const overlay = qs('#mobileNavOverlay');
    const close   = qs('#mobileNavClose');
    if (!btn || !nav) return;

    // Stagger nav links entrance when opened
    function staggerLinks (open) {
      qsa('.mobile-nav-link', nav).forEach((link, i) => {
        if (open) {
          link.style.opacity   = '0';
          link.style.transform = 'translateX(-20px)';
          setTimeout(() => {
            link.style.transition = 'opacity .4s cubic-bezier(.16,1,.3,1), transform .4s cubic-bezier(.16,1,.3,1)';
            link.style.opacity    = '1';
            link.style.transform  = 'translateX(0)';
          }, 80 + i * 50);
        } else {
          link.style.opacity   = '';
          link.style.transform = '';
          link.style.transition = '';
        }
      });
    }

    // Observe class changes (original JS toggles the class)
    const mo = new MutationObserver(() => {
      const isOpen = nav.classList.contains('is-open') ||
                     nav.style.transform === 'translateX(0px)' ||
                     nav.style.transform === 'translateX(0)' ||
                     nav.getAttribute('aria-hidden') === 'false';
      // Use a data flag to avoid firing repeatedly
      const wasOpen = nav.dataset.wasOpen === '1';
      if (isOpen && !wasOpen) {
        nav.dataset.wasOpen = '1';
        staggerLinks(true);
      } else if (!isOpen && wasOpen) {
        nav.dataset.wasOpen = '0';
        staggerLinks(false);
      }
    });
    mo.observe(nav, { attributes: true, attributeFilter: ['class', 'style', 'aria-hidden'] });
  }

  /* ────────────────────────────────────────────────────────────
     INIT ALL
  ──────────────────────────────────────────────────────────── */
  // Progress bar runs immediately (before DOMContentLoaded)
  initProgressBar();

  onLoad(() => {
    injectRevealClasses();   // must run before observers
    initScrollReveal();
    initHeaderScroll();
    initParallax();
    initCounters();
    initSmoothScroll();
    initRipple();
    initHeroSearch();
    initCardTilt();
    initTextReveal();
    initFooterReveal();
    enhanceMobileNav();
    initCursor();

    // Re-init lucide after DOM manipulation (safe to call multiple times)
    if (window.lucide) window.lucide.createIcons();
  });

})();