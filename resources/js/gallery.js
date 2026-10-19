// Ảnh chi tiết sản phẩm: bấm ảnh nhỏ để đổi ảnh chính (không lightbox). Không JS thì vẫn thấy ảnh chính.
export function initGallery() {
    document.querySelectorAll('[data-gallery]').forEach((gallery) => {
        const main = gallery.querySelector('[data-gallery-main]');
        const thumbs = gallery.querySelectorAll('[data-gallery-thumb]');
        if (!main) {
            return;
        }

        thumbs.forEach((thumb) => {
            thumb.addEventListener('click', () => {
                main.src = thumb.dataset.src;
                thumbs.forEach((t) => t.removeAttribute('aria-current'));
                thumb.setAttribute('aria-current', 'true');
            });
        });
    });
}
