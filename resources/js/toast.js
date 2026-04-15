// Lightweight toast helper (Tailwind-styled, no Bootstrap, no jQuery)
// Usage:
//   import './toast';
//   window.toast.success('Saved!');
//   window.toast.error('Something went wrong');

function ensureContainer() {
    let el = document.getElementById('toast-container');
    if (!el) {
        el = document.createElement('div');
        el.id = 'toast-container';
        el.className = 'toast-container';
        document.body.appendChild(el);
    }
    return el;
}

function show(message, type = 'info', timeout = 4000) {
    const container = ensureContainer();
    const el = document.createElement('div');
    el.className = `toast toast-${type}`;
    el.setAttribute('role', 'alert');

    const icons = {
        success: '✓',
        error: '✕',
        warning: '!',
        info: 'i',
    };

    el.innerHTML = `
        <span class="font-bold">${icons[type] ?? 'i'}</span>
        <span class="flex-1">${message}</span>
        <button type="button" class="text-current/60 hover:text-current" aria-label="Close">×</button>
    `;

    const close = () => {
        el.classList.add('opacity-0', 'translate-x-2', 'transition');
        setTimeout(() => el.remove(), 200);
    };

    el.querySelector('button').addEventListener('click', close);
    container.appendChild(el);
    if (timeout) setTimeout(close, timeout);
}

window.toast = {
    success: (msg, t) => show(msg, 'success', t),
    error:   (msg, t) => show(msg, 'error', t),
    warning: (msg, t) => show(msg, 'warning', t),
    info:    (msg, t) => show(msg, 'info', t),
};
