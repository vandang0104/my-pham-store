// Trang chủ (DESIGN.md §8, §10.1): crossfade hero và tab "Sản phẩm nổi bật".

const HOLD = 6000; // giữ mỗi ảnh 6s (crossfade 1.6s đặt trong CSS)

const reducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// Hero: từ 2 banner trở lên mới chạy; giảm chuyển động → chỉ ảnh đầu. Dừng khi tab ẩn hoặc khi khách bấm "Dừng".
function initHero(root) {
    const slides = [...root.querySelectorAll('[data-hero-slide]')];
    if (slides.length < 2 || reducedMotion()) {
        return;
    }

    const title = root.querySelector('[data-hero-title]');
    const link = root.querySelector('[data-hero-link]');
    const pause = root.querySelector('[data-hero-pause]');
    let current = 0;
    let timer = null;
    let paused = false;

    const show = (index) => {
        slides[current].classList.remove('is-active');
        slides[current].setAttribute('aria-hidden', 'true');
        current = index;
        const slide = slides[current];
        slide.classList.add('is-active');
        slide.removeAttribute('aria-hidden');
        // Tiêu đề và link của banner đang hiện dùng cho h1 và nút
        title.textContent = slide.dataset.title;
        link.href = slide.dataset.url;
    };

    const schedule = () => {
        clearTimeout(timer);
        if (!paused && !document.hidden) {
            timer = setTimeout(() => {
                show((current + 1) % slides.length);
                schedule();
            }, HOLD);
        }
    };

    pause?.addEventListener('click', () => {
        paused = !paused;
        pause.setAttribute('aria-pressed', String(paused));
        pause.textContent = paused ? 'Tiếp tục chuyển ảnh' : 'Dừng chuyển ảnh';
        schedule();
    });

    document.addEventListener('visibilitychange', schedule);
    schedule();
}

// Tab nổi bật: không JS thì tab là link tới trang sản phẩm; có JS thì đổi panel tại chỗ (mẫu ARIA tabs).
function initTabs(root) {
    const list = root.querySelector('[data-tablist]');
    const tabs = [...root.querySelectorAll('[data-tab]')];
    const panels = [...root.querySelectorAll('[data-tab-panel]')];
    const media = [...root.querySelectorAll('[data-tab-media]')];
    if (tabs.length < 2) {
        return;
    }

    list.setAttribute('role', 'tablist');
    tabs.forEach((tab, i) => {
        tab.setAttribute('role', 'tab');
        tab.removeAttribute('aria-current');
        panels[i].setAttribute('role', 'tabpanel');
        panels[i].setAttribute('aria-labelledby', tab.id);
        panels[i].tabIndex = 0;
    });

    const select = (index, focus = false) => {
        tabs.forEach((tab, i) => {
            const active = i === index;
            tab.setAttribute('aria-selected', String(active));
            tab.tabIndex = active ? 0 : -1;
            panels[i].hidden = !active;
            if (media[i]) {
                media[i].hidden = !active;
            }
        });
        if (focus) {
            tabs[index].focus();
        }
    };

    tabs.forEach((tab, i) => {
        tab.addEventListener('click', (event) => {
            event.preventDefault();
            select(i);
        });
        tab.addEventListener('keydown', (event) => {
            const keys = { ArrowRight: i + 1, ArrowLeft: i - 1, Home: 0, End: tabs.length - 1 };
            if (event.key in keys) {
                event.preventDefault();
                select((keys[event.key] + tabs.length) % tabs.length, true);
            }
        });
    });

    select(0);
}

export function initHome() {
    document.querySelectorAll('[data-hero]').forEach(initHero);
    document.querySelectorAll('[data-tabs]').forEach(initTabs);
}
