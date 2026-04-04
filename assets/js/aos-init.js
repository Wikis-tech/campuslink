'use strict';
// Campuslink — Scroll-triggered reveal animations (no external library needed)
(function() {
  const els = document.querySelectorAll('[data-aos]');
  if (!els.length) return;

  // Set initial state
  els.forEach(el => {
    const anim = el.dataset.aos;
    const delay = parseInt(el.dataset.aosDelay || 0);
    el.style.transitionDelay = delay + 'ms';
    el.style.transition = `opacity 0.7s cubic-bezier(0.16,1,0.3,1) ${delay}ms, transform 0.7s cubic-bezier(0.16,1,0.3,1) ${delay}ms`;
    el.style.opacity = '0';
    switch(anim) {
      case 'fade-up':    el.style.transform = 'translateY(30px)'; break;
      case 'fade-down':  el.style.transform = 'translateY(-30px)'; break;
      case 'fade-left':  el.style.transform = 'translateX(40px)'; break;
      case 'fade-right': el.style.transform = 'translateX(-40px)'; break;
      case 'zoom-in':    el.style.transform = 'scale(0.92)'; break;
      default:           el.style.transform = 'translateY(20px)';
    }
  });

  const obs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const el = entry.target;
        el.style.opacity = '1';
        el.style.transform = 'none';
        obs.unobserve(el);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

  els.forEach(el => obs.observe(el));
})();