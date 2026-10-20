# Checklist chức năng theo đề cương

> Theo sheet "Ma trận chức năng" trong file kế hoạch. ✅ đã làm (route → controller) · ✂️ đã cắt theo **phạm vi rút gọn 19/10/2026** (CLAUDE.md, hạn nộp 25/10/2026) · ⚠️ làm một phần.
> Test tương ứng: `docs/test-cases/` (5.1 → 5.5, phụ lục hỗ trợ).
> Ngành hàng (từ 20/10/2026): chỉ chăm sóc da mặt — dữ liệu mẫu 4 danh mục cha (Làm sạch, Dưỡng da, Mặt nạ, Chống nắng), 9 danh mục con, 5 thương hiệu hư cấu, 21 sản phẩm.

## 1. Trang chủ

| Chức năng con | Trạng thái | Route → controller | Ghi chú |
|---|:-:|---|---|
| Giới thiệu tổng quan về cửa hàng | ✅ | `GET /` → `HomeController` | Khối "Về Mộc Lam" (`#gioi-thieu`) |
| Sản phẩm nổi bật, khuyến mãi, banner quảng cáo | ✅ | `GET /` → `HomeController` | Banner lấy từ seed (CRUD banner ✂️), tối đa 8 SP nổi bật + 8 SP đang giảm giá |

## 2. Quản lý sản phẩm (phía khách)

| Chức năng con | Trạng thái | Route → controller | Ghi chú |
|---|:-:|---|---|
| Danh sách sản phẩm phân loại theo danh mục | ✅ | `GET /san-pham`, `/danh-muc/{slug}`, `/thuong-hieu/{slug}` → `ProductController@index/category/brand` | Danh mục cha gồm cả danh mục con; 12 SP/trang |
| Chi tiết sản phẩm (tên, mô tả, giá, hình ảnh, đánh giá) | ⚠️ | `GET /san-pham/{slug}` → `ProductController@show` | Đủ tên, mô tả (Markdown đã lọc), giá, ảnh, tồn kho, SP liên quan. **Đánh giá ✂️** |
| Tìm kiếm & lọc theo giá, danh mục, tên, thương hiệu | ✅ | `GET /san-pham?q=&danh-muc=&thuong-hieu[]=&gia-tu=&gia-den=&khuyen-mai=1` → `ProductController@index` | Xem nhóm 7 |

## 3. Giỏ hàng

| Chức năng con | Trạng thái | Route → controller | Ghi chú |
|---|:-:|---|---|
| Thêm, cập nhật, xoá sản phẩm | ✅ | `POST /gio-hang`, `PATCH/DELETE /gio-hang/{id}` → `CartController` (`CartService`) | Khách vãng lai: session; tài khoản: DB; gộp khi đăng nhập/đăng ký. Mini-cart ✂️ |
| Tính tổng tiền đơn hàng | ✅ | `GET /gio-hang` → `CartController@index` | Theo giá hiện hành; hiện số tiền còn thiếu để miễn phí ship |

## 4. Thanh toán

| Chức năng con | Trạng thái | Route → controller | Ghi chú |
|---|:-:|---|---|
| Nhập thông tin giao hàng, phương thức thanh toán | ✅ | `GET/POST /thanh-toan` → `CheckoutController` (`OrderService::placeOrder`) | COD + chuyển khoản; khách vãng lai được đặt. VNPay ✂️, sổ địa chỉ ✂️ |
| Xác nhận đơn hàng | ✅ | `GET /dat-hang-thanh-cong/{mã}` → `CheckoutController@success`; mail `OrderPlaced` (driver log) | Chống gửi trùng bằng token một lần |

## 5. Tài khoản người dùng

| Chức năng con | Trạng thái | Route → controller | Ghi chú |
|---|:-:|---|---|
| Đăng ký, đăng nhập, quên mật khẩu | ✅ | `/dang-ky`, `/dang-nhap`, `/quen-mat-khau`, `/dat-lai-mat-khau/{token}` → `Auth\*Controller` | Tự viết, không starter kit |
| Thông tin cá nhân, lịch sử đơn hàng, đổi mật khẩu | ✅ | `/tai-khoan`, `/tai-khoan/thong-tin`, `/tai-khoan/mat-khau`, `/tai-khoan/don-hang` → `Account\*Controller` | Huỷ đơn chờ/đã xác nhận (hoàn kho). Mua lại ✂️, avatar ✂️ |

## 6. Quản trị (Admin)

| Chức năng con | Trạng thái | Route → controller | Ghi chú |
|---|:-:|---|---|
| Đăng nhập quản trị | ✅ | `GET/POST /admin/login` → `Auth\LoginController@createAdmin/storeAdmin` | Chỉ admin/staff |
| Quản lý sản phẩm (thêm, sửa, xoá) | ✅ | `/admin/products` → `Admin\ProductController` | 1 ảnh chính/SP; xoá mềm + khôi phục (chỉ admin). Nhiều ảnh/kéo thả ✂️ |
| Quản lý đơn hàng (xác nhận, xử lý, giao hàng) | ✅ | `/admin/orders` → `Admin\OrderController` (`OrderService::changeStatus/cancel`) | Đúng luồng trạng thái, huỷ hoàn kho, đánh dấu đã thanh toán, không xoá đơn |
| Quản lý nhân viên, khách hàng | ✅ | `/admin/users` → `Admin\UserController` (chỉ admin) | Gộp một màn: đổi vai trò, khoá/mở khoá; luôn còn ≥ 1 admin |
| Quản lý danh mục sản phẩm | ✅ | `/admin/categories` → `Admin\CategoryController` | Cây 2 cấp; không xoá khi còn SP / danh mục con |
| Quản lý nội dung (banner, tin tức…) | ✂️ | — | CRUD banner / tin tức / FAQ đã cắt; nội dung lấy từ seed |
| Thống kê doanh thu (loại SP, SP, khách hàng…) | ⚠️ | `/admin/reports` → `Admin\ReportController` (`ReportService`, chỉ admin) | Doanh thu theo ngày / 12 tháng + top 10 SP bán chạy (Chart.js). Thống kê theo loại SP / khách hàng, xuất CSV ✂️ |
| (thêm) Chat, tin liên hệ | ✅ | `/admin/chats`, `/admin/contacts` → `Admin\ChatController`, `Admin\ContactController` | Xem nhóm 9 |

## 7. Tìm kiếm và lọc

| Chức năng con | Trạng thái | Route → controller | Ghi chú |
|---|:-:|---|---|
| Tìm theo từ khoá | ✅ | `GET /san-pham?q=` → `ProductController@index` | Tên, SKU, mô tả, tên thương hiệu. Gợi ý tự động ✂️ |
| Lọc theo giá, danh mục, hãng sản xuất | ✅ | `?gia-tu=&gia-den=&danh-muc=&thuong-hieu[]=&khuyen-mai=1` | Tham số sai bị bỏ qua. Lọc loại da / còn hàng / sắp xếp phức tạp ✂️ |

## 8. Đánh giá và bình luận

| Chức năng con | Trạng thái | Route → controller | Ghi chú |
|---|:-:|---|---|
| Khách hàng đánh giá & nhận xét sản phẩm | ✂️ | — | Đã cắt; bảng `reviews` + seed giữ nguyên, không hiển thị sao |

## 9. Hỗ trợ khách hàng

| Chức năng con | Trạng thái | Route → controller | Ghi chú |
|---|:-:|---|---|
| Chat trực tuyến | ✅ | `GET/POST /chat/tin-nhan` → `ChatController`; `/admin/chats` → `Admin\ChatController` (`ChatService`) | Tự làm, polling 5 giây, 2 bảng `chat_conversations`, `chat_messages`; bật/tắt bằng setting `chat_enabled` |
| Form liên hệ | ✅ | `GET/POST /lien-he` → `ContactController`; `/admin/contacts` | Honeypot + giới hạn 3 lượt / 10 phút |
| Câu hỏi thường gặp (FAQ) | ✅ | `GET /cau-hoi-thuong-gap` → `FaqController` | Chỉ hiển thị từ seed (CRUD FAQ ✂️) |

## 10. Khuyến mãi và mã giảm giá

| Chức năng con | Trạng thái | Route → controller | Ghi chú |
|---|:-:|---|---|
| Áp dụng voucher, mã giảm giá trong giỏ hàng hoặc khi thanh toán | ✂️ | — | Mã giảm giá đã cắt (`orders.coupon_id` luôn null, `discount` = 0; bảng `coupons` giữ nguyên). **Khuyến mãi theo giá** (giá KM `sale_price`) vẫn có: nhãn −%, khối "Đang ưu đãi", lọc `?khuyen-mai=1` |

## Đã cắt khỏi phạm vi (CLAUDE.md, 19/10/2026)

Đánh giá & bình luận; mã giảm giá; CRUD banner / tin tức / FAQ; sổ địa chỉ; bộ lọc nâng cao (loại da, còn hàng, sắp xếp phức tạp); nhiều ảnh / kéo thả ảnh; thống kê theo loại SP / khách hàng, xuất CSV; VNPay; tra cứu đơn cho khách vãng lai; mua lại; mini-cart; gợi ý tìm kiếm tự động; SEO / sitemap; trang tĩnh, tin tức; CRUD thương hiệu (thương hiệu chỉ từ seed).
