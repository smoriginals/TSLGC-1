/* ============================================================
   TSLGC Alliance Market – Main JavaScript
   ============================================================ */

'use strict';

// ============================================================
// INIT AOS
// ============================================================
AOS.init({
  duration: 700,
  once: true,
  easing: 'ease-out-cubic',
  offset: 60,
});

// ============================================================
// PARTICLE CANVAS (Hero Background)
// ============================================================
(function initParticles() {
  const canvas = document.getElementById('particleCanvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');

  let W, H, particles = [];

  function resize() {
    W = canvas.width  = canvas.offsetWidth;
    H = canvas.height = canvas.offsetHeight;
  }
  resize();
  window.addEventListener('resize', resize);

  function rand(min, max) { return Math.random() * (max - min) + min; }

  function createParticles(count) {
    particles = [];
    for (let i = 0; i < count; i++) {
      particles.push({
        x: rand(0, W), y: rand(0, H),
        vx: rand(-0.3, 0.3), vy: rand(-0.3, 0.3),
        r: rand(1.5, 3.5),
        alpha: rand(0.2, 0.7),
      });
    }
  }
  createParticles(80);

  function drawLine(a, b, dist) {
    const opacity = 1 - dist / 140;
    ctx.beginPath();
    ctx.moveTo(a.x, a.y);
    ctx.lineTo(b.x, b.y);
    ctx.strokeStyle = `rgba(200,210,255,${opacity * 0.25})`;
    ctx.lineWidth = 0.8;
    ctx.stroke();
  }

  function animate() {
    ctx.clearRect(0, 0, W, H);
    particles.forEach(p => {
      p.x += p.vx; p.y += p.vy;
      if (p.x < 0) p.x = W;
      if (p.x > W) p.x = 0;
      if (p.y < 0) p.y = H;
      if (p.y > H) p.y = 0;

      ctx.beginPath();
      ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
      ctx.fillStyle = `rgba(200,220,255,${p.alpha})`;
      ctx.fill();
    });

    // Draw connecting lines
    for (let i = 0; i < particles.length; i++) {
      for (let j = i + 1; j < particles.length; j++) {
        const dx = particles[i].x - particles[j].x;
        const dy = particles[i].y - particles[j].y;
        const d  = Math.sqrt(dx * dx + dy * dy);
        if (d < 140) drawLine(particles[i], particles[j], d);
      }
    }
    requestAnimationFrame(animate);
  }
  animate();
})();

// ============================================================
// CURSOR FOLLOWER
// ============================================================
(function initCursor() {
  const dot  = document.getElementById('cursorDot');
  const ring = document.getElementById('cursorRing');
  if (!dot || !ring) return;

  // Only active on non-touch devices
  if (!window.matchMedia('(hover: hover)').matches) {
    dot.style.display = ring.style.display = 'none';
    return;
  }

  let mx = 0, my = 0, rx = 0, ry = 0;

  document.addEventListener('mousemove', e => {
    mx = e.clientX; my = e.clientY;
    dot.style.transform = `translate(${mx}px, ${my}px) translate(-50%,-50%)`;
  });

  document.querySelectorAll('a, button, .biz-tile, .diff-card').forEach(el => {
    el.addEventListener('mouseenter', () => { ring.style.width = ring.style.height = '52px'; });
    el.addEventListener('mouseleave', () => { ring.style.width = ring.style.height = '32px'; });
  });

  function animateRing() {
    rx += (mx - rx) * 0.12;
    ry += (my - ry) * 0.12;
    ring.style.transform = `translate(${rx}px, ${ry}px) translate(-50%,-50%)`;
    requestAnimationFrame(animateRing);
  }
  animateRing();
})();

// ============================================================
// NAVBAR SCROLL BEHAVIOR
// ============================================================
(function initNav() {
  const nav = document.getElementById('mainNav');
  if (!nav) return;

  function onScroll() {
    if (window.scrollY > 60) nav.classList.add('scrolled');
    else nav.classList.remove('scrolled');
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
})();

// ============================================================
// ACTIVE NAV SECTION HIGHLIGHT
// ============================================================
(function initActiveNav() {
  const sections = document.querySelectorAll('section[id]');
  const navLinks = document.querySelectorAll('.nav-link[href^="#"]');

  function highlight() {
    let current = '';
    sections.forEach(sec => {
      if (window.scrollY >= sec.offsetTop - 120) current = sec.id;
    });
    navLinks.forEach(link => {
      link.classList.remove('active-section');
      if (link.getAttribute('href') === `#${current}`) {
        link.classList.add('active-section');
      }
    });
  }
  window.addEventListener('scroll', highlight, { passive: true });
  highlight();
})();



// ============================================================
// TYPEWRITER EFFECT (Hero)
// ============================================================
(function initTypewriter() {
  const el = document.getElementById('heroTypewriter');
  if (!el) return;

  const text = "डिजिटल युग का सबसे बड़ा 'Alliance Market'";
  let i = 0;

  function type() {
    if (i < text.length) {
      el.textContent += text[i++];
      setTimeout(type, i === 1 ? 400 : 55);
    }
  }
  setTimeout(type, 800);
})();

// ============================================================
// COUNT-UP ANIMATION
// ============================================================
(function initCountUp() {
  const counters = document.querySelectorAll('.count-up');
  if (!counters.length) return;

  const options = { threshold: 0.5 };
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      const el      = entry.target;
      const target  = parseInt(el.dataset.target, 10);
      const duration = target > 999 ? 1200 : 900;
      const step     = Math.ceil(target / (duration / 16));
      let current    = 0;

      const timer = setInterval(() => {
        current = Math.min(current + step, target);
        el.textContent = current.toLocaleString('en-IN');
        if (current >= target) clearInterval(timer);
      }, 16);

      observer.unobserve(el);
    });
  }, options);

  counters.forEach(c => observer.observe(c));
})();

// ============================================================
// INCOME TICKER (World Map)
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
// FAQ ACCORDION
// ============================================================
function toggleFAQ(btn) {
  const item   = btn.closest('.faq-item');
  const answer = item.querySelector('.faq-answer');
  const isOpen = btn.classList.contains('active');

  // Close all
  document.querySelectorAll('.faq-question.active').forEach(q => {
    q.classList.remove('active');
    q.closest('.faq-item').querySelector('.faq-answer').classList.remove('open');
  });

  if (!isOpen) {
    btn.classList.add('active');
    answer.classList.add('open');
  }
}

// ============================================================
// REWARDS PROGRESS BAR
// ============================================================
(function initRewardsProgress() {
  const bar = document.getElementById('rewardsProgress');
  if (!bar) return;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        bar.style.width = '35%';
        observer.unobserve(bar);
      }
    });
  }, { threshold: 0.5 });

  observer.observe(bar);
})();

// ============================================================
// DARK / LIGHT MODE TOGGLE
// ============================================================
(function initThemeToggle() {
  const btn  = document.getElementById('themeToggle');
  const icon = document.getElementById('themeIcon');
  const body = document.body;

  // Persist preference
  const saved = localStorage.getItem('uh-theme');
  if (saved === 'dark') {
    body.classList.add('dark-mode');
    if (icon) { icon.classList.replace('fa-moon', 'fa-sun'); }
  }

  if (!btn) return;
  btn.addEventListener('click', () => {
    body.classList.toggle('dark-mode');
    const isDark = body.classList.contains('dark-mode');
    localStorage.setItem('uh-theme', isDark ? 'dark' : 'light');
    if (icon) {
      icon.classList.replace(isDark ? 'fa-moon' : 'fa-sun', isDark ? 'fa-sun' : 'fa-moon');
    }
  });
})();

// ============================================================
// DASHBOARD BAR ANIMATION ON SCROLL
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
// MAGIC TABLE ANIMATION
// ============================================================
(function initTableAnimation() {
  const table = document.querySelector('.magic-table');
  if (!table) return;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.querySelectorAll('tbody tr').forEach(row => {
          row.style.animationPlayState = 'running';
        });
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.3 });

  // Pause animations initially
  table.querySelectorAll('tbody tr').forEach(row => {
    row.style.animationPlayState = 'paused';
  });

  observer.observe(table);
})();

// ============================================================
// SMOOTH SCROLL for anchor links
// ============================================================
document.querySelectorAll('a[href^="#"]').forEach(link => {
  link.addEventListener('click', function(e) {
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      e.preventDefault();
      const offset = 80;
      const top = target.getBoundingClientRect().top + window.scrollY - offset;
      window.scrollTo({ top, behavior: 'smooth' });

      // Close mobile nav if open
      const navCollapse = document.getElementById('navbarNav');
      if (navCollapse && navCollapse.classList.contains('show')) {
        const bsCollapse = bootstrap.Collapse.getInstance(navCollapse);
        if (bsCollapse) bsCollapse.hide();
      }
    }
  });
});

// ============================================================
// STATS ZOOM-IN OBSERVER (fallback, in addition to AOS)
// ============================================================
(function initStatCards() {
  const items = document.querySelectorAll('.stat-item');
  if (!items.length) return;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry, i) => {
      if (entry.isIntersecting) {
        setTimeout(() => {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
        }, i * 120);
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.3 });

  items.forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    observer.observe(el);
  });
})();

// ============================================================
// BIZ TILE STAGGER
// ============================================================
(function initBizTileStagger() {
  const tiles = document.querySelectorAll('.biz-tile');
  if (!tiles.length) return;

  const observer = new IntersectionObserver((entries, obs) => {
    let delay = 0;
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const tile = entry.target;
        setTimeout(() => {
          tile.style.opacity = '1';
          tile.style.transform = 'scale(1)';
        }, delay);
        delay += 30;
        obs.unobserve(tile);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

  tiles.forEach(tile => {
    tile.style.opacity = '0';
    tile.style.transform = 'scale(0.85)';
    tile.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
    observer.observe(tile);
  });
})();

// ============================================================
// VISION TIMELINE – animate items on scroll
// ============================================================
(function initVisionTimeline() {
  const items = document.querySelectorAll('.vt-content');
  if (!items.length) return;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateX(0)';
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.4 });

  items.forEach((el, i) => {
    const isLeft = el.closest('.left');
    el.style.opacity = '0';
    el.style.transform = isLeft ? 'translateX(-30px)' : 'translateX(30px)';
    el.style.transition = `opacity 0.6s ease ${i * 0.15}s, transform 0.6s ease ${i * 0.15}s`;
    observer.observe(el);
  });
})();

// ============================================================
// PAGE LOAD FADE-IN
// ============================================================
window.addEventListener('load', () => {
  document.body.style.opacity = '0';
  document.body.style.transition = 'opacity 0.5s ease';
  requestAnimationFrame(() => {
    document.body.style.opacity = '1';
  });
});
