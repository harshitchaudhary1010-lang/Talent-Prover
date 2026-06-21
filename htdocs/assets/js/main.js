function togglePassword(id, btn) {
    const input = document.getElementById(id);
    if (!input) return;
    input.type = input.type === 'password' ? 'text' : 'password';
    const icon = btn ? btn.querySelector('i') : null;
    if (icon) icon.className = input.type === 'password' ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
}

function showToast(message, type = 'info') {
    let toast = document.getElementById('toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'toast';
        toast.className = 'fixed right-4 top-4 z-[80] max-w-sm rounded-2xl px-4 py-3 text-sm font-bold shadow-2xl transition-all';
        document.body.appendChild(toast);
    }
    const palette = {
        success: 'bg-emerald-600 text-white',
        error: 'bg-rose-600 text-white',
        info: 'bg-slate-900 text-white'
    };
    toast.className = 'fixed right-4 top-4 z-[80] max-w-sm rounded-2xl px-4 py-3 text-sm font-bold shadow-2xl transition-all ' + (palette[type] || palette.info);
    toast.textContent = message;
    toast.style.opacity = '1';
    toast.style.transform = 'translateY(0)';
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-8px)';
    }, 2800);
}

async function postForm(url, formData) {
    const response = await fetch(url, { method: 'POST', body: formData });
    const text = await response.text();
    try {
        return JSON.parse(text);
    } catch {
        return { success: false, message: 'The server returned an invalid response.' };
    }
}

function openModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.add('open');
}

function closeModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.remove('open');
}

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) sidebar.classList.toggle('open');
}

function toggleTopNav() {
    const nav = document.getElementById('mobileNav');
    if (nav) nav.classList.toggle('open');
}

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, char => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    }[char]));
}

function insertEmoji(inputId, emoji) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const start = input.selectionStart ?? input.value.length;
    const end = input.selectionEnd ?? input.value.length;
    input.value = input.value.slice(0, start) + emoji + input.value.slice(end);
    input.focus();
    const next = start + emoji.length;
    input.setSelectionRange(next, next);
}

function toggleEmojiPanel(id) {
    const panel = document.getElementById(id);
    if (!panel) return;
    panel.classList.toggle('open');
}

async function updateMessageBadges() {
    const links = document.querySelectorAll('a[href*="messages.php"]');
    if (!links.length) return;

    try {
        const response = await fetch('/api/messages.php?action=unread_count');
        const data = await response.json();
        if (!data.success) return;

        links.forEach(link => {
            const oldBadge = link.querySelector('.nav-message-count');
            if (oldBadge) oldBadge.remove();
            if (Number(data.count) > 0) {
                const badge = document.createElement('span');
                badge.className = 'nav-message-count';
                badge.textContent = data.count > 99 ? '99+' : data.count;
                link.appendChild(badge);
            }
        });
    } catch {
        // Ignore badge refresh errors on public pages.
    }
}

function badge(status) {
    const safe = escapeHtml(status || 'pending');
    return `<span class="badge badge-${safe.toLowerCase()}">${safe}</span>`;
}

function goBackOrHome() {
    if (window.history.length > 1) {
        window.history.back();
        return;
    }
    window.location.href = '/';
}

function addBackButton() {
    const path = window.location.pathname;
    if (path === '/' || path.endsWith('/index.php') || document.querySelector('.auth-nav') || document.querySelector('.app-back-btn')) return;

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'app-back-btn';
    button.setAttribute('aria-label', 'Go back');
    button.innerHTML = '<i class="fa-solid fa-arrow-left"></i><span>Back</span>';
    button.addEventListener('click', goBackOrHome);
    document.body.appendChild(button);
}

function addPagePreloader() {
    if (document.querySelector('.site-preloader')) return;

    const preloader = document.createElement('div');
    preloader.className = 'site-preloader';
    preloader.innerHTML = `
        <div class="preloader-particles" aria-hidden="true">
            <span></span><span></span><span></span><span></span>
        </div>
        <div class="preloader-card">
            <img src="/assets/images/logo.jpeg" alt="TalentProve logo">
            <div class="preloader-mark" aria-hidden="true">
                <div class="preloader-ring"></div>
                <i class="fa-solid fa-list-check"></i>
            </div>
            <p>Preparing your workspace</p>
            <div class="preloader-bar" aria-hidden="true"><span></span></div>
            <small>Loading tasks, profiles, and proof flows</small>
        </div>
    `;
    document.body.prepend(preloader);

    const hide = () => {
        preloader.classList.add('is-hidden');
        setTimeout(() => preloader.remove(), 520);
    };

    if (document.readyState === 'complete') {
        setTimeout(hide, 450);
    } else {
        window.addEventListener('load', () => setTimeout(hide, 450), { once: true });
    }
}

document.addEventListener('click', event => {
    const backdrop = event.target.closest('[data-modal-backdrop]');
    if (backdrop && event.target === backdrop) {
        backdrop.classList.remove('open');
    }

    if (!event.target.closest('.emoji-picker-wrap')) {
        document.querySelectorAll('.emoji-panel.open').forEach(panel => panel.classList.remove('open'));
    }
});

document.addEventListener('DOMContentLoaded', () => {
    addPagePreloader();
    addBackButton();
    updateMessageBadges();

    const revealItems = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window && revealItems.length) {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });
        revealItems.forEach(item => observer.observe(item));
    } else {
        revealItems.forEach(item => item.classList.add('is-visible'));
    }

    // Landing page motion is intentionally restrained for a professional product feel.
});

// ── Rose Petal Cursor Effect ───────────────────────────────
(function () {
    // Petal SVG paths — 6 natural petal shapes
    const petalPaths = [
        'M10,0 C18,0 24,8 20,16 C16,24 4,24 2,16 C0,8 2,0 10,0Z',
        'M10,1 C20,1 26,10 22,18 C18,26 2,25 1,17 C0,9 2,1 10,1Z',
        'M10,0 C22,2 26,12 20,20 C14,28 0,24 1,15 C2,6 0,0 10,0Z',
        'M10,2 C19,0 27,9 23,18 C19,27 1,26 0,17 C-1,8 3,4 10,2Z',
        'M10,0 C21,1 25,11 19,19 C13,27 1,23 2,14 C3,5 1,0 10,0Z',
        'M10,1 C20,3 24,13 18,21 C12,29 0,25 1,16 C2,7 2,0 10,1Z',
    ];

    // Petal colours — soft rose/pink palette
    const petalColors = [
        '#f9a8d4', // rose-300
        '#f472b6', // pink-400
        '#fb7185', // rose-400
        '#fda4af', // rose-300 warm
        '#f9c0d0', // blush
        '#fce7f3', // pink-100
        '#fbcfe8', // pink-200
        '#e879a0', // deep rose
    ];

    let lastX = 0, lastY = 0, lastTime = 0;
    const THROTTLE_MS = 40; // spawn a petal every ~40ms max

    function spawnPetal(x, y) {
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        svg.setAttribute('viewBox', '0 0 28 30');

        const size = 14 + Math.random() * 16; // 14–30px
        svg.style.cssText = `
            position: fixed;
            pointer-events: none;
            z-index: 99999;
            width: ${size}px;
            height: ${size}px;
            left: ${x - size / 2}px;
            top:  ${y - size / 2}px;
            opacity: 1;
            transform: rotate(${Math.random() * 360}deg) scale(1);
            transition: none;
            will-change: transform, opacity, top, left;
        `;

        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path.setAttribute('d', petalPaths[Math.floor(Math.random() * petalPaths.length)]);
        path.setAttribute('fill', petalColors[Math.floor(Math.random() * petalColors.length)]);
        path.setAttribute('opacity', '0.88');
        svg.appendChild(path);
        document.body.appendChild(svg);

        // Physics: drift + fall + spin
        const driftX  = (Math.random() - 0.5) * 80;   // -40 to +40px
        const fallY   = 60 + Math.random() * 80;        // 60–140px down
        const spin    = (Math.random() - 0.5) * 540;    // -270 to +270 deg
        const dur     = 900 + Math.random() * 600;       // 900–1500ms

        const startX  = x - size / 2;
        const startY  = y - size / 2;

        let start = null;
        function animate(ts) {
            if (!start) start = ts;
            const p = Math.min((ts - start) / dur, 1);

            // ease-out cubic
            const ease = 1 - Math.pow(1 - p, 3);

            svg.style.left    = `${startX + driftX * ease}px`;
            svg.style.top     = `${startY + fallY  * ease}px`;
            svg.style.opacity = `${1 - ease}`;
            svg.style.transform = `rotate(${spin * ease}deg) scale(${1 - ease * 0.3})`;

            if (p < 1) {
                requestAnimationFrame(animate);
            } else {
                svg.remove();
            }
        }
        requestAnimationFrame(animate);
    }

    // Burst of petals on click
    function spawnBurst(x, y) {
        const count = 6 + Math.floor(Math.random() * 5); // 6–10 petals
        for (let i = 0; i < count; i++) {
            setTimeout(() => spawnPetal(x, y), i * 30);
        }
    }

    document.addEventListener('mousemove', e => {
        const now = Date.now();
        if (now - lastTime < THROTTLE_MS) return;

        const dx = e.clientX - lastX;
        const dy = e.clientY - lastY;
        const dist = Math.sqrt(dx * dx + dy * dy);
        if (dist < 8) return; // only spawn when actually moving

        lastX = e.clientX;
        lastY = e.clientY;
        lastTime = now;

        spawnPetal(e.clientX, e.clientY);
    });

    document.addEventListener('click', e => {
        spawnBurst(e.clientX, e.clientY);
    });
})();
