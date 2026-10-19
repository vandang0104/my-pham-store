// Header (DESIGN.md §9): dính đầu trang, ẩn khi cuộn xuống và hiện lại khi cuộn lên; mega menu.

const CLOSE_DELAY = 200;

function initStickyHeader(header) {
    let lastY = window.scrollY;
    let ticking = false;

    const update = () => {
        const y = window.scrollY;
        const menuOpen = header.querySelector('[data-mega-trigger][aria-expanded="true"]');

        header.classList.toggle('is-scrolled', y > 0);
        // Không ẩn khi đang mở menu hoặc đang thao tác bàn phím trong header
        if (!menuOpen && !header.contains(document.activeElement)) {
            header.classList.toggle('is-hidden', y > lastY && y > header.offsetHeight);
        }
        lastY = y;
        ticking = false;
    };

    window.addEventListener('scroll', () => {
        if (!ticking) {
            window.requestAnimationFrame(update);
            ticking = true;
        }
    }, { passive: true });
}

function initMegaMenu(header) {
    const items = [...header.querySelectorAll('[data-mega]')];
    const canHover = window.matchMedia('(hover: hover) and (pointer: fine)');

    const setOpen = (item, open) => {
        const trigger = item.querySelector('[data-mega-trigger]');
        const panel = item.querySelector('[data-mega-panel]');
        trigger.setAttribute('aria-expanded', String(open));
        panel.hidden = !open;
        item.classList.toggle('is-open', open);
    };

    const closeAll = (except = null) => items.forEach((item) => item !== except && setOpen(item, false));

    items.forEach((item) => {
        const trigger = item.querySelector('[data-mega-trigger]');
        let closeTimer;

        trigger.addEventListener('click', () => {
            const open = trigger.getAttribute('aria-expanded') !== 'true';
            closeAll(item);
            setOpen(item, open);
        });

        item.addEventListener('mouseenter', () => {
            if (!canHover.matches) return;
            clearTimeout(closeTimer);
            closeAll(item);
            setOpen(item, true);
        });

        item.addEventListener('mouseleave', () => {
            if (!canHover.matches) return;
            closeTimer = setTimeout(() => setOpen(item, false), CLOSE_DELAY);
        });

        // Rời khỏi mục bằng bàn phím thì đóng panel
        item.addEventListener('focusout', (event) => {
            if (!item.contains(event.relatedTarget)) setOpen(item, false);
        });

        item.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && trigger.getAttribute('aria-expanded') === 'true') {
                setOpen(item, false);
                trigger.focus();
            }
        });
    });

    document.addEventListener('click', (event) => {
        if (!header.contains(event.target)) closeAll();
    });
}

function initSearch() {
    const panel = document.querySelector('[data-search-panel]');
    if (!panel) return;

    panel.addEventListener('shown.bs.offcanvas', () => panel.querySelector('input[type="search"]')?.focus());
}

export function initHeader() {
    const header = document.querySelector('[data-site-header]');
    if (!header) return;

    initStickyHeader(header);
    initMegaMenu(header);
    initSearch();
}
