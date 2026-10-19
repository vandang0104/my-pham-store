// Bootstrap JS: dropdown (menu tài khoản), offcanvas (menu điện thoại, tìm kiếm), collapse/modal cho trang mới
import 'bootstrap';
import { initHeader } from './header';
import { initFlash } from './flash';
import { initGallery } from './gallery';

document.addEventListener('DOMContentLoaded', () => {
    initHeader();
    initFlash();
    initGallery();
});
