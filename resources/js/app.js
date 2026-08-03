import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

/**
 * x-tooltip — tooltip kustom premium (Alpine directive)
 *
 * Pemakaian:  <span x-tooltip="'teks' atau ekspresi">...</span>
 * Satu elemen tooltip global di <body>, posisi dihitung dari bounding rect
 * (fixed), animasi fade + slide halus. Tidak terpotong elemen overflow-hidden.
 */
document.addEventListener('alpine:init', () => {
    let sharedTip = null;
    let showTimer = null;
    let activeEl = null;

    const getTip = () => {
        if (sharedTip) return sharedTip;
        sharedTip = document.createElement('div');
        sharedTip.className =
            'pointer-events-none fixed z-[70] px-2.5 py-1.5 rounded-lg bg-slate-900/95 text-white text-[11px] font-semibold shadow-xl ring-1 ring-white/10 backdrop-blur-sm whitespace-nowrap transition-all duration-150 opacity-0 translate-y-1';
        sharedTip.style.display = 'none';
        document.body.appendChild(sharedTip);
        return sharedTip;
    };

    const hide = () => {
        window.clearTimeout(showTimer);
        if (!sharedTip) return;
        sharedTip.classList.add('opacity-0', 'translate-y-1');
        window.setTimeout(() => {
            if (sharedTip) sharedTip.style.display = 'none';
        }, 150);
        activeEl = null;
    };

    const show = (el, text) => {
        if (!text) return;
        const tip = getTip();
        tip.textContent = text;
        tip.style.display = 'block';
        tip.classList.add('opacity-0', 'translate-y-1');

        const r = el.getBoundingClientRect();
        const tw = tip.offsetWidth;
        const th = tip.offsetHeight;
        let x = Math.round(r.left + r.width / 2 - tw / 2);
        let y = r.top - th - 8;
        if (y < 8) y = r.bottom + 8; // fallback: tampil di bawah elemen
        x = Math.max(8, Math.min(x, window.innerWidth - tw - 8));
        tip.style.left = x + 'px';
        tip.style.top = y + 'px';

        requestAnimationFrame(() => {
            tip.classList.remove('opacity-0', 'translate-y-1');
        });
    };

    Alpine.directive('tooltip', (el, { expression }) => {
        const getText = () => {
            try {
                return Alpine.evaluate(el, expression);
            } catch {
                return expression;
            }
        };

        el.addEventListener('mouseenter', () => {
            window.clearTimeout(showTimer);
            const text = getText();
            activeEl = el;
            // delay kecil agar tidak berkedip saat hover lintas elemen
            showTimer = window.setTimeout(() => {
                if (activeEl === el) show(el, text);
            }, 120);
        });

        el.addEventListener('mouseleave', hide);
        el.addEventListener('click', hide);
    });

    // Sembunyikan saat halaman digulir/di-klik (posisi berubah / sel kalender re-render)
    window.addEventListener('scroll', hide, true);
    window.addEventListener('resize', hide);
    document.addEventListener('click', hide);
});

Alpine.start();
