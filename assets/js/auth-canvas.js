'use strict';
// Lightweight particle canvas for auth pages
(function(){
  const canvas = document.getElementById('authCanvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  let W, H, pts, animId;

  function resize() {
    W = canvas.width  = canvas.offsetWidth;
    H = canvas.height = canvas.offsetHeight;
  }

  function init() {
    pts = Array.from({ length: 55 }, () => ({
      x:  Math.random() * W,
      y:  Math.random() * H,
      vx: (Math.random() - 0.5) * 0.3,
      vy: (Math.random() - 0.5) * 0.3,
      r:  1 + Math.random() * 2,
      a:  0.3 + Math.random() * 0.7,
    }));
  }

  function frame() {
    ctx.clearRect(0,0,W,H);
    pts.forEach(p => {
      p.x += p.vx; p.y += p.vy;
      if (p.x < 0 || p.x > W) p.vx *= -1;
      if (p.y < 0 || p.y > H) p.vy *= -1;
      ctx.beginPath();
      ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
      ctx.fillStyle = `rgba(255,255,255,${p.a})`;
      ctx.fill();
    });
    for (let i = 0; i < pts.length; i++) {
      for (let j = i + 1; j < pts.length; j++) {
        const d = Math.hypot(pts[i].x - pts[j].x, pts[i].y - pts[j].y);
        if (d < 120) {
          ctx.beginPath();
          ctx.moveTo(pts[i].x, pts[i].y);
          ctx.lineTo(pts[j].x, pts[j].y);
          ctx.strokeStyle = `rgba(255,255,255,${(1 - d/120) * 0.12})`;
          ctx.lineWidth = 0.5;
          ctx.stroke();
        }
      }
    }
    animId = requestAnimationFrame(frame);
  }

  resize(); init();
  if (animId) cancelAnimationFrame(animId);
  frame();
  window.addEventListener('resize', () => { resize(); init(); }, { passive: true });
})();