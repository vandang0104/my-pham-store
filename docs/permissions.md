# Ma trận phân quyền

> **Nguồn sự thật về quyền.** Code phải khớp tài liệu này; khi đổi quyền, sửa tài liệu trước rồi sửa Policy/route và test.
> **Phạm vi rút gọn (19/10/2026, xem CLAUDE.md):** đã cắt mã giảm giá, đánh giá, CRUD banner/tin tức/FAQ, cài đặt cửa hàng, CRUD thương hiệu, sổ địa chỉ, tra cứu đơn cho khách vãng lai. Nhân viên và khách hàng gộp vào một màn **Tài khoản**, chỉ admin.

Ký hiệu: ✅ được phép · ❌ không được phép · ⚠️ được phép có điều kiện (xem cột Ghi chú).
Vai trò: **Admin** (quản trị), **Staff** (nhân viên), **Customer** (khách có tài khoản), **Guest** (khách vãng lai).

## Phân quyền hai tầng

| Tầng | Cơ chế | Phạm vi |
|---|---|---|
| 1. Route | Middleware `auth` + `role:admin,staff` cho toàn bộ `/admin`; nhóm con `role:admin` cho tài khoản và thống kê doanh thu | Chặn theo khu vực. Guest → chuyển về trang đăng nhập; Customer → 403; Staff vào nhóm `role:admin` → 403 |
| 2. Đối tượng | Policy trong `app/Policies` (gọi qua `Gate`/`authorize()` trong FormRequest) | Chặn theo từng bản ghi và điều kiện nghiệp vụ |
| Toàn cục | Middleware `EnsureAccountIsActive` (nhóm `web`) | Tài khoản bị khoá đang đăng nhập bị đăng xuất ở request kế tiếp; tài khoản bị khoá không đăng nhập được |

Sau khi đăng nhập: Admin/Staff → `/admin`; Customer → trang chủ. Trang đăng nhập quản trị riêng `/admin/login` làm ở S7.

## Khu quản trị (`/admin`)

Route dự kiến: `dashboard`, `products`, `categories`, `orders`, `users`, `reports`, `chats`, `contacts`.

| Tài nguyên | Hành động | Admin | Staff | Customer | Guest | Thực thi | Ghi chú |
|---|---|:-:|:-:|:-:|:-:|---|---|
| Dashboard | Xem | ✅ | ✅ | ❌ | ❌ | Route | Dashboard của staff chỉ có số liệu vận hành (đơn mới, đơn chờ xử lý, sản phẩm sắp hết hàng), **không** có doanh thu |
| Sản phẩm | Xem, tạo, sửa | ✅ | ✅ | ❌ | ❌ | `ProductPolicy` | 1 ảnh/sản phẩm |
| Sản phẩm | Xoá mềm, khôi phục | ✅ | ❌ | ❌ | ❌ | `ProductPolicy` | Staff không xoá dữ liệu |
| Sản phẩm | Xoá cứng | ⚠️ | ❌ | ❌ | ❌ | `ProductPolicy::forceDelete` | Chỉ khi sản phẩm chưa có trong đơn nào (quy tắc 10) |
| Danh mục | Xem, tạo, sửa | ✅ | ✅ | ❌ | ❌ | Route (Policy làm ở S7) | Thương hiệu chỉ lấy từ seed, không có CRUD |
| Danh mục | Xoá | ⚠️ | ❌ | ❌ | ❌ | Route + FK restrict | Không xoá khi còn sản phẩm / danh mục con |
| Đơn hàng | Xem danh sách, chi tiết | ✅ | ✅ | ❌ | ❌ | `OrderPolicy::viewAny/view` | |
| Đơn hàng | Đổi trạng thái theo luồng | ✅ | ✅ | ❌ | ❌ | `OrderPolicy::updateStatus` | Luồng hợp lệ do `OrderStatus::canTransitionTo()` + OrderService kiểm tra |
| Đơn hàng | Đánh dấu đã thanh toán | ✅ | ✅ | ❌ | ❌ | `OrderPolicy::updatePayment` | Chuyển khoản |
| Đơn hàng | Huỷ | ⚠️ | ⚠️ | ❌ | ❌ | `OrderPolicy::cancel` | Chỉ khi đơn đang `pending`/`confirmed`; hoàn kho |
| Đơn hàng | Xoá | ❌ | ❌ | ❌ | ❌ | `OrderPolicy::delete` | Không ai xoá được đơn hàng, kể cả đơn đã huỷ (quy tắc 11) |
| Chat trực tuyến | Xem hội thoại, trả lời | ✅ | ✅ | ❌ | ❌ | Route (Policy làm ở S6) | |
| Tin nhắn liên hệ | Xem, đánh dấu đã xử lý | ✅ | ✅ | ❌ | ❌ | Route (làm ở S5/S7) | |
| Tài khoản (khách + nhân viên) | Xem danh sách, chi tiết | ✅ | ❌ | ❌ | ❌ | Route `role:admin` + `UserPolicy::viewAny/view` | Một màn chung cho mọi vai trò |
| Tài khoản | Đổi vai trò | ⚠️ | ❌ | ❌ | ❌ | `UserPolicy::changeRole` | Không hạ quyền **admin đang hoạt động cuối cùng** (quy tắc 9) |
| Tài khoản | Khoá / mở khoá | ⚠️ | ❌ | ❌ | ❌ | `UserPolicy::toggleActive` | Không tự khoá mình; không khoá admin hoạt động cuối cùng |
| Tài khoản | Xoá | ⚠️ | ❌ | ❌ | ❌ | `UserPolicy::delete` | Không tự xoá mình; đơn hàng được giữ lại (user_id → NULL) |
| Thống kê doanh thu | Xem (theo ngày/tháng + top SP bán chạy) | ✅ | ❌ | ❌ | ❌ | Route `role:admin` (`/admin/reports`) | Staff không xem doanh thu; không xuất CSV |

**Đã cắt nhưng còn Policy + test (không dùng trong giao diện):** `CouponPolicy`, `ReviewPolicy`, `SettingPolicy` — giữ vì bảng và test còn tồn tại; không có route.

## Khu khách hàng

| Chức năng | Admin | Staff | Customer | Guest | Thực thi | Ghi chú |
|---|:-:|:-:|:-:|:-:|---|---|
| Xem sản phẩm, tìm kiếm, FAQ | ✅ | ✅ | ✅ | ✅ | Công khai | Chỉ hiện dữ liệu đang bật |
| Giỏ hàng, đặt hàng | ✅ | ✅ | ✅ | ✅ | Công khai | Khách vãng lai được đặt hàng (quy tắc 7) |
| Gửi liên hệ, chat với cửa hàng | ✅ | ✅ | ✅ | ✅ | Công khai | |
| Xem đơn của mình (`/tai-khoan/don-hang`) | ✅ | ✅ | ⚠️ | ❌ | `OrderPolicy::view` + kiểm `user_id` | Khu tài khoản chỉ liệt kê/hiện đơn của chính người đăng nhập (kể cả admin/staff); đơn người khác → **404** (không lộ mã đơn) |
| Huỷ đơn của mình | ⚠️ | ⚠️ | ⚠️ | ❌ | Kiểm `user_id` + `OrderService::cancel` (luồng trạng thái) | Chỉ đơn của mình (khác → 404), khi đang `pending`/`confirmed` (khác → báo lỗi). Huỷ đơn người khác làm ở khu quản trị |
| Hồ sơ, đổi mật khẩu | ✅ | ✅ | ✅ | ❌ | Middleware `auth` | Chỉ tài khoản của chính mình |

## Quyết định đã chốt

1. **Không ai xoá được đơn hàng** (kể cả admin, kể cả đơn đã huỷ) — đơn huỷ được giữ để thống kê.
2. **Thống kê doanh thu chỉ dành cho admin** (nhóm route `role:admin`); dashboard của staff chỉ hiển thị số liệu vận hành.
3. **Tài khoản khách và nhân viên gộp một màn, chỉ admin** (19/10/2026) — staff không xem/quản lý tài khoản.

## Còn để ngỏ

- **Danh mục, chat, liên hệ** chưa có Policy riêng; quyền ghi ở đây được thực thi bằng route (và Policy nếu cần) khi làm các bước S5–S7.
