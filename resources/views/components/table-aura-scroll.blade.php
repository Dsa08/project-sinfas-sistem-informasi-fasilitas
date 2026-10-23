{{-- 
  KOMPONEN TABLE AURA SCROLL — SINFAS
  File: resources/views/components/table-aura-scroll.blade.php
  
  Fungsi:
  - Mendeteksi tabel admin yang terpotong karena resolusi layar laptop / mobile.
  - Menghilangkan scrollbar browser yang kaku/kurang estetik.
  - Menampilkan "Aura Glowing Cue" di tepi kanan/kiri dengan tombol panah bulat frosted glass.
  - Ketika ditekan, tabel bergeser halus (smooth scroll) ke kanan/kiri dan aura otomatis memudar/hilang jika sudah mencapai ujung kolom.
--}}
<style>
/* ============================================================
   SINFAS — Table Aura Horizontal Scroll Indicator
   ============================================================ */

.table-aura-card,
.system-table-card {
    position: relative !important;
    overflow: hidden !important;
}

.table-aura-viewport {
    width: 100%;
    overflow-x: auto !important;
    overflow-y: hidden !important;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none !important; /* Firefox */
    -ms-overflow-style: none !important; /* IE 10+ and Edge */
    position: relative;
    z-index: 1;
}

.table-aura-viewport::-webkit-scrollbar {
    display: none !important; /* Chrome, Safari, Opera */
    width: 0 !important;
    height: 0 !important;
}

/* Pastikan kolom tabel tidak gepeng di resolusi layar kecil */
.system-table,
.report-table {
    min-width: 960px !important;
}

.system-table th:last-child,
.system-table td:last-child,
.system-table .td-actions {
    white-space: nowrap !important;
}

/* Aura Glow Overlays di Ujung Kiri & Kanan */
.table-aura-edge {
    position: absolute;
    top: 0;
    bottom: 0;
    width: 76px;
    display: flex;
    align-items: center;
    pointer-events: none;
    z-index: 25;
    opacity: 0;
    cursor: pointer;
    transition: opacity 0.35s cubic-bezier(0.16, 1, 0.3, 1), transform 0.35s ease;
}

.table-aura-edge--left {
    left: 0;
    justify-content: flex-start;
    padding-left: 12px;
    background: linear-gradient(to right, rgba(255, 255, 255, 0.98) 0%, rgba(255, 255, 255, 0.75) 45%, rgba(255, 255, 255, 0) 100%);
    transform: translateX(-12px);
}

.table-aura-edge--right {
    right: 0;
    justify-content: flex-end;
    padding-right: 12px;
    background: linear-gradient(to left, rgba(255, 255, 255, 0.98) 0%, rgba(255, 255, 255, 0.75) 45%, rgba(255, 255, 255, 0) 100%);
    transform: translateX(12px);
}

.table-aura-edge.is-active {
    opacity: 1;
    pointer-events: auto;
    transform: translateX(0);
}

/* Tombol Melayang Ber-aura Frosted Glass (Sesuai Referensi Gambar) */
.table-aura-btn {
    pointer-events: auto;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1.5px solid rgba(249, 115, 22, 0.45);
    box-shadow: 0 4px 18px rgba(249, 115, 22, 0.32), 0 2px 8px rgba(15, 23, 42, 0.08);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #ea580c; /* Panah chevron oranye cerah */
    padding: 0;
    transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
    user-select: none;
    outline: none;
}

/* Efek Aura Gelombang Berdenyut Lembut */
.table-aura-btn::before {
    content: '';
    position: absolute;
    inset: -5px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(249, 115, 22, 0.4) 0%, rgba(249, 115, 22, 0) 70%);
    z-index: -1;
    animation: tableAuraPulse 2.2s infinite ease-in-out;
}

@keyframes tableAuraPulse {
    0%, 100% {
        transform: scale(0.95);
        opacity: 0.55;
    }
    50% {
        transform: scale(1.3);
        opacity: 1;
    }
}

.table-aura-btn:hover {
    transform: scale(1.14);
    background: #ffffff;
    border-color: #ea580c;
    color: #c2410c;
    box-shadow: 0 6px 24px rgba(249, 115, 22, 0.5), 0 0 16px rgba(249, 115, 22, 0.4);
}

.table-aura-btn:active {
    transform: scale(0.92);
    box-shadow: 0 2px 8px rgba(249, 115, 22, 0.3);
}

.table-aura-btn svg {
    display: block;
    width: 20px;
    height: 20px;
    stroke-width: 3;
    transition: transform 0.2s ease;
}

.table-aura-btn--right:hover svg {
    transform: translateX(2px);
}

.table-aura-btn--left:hover svg {
    transform: translateX(-2px);
}
</style>

<script>
(() => {
    function initTableAuraScroll() {
        const tableCards = document.querySelectorAll('.system-table-card, .report-table-wrap, .report-table-card, .account-table-wrapper');
        
        tableCards.forEach((card) => {
            if (card.dataset.tableAuraReady === 'true') return;

            const table = card.querySelector('table');
            if (!table) return;

            card.dataset.tableAuraReady = 'true';
            card.classList.add('table-aura-card');

            // Bungkus table dengan viewport scrollable jika belum ada
            let viewport = card.querySelector('.table-aura-viewport');
            if (!viewport) {
                viewport = document.createElement('div');
                viewport.className = 'table-aura-viewport';
                table.parentNode.insertBefore(viewport, table);
                viewport.appendChild(table);
            }

            // Buat Edge Aura Kiri
            const leftEdge = document.createElement('div');
            leftEdge.className = 'table-aura-edge table-aura-edge--left';
            leftEdge.setAttribute('aria-hidden', 'true');
            leftEdge.innerHTML = `
                <button type="button" class="table-aura-btn table-aura-btn--left" aria-label="Geser ke kiri" title="Geser tabel ke kiri">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </button>
            `;

            // Buat Edge Aura Kanan
            const rightEdge = document.createElement('div');
            rightEdge.className = 'table-aura-edge table-aura-edge--right';
            rightEdge.setAttribute('aria-hidden', 'true');
            rightEdge.innerHTML = `
                <button type="button" class="table-aura-btn table-aura-btn--right" aria-label="Geser ke kanan" title="Geser tabel ke kanan">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
            `;

            card.appendChild(leftEdge);
            card.appendChild(rightEdge);

            const leftBtn = leftEdge.querySelector('.table-aura-btn');
            const rightBtn = rightEdge.querySelector('.table-aura-btn');

            function updateAura() {
                requestAnimationFrame(() => {
                    const maxScrollLeft = viewport.scrollWidth - viewport.clientWidth;
                    const tolerance = 6;

                    // Jika seluruh kolom tabel muat di layar tanpa terpotong
                    if (maxScrollLeft <= tolerance) {
                        leftEdge.classList.remove('is-active');
                        rightEdge.classList.remove('is-active');
                        return;
                    }

                    // Tampilkan aura kiri jika sudah digeser ke kanan
                    if (viewport.scrollLeft > 12) {
                        leftEdge.classList.add('is-active');
                    } else {
                        leftEdge.classList.remove('is-active');
                    }

                    // Tampilkan aura kanan jika masih ada kolom tersembunyi di kanan
                    if (viewport.scrollLeft < maxScrollLeft - tolerance) {
                        rightEdge.classList.add('is-active');
                    } else {
                        rightEdge.classList.remove('is-active');
                    }
                });
            }

            const scrollDistance = () => Math.max(280, Math.round(viewport.clientWidth * 0.6));

            // Geser saat tombol atau area aura diklik
            rightBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                viewport.scrollBy({ left: scrollDistance(), behavior: 'smooth' });
            });

            leftBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                viewport.scrollBy({ left: -scrollDistance(), behavior: 'smooth' });
            });

            rightEdge.addEventListener('click', (e) => {
                if (e.target === rightEdge) {
                    viewport.scrollBy({ left: scrollDistance(), behavior: 'smooth' });
                }
            });

            leftEdge.addEventListener('click', (e) => {
                if (e.target === leftEdge) {
                    viewport.scrollBy({ left: -scrollDistance(), behavior: 'smooth' });
                }
            });

            // Pantau event scroll pada viewport tabel
            viewport.addEventListener('scroll', updateAura, { passive: true });

            // Pantau perubahan ukuran layar (ResizeObserver)
            if (window.ResizeObserver) {
                const ro = new ResizeObserver(() => updateAura());
                ro.observe(viewport);
                ro.observe(table);
            }

            window.addEventListener('resize', updateAura, { passive: true });

            // Evaluasi awal
            updateAura();
            setTimeout(updateAura, 150);
            setTimeout(updateAura, 500);
        });
    }

    // Jalankan saat DOM siap
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTableAuraScroll);
    } else {
        initTableAuraScroll();
    }

    // Pantau tab switcher (misal di halaman Verifikasi atau Dashboard)
    document.addEventListener('click', (e) => {
        if (e.target.closest('.sarana-tab-btn, .system-tab-btn, [data-tab], .system-nav-link')) {
            setTimeout(() => {
                document.querySelectorAll('.table-aura-viewport').forEach((vp) => {
                    vp.dispatchEvent(new Event('scroll'));
                });
                initTableAuraScroll();
            }, 100);
        }
    });

    window.refreshTableAuraScroll = initTableAuraScroll;
})();
</script>
