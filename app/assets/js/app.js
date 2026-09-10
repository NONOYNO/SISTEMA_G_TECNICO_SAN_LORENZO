(() => {
  'use strict';

  function csrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta && meta.getAttribute('content')) {
      return meta.getAttribute('content');
    }

    const input = document.querySelector('input[name="_token"]');
    return input ? input.value : '';
  }

  async function apiFetch(url, options = {}) {
    const opts = { ...options };
    const headers = new Headers(opts.headers || {});

    if (!headers.has('Accept')) {
      headers.set('Accept', 'application/json');
    }

    const method = (opts.method || 'GET').toUpperCase();
    if (['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)) {
      headers.set('X-CSRF-TOKEN', csrfToken());
      headers.set('X-Requested-With', 'XMLHttpRequest');
    }

    if (opts.body && !(opts.body instanceof FormData) && !headers.has('Content-Type')) {
      headers.set('Content-Type', 'application/json');
      if (typeof opts.body === 'object') {
        opts.body = JSON.stringify(opts.body);
      }
    }

    opts.headers = headers;
    opts.credentials = opts.credentials || 'same-origin';

    const response = await fetch(url, opts);
    const contentType = response.headers.get('content-type') || '';
    const payload = contentType.includes('application/json')
      ? await response.json()
      : { success: response.ok, message: await response.text(), data: null, errors: {} };

    if (!response.ok) {
      const error = new Error(payload.message || 'Error en la solicitud');
      error.status = response.status;
      error.payload = payload;
      throw error;
    }

    return payload;
  }

  function bindMarkReadButtons() {
    document.querySelectorAll('.btn-mark-read').forEach((button) => {
      button.addEventListener('click', async () => {
        const url = button.getAttribute('data-url');
        const id = button.getAttribute('data-id');
        if (!url) {
          return;
        }

        button.disabled = true;

        try {
          const payload = await apiFetch(url, { method: 'POST', body: {} });
          const card = document.querySelector(`[data-notification-card="${id}"]`);
          if (card) {
            const soft = card.querySelector('.card-soft');
            if (soft) {
              soft.classList.remove('border-start', 'border-4', 'border-primary');
            }
            const label = card.querySelector('.mark-unread-label');
            if (label) {
              label.remove();
            }
            button.remove();
          }

          const badge = document.getElementById('unread-badge');
          const unread = payload?.data?.unread_count;
          if (badge && typeof unread === 'number') {
            badge.textContent = unread > 0 ? `${unread} sin leer` : 'Todo al día';
            badge.className = unread > 0 ? 'badge text-bg-danger' : 'text-success small';
          }
        } catch (error) {
          button.disabled = false;
          window.alert(error.message || 'No se pudo marcar como leída.');
        }
      });
    });
  }

  function formatBytes(bytes) {
    if (bytes >= 1073741824) {
      return (bytes / 1073741824).toFixed(2) + ' GB';
    }
    if (bytes >= 1048576) {
      return (bytes / 1048576).toFixed(1) + ' MB';
    }
    return Math.max(1, Math.round(bytes / 1024)) + ' KB';
  }

  function bindMultiAttachments() {
    const input = document.getElementById('attachments');
    const preview = document.getElementById('attachments-preview');
    if (!input || !preview) {
      return;
    }

    const maxMb = parseInt(input.getAttribute('data-max-mb') || '512', 10);
    const maxBytes = maxMb * 1024 * 1024;

    input.addEventListener('change', () => {
      preview.innerHTML = '';
      const files = Array.from(input.files || []);
      if (files.length === 0) {
        return;
      }

      let hasOversize = false;
      files.forEach((file) => {
        const li = document.createElement('li');
        const over = file.size > maxBytes;
        if (over) {
          hasOversize = true;
        }
        li.className = over ? 'text-danger' : '';
        li.textContent = `${file.name} — ${formatBytes(file.size)}${over ? ` (supera ${maxMb} MB)` : ''}`;
        preview.appendChild(li);
      });

      if (hasOversize) {
        window.alert(`Uno o más archivos superan el límite de ${maxMb} MB por archivo.`);
      }
    });
  }

  function bindMobileSidebar() {
    const toggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('app-sidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const closeBtn = document.getElementById('sidebarClose');

    if (!toggle || !sidebar) {
      return;
    }

    const iconOpen = toggle.querySelector('[data-icon="open"]');
    const iconClose = toggle.querySelector('[data-icon="close"]');

    function setOpen(open) {
      sidebar.classList.toggle('is-open', open);
      document.body.classList.toggle('sidebar-open', open);
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      toggle.setAttribute(
        'aria-label',
        open ? 'Cerrar menú de navegación' : 'Abrir menú de navegación'
      );

      if (iconOpen && iconClose) {
        iconOpen.classList.toggle('d-none', open);
        iconClose.classList.toggle('d-none', !open);
      }

      if (backdrop) {
        backdrop.hidden = !open;
        backdrop.classList.toggle('is-visible', open);
      }
    }

    function isDesktop() {
      return window.matchMedia('(min-width: 992px)').matches;
    }

    toggle.addEventListener('click', () => {
      if (isDesktop()) {
        return;
      }
      setOpen(!sidebar.classList.contains('is-open'));
    });

    if (closeBtn) {
      closeBtn.addEventListener('click', () => setOpen(false));
    }

    if (backdrop) {
      backdrop.addEventListener('click', () => setOpen(false));
    }

    sidebar.querySelectorAll('a.nav-link').forEach((link) => {
      link.addEventListener('click', () => {
        if (!isDesktop()) {
          setOpen(false);
        }
      });
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && sidebar.classList.contains('is-open')) {
        setOpen(false);
      }
    });

    window.addEventListener('resize', () => {
      if (isDesktop()) {
        setOpen(false);
      }
    });
  }

  function bindDashboardMotion() {
    const path = document.querySelector('.chart-line path');
    if (path && path.getTotalLength) {
      const length = path.getTotalLength();
      path.style.strokeDasharray = String(length);
      path.style.strokeDashoffset = String(length);
      requestAnimationFrame(() => {
        path.style.transition = 'stroke-dashoffset 1.4s ease';
        path.style.strokeDashoffset = '0';
      });
    }
  }

  function bindUserMenu() {
    const menu = document.querySelector('.app-navbar .dropdown-menu');
    if (!menu) {
      return;
    }

    menu.addEventListener(
      'click',
      (event) => {
        const profileLink = event.target.closest('a.dropdown-item');
        if (profileLink && profileLink.getAttribute('href')) {
          event.preventDefault();
          window.location.assign(profileLink.href);
          return;
        }

        const logoutBtn = event.target.closest('button[form="logoutForm"]');
        const form = document.getElementById('logoutForm');
        if (logoutBtn && form instanceof HTMLFormElement) {
          event.preventDefault();
          form.submit();
        }
      },
      true
    );
  }

  document.addEventListener('DOMContentLoaded', () => {
    bindMarkReadButtons();
    bindMultiAttachments();
    bindMobileSidebar();
    bindDashboardMotion();
    bindUserMenu();
  });

  window.PortalInfor = {
    csrfToken,
    apiFetch,
    bindMarkReadButtons,
    bindMultiAttachments,
    bindMobileSidebar,
  };
})();
