// Redesigned toast helper (Tailwind-styled, no Bootstrap, no jQuery)
// Usage:
//   import './toast';
//   window.toast.success('Saved!');
//   window.toast.error('Something went wrong');
// Signature stays (message, timeoutMs?) so existing calls keep working.

const MAX_TOASTS = 4;
const DEFAULT_TIMEOUT = 4000;

const META = {
    success: { icon: 'ri-check-line', label: 'Success' },
    error: { icon: 'ri-error-warning-line', label: 'Error' },
    warning: { icon: 'ri-alert-line', label: 'Warning' },
    info: { icon: 'ri-information-line', label: 'Info' },
};

function ensureContainer() {
    let el = document.getElementById('toast-container');
    if (!el) {
        el = document.createElement('div');
        el.id = 'toast-container';
        el.className = 'toast-container';
        el.setAttribute('aria-live', 'polite');
        document.body.appendChild(el);
    }
    return el;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

function show(message, type = 'info', timeout = DEFAULT_TIMEOUT) {
    const meta = META[type] ?? META.info;
    const container = ensureContainer();

    while (container.children.length >= MAX_TOASTS) {
        container.firstElementChild?.remove();
    }

    const el = document.createElement('div');
    el.className = `toast toast-${type}`;
    el.setAttribute('role', 'alert');
    el.setAttribute('aria-label', meta.label);

    el.innerHTML = `
        <span class="toast-icon"><i class="${meta.icon}"></i></span>
        <span class="toast-body">${escapeHtml(message)}</span>
        <button type="button" class="toast-close" aria-label="Dismiss"><i class="ri-close-line"></i></button>
        ${timeout ? `<span class="toast-bar" style="animation-duration:${timeout}ms"></span>` : ''}
    `;

    let timer = null;
    const close = () => {
        if (timer) clearTimeout(timer);
        el.classList.add('toast-leaving');
        setTimeout(() => el.remove(), 200);
    };

    el.querySelector('.toast-close').addEventListener('click', close);
    el.addEventListener('mouseenter', () => {
        if (timer) clearTimeout(timer);
        el.querySelector('.toast-bar')?.remove();
    });
    container.appendChild(el);
    if (timeout) timer = setTimeout(close, timeout);

    return close;
}

window.toast = {
    success: (msg, t) => show(msg, 'success', t),
    error: (msg, t) => show(msg, 'error', t),
    warning: (msg, t) => show(msg, 'warning', t),
    info: (msg, t) => show(msg, 'info', t),
};
