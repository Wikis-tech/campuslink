'use strict';

// ============================================================
// CAMPUSLINK INTERACTIVE HERO CANVAS
// Particle system with mouse interaction, connections, and
// orbiting rings — fully GPU-friendly with requestAnimationFrame
// ============================================================

(function () {
  const canvas = document.getElementById('heroCanvas');
  if (!canvas) return;

  const ctx    = canvas.getContext('2d');
  let W, H, particles, mouse, animId, orbs;
  const CONFIG = {
    particleCount: 90,
    maxDist:       160,
    particleSpeed: 0.45,
    particleSize:  { min: 1, max: 3.5 },
    colors:        ['rgba(255,255,255,0.8)', 'rgba(30,169,82,0.9)', 'rgba(134,239,172,0.7)', 'rgba(255,255,255,0.5)'],
    lineColor:     'rgba(255,255,255,0.08)',
    mouseRadius:   140,
    orbs: [
      { x: 0.75, y: 0.25, r: 220, speed: 0.0004, phase: 0,   color: 'rgba(30,169,82,0.06)'    },
      { x: 0.15, y: 0.65, r: 300, speed: 0.0003, phase: 2.1, color: 'rgba(255,255,255,0.04)'  },
      { x: 0.55, y: 0.8,  r: 180, speed: 0.0006, phase: 4.2, color: 'rgba(11,61,145,0.08)'    },
    ],
  };

  function resize() {
    W = canvas.width  = canvas.offsetWidth;
    H = canvas.height = canvas.offsetHeight;
  }

  function randomBetween(a, b) { return a + Math.random() * (b - a); }

  function createParticles() {
    particles = Array.from({ length: CONFIG.particleCount }, () => ({
      x:   Math.random() * W,
      y:   Math.random() * H,
      vx:  (Math.random() - 0.5) * CONFIG.particleSpeed,
      vy:  (Math.random() - 0.5) * CONFIG.particleSpeed,
      r:   randomBetween(CONFIG.particleSize.min, CONFIG.particleSize.max),
      col: CONFIG.colors[Math.floor(Math.random() * CONFIG.colors.length)],
      pulse: Math.random() * Math.PI * 2,
      pulseSpeed: 0.02 + Math.random() * 0.03,
    }));
  }

  function createOrbs() {
    orbs = CONFIG.orbs.map(o => ({ ...o, angle: o.phase }));
  }

  function drawOrbs(t) {
    orbs.forEach(orb => {
      orb.angle += orb.speed * t;
      const ox = W * orb.x + Math.cos(orb.angle) * orb.r * 0.3;
      const oy = H * orb.y + Math.sin(orb.angle) * orb.r * 0.2;
      const grad = ctx.createRadialGradient(ox, oy, 0, ox, oy, orb.r);
      grad.addColorStop(0, orb.color);
      grad.addColorStop(1, 'transparent');
      ctx.beginPath();
      ctx.arc(ox, oy, orb.r, 0, Math.PI * 2);
      ctx.fillStyle = grad;
      ctx.fill();
    });
  }

  function drawParticles() {
    particles.forEach(p => {
      p.pulse += p.pulseSpeed;
      const pulsedR = p.r + Math.sin(p.pulse) * 0.8;

      // Mouse repulsion
      const dx = p.x - mouse.x;
      const dy = p.y - mouse.y;
      const dist = Math.sqrt(dx * dx + dy * dy);
      if (dist < CONFIG.mouseRadius) {
        const force = (CONFIG.mouseRadius - dist) / CONFIG.mouseRadius;
        p.vx += (dx / dist) * force * 0.6;
        p.vy += (dy / dist) * force * 0.6;
      }

      // Speed cap
      const speed = Math.sqrt(p.vx * p.vx + p.vy * p.vy);
      if (speed > 2.5) { p.vx = (p.vx / speed) * 2.5; p.vy = (p.vy / speed) * 2.5; }

      // Drag
      p.vx *= 0.99;
      p.vy *= 0.99;

      p.x += p.vx;
      p.y += p.vy;

      // Wrap
      if (p.x < -10) p.x = W + 10;
      if (p.x > W + 10) p.x = -10;
      if (p.y < -10) p.y = H + 10;
      if (p.y > H + 10) p.y = -10;

      ctx.beginPath();
      ctx.arc(p.x, p.y, pulsedR, 0, Math.PI * 2);
      ctx.fillStyle = p.col;
      ctx.fill();
    });
  }

  function drawConnections() {
    for (let i = 0; i < particles.length; i++) {
      for (let j = i + 1; j < particles.length; j++) {
        const dx   = particles[i].x - particles[j].x;
        const dy   = particles[i].y - particles[j].y;
        const dist = Math.sqrt(dx * dx + dy * dy);
        if (dist < CONFIG.maxDist) {
          const alpha = (1 - dist / CONFIG.maxDist) * 0.4;
          ctx.beginPath();
          ctx.moveTo(particles[i].x, particles[i].y);
          ctx.lineTo(particles[j].x, particles[j].y);
          ctx.strokeStyle = `rgba(255,255,255,${alpha})`;
          ctx.lineWidth   = 0.5;
          ctx.stroke();
        }
      }

      // Connect to mouse
      const dx   = particles[i].x - mouse.x;
      const dy   = particles[i].y - mouse.y;
      const dist = Math.sqrt(dx * dx + dy * dy);
      if (dist < CONFIG.mouseRadius * 1.2) {
        const alpha = (1 - dist / (CONFIG.mouseRadius * 1.2)) * 0.6;
        ctx.beginPath();
        ctx.moveTo(particles[i].x, particles[i].y);
        ctx.lineTo(mouse.x, mouse.y);
        ctx.strokeStyle = `rgba(30,169,82,${alpha})`;
        ctx.lineWidth   = 0.8;
        ctx.stroke();
      }
    }
  }

  let lastTime = 0;
  function loop(timestamp) {
    const dt = timestamp - lastTime;
    lastTime  = timestamp;

    ctx.clearRect(0, 0, W, H);
    drawOrbs(dt);
    drawConnections();
    drawParticles();
    animId = requestAnimationFrame(loop);
  }

  function init() {
    mouse = { x: W / 2, y: H / 2 };
    createParticles();
    createOrbs();
    if (animId) cancelAnimationFrame(animId);
    animId = requestAnimationFrame(loop);
  }

  resize();
  init();

  window.addEventListener('resize', () => { resize(); init(); }, { passive: true });

  canvas.addEventListener('mousemove', (e) => {
    const rect = canvas.getBoundingClientRect();
    mouse.x = e.clientX - rect.left;
    mouse.y = e.clientY - rect.top;
  }, { passive: true });

  canvas.addEventListener('touchmove', (e) => {
    const rect  = canvas.getBoundingClientRect();
    const touch = e.touches[0];
    mouse.x = touch.clientX - rect.left;
    mouse.y = touch.clientY - rect.top;
  }, { passive: true });

  // Click burst
  canvas.addEventListener('click', (e) => {
    const rect = canvas.getBoundingClientRect();
    const cx   = e.clientX - rect.left;
    const cy   = e.clientY - rect.top;
    particles.forEach(p => {
      const dx   = p.x - cx;
      const dy   = p.y - cy;
      const dist = Math.sqrt(dx * dx + dy * dy);
      if (dist < 200) {
        const force = (200 - dist) / 200;
        p.vx += (dx / (dist || 1)) * force * 4;
        p.vy += (dy / (dist || 1)) * force * 4;
      }
    });
  });

  // VCTA Canvas — subtle animated gradient
  const vctaCanvas = document.getElementById('vctaCanvas');
  if (vctaCanvas) {
    const vc  = vctaCanvas.getContext('2d');
    let vt = 0;

    function resizeVCTA() {
      vctaCanvas.width  = vctaCanvas.offsetWidth;
      vctaCanvas.height = vctaCanvas.offsetHeight;
    }
    resizeVCTA();
    window.addEventListener('resize', resizeVCTA, { passive: true });

    function drawVCTA() {
      vt += 0.008;
      const VW = vctaCanvas.width;
      const VH = vctaCanvas.height;
      vc.clearRect(0, 0, VW, VH);

      // Animated gradient orbs
      const orb1x = VW * 0.2 + Math.cos(vt) * VW * 0.1;
      const orb1y = VH * 0.3 + Math.sin(vt * 0.7) * VH * 0.15;
      const g1    = vc.createRadialGradient(orb1x, orb1y, 0, orb1x, orb1y, VW * 0.35);
      g1.addColorStop(0, 'rgba(30,169,82,0.12)');
      g1.addColorStop(1, 'transparent');
      vc.beginPath(); vc.arc(orb1x, orb1y, VW * 0.35, 0, Math.PI * 2);
      vc.fillStyle = g1; vc.fill();

      const orb2x = VW * 0.8 + Math.cos(vt * 0.6 + 2) * VW * 0.08;
      const orb2y = VH * 0.7 + Math.sin(vt * 0.8) * VH * 0.12;
      const g2    = vc.createRadialGradient(orb2x, orb2y, 0, orb2x, orb2y, VW * 0.28);
      g2.addColorStop(0, 'rgba(255,255,255,0.07)');
      g2.addColorStop(1, 'transparent');
      vc.beginPath(); vc.arc(orb2x, orb2y, VW * 0.28, 0, Math.PI * 2);
      vc.fillStyle = g2; vc.fill();

      requestAnimationFrame(drawVCTA);
    }
    drawVCTA();
  }
})();