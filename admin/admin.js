/* ============================================================
   TSLGC – Admin Panel Shared JS  |  admin/admin.js
   Session guard + UI utilities — admin panel only
   Never touches public site / shared keys
   ============================================================ */
'use strict';

var ADMIN_SESSION_KEY = 'tslgc_admin_session';

/* ── Session Guard (runs immediately on script load) ──────────── */
;(function guardAdminSession() {
  if (!localStorage.getItem(ADMIN_SESSION_KEY)) {
    window.location.replace('../admin.html');
  }
}());

/* ── Logout ───────────────────────────────────────────────────── */
function adminLogout() {
  if (confirm('Are you sure you want to logout?')) {
    localStorage.removeItem(ADMIN_SESSION_KEY);
    window.location.href = '../admin.html';
  }
}

/* ── Sidebar open / close ─────────────────────────────────────── */
function openSidebar() {
  var sb = document.getElementById('adminSidebar');
  var ov = document.getElementById('sidebarOverlay');
  if (sb) sb.classList.add('open');
  if (ov) ov.classList.add('show');
}

function closeSidebar() {
  var sb = document.getElementById('adminSidebar');
  var ov = document.getElementById('sidebarOverlay');
  if (sb) sb.classList.remove('open');
  if (ov) ov.classList.remove('show');
}

/* ── DOM-ready initialisation ─────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function () {

  /* Sidebar toggle button */
  var toggleBtn = document.getElementById('sidebarToggle');
  if (toggleBtn) {
    toggleBtn.addEventListener('click', function () {
      var sb = document.getElementById('adminSidebar');
      if (sb && sb.classList.contains('open')) { closeSidebar(); }
      else { openSidebar(); }
    });
  }

  /* Dark / Light theme toggle */
  var body     = document.body;
  var saved    = localStorage.getItem('admin-theme');
  if (saved === 'dark') { body.classList.add('dark-mode'); }

  var themeBtn = document.getElementById('adminThemeToggle');
  if (themeBtn) {
    var tIcon = themeBtn.querySelector('i');
    if (saved === 'dark' && tIcon) { tIcon.classList.replace('fa-moon', 'fa-sun'); }
    themeBtn.addEventListener('click', function () {
      body.classList.toggle('dark-mode');
      var isDark = body.classList.contains('dark-mode');
      localStorage.setItem('admin-theme', isDark ? 'dark' : 'light');
      var ic = themeBtn.querySelector('i');
      if (ic) {
        ic.classList.toggle('fa-moon', !isDark);
        ic.classList.toggle('fa-sun',   isDark);
      }
    });
  }

  /* Table live search */
  document.querySelectorAll('[data-table-search]').forEach(function (input) {
    var table = document.getElementById(input.dataset.tableSearch);
    if (!table) { return; }
    input.addEventListener('input', function () {
      var q = input.value.toLowerCase();
      table.querySelectorAll('tbody tr').forEach(function (row) {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  });

  /* Table status filter */
  document.querySelectorAll('[data-table-filter]').forEach(function (sel) {
    var table = document.getElementById(sel.dataset.tableFilter);
    if (!table) { return; }
    sel.addEventListener('change', function () {
      var val = sel.value.toLowerCase();
      table.querySelectorAll('tbody tr').forEach(function (row) {
        if (!val) { row.style.display = ''; return; }
        row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
      });
    });
  });

  /* Init charts if Chart.js is present */
  if (typeof initAdminCharts === 'function') { initAdminCharts(); }
});

/* ── HTML Escape ──────────────────────────────────────────────── */
function escHtml(s) {
  return String(s == null ? '' : s)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

/* ── Toast Notification ───────────────────────────────────────── */
function showAdminToast(message, type) {
  type = type || 'success';
  var palette = { success: '#00D4AA', warning: '#FFBB00', error: '#FF3E5E', info: '#3B82F6' };
  var icons   = { success: 'fa-circle-check', warning: 'fa-triangle-exclamation', error: 'fa-circle-xmark', info: 'fa-circle-info' };
  var color   = palette[type]  || palette.info;
  var icon    = icons[type]    || icons.info;

  var toast   = document.createElement('div');
  toast.innerHTML = '<i class="fa-solid ' + icon + '" style="color:' + color + ';flex-shrink:0"></i><span>' + escHtml(message) + '</span>';
  Object.assign(toast.style, {
    position: 'fixed', top: '76px', right: '20px', zIndex: '99999',
    display: 'flex', alignItems: 'center', gap: '8px',
    background: 'var(--acard, #fff)', color: 'var(--atext, #1a1a2e)',
    border: '1px solid var(--aborder)',
    borderLeft: '4px solid ' + color,
    borderRadius: '10px', padding: '12px 18px', fontSize: '.84rem',
    boxShadow: '0 8px 30px rgba(0,0,0,.15)',
    transition: 'all .35s ease',
    animation: 'toastSlide .3s ease',
    maxWidth: '340px'
  });
  document.body.appendChild(toast);
  setTimeout(function () {
    toast.style.opacity   = '0';
    toast.style.transform = 'translateX(60px)';
    setTimeout(function () { toast.remove(); }, 350);
  }, 3500);
}

/* ── CSV Export from any table ────────────────────────────────── */
function exportTableCSV(tableId, filename) {
  var table = document.getElementById(tableId);
  if (!table) { return; }
  var rows = Array.from(table.querySelectorAll('tr'));
  var csv  = rows.map(function (row) {
    return Array.from(row.querySelectorAll('th,td'))
      .map(function (cell) { return '"' + cell.textContent.trim().replace(/"/g, '""') + '"'; })
      .join(',');
  }).join('\n');
  var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  var url  = URL.createObjectURL(blob);
  var a    = document.createElement('a');
  a.href     = url;
  a.download = filename || 'export.csv';
  document.body.appendChild(a);
  a.click();
  setTimeout(function () { document.body.removeChild(a); URL.revokeObjectURL(url); }, 500);
}

/* ── Download Report (with backend fallback) ─────────────────── */
function downloadReport(type) {
  var url = '../backend/admin-api.php?action=export_' + type;
  fetch(url)
    .then(function (r) {
      if (!r.ok) { throw new Error('not ready'); }
      return r.blob();
    })
    .then(function (blob) {
      var a      = document.createElement('a');
      a.href     = URL.createObjectURL(blob);
      a.download = type + '-report.csv';
      a.click();
    })
    .catch(function () {
      showAdminToast('Backend not connected yet — CSV export available after PHP setup.', 'info');
    });
}
