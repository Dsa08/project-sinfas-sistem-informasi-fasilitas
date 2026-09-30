<div class="app-dialog-overlay" id="app-confirm-overlay" aria-hidden="true">
    <section class="app-dialog-card" role="dialog" aria-modal="true" aria-labelledby="app-confirm-title">
        <div class="app-dialog-icon" id="app-confirm-icon">?</div>
        <h2 class="app-dialog-title" id="app-confirm-title">Konfirmasi</h2>
        <p class="app-dialog-message" id="app-confirm-message"></p>
        <div class="app-dialog-actions">
            <button type="button" class="app-dialog-btn app-dialog-cancel" id="app-confirm-cancel">Batal</button>
            <button type="button" class="app-dialog-btn app-dialog-accept" id="app-confirm-accept">Ya, lanjutkan</button>
        </div>
    </section>
</div>
<div class="app-toast-container" id="app-toast-container" aria-live="polite" aria-atomic="true"></div>
<style>
    .app-dialog-overlay { position:fixed; inset:0; z-index:100000; display:none; align-items:center; justify-content:center; padding:1rem; background:rgba(15,23,42,.48); }
    .app-dialog-overlay.is-open { display:flex; }
    .app-dialog-card { width:min(100%,420px); padding:1.5rem; border-radius:16px; background:#fff; box-shadow:0 24px 60px rgba(15,23,42,.24); text-align:center; animation:appDialogIn .18s ease-out; }
    .app-dialog-icon { width:44px; height:44px; display:grid; place-items:center; margin:0 auto .85rem; border-radius:50%; color:#1d4ed8; background:#eff6ff; font-size:1.4rem; font-weight:700; }
    .app-dialog-icon.is-danger { color:#dc2626; background:#fef2f2; }
    .app-dialog-title { margin:0 0 .45rem; color:#111827; font-size:1.1rem; font-weight:700; }
    .app-dialog-message { margin:0; color:#64748b; line-height:1.55; white-space:pre-line; }
    .app-dialog-actions { display:flex; justify-content:center; gap:.65rem; margin-top:1.4rem; }
    .app-dialog-btn { min-height:40px; padding:.55rem 1rem; border:0; border-radius:8px; font:inherit; font-weight:600; cursor:pointer; }
    .app-dialog-cancel { color:#334155; background:#f1f5f9; }
    .app-dialog-accept { color:#fff; background:#1d4ed8; }
    .app-dialog-accept.is-danger { background:#dc2626; }
    .app-toast-container { position:fixed; top:1.25rem; right:1.25rem; z-index:100001; display:grid; gap:.65rem; width:min(420px,calc(100vw - 2rem)); pointer-events:none; }
    .app-toast { padding:.95rem 1rem; border:1px solid #fecaca; border-left:4px solid #ef4444; border-radius:12px; color:#7f1d1d; background:#fff; box-shadow:0 12px 32px rgba(15,23,42,.15); font-size:.9rem; line-height:1.45; animation:appDialogIn .18s ease-out; pointer-events:auto; }
    .app-toast strong { display:block; margin-bottom:.15rem; color:#991b1b; }
    @keyframes appDialogIn { from { opacity:0; transform:translateY(8px) scale(.98); } to { opacity:1; transform:translateY(0) scale(1); } }
    @media (prefers-reduced-motion:reduce) { .app-dialog-card,.app-toast { animation:none; } }
</style>
<script>
    (() => {
        const overlay = document.getElementById('app-confirm-overlay');
        if (!overlay) return;
        const title = document.getElementById('app-confirm-title');
        const message = document.getElementById('app-confirm-message');
        const icon = document.getElementById('app-confirm-icon');
        const cancel = document.getElementById('app-confirm-cancel');
        const accept = document.getElementById('app-confirm-accept');
        let settle = null;

        const close = (result) => {
            overlay.classList.remove('is-open');
            overlay.setAttribute('aria-hidden', 'true');
            document.removeEventListener('keydown', onKeydown);
            const resolve = settle;
            settle = null;
            if (resolve) resolve(result);
        };
        const onKeydown = (event) => { if (event.key === 'Escape') close(false); };
        cancel.addEventListener('click', () => close(false));
        accept.addEventListener('click', () => close(true));
        overlay.addEventListener('click', (event) => { if (event.target === overlay) close(false); });

        window.showAppConfirm = (text, options = {}) => {
            title.textContent = options.title || 'Konfirmasi';
            message.textContent = text;
            const danger = Boolean(options.danger);
            icon.classList.toggle('is-danger', danger);
            icon.textContent = danger ? '!' : '?';
            accept.classList.toggle('is-danger', danger);
            accept.textContent = options.confirmText || 'Ya, lanjutkan';
            cancel.textContent = options.cancelText || 'Batal';
            overlay.classList.add('is-open');
            overlay.setAttribute('aria-hidden', 'false');
            document.addEventListener('keydown', onKeydown);
            accept.focus();
            return new Promise(resolve => { settle = resolve; });
        };

        window.showAppToast = (text, options = {}) => {
            const container = document.getElementById('app-toast-container');
            if (!container) return;
            const toast = document.createElement('div');
            toast.className = 'app-toast';
            const heading = document.createElement('strong');
            heading.textContent = options.title || 'Terjadi kendala';
            toast.append(heading, document.createTextNode(text));
            container.appendChild(toast);
            window.setTimeout(() => toast.remove(), options.duration || 5000);
        };

        window.confirmAppForm = (form, text, options = {}, event) => {
            if (form.dataset.appConfirmed === 'true') return true;
            event?.preventDefault();
            window.showAppConfirm(text, options).then(approved => {
                if (!approved) return;
                form.dataset.appConfirmed = 'true';
                form.requestSubmit();
                delete form.dataset.appConfirmed;
            });
            return false;
        };
    })();
</script>
