# Ma trận phân quyền

> **Nguồn sự thật về quyền** (chờ duyệt). Code phải khớp tài liệu này; khi đổi quyền, sửa tài liệu trước rồi sửa Policy/route và test.

Ký hiệu: ✅ được phép · ❌ không được phép · ⚠️ được phép có điều kiện (xem cột Ghi chú).
Vai trò: **Admin** (quản trị), **Staff** (nhân viên), **Customer** (khách có tài khoản), **Guest** (khách vãng lai).

## Phân quyền hai tầng

| Tầng | Cơ chế | Phạm vi |
|---|---|---|
| 1. Route | Middleware `auth` + `role:admin,staff` cho toàn bộ `/admin`; nhóm con `role:admin` cho nhân viên, cài đặt và thống kê doanh thu | Chặn theo khu vực. Guest → chuyển về `/dang-nhap`; Customer → 403; Staff vào nhóm `role:admin` → 403 |
| 2. Đối tượng | Policy trong `app/Policies` (gọi qua `Gate`/`authorize()` trong FormRequest) | Chặn theo từng bản ghi và điều kiện nghiệp vụ |
| Toàn cục | Middleware `EnsureAccountIsActive` (nhóm `web`) | Tài khoản bị khoá đang đăng nhập bị đăng xuất ở request kế tiếp; tài khoản bị khoá không đăng nhập được |

Sau khi đăng nhập: Admin/Staff → `/admin`; Customer → trang chủ.

## Khu quản trị (`/admin`)

| Tài nguyên | Hành động | Admin | Staff | Customer | Guest | Thực thi | Ghi chú |
|---|---|:-:|:-:|:-:|:-:|---|---|
| Dashboard | Xem | ✅ | ✅ | ❌ | ❌ | Route | Dashboard của staff chỉ có số liệu vận hành (đơn mới, đơn chờ xử lý, sản phẩm sắp hết hàng), **không** có doanh thu |
| Sản phẩm | Xem, tạo, sửa | ✅ | ✅ | ❌ | ❌ | `ProductPolicy` | |
| Sản phẩm | Xoá mềm, khôi phục | ✅ | ❌ | ❌ | ❌ | `ProductPolicy` | Staff không xoá dữ liệu |
| Sản phẩm | Xoá cứng | ⚠️ | ❌ | ❌ | ❌ | `ProductPolicy::forceDelete` | Chỉ khi sản phẩm chưa có trong đơn nào (quy tắc 10) |
| Danh mục, Thương hiệu | Xem, tạo, sửa | ✅ | ✅ | ❌ | ❌ | Route (Policy làm ở bước CRUD) | |
| Danh mục, Thương hiệu | Xoá | ⚠️ | ❌ | ❌ | ❌ | Route + FK restrict | Không xoá khi còn sản phẩm / danh mục con |
| Đơn hàng | Xem danh sách, chi tiết | ✅ | ✅ | ❌ | ❌ | `OrderPolicy::viewAny/view` | |
| Đơn hàng | Đổi trạng thái theo luồng | ✅ | ✅ | ❌ | ❌ | `OrderPolicy::updateStatus` | Luồng hợp lệ do `OrderStatus::canTransitionTo()` + OrderService kiểm tra |
| Đơn hàng | Đánh dấu đã thanh toán | ✅ | ✅ | ❌ | ❌ | `OrderPolicy::updatePayment` | |
| Đơn hàng | Huỷ | ⚠️ | ⚠️ | ❌ | ❌ | `OrderPolicy::cancel` | Chỉ khi đơn đang `pending`/`confirmed` |
| Đơn hàng | Xoá | ❌ | ❌ | ❌ | ❌ | `OrderPolicy::delete` | Không ai xoá được đơn hàng, kể cả đơn đã huỷ (giữ để thống kê doanh thu và tỉ lệ huỷ) |
| Đánh giá | Xem, duyệt / từ chối | ✅ | ✅ | ❌ | ❌ | `ReviewPolicy::viewAny/moderate` | |
| Đánh giá | Xoá | ✅ | ❌ | ❌ | ❌ | `ReviewPolicy::delete` | |
| Banner, Tin tức, FAQ | Xem, tạo, sửa | ✅ | ✅ | ❌ | ❌ | Route (Policy làm ở bước CRUD) | |
| Banner, Tin tức, FAQ | Xoá | ✅ | ❌ | ❌ | ❌ | Route (Policy làm ở bước CRUD) | |
| Tin nhắn liên hệ | Xem, đánh dấu đã xử lý | ✅ | ✅ | ❌ | ❌ | Route (Policy làm ở bước CRUD) | |
| Tin nhắn liên hệ | Xoá | ✅ | ❌ | ❌ | ❌ | Route (Policy làm ở bước CRUD) | |
| Khách hàng | Xem danh sách, chi tiết | ✅ | ✅ | ❌ | ❌ | `UserPolicy::viewAny/view` | Staff chỉ xem tài khoản khách hàng (và chính mình) |
| Khách hàng | Sửa, khoá / mở khoá | ✅ | ❌ | ❌ | ❌ | `UserPolicy::update/toggleActive` | Không tự khoá mình |
| Khách hàng | Xoá | ✅ | ❌ | ❌ | ❌ | `UserPolicy::delete` | Đơn hàng được giữ lại (user_id → NULL) |
| Nhân viên | Xem, tạo, sửa, khoá, xoá, đổi vai trò | ⚠️ | ❌ | ❌ | ❌ | Route `role:admin` + `UserPolicy` | Không tự xoá mình; không xoá / khoá / hạ quyền **admin đang hoạt động cuối cùng** (quy tắc 9) |
| Coupon | Xem | ✅ | ✅ | ❌ | ❌ | `CouponPolicy::viewAny/view` | |
| Coupon | Tạo, sửa | ✅ | ❌ | ❌ | ❌ | `CouponPolicy::create/update` | |
| Coupon | Xoá | ⚠️ | ❌ | ❌ | ❌ | `CouponPolicy::delete` | Chỉ khi chưa dùng trong đơn nào; nếu đã dùng thì tắt `is_active` |
| Cài đặt cửa hàng | Xem, sửa | ✅ | ❌ | ❌ | ❌ | Route `role:admin` + `SettingPolicy` | |
| Thống kê doanh thu | Xem, xuất CSV | ✅ | ❌ | ❌ | ❌ | Route `role:admin` (`/admin/reports`) | Staff không xem doanh thu |

## Khu khách hàng

| Chức năng | Admin | Staff | Customer | Guest | Thực thi | Ghi chú |
|---|:-:|:-:|:-:|:-:|---|---|
| Xem sản phẩm, tin tức, FAQ | ✅ | ✅ | ✅ | ✅ | Công khai | Chỉ hiện dữ liệu đang bật / đã duyệt / đã xuất bản |
| Giỏ hàng, đặt hàng | ✅ | ✅ | ✅ | ✅ | Công khai | Khách vãng lai được đặt hàng (quy tắc 7) |
| Tra cứu đơn bằng mã + SĐT | ✅ | ✅ | ✅ | ✅ | Công khai | |
| Xem đơn của mình | ✅ | ✅ | ⚠️ | ❌ | `OrderPolicy::view` | Customer chỉ xem đơn của chính mình |
| Huỷ đơn của mình | ⚠️ | ⚠️ | ⚠️ | ❌ | `OrderPolicy::cancel` | Chỉ đơn của mình, khi đang `pending`/`confirmed` |
| Viết đánh giá | ⚠️ | ⚠️ | ⚠️ | ❌ | `ReviewPolicy::create` | Đã có đơn **đã giao** chứa sản phẩm và chưa đánh giá sản phẩm đó (quy tắc 6) |
| Hồ sơ, đổi mật khẩu, sổ địa chỉ | ✅ | ✅ | ✅ | ❌ | Middleware `auth` | Chỉ tài khoản của chính mình |

## Quyết định đã chốt

1. **Không ai xoá được đơn hàng** (kể cả admin, kể cả đơn đã huỷ) — đơn huỷ được giữ để thống kê tỉ lệ huỷ.
2. **Thống kê doanh thu chỉ dành cho admin** (nhóm route `role:admin`); dashboard của staff chỉ hiển thị số liệu vận hành.

## Còn để ngỏ

- **Danh mục, thương hiệu, banner, tin tức, FAQ, liên hệ** chưa có Policy riêng (không nằm trong danh sách Policy của architecture.md); quyền đang ghi ở đây sẽ được thực thi bằng Policy khi làm các bước CRUD tương ứng.
