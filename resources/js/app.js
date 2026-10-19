// Bootstrap JS: dropdown (menu tài khoản), offcanvas (menu điện thoại, tìm kiếm; mini-cart ở bước 5.2)
import 'bootstrap';
import { initHeader } from './header';
import { initFlash } from './flash';

document.addEventListener('DOMContentLoaded', () => {
    initHeader();
    initFlash();
});
