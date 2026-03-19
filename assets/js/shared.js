/* ============================================================
   UNIFYHUB – Shared JavaScript (All Public Pages)
   theme toggle, navbar scroll, AOS init, cursor, back-to-top,
   active nav link (page-based), toast helper
   ============================================================ */
'use strict';

// ============================================================
// INIT AOS
// ============================================================
if (typeof AOS !== 'undefined') {
  AOS.init({ duration: 700, once: true, easing: 'ease-out-cubic', offset: 60 });
}

// ============================================================
// THEME TOGGLE (localStorage persistence)
// ============================================================
(function initTheme() {
  const body = document.body;
  const saved = localStorage.getItem('uh-theme');
  if (saved === 'dark') body.classList.add('dark-mode');

  function updateIcon(isDark) {
    const icon = document.getElementById('themeIcon');
    if (!icon) return;
    icon.classList.toggle('fa-moon', !isDark);
    icon.classList.toggle('fa-sun', isDark);
  }
  updateIcon(body.classList.contains('dark-mode'));

  const btn = document.getElementById('themeToggle');
  if (!btn) return;
  btn.addEventListener('click', () => {
    body.classList.toggle('dark-mode');
    const isDark = body.classList.contains('dark-mode');
    localStorage.setItem('uh-theme', isDark ? 'dark' : 'light');
    updateIcon(isDark);
  });
})();

// ============================================================
// NAVBAR SCROLL BEHAVIOR
// ============================================================
(function initNav() {
  const nav = document.getElementById('mainNav');
  if (!nav) return;
  function onScroll() {
    nav.classList.toggle('scrolled', window.scrollY > 60);
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
})();

// ============================================================
// ACTIVE NAV LINK (page-based using current filename)
// ============================================================
(function initActiveNav() {
  const path = window.location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.nav-link[href]').forEach(link => {
    const href = link.getAttribute('href').split('/').pop();
    if (href === path) {
      link.classList.add('nav-active');
    }
  });
})();

// ============================================================
// CURSOR FOLLOWER
// ============================================================
(function initCursor() {
  const dot  = document.getElementById('cursorDot');
  const ring = document.getElementById('cursorRing');
  if (!dot || !ring) return;
  if (!window.matchMedia('(hover: hover)').matches) {
    dot.style.display = ring.style.display = 'none';
    return;
  }
  let mx = 0, my = 0, rx = 0, ry = 0;
  document.addEventListener('mousemove', e => {
    mx = e.clientX; my = e.clientY;
    dot.style.transform = `translate(${mx}px, ${my}px) translate(-50%,-50%)`;
  });
  document.querySelectorAll('a, button, .biz-tile, .diff-card, .plan-option').forEach(el => {
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
// BACK TO TOP
// ============================================================
(function initBackToTop() {
  const btn = document.getElementById('backToTop');
  if (!btn) return;
  window.addEventListener('scroll', () => {
    btn.classList.toggle('visible', window.scrollY > 400);
  }, { passive: true });
  btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
})();

// ============================================================
// FAQ ACCORDION (shared across pages)
// ============================================================
function toggleFAQ(btn) {
  const item   = btn.closest('.faq-item');
  const answer = item.querySelector('.faq-answer');
  const isOpen = btn.classList.contains('active');
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
// TOAST NOTIFICATION HELPER
// ============================================================
function showToast(message, type = 'success', title = '') {
  let container = document.querySelector('.toast-container-custom');
  if (!container) {
    container = document.createElement('div');
    container.className = 'toast-container-custom';
    document.body.appendChild(container);
  }
  const icons = { success: 'fa-check-circle', error: 'fa-times-circle', warning: 'fa-exclamation-triangle' };
  const toast = document.createElement('div');
  toast.className = `toast-custom ${type}`;
  toast.innerHTML = `
    <i class="fa-solid ${icons[type] || icons.success}"></i>
    <div class="toast-custom-msg">
      ${title ? `<strong>${title}</strong>` : ''}
      <span>${message}</span>
    </div>`;
  container.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(60px)';
    toast.style.transition = 'all 0.4s ease';
    setTimeout(() => toast.remove(), 400);
  }, 4000);
}

// ============================================================
// MOBILE NAVBAR CLOSE ON LINK CLICK
// ============================================================
(function closeMobileNav() {
  const collapse = document.getElementById('navbarNav');
  if (!collapse) return;
  collapse.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', () => {
      const bsc = window.bootstrap && bootstrap.Collapse.getInstance(collapse);
      if (bsc) bsc.hide();
    });
  });
})();
