/* ============================================================
   TSLGC – Franchise Panel JavaScript
   ============================================================ */
'use strict';

// ============================================================
// THEME TOGGLE
// ============================================================
(function initFrTheme() {
  const body = document.body;
  const saved = localStorage.getItem('uh-theme');
  if (saved === 'dark') body.classList.add('dark-mode');
  const btn = document.getElementById('themeToggle');
  if (!btn) return;
  btn.addEventListener('click', () => {
    body.classList.toggle('dark-mode');
    const isDark = body.classList.contains('dark-mode');
    localStorage.setItem('uh-theme', isDark ? 'dark' : 'light');
    const icon = btn.querySelector('i');
    if (icon) { icon.classList.toggle('fa-moon', !isDark); icon.classList.toggle('fa-sun', isDark); }
  });
  const icon = btn.querySelector('i');
  if (icon && saved === 'dark') icon.classList.replace('fa-moon', 'fa-sun');
})();

// ============================================================
// SIDEBAR TOGGLE
// ============================================================
(function initFrSidebar() {
  const toggleBtn = document.getElementById('frSidebarToggle');
  const sidebar   = document.getElementById('frSidebar');
  const overlay   = document.getElementById('frSidebarOverlay');
  if (!toggleBtn || !sidebar) return;
  const open  = () => { sidebar.classList.add('open');  if (overlay) overlay.classList.add('show'); };
  const close = () => { sidebar.classList.remove('open'); if (overlay) overlay.classList.remove('show'); };
  toggleBtn.addEventListener('click', () => sidebar.classList.contains('open') ? close() : open());
  if (overlay) overlay.addEventListener('click', close);
})();

// ============================================================
// INCOME CHART (Franchise Dashboard)
// ============================================================
function initFrCharts() {
  const incCtx = document.getElementById('frIncomeChart');
  if (incCtx && typeof Chart !== 'undefined') {
    new Chart(incCtx, {
      type: 'line',
      data: {
        labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
        datasets: [{
          label: 'Income (₹)',
          data: [4500, 7800, 6200, 9100],
          borderColor: '#00D4AA',
          backgroundColor: 'rgba(0,212,170,0.1)',
          borderWidth: 2.5, pointBackgroundColor: '#00D4AA', pointRadius: 5,
          fill: true, tension: 0.4,
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
          x: { grid: { display: false } }
        }
      }
    });
  }

  const teamCtx = document.getElementById('frTeamChart');
  if (teamCtx && typeof Chart !== 'undefined') {
    new Chart(teamCtx, {
      type: 'doughnut',
      data: {
        labels: ['Active', 'Inactive', 'Pending'],
        datasets: [{
          data: [72, 18, 10],
          backgroundColor: ['#00D4AA', '#6B7A99', '#FFBB00'],
          borderWidth: 0, hoverOffset: 4,
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { padding: 12, font: { size: 11 } } } },
        cutout: '68%',
      }
    });
  }
}

// ============================================================
// ADD LEAD FORM
// ============================================================
(function initLeadForm() {
  const form = document.getElementById('addLeadForm');
  if (!form) return;
  form.addEventListener('submit', e => {
    e.preventDefault();
    const name  = form.querySelector('#leadName').value.trim();
    const phone = form.querySelector('#leadPhone').value.trim();
    const city  = form.querySelector('#leadCity').value.trim();
    if (!name || !phone || !city) { showFrToast('Please fill all fields', 'error'); return; }
    if (!/^[6-9]\d{9}$/.test(phone)) { showFrToast('Enter valid 10-digit Indian mobile number', 'error'); return; }
    showFrToast(`Lead "${name}" added successfully!`, 'success');
    form.reset();
    // In production: POST to backend/franchise-api.php
  });
})();

// ============================================================
// PROFILE FORM
// ============================================================
(function initProfileForm() {
  const form = document.getElementById('profileForm');
  if (!form) return;
  form.addEventListener('submit', e => {
    e.preventDefault();
    showFrToast('Profile updated successfully!', 'success');
    // In production: POST to backend/franchise-api.php
  });
})();

// ============================================================
// INCOME FILTER
// ============================================================
(function initIncomeFilter() {
  const filter = document.getElementById('incomeFilter');
  if (!filter) return;
  filter.addEventListener('change', () => {
    showFrToast(`Showing ${filter.options[filter.selectedIndex].text} income`, 'success');
    // In production: fetch from backend/franchise-api.php?period=...
  });
})();

// ============================================================
// TOAST HELPER
// ============================================================
function showFrToast(message, type = 'success') {
  const colors = { success: '#00D4AA', error: '#FF3E5E', warning: '#FFBB00' };
  const icons  = { success: 'fa-check-circle', error: 'fa-times-circle', warning: 'fa-exclamation-triangle' };
  const toast  = document.createElement('div');
  toast.style.cssText = `position:fixed;top:80px;right:20px;background:var(--fr-card,#fff);color:var(--fr-text,#1A1A2E);border:1px solid var(--fr-border);border-left:4px solid ${colors[type]};border-radius:10px;padding:12px 18px;font-size:.85rem;box-shadow:0 8px 30px rgba(0,0,0,.15);z-index:99999;max-width:320px;`;
  toast.innerHTML = `<i class="fa-solid ${icons[type]}" style="color:${colors[type]};margin-right:8px"></i>${message}`;
  document.body.appendChild(toast);
  setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'all .4s'; setTimeout(() => toast.remove(), 400); }, 3500);
}

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded', initFrCharts);
