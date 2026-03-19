/* ============================================================
   UNIFYHUB – Admin Panel JavaScript
   ============================================================ */
'use strict';

// ============================================================
// THEME TOGGLE
// ============================================================
(function initAdminTheme() {
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
  if (icon && saved === 'dark') { icon.classList.replace('fa-moon', 'fa-sun'); }
})();

// ============================================================
// SIDEBAR TOGGLE (mobile)
// ============================================================
(function initSidebar() {
  const toggleBtn = document.getElementById('sidebarToggle');
  const sidebar   = document.getElementById('adminSidebar');
  const overlay   = document.getElementById('sidebarOverlay');
  if (!toggleBtn || !sidebar) return;
  function open()  { sidebar.classList.add('open');  if (overlay) overlay.classList.add('show'); }
  function close() { sidebar.classList.remove('open'); if (overlay) overlay.classList.remove('show'); }
  toggleBtn.addEventListener('click', () => sidebar.classList.contains('open') ? close() : open());
  if (overlay) overlay.addEventListener('click', close);
})();

// ============================================================
// TABLE SEARCH & FILTER
// ============================================================
(function initTableSearch() {
  const searchInputs = document.querySelectorAll('[data-table-search]');
  searchInputs.forEach(input => {
    const tableId = input.dataset.tableSearch;
    const table = document.getElementById(tableId);
    if (!table) return;
    input.addEventListener('input', () => {
      const q = input.value.toLowerCase().trim();
      table.querySelectorAll('tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  });
})();

// ============================================================
// TABLE FILTER BY SELECT
// ============================================================
(function initTableFilter() {
  const filters = document.querySelectorAll('[data-table-filter]');
  filters.forEach(sel => {
    const tableId = sel.dataset.tableFilter;
    const col     = parseInt(sel.dataset.filterCol, 10);
    const table   = document.getElementById(tableId);
    if (!table) return;
    sel.addEventListener('change', () => {
      const val = sel.value.toLowerCase();
      table.querySelectorAll('tbody tr').forEach(row => {
        if (!val) { row.style.display = ''; return; }
        const cell = row.querySelectorAll('td')[col];
        row.style.display = cell && cell.textContent.toLowerCase().includes(val) ? '' : 'none';
      });
    });
  });
})();

// ============================================================
// CHART.JS INITIALIZER (Dashboard)
// ============================================================
function initAdminCharts() {
  // Members Growth Chart
  const growthCtx = document.getElementById('membersGrowthChart');
  if (growthCtx && typeof Chart !== 'undefined') {
    new Chart(growthCtx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
          label: 'New Members',
          data: [45, 78, 120, 95, 165, 210],
          borderColor: '#8B31D4',
          backgroundColor: 'rgba(139,49,212,0.1)',
          borderWidth: 2.5,
          pointBackgroundColor: '#8B31D4',
          pointRadius: 4,
          fill: true,
          tension: 0.4,
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

  // Payment Chart
  const payCtx = document.getElementById('paymentChart');
  if (payCtx && typeof Chart !== 'undefined') {
    new Chart(payCtx, {
      type: 'doughnut',
      data: {
        labels: ['Full Paid', '1st Installment', 'Pending'],
        datasets: [{
          data: [65, 25, 10],
          backgroundColor: ['#00D4AA', '#FFBB00', '#FF3E5E'],
          borderWidth: 0,
          hoverOffset: 4,
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { padding: 15, font: { size: 12 } } } },
        cutout: '65%',
      }
    });
  }

  // Revenue Chart
  const revCtx = document.getElementById('revenueChart');
  if (revCtx && typeof Chart !== 'undefined') {
    new Chart(revCtx, {
      type: 'bar',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
          label: 'Revenue (₹)',
          data: [22500, 39000, 60000, 47500, 82500, 105000],
          backgroundColor: 'rgba(139,49,212,0.7)',
          borderRadius: 6,
          borderSkipped: false,
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
}

// ============================================================
// STATUS UPDATE MODALS (Approve / Reject)
// ============================================================
function updateMemberStatus(id, status) {
  const row = document.querySelector(`[data-member-id="${id}"]`);
  if (!row) return;
  const badge = row.querySelector('.badge-status');
  if (badge) {
    badge.className = `badge-status badge-${status === 'active' ? 'active' : 'rejected'}`;
    badge.innerHTML = `<span></span> ${status === 'active' ? 'Active' : 'Rejected'}`;
  }
  showAdminToast(`Member #${id} status updated to ${status}`, status === 'active' ? 'success' : 'warning');
}

function verifyPayment(id) {
  const row = document.querySelector(`[data-payment-id="${id}"]`);
  if (!row) return;
  const badge = row.querySelector('.badge-status');
  if (badge) { badge.className = 'badge-status badge-verified'; badge.innerHTML = '<span></span> Verified'; }
  const btn = row.querySelector('.verify-btn');
  if (btn) btn.remove();
  showAdminToast(`Payment #${id} verified successfully`, 'success');
}

// ============================================================
// ADMIN TOAST
// ============================================================
function showAdminToast(message, type = 'success') {
  const icons = { success: '✓', warning: '⚠', error: '✕' };
  const colors = { success: '#00D4AA', warning: '#FFBB00', error: '#FF3E5E' };
  const toast = Object.assign(document.createElement('div'), {
    innerHTML: `<span style="margin-right:8px;font-weight:700;color:${colors[type]}">${icons[type]}</span>${message}`,
    style: `position:fixed;top:80px;right:20px;background:var(--admin-card,#fff);color:var(--admin-text,#1A1A2E);border:1px solid var(--admin-border);border-radius:10px;padding:12px 18px;font-size:.85rem;box-shadow:0 8px 30px rgba(0,0,0,.15);z-index:99999;animation:toastIn .4s ease;border-left:4px solid ${colors[type]}`,
  });
  document.body.appendChild(toast);
  setTimeout(() => { toast.style.opacity = '0'; toast.style.transform = 'translateX(60px)'; toast.style.transition = 'all .4s'; setTimeout(() => toast.remove(), 400); }, 3500);
}

// ============================================================
// ANNOUNCEMENT FORM
// ============================================================
(function initAnnouncementForm() {
  const form = document.getElementById('announcementForm');
  if (!form) return;
  form.addEventListener('submit', e => {
    e.preventDefault();
    const title = form.querySelector('#annTitle').value.trim();
    const msg   = form.querySelector('#annMessage').value.trim();
    if (!title || !msg) { showAdminToast('Please fill in all fields', 'error'); return; }
    showAdminToast('Announcement posted successfully!', 'success');
    form.reset();
    // In production: POST to backend/admin-api.php
  });
})();

// ============================================================
// REPORTS DOWNLOAD STUB
// ============================================================
function downloadReport(type) {
  showAdminToast(`${type} report download started...`, 'success');
  // In production: window.location = 'backend/admin-api.php?action=download_report&type=' + type;
}

// Init charts on DOM ready
document.addEventListener('DOMContentLoaded', initAdminCharts);
