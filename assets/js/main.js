/* ============================================================
   TSLGC – Landing Page JS (Particles, Typewriter, CountUp etc.)
   ============================================================ */
'use strict';

// ============================================================
// PARTICLE CANVAS (Hero Background)
// ============================================================
(function initParticles() {
  const canvas = document.getElementById('particleCanvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  let W, H, particles = [];
  function resize() { W = canvas.width = canvas.offsetWidth; H = canvas.height = canvas.offsetHeight; }
  resize();
  window.addEventListener('resize', resize);
  function rand(min, max) { return Math.random() * (max - min) + min; }
  function createParticles(count) {
    particles = [];
    for (let i = 0; i < count; i++) {
      particles.push({ x: rand(0, W), y: rand(0, H), vx: rand(-0.3, 0.3), vy: rand(-0.3, 0.3), r: rand(1.5, 3.5), alpha: rand(0.2, 0.7) });
    }
  }
  createParticles(80);
  function drawLine(a, b, dist) {
    const opacity = 1 - dist / 140;
    ctx.beginPath(); ctx.moveTo(a.x, a.y); ctx.lineTo(b.x, b.y);
    ctx.strokeStyle = `rgba(200,210,255,${opacity * 0.25})`; ctx.lineWidth = 0.8; ctx.stroke();
  }
  function animate() {
    ctx.clearRect(0, 0, W, H);
    particles.forEach(p => {
      p.x += p.vx; p.y += p.vy;
      if (p.x < 0) p.x = W; if (p.x > W) p.x = 0;
      if (p.y < 0) p.y = H; if (p.y > H) p.y = 0;
      ctx.beginPath(); ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
      ctx.fillStyle = `rgba(200,220,255,${p.alpha})`; ctx.fill();
    });
    for (let i = 0; i < particles.length; i++) {
      for (let j = i + 1; j < particles.length; j++) {
        const dx = particles[i].x - particles[j].x, dy = particles[i].y - particles[j].y;
        const d = Math.sqrt(dx * dx + dy * dy);
        if (d < 140) drawLine(particles[i], particles[j], d);
      }
    }
    requestAnimationFrame(animate);
  }
  animate();
})();

// ============================================================
// TYPEWRITER EFFECT
// ============================================================
(function initTypewriter() {
  const el = document.getElementById('heroTypewriter');
  if (!el) return;
  const text = "डिजिटल युग का सबसे बड़ा 'Alliance Market'";
  let i = 0;
  function type() {
    if (i < text.length) { el.textContent += text[i++]; setTimeout(type, i === 1 ? 400 : 55); }
  }
  setTimeout(type, 800);
})();

// ============================================================
// COUNT-UP ANIMATION
// ============================================================
(function initCountUp() {
  const counters = document.querySelectorAll('.count-up');
  if (!counters.length) return;
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      const el = entry.target, target = parseInt(el.dataset.target, 10);
      const duration = target > 999 ? 1200 : 900;
      const step = Math.ceil(target / (duration / 16));
      let current = 0;
      const timer = setInterval(() => {
        current = Math.min(current + step, target);
        el.textContent = current.toLocaleString('en-IN');
        if (current >= target) clearInterval(timer);
      }, 16);
      observer.unobserve(el);
    });
  }, { threshold: 0.5 });
  counters.forEach(c => observer.observe(c));
})();

// ============================================================
// INCOME TICKER
// ============================================================
(function initIncomeTicker() {
  const el = document.getElementById('incomeTickerAmount');
  if (!el) return;
  let base = 125000;
  setInterval(() => {
    base += Math.floor(Math.random() * 2000 + 500);
    el.textContent = '₹' + base.toLocaleString('en-IN');
  }, 1200);
})();

// ============================================================
// REWARDS PROGRESS BAR
// ============================================================
(function initRewardsProgress() {
  const bar = document.getElementById('rewardsProgress');
  if (!bar) return;
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) { bar.style.width = '35%'; observer.unobserve(bar); }
    });
  }, { threshold: 0.5 });
  observer.observe(bar);
})();

// ============================================================
// DASHBOARD BAR ANIMATION
// ============================================================
(function initDashboardBars() {
  const preview = document.querySelector('.dashboard-preview');
  if (!preview) return;
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.querySelectorAll('.mc-bar').forEach(bar => {
          const h = bar.style.height;
          bar.style.height = '0';
          requestAnimationFrame(() => {
            setTimeout(() => { bar.style.transition = 'height 0.8s ease'; bar.style.height = h; }, 100);
          });
        });
        const progBar = entry.target.querySelector('.dp-bar');
        if (progBar) {
          const w = progBar.style.width;
          progBar.style.width = '0';
          requestAnimationFrame(() => {
            setTimeout(() => { progBar.style.transition = 'width 1.5s ease'; progBar.style.width = w; }, 200);
          });
        }
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.4 });
  observer.observe(preview);
})();

// ============================================================
// SECTION-BASED ACTIVE NAV HIGHLIGHT (for index.html)
// ============================================================
(function initActiveSectionNav() {
  const sections = document.querySelectorAll('section[id]');
  const navLinks = document.querySelectorAll('.nav-link[href^="#"]');
  if (!navLinks.length) return;
  function highlight() {
    let current = '';
    sections.forEach(sec => { if (window.scrollY >= sec.offsetTop - 120) current = sec.id; });
    navLinks.forEach(link => {
      link.classList.remove('active-section');
      if (link.getAttribute('href') === `#${current}`) link.classList.add('active-section');
    });
  }
  window.addEventListener('scroll', highlight, { passive: true });
  highlight();
})();
