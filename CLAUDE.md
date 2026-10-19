# CLAUDE.md - Web bán mỹ phẩm (Laravel)

## Tổng quan dự án
Dự án đồ án: Website thương mại điện tử bán mỹ phẩm (cửa hàng cao cấp, thị trường Việt Nam). Người dùng: khách vãng lai, khách hàng, nhân viên (staff), quản trị (admin). Ngôn ngữ giao diện & nội dung: tiếng Việt (có dấu). Tiền tệ: VND, lưu số nguyên không có phần thập phân.

## Công nghệ
PHP 8.3+, Laravel (bản ổn định mới nhất), MySQL 8 (utf8mb4), Eloquent ORM, Blade, Bootstrap 5 (Sass tuỳ biến, build bằng Vite), JavaScript thuần/Alpine nhẹ cho AJAX, Chart.js cho thống kê, Pest/PHPUnit cho kiểm thử, Laravel Pint cho định dạng code. Hạn chế package ngoài; mỗi package thêm vào phải nêu lý do.

## Kiến trúc
MVC + lớp Service. Controller mỏng (validate bằng FormRequest → gọi Service → trả view/JSON). Logic nghiệp vụ nằm trong app/Services: CartService, OrderService, CouponService, ReportService. Phân quyền bằng middleware role + Policy. Controller chia nhóm App\Http\Controllers (khách) và App\Http\Controllers\Admin. View: resources/views/{layouts,components,pages,account,admin}.

## Quy ước code
Tên bảng số nhiều snake_case; Model số ít PascalCase; route name dạng nhóm.chuc-nang (vd cart.add, admin.products.index). URL công khai tiếng Việt không dấu (/san-pham, /gio-hang, /thanh-toan, /tai-khoan, /tin-tuc). Slug duy nhất. Comment code bằng tiếng Việt ngắn gọn ở logic khó. Không để logic trong Blade. Không hardcode chuỗi tiếng Việt trong controller nếu có thể dùng lang/vi.

## Quy tắc nghiệp vụ
1) Giá hiện hành = sale_price nếu có và < price, ngược lại price. 2) order_items lưu SNAPSHOT tên/sku/giá tại thời điểm đặt. 3) Trạng thái đơn: pending → confirmed → shipping → delivered; cancelled chỉ từ pending/confirmed; huỷ phải hoàn kho + hoàn lượt coupon. 4) Đặt hàng chạy trong transaction, khoá dòng khi trừ kho. 5) Coupon: percent (có max_discount) hoặc fixed; kiểm tra hiệu lực, min_order, usage_limit. 6) Chỉ khách đã mua và đơn delivered mới được đánh giá (1 lần/SP), admin duyệt trước khi hiển thị. 7) Khách vãng lai được đặt hàng. 8) Giỏ session cho khách, DB cho user, gộp khi đăng nhập. 9) Staff không quản lý nhân viên/cài đặt; luôn còn ≥ 1 admin. 10) Sản phẩm xoá mềm, không xoá cứng nếu đã có trong đơn.

## Cơ sở dữ liệu
Xem sheet 'Database' trong file kế hoạch; nguồn sự thật là migrations. Mọi thay đổi schema phải qua migration mới và cập nhật mục này + docs/erd.mmd.

## Giao diện
Phong cách sang trọng, tối giản cho thương hiệu mỹ phẩm cao cấp. Mọi quyết định thiết kế theo DESIGN.md (tạo ở bước 3.1). Dùng taste skill cho các bước giao diện. Không dùng giao diện Bootstrap mặc định. Responsive mobile-first. Ảnh lazy-load, có alt.

## Quy trình làm việc với AI
Mỗi lần chỉ làm ĐÚNG 1 bước trong kế hoạch, không làm trước phần sau. Bước phức tạp: lập kế hoạch (plan) và hỏi lại nếu mơ hồ trước khi code. Sau mỗi bước: chạy php artisan test và ./vendor/bin/pint, tóm tắt file đã tạo/sửa, đề xuất commit message (Conventional Commits). Không tự ý đổi schema/kiến trúc ngoài phạm vi bước; nếu cần thì dừng và hỏi. Khi gặp lỗi: đọc log, tìm nguyên nhân gốc, không che lỗi bằng try/catch rỗng.

## Ngoài phạm vi
Đa kho/đa chi nhánh, ứng dụng mobile native, hệ thống điểm thưởng, so sánh sản phẩm, đa ngôn ngữ ngoài tiếng Việt, tích hợp vận chuyển thật (GHN/GHTK) — chỉ làm khi tôi yêu cầu rõ.