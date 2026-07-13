/**
 * WorkForce Pro – Core JS Module
 * Provides CSRF helpers, toast notifications, AJAX wrappers, and modal utilities.
 */

// ── CSRF token (injected from PHP into <meta> tag) ──────────────────────────
const CSRF = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// ── Toast notifications ─────────────────────────────────────────────────────
const Toast = (() => {
  let container = null;
  const init = () => {
    if (!container) {
      container = document.createElement('div');
      container.className = 'toast-container';
      document.body.appendChild(container);
    }
  };
  const show = (message, type = 'info', duration = 3500) => {
    init();
    const icons = { success: 'fa-check-circle', error: 'fa-times-circle', warning: 'fa-exclamation-triangle', info: 'fa-info-circle' };
    const colors = { success: '#22c55e', error: '#ef4444', warning: '#f59e0b', info: '#38bdf8' };
    const el = document.createElement('div');
    el.className = `toast-msg ${type}`;
    el.innerHTML = `<i class="fa-solid ${icons[type] || icons.info}" style="color:${colors[type]};font-size:1.1rem"></i><span>${message}</span>`;
    container.appendChild(el);
    setTimeout(() => { el.style.opacity = '0'; el.style.transform = 'translateX(120%)'; el.style.transition = '.3s'; setTimeout(() => el.remove(), 300); }, duration);
  };
  return { success: m => show(m,'success'), error: m => show(m,'error'), warning: m => show(m,'warning'), info: m => show(m,'info') };
})();

// ── HTTP helpers ────────────────────────────────────────────────────────────
const Api = {
  async post(url, data) {
    if (data instanceof FormData) {
      data.append('csrf_token', CSRF());
    } else {
      data = { ...data, csrf_token: CSRF() };
    }
    const body = data instanceof FormData ? data : new URLSearchParams(data);
    const res = await fetch(url, { method: 'POST', body });
    return res.json();
  },
  async get(url, params = {}) {
    const q = new URLSearchParams({ ...params, csrf_token: CSRF() });
    const res = await fetch(`${url}?${q}`);
    return res.json();
  }
};

// ── Loading spinner overlay ─────────────────────────────────────────────────
const Loader = (() => {
  let el = null;
  const create = () => {
    el = document.createElement('div');
    el.style.cssText = 'position:fixed;inset:0;background:rgba(15,23,42,.6);display:grid;place-items:center;z-index:9998;';
    el.innerHTML = '<div style="width:40px;height:40px;border:3px solid #334155;border-top-color:#6366f1;border-radius:50%;animation:spin .7s linear infinite"></div>';
    if (!document.querySelector('#spin-style')) {
      const s = document.createElement('style'); s.id='spin-style';
      s.textContent = '@keyframes spin{to{transform:rotate(360deg)}}';
      document.head.appendChild(s);
    }
  };
  return {
    show() { if(!el) create(); document.body.appendChild(el); },
    hide() { el && el.parentNode && el.parentNode.removeChild(el); }
  };
})();

// ── Form validation helper ──────────────────────────────────────────────────
function displayErrors(form, errors) {
  form.querySelectorAll('.is-invalid').forEach(e => e.classList.remove('is-invalid'));
  form.querySelectorAll('.invalid-feedback').forEach(e => e.remove());
  for (const [field, msg] of Object.entries(errors || {})) {
    const input = form.querySelector(`[name="${field}"]`);
    if (input) {
      input.classList.add('is-invalid');
      const fb = document.createElement('div');
      fb.className = 'invalid-feedback';
      fb.textContent = msg;
      input.after(fb);
    }
  }
}

function clearErrors(form) {
  form.querySelectorAll('.is-invalid').forEach(e => e.classList.remove('is-invalid'));
  form.querySelectorAll('.invalid-feedback').forEach(e => e.remove());
}

// ── Dark / Light mode toggle ─────────────────────────────────────────────────
function initTheme() {
  const saved = localStorage.getItem('wfp_theme') || 'dark';
  document.documentElement.setAttribute('data-theme', saved);
  document.querySelectorAll('.theme-icon').forEach(i => {
    i.className = `fa-solid ${saved === 'dark' ? 'fa-sun' : 'fa-moon'} theme-icon`;
  });
}
function toggleTheme() {
  const curr = document.documentElement.getAttribute('data-theme') || 'dark';
  const next = curr === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', next);
  localStorage.setItem('wfp_theme', next);
  document.querySelectorAll('.theme-icon').forEach(i => {
    i.className = `fa-solid ${next === 'dark' ? 'fa-sun' : 'fa-moon'} theme-icon`;
  });
}

// ── Sidebar mobile toggle ────────────────────────────────────────────────────
function initSidebar() {
  document.querySelectorAll('.mobile-toggle').forEach(btn => {
    btn.addEventListener('click', () => document.querySelector('.sidebar')?.classList.toggle('open'));
  });
}

// ── Avatar image preview ─────────────────────────────────────────────────────
function initAvatarPreview(inputId, previewId) {
  const input = document.getElementById(inputId);
  const preview = document.getElementById(previewId);
  if (!input || !preview) return;
  input.addEventListener('change', () => {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => { preview.src = e.target.result; };
    reader.readAsDataURL(file);
  });
}

// ── Confirm dialog (SweetAlert2 wrapper) ─────────────────────────────────────
async function confirmDelete(message = 'This action cannot be undone.') {
  if (typeof Swal === 'undefined') return confirm('Delete? ' + message);
  const result = await Swal.fire({
    title: 'Are you sure?', text: message, icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444', cancelButtonColor: '#475569',
    confirmButtonText: 'Yes, delete it!',
    background: 'var(--surface)', color: 'var(--text)'
  });
  return result.isConfirmed;
}

// ── Export helpers ────────────────────────────────────────────────────────────
function exportTable(endpoint, format) {
  window.location.href = `${endpoint}?action=export&format=${format}&csrf_token=${encodeURIComponent(CSRF())}`;
}

// ── Notifications ─────────────────────────────────────────────────────────────
async function loadNotifications() {
  try {
    const res = await Api.post('/ajax/settings.php', { action: 'notifications' });
    if (!res.success) return;
    const badge = document.querySelector('.notif-badge');
    if (badge) badge.textContent = res.unread > 0 ? res.unread : '';

    const list = document.querySelector('.notif-list');
    if (!list) return;
    list.innerHTML = '';
    if (!res.data.length) { list.innerHTML = '<div class="notif-item text-muted" style="font-size:.82rem;padding:16px">No notifications</div>'; return; }
    res.data.forEach(n => {
      const el = document.createElement('div');
      el.className = `notif-item${!n.read_at ? ' unread' : ''}`;
      el.innerHTML = `<div class="notif-title">${n.title}</div><div class="notif-msg">${n.message}</div><div class="notif-time">${n.created_at}</div>`;
      if (!n.read_at) {
        el.addEventListener('click', async () => {
          await Api.post('/ajax/settings.php', { action: 'mark_notification_read', id: n.id });
          el.classList.remove('unread');
          await loadNotifications();
        });
      }
      list.appendChild(el);
    });
  } catch (e) { /* silent */ }
}

// ── Init on DOM ready ─────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  initTheme();
  initSidebar();
  loadNotifications();

  // Theme toggle buttons
  document.querySelectorAll('[data-action="toggle-theme"]').forEach(btn => {
    btn.addEventListener('click', toggleTheme);
  });
});
