// Bootstrap JS: dropdown (menu tài khoản), offcanvas (menu điện thoại, tìm kiếm, chat), collapse/modal cho trang mới
import 'bootstrap';
import { initHeader } from './header';
import { initFlash } from './flash';
import { initGallery } from './gallery';
import { initChat } from './chat';

document.addEventListener('DOMContentLoaded', () => {
    initHeader();
    initFlash();
    initGallery();
    initChat();

    // Chart.js chỉ tải ở trang có biểu đồ (thống kê admin) — Vite tách thành chunk riêng
    const charts = document.querySelectorAll('canvas[data-chart]');
    if (charts.length) {
        import('./charts').then(({ initCharts }) => initCharts(charts));
    }
});
