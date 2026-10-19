# Từ điển dữ liệu — Mỹ Phẩm Shop

> Sinh từ schema MySQL hiện tại (sau toàn bộ migrations đến `2026_10_19_120300`). Nguồn sự thật là migrations; khi đổi schema phải cập nhật tài liệu này và `docs/erd.mmd`.

Quy ước: tiền tệ VND lưu `int unsigned` (không phần thập phân); chuỗi utf8mb4_unicode_ci; thời gian theo `Asia/Ho_Chi_Minh`. Không liệt kê bảng hạ tầng của Laravel: `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `sessions`, `password_reset_tokens`, `migrations`.

## Hành vi khi xoá bản ghi cha

| Bảng con | Cột | Tham chiếu | Khi xoá bản ghi cha |
|---|---|---|---|
| `addresses` | `user_id` | `users.id` | **CASCADE** — xoá theo |
| `cart_items` | `cart_id` | `carts.id` | **CASCADE** — xoá theo |
| `cart_items` | `product_id` | `products.id` | **CASCADE** — xoá theo |
| `carts` | `user_id` | `users.id` | **CASCADE** — xoá theo |
| `categories` | `parent_id` | `categories.id` | **RESTRICT** — chặn xoá |
| `order_items` | `order_id` | `orders.id` | **CASCADE** — xoá theo |
| `order_items` | `product_id` | `products.id` | **RESTRICT** — chặn xoá |
| `order_status_histories` | `changed_by` | `users.id` | **SET NULL** — đặt NULL, giữ bản ghi |
| `order_status_histories` | `order_id` | `orders.id` | **CASCADE** — xoá theo |
| `orders` | `coupon_id` | `coupons.id` | **RESTRICT** — chặn xoá |
| `orders` | `user_id` | `users.id` | **SET NULL** — đặt NULL, giữ bản ghi |
| `posts` | `author_id` | `users.id` | **SET NULL** — đặt NULL, giữ bản ghi |
| `product_images` | `product_id` | `products.id` | **CASCADE** — xoá theo |
| `products` | `brand_id` | `brands.id` | **RESTRICT** — chặn xoá |
| `products` | `category_id` | `categories.id` | **RESTRICT** — chặn xoá |
| `reviews` | `order_id` | `orders.id` | **SET NULL** — đặt NULL, giữ bản ghi |
| `reviews` | `product_id` | `products.id` | **CASCADE** — xoá theo |
| `reviews` | `user_id` | `users.id` | **CASCADE** — xoá theo |

Ghi chú: `products` dùng xoá mềm nên thao tác "xoá" thông thường không kích hoạt các ràng buộc trên; ràng buộc chỉ có tác dụng khi xoá cứng.

## `users`

Tài khoản người dùng (khách hàng, nhân viên, quản trị) — phân quyền bằng cột role.

| Cột | Kiểu | NULL | Mặc định | Ràng buộc | Mô tả |
|---|---|---|---|---|---|
| `id` | `bigint unsigned` | không |  | PK | Khoá chính tự tăng. |
| `name` | `varchar(255)` | không |  |  | Họ tên hiển thị. |
| `email` | `varchar(255)` | không |  | UNIQUE | Email đăng nhập, duy nhất. |
| `phone` | `varchar(20)` | có |  |  | Số điện thoại liên hệ. |
| `email_verified_at` | `timestamp` | có |  |  | Thời điểm xác minh email (cột mặc định của Laravel; dự án chưa dùng xác minh email). |
| `password` | `varchar(255)` | không |  |  | Mật khẩu đã băm (bcrypt). |
| `role` | `enum('admin','staff','customer')` | không | `customer` | INDEX | Vai trò: admin \| staff \| customer (Enum UserRole). Không gán hàng loạt. |
| `avatar` | `varchar(255)` | có |  |  | Đường dẫn ảnh đại diện trên disk public. |
| `birthday` | `date` | có |  |  | Ngày sinh. |
| `skin_type` | `varchar(30)` | có |  |  | Loại da của khách: oily \| dry \| combination \| sensitive \| all (Enum SkinType). |
| `is_active` | `tinyint(1)` | không | `1` |  | Tài khoản còn hoạt động; false = bị khoá. Không gán hàng loạt. |
| `remember_token` | `varchar(100)` | có |  |  | Token "ghi nhớ đăng nhập" của Laravel. |
| `created_at` | `timestamp` | có |  |  | Thời điểm tạo bản ghi. |
| `updated_at` | `timestamp` | có |  |  | Thời điểm cập nhật gần nhất. |

Khi xoá người dùng: `addresses.user_id` → xoá theo; `carts.user_id` → xoá theo; `order_status_histories.changed_by` → đặt NULL, giữ bản ghi; `orders.user_id` → đặt NULL, giữ bản ghi; `posts.author_id` → đặt NULL, giữ bản ghi; `reviews.user_id` → xoá theo.

## `addresses`

Sổ địa chỉ giao hàng của user.

| Cột | Kiểu | NULL | Mặc định | Ràng buộc | Mô tả |
|---|---|---|---|---|---|
| `id` | `bigint unsigned` | không |  | PK | Khoá chính tự tăng. |
| `user_id` | `bigint unsigned` | không |  | INDEX, FK → `users.id` (CASCADE) | Chủ sổ địa chỉ. |
| `receiver_name` | `varchar(100)` | không |  |  | Họ tên người nhận. |
| `phone` | `varchar(20)` | không |  |  | Số điện thoại người nhận. |
| `province` | `varchar(100)` | không |  |  | Tỉnh/thành phố. |
| `district` | `varchar(100)` | có |  |  | Quận/huyện — để trống với địa chỉ 2 cấp (từ 01/7/2025). |
| `ward` | `varchar(100)` | không |  |  | Phường/xã. |
| `detail` | `varchar(255)` | không |  |  | Số nhà, tên đường. |
| `is_default` | `tinyint(1)` | không | `0` |  | Địa chỉ mặc định khi thanh toán (mỗi user một địa chỉ mặc định — kiểm soát ở tầng ứng dụng). |
| `created_at` | `timestamp` | có |  |  | Thời điểm tạo bản ghi. |
| `updated_at` | `timestamp` | có |  |  | Thời điểm cập nhật gần nhất. |

## `categories`

Danh mục sản phẩm nhiều cấp (tự tham chiếu cha – con).

| Cột | Kiểu | NULL | Mặc định | Ràng buộc | Mô tả |
|---|---|---|---|---|---|
| `id` | `bigint unsigned` | không |  | PK | Khoá chính tự tăng. |
| `parent_id` | `bigint unsigned` | có |  | INDEX, FK → `categories.id` (RESTRICT) | Danh mục cha; NULL = danh mục gốc. |
| `name` | `varchar(150)` | không |  |  | Tên danh mục. |
| `slug` | `varchar(170)` | không |  | UNIQUE | Đường dẫn thân thiện, duy nhất (route binding). |
| `image` | `varchar(255)` | có |  |  | Ảnh đại diện danh mục. |
| `sort_order` | `int unsigned` | không | `0` |  | Thứ tự hiển thị (tăng dần). |
| `is_active` | `tinyint(1)` | không | `1` | INDEX | Hiển thị ngoài trang khách. |
| `created_at` | `timestamp` | có |  |  | Thời điểm tạo bản ghi. |
| `updated_at` | `timestamp` | có |  |  | Thời điểm cập nhật gần nhất. |

Khi xoá danh mục: `categories.parent_id` → chặn xoá; `products.category_id` → chặn xoá.

## `brands`

Thương hiệu / hãng sản xuất.

| Cột | Kiểu | NULL | Mặc định | Ràng buộc | Mô tả |
|---|---|---|---|---|---|
| `id` | `bigint unsigned` | không |  | PK | Khoá chính tự tăng. |
| `name` | `varchar(150)` | không |  |  | Tên thương hiệu. |
| `slug` | `varchar(170)` | không |  | UNIQUE | Đường dẫn thân thiện, duy nhất (route binding). |
| `logo` | `varchar(255)` | có |  |  | Ảnh logo. |
| `description` | `text` | có |  |  | Giới thiệu thương hiệu. |
| `is_active` | `tinyint(1)` | không | `1` | INDEX | Hiển thị ngoài trang khách. |
| `created_at` | `timestamp` | có |  |  | Thời điểm tạo bản ghi. |
| `updated_at` | `timestamp` | có |  |  | Thời điểm cập nhật gần nhất. |

Khi xoá thương hiệu: `products.brand_id` → chặn xoá.

## `products`

Sản phẩm. Xoá mềm (deleted_at); không xoá cứng khi đã có trong đơn (quy tắc 10).

| Cột | Kiểu | NULL | Mặc định | Ràng buộc | Mô tả |
|---|---|---|---|---|---|
| `id` | `bigint unsigned` | không |  | PK | Khoá chính tự tăng. |
| `category_id` | `bigint unsigned` | không |  | INDEX, FK → `categories.id` (RESTRICT) | Danh mục (thường là danh mục con). |
| `brand_id` | `bigint unsigned` | không |  | INDEX, FK → `brands.id` (RESTRICT) | Thương hiệu. |
| `name` | `varchar(200)` | không |  |  | Tên sản phẩm. |
| `slug` | `varchar(220)` | không |  | UNIQUE | Đường dẫn thân thiện, duy nhất (route binding). |
| `sku` | `varchar(50)` | không |  | UNIQUE | Mã hàng, duy nhất. |
| `short_description` | `varchar(500)` | có |  |  | Mô tả ngắn hiển thị ở thẻ sản phẩm. |
| `description` | `text` | có |  |  | Mô tả chi tiết (Markdown). |
| `ingredients` | `text` | có |  |  | Bảng thành phần. |
| `usage_guide` | `text` | có |  |  | Hướng dẫn sử dụng (Markdown). |
| `price` | `int unsigned` | không |  | INDEX | Giá gốc (VND, số nguyên). |
| `sale_price` | `int unsigned` | có |  |  | Giá khuyến mãi (VND); chỉ có hiệu lực khi nhỏ hơn price (quy tắc 1). |
| `stock` | `int unsigned` | không | `0` | INDEX | Số lượng tồn kho; trừ khi đặt hàng, hoàn khi huỷ đơn. |
| `volume` | `varchar(50)` | có |  |  | Dung tích / quy cách (vd 50ml, 3 món). |
| `skin_type` | `varchar(30)` | có |  | INDEX | Loại da phù hợp (Enum SkinType); NULL với sản phẩm không áp dụng (tóc, nước hoa, dụng cụ). |
| `is_featured` | `tinyint(1)` | không | `0` | INDEX | Sản phẩm nổi bật trên trang chủ. |
| `is_active` | `tinyint(1)` | không | `1` | INDEX | Đang bán / hiển thị ngoài trang khách. |
| `rating_avg` | `decimal(3,2)` | không | `0.00` | INDEX | Điểm đánh giá trung bình (phi chuẩn hoá từ reviews đã duyệt). |
| `rating_count` | `int unsigned` | không | `0` |  | Số đánh giá đã duyệt (phi chuẩn hoá). |
| `sold_count` | `int unsigned` | không | `0` | INDEX | Số lượng đã bán trong các đơn đã giao (phi chuẩn hoá). |
| `created_at` | `timestamp` | có |  | INDEX | Thời điểm tạo bản ghi. |
| `updated_at` | `timestamp` | có |  |  | Thời điểm cập nhật gần nhất. |
| `deleted_at` | `timestamp` | có |  |  | Thời điểm xoá mềm; NULL = chưa xoá. |

Khi xoá sản phẩm: `cart_items.product_id` → xoá theo; `order_items.product_id` → chặn xoá; `product_images.product_id` → xoá theo; `reviews.product_id` → xoá theo.

## `product_images`

Ảnh sản phẩm (3–4 ảnh/sản phẩm).

| Cột | Kiểu | NULL | Mặc định | Ràng buộc | Mô tả |
|---|---|---|---|---|---|
| `id` | `bigint unsigned` | không |  | PK | Khoá chính tự tăng. |
| `product_id` | `bigint unsigned` | không |  | INDEX, FK → `products.id` (CASCADE) | Sản phẩm sở hữu ảnh. |
| `path` | `varchar(255)` | không |  |  | Đường dẫn tương đối trên disk public. |
| `is_primary` | `tinyint(1)` | không | `0` |  | Ảnh chính (ảnh đại diện). |
| `sort_order` | `int unsigned` | không | `0` |  | Thứ tự trong thư viện ảnh. |
| `created_at` | `timestamp` | có |  |  | Thời điểm tạo bản ghi. |
| `updated_at` | `timestamp` | có |  |  | Thời điểm cập nhật gần nhất. |

## `carts`

Giỏ hàng của user đã đăng nhập (giỏ khách vãng lai lưu trong session, không lưu bảng này).

| Cột | Kiểu | NULL | Mặc định | Ràng buộc | Mô tả |
|---|---|---|---|---|---|
| `id` | `bigint unsigned` | không |  | PK | Khoá chính tự tăng. |
| `user_id` | `bigint unsigned` | không |  | UNIQUE, FK → `users.id` (CASCADE) | Chủ giỏ; mỗi user tối đa 1 giỏ. |
| `created_at` | `timestamp` | có |  |  | Thời điểm tạo bản ghi. |
| `updated_at` | `timestamp` | có |  |  | Thời điểm cập nhật gần nhất. |

Khi xoá giỏ: `cart_items.cart_id` → xoá theo.

## `cart_items`

Dòng sản phẩm trong giỏ của user.

| Cột | Kiểu | NULL | Mặc định | Ràng buộc | Mô tả |
|---|---|---|---|---|---|
| `id` | `bigint unsigned` | không |  | PK | Khoá chính tự tăng. |
| `cart_id` | `bigint unsigned` | không |  | FK → `carts.id` (CASCADE) | Giỏ chứa dòng này. |
| `product_id` | `bigint unsigned` | không |  | INDEX, FK → `products.id` (CASCADE) | Sản phẩm. |
| `quantity` | `int unsigned` | không | `1` |  | Số lượng (≥ 1). |
| `created_at` | `timestamp` | có |  |  | Thời điểm tạo bản ghi. |
| `updated_at` | `timestamp` | có |  |  | Thời điểm cập nhật gần nhất. |

Index nhiều cột: `cart_items_cart_id_product_id_unique` (cart_id, product_id) UNIQUE.

## `coupons`

Mã giảm giá.

| Cột | Kiểu | NULL | Mặc định | Ràng buộc | Mô tả |
|---|---|---|---|---|---|
| `id` | `bigint unsigned` | không |  | PK | Khoá chính tự tăng. |
| `code` | `varchar(50)` | không |  | UNIQUE | Mã nhập khi thanh toán, duy nhất. |
| `type` | `enum('percent','fixed')` | không |  |  | percent (giảm %) \| fixed (giảm số tiền) — Enum CouponType. |
| `value` | `int unsigned` | không |  |  | Phần trăm (với percent) hoặc số tiền VND (với fixed). |
| `min_order` | `int unsigned` | không | `0` |  | Giá trị tạm tính tối thiểu để áp dụng (VND). |
| `max_discount` | `int unsigned` | có |  |  | Mức giảm tối đa (VND) cho loại percent. |
| `usage_limit` | `int unsigned` | có |  |  | Tổng số lượt dùng tối đa; NULL = không giới hạn. |
| `used_count` | `int unsigned` | không | `0` |  | Số lượt đã dùng (đơn chưa huỷ); hoàn lượt khi huỷ đơn. Không gán hàng loạt. |
| `starts_at` | `timestamp` | có |  |  | Bắt đầu hiệu lực; NULL = không giới hạn. |
| `expires_at` | `timestamp` | có |  |  | Hết hiệu lực; NULL = không giới hạn. |
| `is_active` | `tinyint(1)` | không | `1` | INDEX | Bật/tắt mã. |
| `created_at` | `timestamp` | có |  |  | Thời điểm tạo bản ghi. |
| `updated_at` | `timestamp` | có |  |  | Thời điểm cập nhật gần nhất. |

Khi xoá coupon: `orders.coupon_id` → chặn xoá.

## `orders`

Đơn hàng (cho phép khách vãng lai: user_id NULL). Thông tin giao hàng lưu trực tiếp trong đơn.

| Cột | Kiểu | NULL | Mặc định | Ràng buộc | Mô tả |
|---|---|---|---|---|---|
| `id` | `bigint unsigned` | không |  | PK | Khoá chính tự tăng. |
| `code` | `varchar(20)` | không |  | UNIQUE | Mã đơn hiển thị cho khách (route binding, tra cứu đơn), duy nhất. |
| `user_id` | `bigint unsigned` | có |  | INDEX, FK → `users.id` (SET NULL) | Khách đặt; NULL = khách vãng lai hoặc tài khoản đã bị xoá. |
| `coupon_id` | `bigint unsigned` | có |  | INDEX, FK → `coupons.id` (RESTRICT) | Mã giảm giá đã áp dụng. |
| `receiver_name` | `varchar(100)` | không |  |  | Họ tên người nhận. |
| `phone` | `varchar(20)` | không |  |  | Số điện thoại người nhận (dùng kèm mã đơn để tra cứu). |
| `email` | `varchar(255)` | có |  |  | Email nhận thông báo đơn. |
| `province` | `varchar(100)` | không |  |  | Tỉnh/thành phố giao hàng. |
| `district` | `varchar(100)` | có |  |  | Quận/huyện — để trống với địa chỉ 2 cấp. |
| `ward` | `varchar(100)` | không |  |  | Phường/xã giao hàng. |
| `detail` | `varchar(255)` | không |  |  | Số nhà, tên đường. |
| `payment_method` | `enum('cod','bank_transfer','vnpay')` | không | `cod` |  | cod \| bank_transfer \| vnpay (Enum PaymentMethod). |
| `payment_status` | `enum('unpaid','paid','refunded')` | không | `unpaid` | INDEX | unpaid \| paid \| refunded (Enum PaymentStatus). Không gán hàng loạt. |
| `status` | `enum('pending','confirmed','shipping','delivered','cancelled')` | không | `pending` |  | pending → confirmed → shipping → delivered; cancelled chỉ từ pending/confirmed (Enum OrderStatus). Không gán hàng loạt. |
| `subtotal` | `int unsigned` | không |  |  | Tạm tính = tổng line_total (VND). |
| `discount` | `int unsigned` | không | `0` |  | Số tiền giảm từ coupon (VND). |
| `shipping_fee` | `int unsigned` | không | `0` |  | Phí giao hàng (VND). |
| `total` | `int unsigned` | không |  |  | Tổng thanh toán = subtotal − discount + shipping_fee (VND). |
| `note` | `text` | có |  |  | Ghi chú của khách. |
| `confirmed_at` | `timestamp` | có |  |  | Thời điểm xác nhận đơn. |
| `shipped_at` | `timestamp` | có |  |  | Thời điểm bàn giao vận chuyển. |
| `delivered_at` | `timestamp` | có |  |  | Thời điểm giao thành công. |
| `cancelled_at` | `timestamp` | có |  |  | Thời điểm huỷ. |
| `created_at` | `timestamp` | có |  | INDEX | Thời điểm tạo bản ghi. |
| `updated_at` | `timestamp` | có |  |  | Thời điểm cập nhật gần nhất. |

Index nhiều cột: `orders_status_created_at_index` (status, created_at).

Khi xoá đơn hàng: `order_items.order_id` → xoá theo; `order_status_histories.order_id` → xoá theo; `reviews.order_id` → đặt NULL, giữ bản ghi.

## `order_items`

Chi tiết đơn — SNAPSHOT tên/sku/giá tại thời điểm đặt (quy tắc 2).

| Cột | Kiểu | NULL | Mặc định | Ràng buộc | Mô tả |
|---|---|---|---|---|---|
| `id` | `bigint unsigned` | không |  | PK | Khoá chính tự tăng. |
| `order_id` | `bigint unsigned` | không |  | INDEX, FK → `orders.id` (CASCADE) | Đơn hàng. |
| `product_id` | `bigint unsigned` | có |  | INDEX, FK → `products.id` (RESTRICT) | Sản phẩm gốc (tham chiếu, kể cả khi đã xoá mềm). |
| `product_name` | `varchar(200)` | không |  |  | Tên sản phẩm lúc đặt (snapshot). |
| `sku` | `varchar(50)` | không |  |  | Mã hàng lúc đặt (snapshot). |
| `price` | `int unsigned` | không |  |  | Đơn giá hiện hành lúc đặt (VND, snapshot). |
| `quantity` | `int unsigned` | không |  |  | Số lượng. |
| `line_total` | `int unsigned` | không |  |  | Thành tiền = price × quantity (VND). |
| `created_at` | `timestamp` | có |  |  | Thời điểm tạo bản ghi. |
| `updated_at` | `timestamp` | có |  |  | Thời điểm cập nhật gần nhất. |

## `order_status_histories`

Nhật ký đổi trạng thái đơn (chỉ ghi thêm, không có updated_at).

| Cột | Kiểu | NULL | Mặc định | Ràng buộc | Mô tả |
|---|---|---|---|---|---|
| `id` | `bigint unsigned` | không |  | PK | Khoá chính tự tăng. |
| `order_id` | `bigint unsigned` | không |  | INDEX, FK → `orders.id` (CASCADE) | Đơn hàng. |
| `from_status` | `enum('pending','confirmed','shipping','delivered','cancelled')` | có |  |  | Trạng thái trước; NULL = lúc vừa đặt đơn. |
| `to_status` | `enum('pending','confirmed','shipping','delivered','cancelled')` | không |  |  | Trạng thái sau. |
| `changed_by` | `bigint unsigned` | có |  | INDEX, FK → `users.id` (SET NULL) | Người thao tác (nhân viên, quản trị hoặc chính khách); NULL = khách vãng lai/người đã bị xoá. |
| `note` | `varchar(255)` | có |  |  | Ghi chú cho lần đổi trạng thái. |
| `created_at` | `timestamp` | không | `CURRENT_TIMESTAMP` |  | Thời điểm tạo bản ghi. |

## `reviews`

Đánh giá sản phẩm — chỉ khách đã mua và đơn đã giao, 1 lần/sản phẩm, cần admin duyệt (quy tắc 6).

| Cột | Kiểu | NULL | Mặc định | Ràng buộc | Mô tả |
|---|---|---|---|---|---|
| `id` | `bigint unsigned` | không |  | PK | Khoá chính tự tăng. |
| `product_id` | `bigint unsigned` | không |  | FK → `products.id` (CASCADE) | Sản phẩm được đánh giá. |
| `user_id` | `bigint unsigned` | không |  | FK → `users.id` (CASCADE) | Khách viết đánh giá. |
| `order_id` | `bigint unsigned` | có |  | INDEX, FK → `orders.id` (SET NULL) | Đơn chứng minh đã mua. |
| `rating` | `tinyint unsigned` | không |  |  | Số sao 1–5 (kiểm tra ở FormRequest). |
| `comment` | `text` | không |  |  | Nội dung nhận xét. |
| `images` | `json` | có |  |  | Danh sách đường dẫn ảnh đính kèm (JSON). |
| `status` | `enum('pending','approved','rejected')` | không | `pending` |  | pending \| approved \| rejected (Enum ReviewStatus). Không gán hàng loạt. |
| `created_at` | `timestamp` | có |  |  | Thời điểm tạo bản ghi. |
| `updated_at` | `timestamp` | có |  |  | Thời điểm cập nhật gần nhất. |

Index nhiều cột: `reviews_product_id_status_created_at_index` (product_id, status, created_at); `reviews_status_created_at_index` (status, created_at); `reviews_user_id_product_id_unique` (user_id, product_id) UNIQUE.

## `banners`

Banner quảng bá (slider trang chủ, đầu trang danh mục).

| Cột | Kiểu | NULL | Mặc định | Ràng buộc | Mô tả |
|---|---|---|---|---|---|
| `id` | `bigint unsigned` | không |  | PK | Khoá chính tự tăng. |
| `title` | `varchar(255)` | không |  |  | Tiêu đề / alt của banner. |
| `image_desktop` | `varchar(255)` | không |  |  | Ảnh cho màn hình lớn. |
| `image_mobile` | `varchar(255)` | có |  |  | Ảnh cho điện thoại; NULL = dùng ảnh desktop. |
| `link` | `varchar(255)` | có |  |  | Đường dẫn khi bấm banner. |
| `position` | `varchar(50)` | không |  |  | Vị trí hiển thị: home_hero \| category_top. |
| `sort_order` | `int unsigned` | không | `0` |  | Thứ tự trong cùng vị trí. |
| `starts_at` | `timestamp` | có |  |  | Bắt đầu hiển thị; NULL = không giới hạn. |
| `ends_at` | `timestamp` | có |  |  | Kết thúc hiển thị; NULL = không giới hạn. |
| `is_active` | `tinyint(1)` | không | `1` |  | Bật/tắt banner. |
| `created_at` | `timestamp` | có |  |  | Thời điểm tạo bản ghi. |
| `updated_at` | `timestamp` | có |  |  | Thời điểm cập nhật gần nhất. |

Index nhiều cột: `banners_position_is_active_index` (position, is_active).

## `posts`

Bài viết tin tức (nội dung Markdown).

| Cột | Kiểu | NULL | Mặc định | Ràng buộc | Mô tả |
|---|---|---|---|---|---|
| `id` | `bigint unsigned` | không |  | PK | Khoá chính tự tăng. |
| `title` | `varchar(255)` | không |  |  | Tiêu đề bài. |
| `slug` | `varchar(255)` | không |  | UNIQUE | Đường dẫn thân thiện, duy nhất (route binding). |
| `excerpt` | `varchar(500)` | có |  |  | Đoạn tóm tắt. |
| `content` | `longtext` | không |  |  | Nội dung Markdown (render bằng Str::markdown, bỏ HTML). |
| `thumbnail` | `varchar(255)` | có |  |  | Ảnh đại diện bài. |
| `status` | `enum('draft','published')` | không | `draft` |  | draft \| published (Enum PostStatus). |
| `published_at` | `timestamp` | có |  |  | Thời điểm đăng; tương lai = hẹn giờ. |
| `author_id` | `bigint unsigned` | có |  | INDEX, FK → `users.id` (SET NULL) | Tác giả (nhân viên/quản trị). |
| `created_at` | `timestamp` | có |  |  | Thời điểm tạo bản ghi. |
| `updated_at` | `timestamp` | có |  |  | Thời điểm cập nhật gần nhất. |

Index nhiều cột: `posts_status_published_at_index` (status, published_at).

## `faqs`

Câu hỏi thường gặp.

| Cột | Kiểu | NULL | Mặc định | Ràng buộc | Mô tả |
|---|---|---|---|---|---|
| `id` | `bigint unsigned` | không |  | PK | Khoá chính tự tăng. |
| `group` | `varchar(100)` | không |  |  | Nhóm câu hỏi (Đặt hàng, Thanh toán, Vận chuyển…). |
| `question` | `varchar(255)` | không |  |  | Câu hỏi. |
| `answer` | `text` | không |  |  | Câu trả lời. |
| `sort_order` | `int unsigned` | không | `0` |  | Thứ tự hiển thị. |
| `is_active` | `tinyint(1)` | không | `1` |  | Hiển thị ngoài trang khách. |
| `created_at` | `timestamp` | có |  |  | Thời điểm tạo bản ghi. |
| `updated_at` | `timestamp` | có |  |  | Thời điểm cập nhật gần nhất. |

Index nhiều cột: `faqs_group_is_active_index` (group, is_active).

## `contact_messages`

Tin nhắn từ form liên hệ.

| Cột | Kiểu | NULL | Mặc định | Ràng buộc | Mô tả |
|---|---|---|---|---|---|
| `id` | `bigint unsigned` | không |  | PK | Khoá chính tự tăng. |
| `name` | `varchar(100)` | không |  |  | Họ tên người gửi. |
| `email` | `varchar(255)` | không |  |  | Email người gửi. |
| `phone` | `varchar(20)` | có |  |  | Số điện thoại người gửi. |
| `subject` | `varchar(255)` | không |  |  | Chủ đề. |
| `message` | `text` | không |  |  | Nội dung. |
| `is_handled` | `tinyint(1)` | không | `0` | INDEX | Đã xử lý chưa. Không gán hàng loạt. |
| `created_at` | `timestamp` | có |  |  | Thời điểm tạo bản ghi. |
| `updated_at` | `timestamp` | có |  |  | Thời điểm cập nhật gần nhất. |

## `settings`

Cài đặt cửa hàng dạng khoá – giá trị (thông tin cửa hàng, phí ship, tài khoản ngân hàng, chat…).

| Cột | Kiểu | NULL | Mặc định | Ràng buộc | Mô tả |
|---|---|---|---|---|---|
| `id` | `bigint unsigned` | không |  | PK | Khoá chính tự tăng. |
| `key` | `varchar(100)` | không |  | UNIQUE | Khoá cài đặt, duy nhất. |
| `value` | `text` | có |  |  | Giá trị (chuỗi/JSON). |
| `created_at` | `timestamp` | có |  |  | Thời điểm tạo bản ghi. |
| `updated_at` | `timestamp` | có |  |  | Thời điểm cập nhật gần nhất. |
