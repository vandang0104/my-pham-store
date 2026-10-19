// Reveal khi cuộn (DESIGN.md §8): nâng 24px + opacity, chạy MỘT lần, các phần tử cùng lượt cách nhau 80ms.
// Chỉ gắn .js-reveal lên <html> khi chạy được, nên nội dung vẫn hiện nếu JS lỗi; giảm chuyển động → không ẩn gì.

const STAGGER = 80;

export function initReveal() {
    const items = document.querySelectorAll('[data-reveal]');
    if (!items.length || !('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries
            .filter((entry) => entry.isIntersecting)
            .forEach((entry, i) => {
                const target = entry.target;
                target.style.transitionDelay = `${i * STAGGER}ms`;
                target.classList.add('is-revealed');
                // Bỏ độ trễ sau khi hiện để không làm chậm các chuyển động khác của phần tử
                target.addEventListener('transitionend', () => target.style.removeProperty('transition-delay'), { once: true });
                observer.unobserve(entry.target);
            });
    }, { rootMargin: '0px 0px -10% 0px' });

    document.documentElement.classList.add('js-reveal');
    items.forEach((item) => observer.observe(item));
}
