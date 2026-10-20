# CLAUDE.md - Web bán mỹ phẩm (Laravel)

## Tổng quan dự án
Dự án đồ án: Website thương mại điện tử bán mỹ phẩm (cửa hàng cao cấp, thị trường Việt Nam). Từ 20/10/2026 cửa hàng CHỈ bán sản phẩm chăm sóc da mặt (khoảng 20 SP, danh mục: làm sạch, dưỡng da, mặt nạ, chống nắng da mặt). Người dùng: khách vãng lai, khách hàng, nhân viên (staff), quản trị (admin). Ngôn ngữ giao diện & nội dung: tiếng Việt (có dấu). Tiền tệ: VND, lưu số nguyên không có phần thập phân.

## Phạm vi rút gọn (chốt 19/10/2026 — hạn nộp 25/10/2026)
Ưu tiên làm XONG, chức năng cơ bản, không cầu kì. Kế hoạch mới: sheet 'Kế hoạch rút gọn' trong file kế hoạch.
GIỮ: trang chủ (giới thiệu cửa hàng, SP nổi bật, khuyến mãi, banner từ seed); danh sách theo danh mục, chi tiết SP; tìm theo từ khoá + lọc giá/danh mục/thương hiệu; giỏ hàng; thanh toán COD + chuyển khoản, xác nhận đơn (email driver log); đăng ký/đăng nhập/quên mật khẩu; hồ sơ, đổi mật khẩu, lịch sử đơn (huỷ đơn pending/confirmed); chat trực tuyến TỰ LÀM (polling, 2 bảng mới); form liên hệ; FAQ (chỉ hiển thị từ seed); admin: đăng nhập quản trị /admin/login, QL sản phẩm (1 ảnh/SP), QL danh mục, QL đơn hàng, QL tài khoản (khách + nhân viên gộp một màn: đổi vai trò, khoá/mở khoá), thống kê rút gọn (doanh thu theo ngày/tháng + top SP bán chạy, chỉ admin).
ĐÃ CẮT: đánh giá & bình luận (bảng reviews giữ nguyên, không dùng, không hiển thị sao); mã giảm giá (bảng coupons giữ nguyên; orders.coupon_id luôn null, discount = 0); CRUD banner/tin tức/FAQ; sổ địa chỉ; bộ lọc nâng cao (loại da, còn hàng, sắp xếp phức tạp); nhiều ảnh/kéo thả ảnh; thống kê theo loại SP/khách hàng, xuất CSV; VNPay; tra cứu đơn cho khách vãng lai; mua lại; mini-cart; gợi ý tìm kiếm tự động; SEO/sitemap; trang tĩnh, tin tức; CRUD thương hiệu (thương hiệu chỉ từ seed).

## Công nghệ
PHP 8.3+, Laravel (bản ổn định mới nhất), MySQL 8 (utf8mb4), Eloquent ORM, Blade, Bootstrap 5 (Sass tuỳ biến, build bằng Vite), JavaScript thuần/Alpine nhẹ cho AJAX, Chart.js cho thống kê, Pest/PHPUnit cho kiểm thử, Laravel Pint cho định dạng code. Hạn chế package ngoài; mỗi package thêm vào phải nêu lý do.

## Kiến trúc
MVC + lớp Service. Controller mỏng (validate bằng FormRequest → gọi Service → trả view/JSON). Logic nghiệp vụ nằm trong app/Services: CartService, OrderService, ReportService, ChatService (CouponService đã cắt). Phân quyền bằng middleware role + Policy. Controller chia nhóm App\Http\Controllers (khách) và App\Http\Controllers\Admin. View: resources/views/{layouts,components,pages,account,admin}.

## Quy ước code
Tên bảng số nhiều snake_case; Model số ít PascalCase; route name dạng nhóm.chuc-nang (vd cart.add, admin.products.index). URL công khai tiếng Việt không dấu (/san-pham, /gio-hang, /thanh-toan, /tai-khoan, /tin-tuc). Slug duy nhất. Comment code bằng tiếng Việt ngắn gọn ở logic khó. Không để logic trong Blade. Không hardcode chuỗi tiếng Việt trong controller nếu có thể dùng lang/vi.

## Quy tắc nghiệp vụ
1) Giá hiện hành = sale_price nếu có và < price, ngược lại price. 2) order_items lưu SNAPSHOT tên/sku/giá tại thời điểm đặt. 3) Trạng thái đơn: pending → confirmed → shipping → delivered; cancelled chỉ từ pending/confirmed; huỷ phải hoàn kho. 4) Đặt hàng chạy trong transaction, khoá dòng khi trừ kho. 5) (Đã cắt) Mã giảm giá. 6) (Đã cắt) Đánh giá sản phẩm. 7) Khách vãng lai được đặt hàng. 8) Giỏ session cho khách, DB cho user, gộp khi đăng nhập. 9) Staff không quản lý tài khoản (khách/nhân viên), không xem thống kê doanh thu; luôn còn ≥ 1 admin (ma trận: docs/permissions.md). 10) Sản phẩm xoá mềm, không xoá cứng nếu đã có trong đơn. 11) Không xoá đơn hàng (kể cả đơn đã huỷ).

## Cơ sở dữ liệu
Xem sheet 'Database' trong file kế hoạch; nguồn sự thật là migrations. Mọi thay đổi schema phải qua migration mới và cập nhật mục này + docs/erd.mmd.
Đã có migration (20 bảng nghiệp vụ): users (+ phone, role, avatar, birthday, skin_type, is_active), addresses, categories, brands, products (xoá mềm), product_images, carts, cart_items, coupons, orders, order_items, order_status_histories, reviews, banners, posts, faqs, contact_messages, settings, chat_conversations, chat_messages (chat — migration 2026_10_20_100000; khách vãng lai theo guest_token trong session).
Ràng buộc xoá chính: xoá user → đơn giữ lại (user_id null), giỏ/địa chỉ/đánh giá xoá theo; coupon/sản phẩm đã có trong đơn không xoá được (restrict); danh mục/thương hiệu còn sản phẩm không xoá được.
carts chỉ cho user đăng nhập (user_id bắt buộc, unique; đã bỏ session_id) — giỏ khách vãng lai lưu trong session. Doanh thu theo ngày tính theo orders.created_at (index ghép status + created_at).
Tài liệu: docs/erd.mmd (ERD), docs/data-dictionary.md (cột, kiểu, ràng buộc, hành vi khi xoá).

## Giao diện
**Từ 19/10/2026: giao diện ĐƠN GIẢN.** Phần đã làm (layouts/app, header, footer, trang chủ, component dùng chung) giữ nguyên. Trang mới (khách và admin) dùng thẳng class Bootstrap 5 mặc định (container/row, card, table, form-control, btn, alert, badge, nav…) trên Sass đã tuỳ biến — KHÔNG cần taste skill, không chụp ảnh đối chiếu, không Lighthouse, không hiệu ứng/animation. DESIGN.md chỉ là tham khảo cho phần đã làm, không ràng buộc trang mới. Vẫn giữ: tiếng Việt có dấu, responsive cơ bản, ảnh có alt, form có nhãn và báo lỗi.
Hướng cũ (tham khảo): hướng "trang là tấm nhãn in" (Tĩnh · Mộc · Tinh); màu Lụa #F5F2EC / Sơn mài #1E1B18 / Men ngọc #2E5A4C (nhấn duy nhất trên nền sáng) / Champagne #C2A878 (chỉ làm mảng, nút trên nền tối — cấm làm màu chữ trên nền sáng); chữ Cormorant Garamond 600 (tiêu đề ≥ 28px, line-height ≥ 1.1 vì dấu tiếng Việt) + Be Vietnam Pro (nội dung, ≥ 13px); góc vuông; không gradient, không bóng khi nghỉ, không emoji, không giao diện Bootstrap mặc định; icon SVG inline. Responsive mobile-first. Ảnh cục bộ, lazy-load, có alt. Mọi cặp chữ/nền ≥ 4.5:1 (bảng trong DESIGN.md §3).
**Utility khoảng cách của Bootstrap đã đổi theo thang 4/8 của DESIGN.md** (`p-*`, `m-*`, `gap-*`): 1 = 4px, 2 = 8px, 3 = 12px, 4 = 16px, 5 = 24px, 6 = 32px, 7 = 48px, 8 = 64px, 9 = 96px, 10 = 128px, 11 = 160px — KHÔNG dùng giá trị mặc định của Bootstrap (p-3 không còn là 16px). Token tương ứng `--sp-1` … `--sp-11`.
Layout & component dùng chung: `layouts/app` (trang khách), `layouts/auth` (đăng nhập… kế thừa app), `layouts/admin` (Bootstrap mặc định); component trong `resources/views/components` + `app/View/Components`; xem bằng mắt ở `/styleguide` (chỉ local). Truyền sản phẩm cho `<x-product-card>` đã `->with(['images', 'brand'])`; phân trang gọi `->withQueryString()`.

## Quy trình làm việc với AI
Mỗi lần chỉ làm ĐÚNG 1 bước trong kế hoạch, không làm trước phần sau. Bước phức tạp: lập kế hoạch (plan) và hỏi lại nếu mơ hồ trước khi code. Sau mỗi bước: chạy php artisan test và ./vendor/bin/pint, tóm tắt file đã tạo/sửa, đề xuất commit message (Conventional Commits). Không tự ý đổi schema/kiến trúc ngoài phạm vi bước; nếu cần thì dừng và hỏi. Khi gặp lỗi: đọc log, tìm nguyên nhân gốc, không che lỗi bằng try/catch rỗng.
Chạy 2 phiên: phiên kiểm tra gửi prompt từng bước, phiên làm code và báo cáo lại; phiên làm KHÔNG commit, phiên kiểm tra commit sau khi kiểm tra đạt (không push). Mỗi bước có Feature test tối thiểu cho luồng chính (tên test tiếng Việt, dùng làm test case Chương 5).

## Ngoài phạm vi
Đa kho/đa chi nhánh, ứng dụng mobile native, hệ thống điểm thưởng, so sánh sản phẩm, đa ngôn ngữ ngoài tiếng Việt, tích hợp vận chuyển thật (GHN/GHTK) — chỉ làm khi tôi yêu cầu rõ.