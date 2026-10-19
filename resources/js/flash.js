// Flash message (DESIGN.md §9): nút đóng; thông báo không phải lỗi tự ẩn sau 6 giây.

const AUTOHIDE_AFTER = 6000;

function dismiss(flash) {
    flash.classList.add('is-leaving');
    flash.addEventListener('transitionend', () => flash.remove(), { once: true });
    // Dự phòng khi không có transition (giảm chuyển động)
    setTimeout(() => flash.remove(), 400);
}

export function initFlash() {
    document.querySelectorAll('[data-flash]').forEach((flash) => {
        flash.querySelector('[data-flash-close]')?.addEventListener('click', () => dismiss(flash));

        if (flash.hasAttribute('data-flash-autohide')) {
            setTimeout(() => dismiss(flash), AUTOHIDE_AFTER);
        }
    });
}
