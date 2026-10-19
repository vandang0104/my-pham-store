# Kiến trúc dự án — Web bán mỹ phẩm (Laravel)

> Tài liệu định hướng cho các bước triển khai. Nguồn quy tắc: `CLAUDE.md`. Nguồn sự thật về schema: migrations.
> Trạng thái: **đề xuất** — 3 điểm ở mục 5 đang chờ chốt (đang theo phương án khuyến nghị).

## 1. Cấu trúc thư mục

```
app/
├─ Enums/                     # PHP backed enum, cast thẳng vào Model
│  ├─ UserRole.php            # admin | staff | customer
│  ├─ OrderStatus.php         # pending|confirmed|shipping|delivered|cancelled + canTransitionTo()
│  ├─ CouponType.php          # percent | fixed
│  └─ PaymentMethod.php
├─ Exceptions/                # lỗi nghiệp vụ, render tập trung ở bootstrap/app.php
│  ├─ OutOfStockException.php
│  ├─ InvalidCouponException.php
│  └─ InvalidOrderTransitionException.php
├─ Http/
│  ├─ Controllers/
│  │  ├─ Controller.php
│  │  ├─ HomeController.php, ProductController.php, CategoryController.php,
│  │  │  BrandController.php, CartController.php, CheckoutController.php,
│  │  │  OrderTrackingController.php, PostController.php, ReviewController.php
│  │  ├─ Auth/                # LoginController, RegisterController, PasswordResetController…
│  │  ├─ Account/             # DashboardController, OrderController, ProfileController
│  │  └─ Admin/               # DashboardController, ProductController, CategoryController,
│  │                          # BrandController, OrderController, CouponController,
│  │                          # ReviewController, PostController, CustomerController,
│  │                          # StaffController, SettingController, ReportController
│  ├─ Middleware/EnsureUserHasRole.php   # alias 'role'
│  └─ Requests/               # cùng cấu trúc với Controllers
│     ├─ Cart/AddToCartRequest.php, Checkout/PlaceOrderRequest.php, StoreReviewRequest.php
│     ├─ Auth/…, Account/…
│     └─ Admin/StoreProductRequest.php, UpdateProductRequest.php, UpdateOrderStatusRequest.php…
├─ Listeners/MergeGuestCart.php          # nghe event Login → CartService::mergeGuestCart()
├─ Models/                    # User, Product, Category, Brand, Order, OrderItem, Coupon, Review, Post, Cart…
├─ Policies/                  # ProductPolicy, OrderPolicy, ReviewPolicy, UserPolicy, CouponPolicy, SettingPolicy
├─ Services/
│  ├─ CartService.php
│  ├─ CouponService.php
│  ├─ OrderService.php
│  └─ ReportService.php
├─ Support/Money.php          # format VND (number_format, không phụ thuộc ext-intl)
└─ View/Components/           # component có logic: ProductCard, Price, Rating, Breadcrumb,
                              # Admin/StatCard, Admin/StatusBadge
resources/
├─ views/
│  ├─ layouts/        app.blade.php, admin.blade.php, auth.blade.php
│  ├─ components/     component Blade (anonymous + view của class component), components/admin/…
│  ├─ pages/          home, products/{index,show}, categories/show, cart, checkout/{index,success},
│  │                  orders/track, posts/{index,show}, auth/{login,register,forgot,reset}
│  ├─ account/        dashboard, orders/{index,show}, profile
│  ├─ admin/          dashboard, products/…, orders/…, coupons/…, reviews/…, reports/…, settings/…
│  └─ emails/         order-placed (mail xác nhận đơn)
├─ scss/              app.scss (biến Bootstrap tuỳ biến, chỉ import module cần), admin.scss, _tokens.scss
└─ js/                app.js (Alpine + fetch giỏ hàng), admin.js (Chart.js)
lang/vi/              messages.php, order.php, cart.php, coupon.php, validation.php, auth.php, passwords.php, pagination.php
routes/               web.php (khách + tài khoản + auth), admin.php
tests/                Feature/{Web,Admin,Auth}, Unit/Services
docs/                 architecture.md, erd.mmd
```

Ghi chú:
- **Controller khách nằm ở namespace gốc `App\Http\Controllers`** (đúng CLAUDE.md), không tạo thư mục `Web/`; nhóm tài khoản tách `Account/`, quản trị tách `Admin/`.
- Không có thư mục Repository — Eloquent đủ dùng, thêm lớp repository chỉ là bọc thêm.
- Không có logic trong Blade: dữ liệu hiển thị phức tạp (giá, badge trạng thái, sao đánh giá) đi qua View Component.

## 2. Quy ước route

**Chung:** URL công khai tiếng Việt không dấu; route name dạng `nhom.chuc-nang`; bind model theo `slug` (sản phẩm, danh mục, bài viết) và theo `code` (đơn hàng — không lộ id tăng dần). Giữ tên route chuẩn của Laravel cho auth (`login`, `register`, `password.request`, `password.reset`) để middleware `auth` và mail reset tự hoạt động.

### routes/web.php
| Method | URL | Route name |
|---|---|---|
| GET | `/` | `home` |
| GET | `/san-pham` (lọc/tìm/sắp xếp bằng query `?q=&danh-muc=&gia=&sap-xep=`) | `products.index` |
| GET | `/san-pham/{product:slug}` | `products.show` |
| GET | `/danh-muc/{category:slug}` | `categories.show` |
| GET | `/thuong-hieu/{brand:slug}` | `brands.show` |
| POST | `/san-pham/{product:slug}/danh-gia` (auth) | `reviews.store` |
| GET | `/gio-hang` | `cart.index` |
| POST | `/gio-hang` | `cart.add` |
| PATCH / DELETE | `/gio-hang/{item}` | `cart.update` / `cart.remove` |
| POST / DELETE | `/gio-hang/ma-giam-gia` | `cart.coupon.apply` / `cart.coupon.remove` |
| GET / POST | `/thanh-toan` | `checkout.index` / `checkout.store` |
| GET | `/thanh-toan/thanh-cong/{order:code}` (signed URL cho khách vãng lai) | `checkout.success` |
| GET | `/tra-cuu-don-hang` (mã đơn + SĐT) | `orders.track` |
| GET | `/tin-tuc`, `/tin-tuc/{post:slug}` | `posts.index`, `posts.show` |
| GET/POST | `/dang-nhap`, `/dang-ky`, `/dang-xuat`, `/quen-mat-khau`, `/dat-lai-mat-khau/{token}` | `login`, `register`, `logout`, `password.request`, `password.reset` |
| GET | `/tai-khoan` (auth) | `account.dashboard` |
| GET | `/tai-khoan/don-hang`, `/tai-khoan/don-hang/{order:code}` | `account.orders.index`, `account.orders.show` |
| POST | `/tai-khoan/don-hang/{order:code}/huy` | `account.orders.cancel` |
| GET / PUT | `/tai-khoan/thong-tin`, PUT `/tai-khoan/mat-khau` | `account.profile.edit` / `.update`, `account.password.update` |

Các endpoint giỏ hàng dùng chung cho form thường và AJAX: controller trả JSON khi `$request->expectsJson()`, ngược lại redirect + flash.

### routes/admin.php
Đăng ký trong `bootstrap/app.php` (`withRouting(then: …)`) với `prefix('admin')`, `name('admin.')`, middleware `['web','auth','role:admin,staff']`. **URL admin dùng tiếng Anh** (không công khai, khớp tên route `admin.products.index`).
- `Route::resource`: `products`, `categories`, `brands`, `coupons`, `posts`.
- `orders`: index, show, `PATCH orders/{order}/status` → `admin.orders.status`.
- `reviews`: index, `PATCH reviews/{review}/approve`, destroy.
- `customers`: index, show. `reports`: index (+ export CSV). `dashboard`: `/admin`.
- Nhóm thêm `role:admin`: `staff` (resource), `settings` (edit/update) — staff không vào được (quy tắc 9).

**Phân quyền 2 tầng:** middleware `role` chặn theo nhóm route; Policy chặn theo đối tượng (vd `UserPolicy::delete` cấm xoá/hạ quyền admin cuối cùng; `OrderPolicy::cancel` khách chỉ huỷ đơn của mình khi pending/confirmed; `ReviewPolicy::create` chỉ khi đã có đơn delivered chứa SP và chưa đánh giá). FormRequest gọi policy trong `authorize()`.

## 3. Tách lớp

**Luồng:** `Route → Middleware → FormRequest (validate + authorize) → Controller (mỏng) → Service (nghiệp vụ, transaction) → Model/Eloquent`.

| Lớp | Được làm | Không được làm |
|---|---|---|
| FormRequest | rule, message tiếng Việt (lang/vi), `authorize()` gọi policy, chuẩn hoá input (`prepareForValidation`) | truy vấn nghiệp vụ |
| Controller | gọi 1 service method với `$request->validated()`, trả view / redirect / JSON; mỗi action ~5–15 dòng | tính tiền, trừ kho, `DB::transaction` |
| Service | toàn bộ quy tắc nghiệp vụ, transaction, khoá dòng, ném exception nghiệp vụ | đọc `request()`, trả view/redirect |
| Model | quan hệ, cast (Enum), scope (`active()`, `approved()`), accessor đơn giản `current_price` (quy tắc 1) | gọi service |
| View Component | chuẩn bị dữ liệu hiển thị | truy vấn DB |

Exception nghiệp vụ được render tập trung trong `bootstrap/app.php`: request JSON → 422 + message; request thường → `back()->withErrors()`. Không try/catch trong controller.

**Trách nhiệm từng Service:**
- **CartService** — `items()`, `add()`, `update()`, `remove()`, `clear()`, `subtotal()`, `mergeGuestCart(User)`. Khách: lưu session; user: lưu DB; nhánh nằm trong private method. Kiểm tra tồn kho “mềm” (không khoá). Gộp giỏ khi đăng nhập qua listener `MergeGuestCart` (event `Login`), cộng dồn số lượng, cắt theo tồn kho.
- **CouponService** — `validate(code, subtotal, ?user)` kiểm hiệu lực thời gian, `min_order`, `usage_limit` → ném `InvalidCouponException` kèm key lang; `discountFor(coupon, subtotal)` (percent có trần `max_discount`, fixed không vượt subtotal); `consume()` / `release()` tăng/giảm lượt dùng (gọi bên trong transaction của OrderService, có `lockForUpdate`).
- **OrderService** — `place(CheckoutData, ?User)`: trong `DB::transaction` → `lockForUpdate` các product **sắp theo id** (tránh deadlock) → kiểm & trừ kho → validate lại + `consume` coupon → tạo `orders` + `order_items` **snapshot** tên/sku/giá → sinh mã đơn → xoá giỏ → gửi mail `afterCommit`. `transition(order, OrderStatus)` dùng bảng chuyển trạng thái trong enum; `cancel(order)` chỉ từ pending/confirmed, hoàn kho + `release` coupon trong cùng transaction.
- **ReportService** — doanh thu theo ngày/tháng (chỉ đơn delivered), số đơn theo trạng thái, top sản phẩm bán chạy, sản phẩm sắp hết hàng; trả mảng `labels/datasets` sẵn cho Chart.js; cache ngắn (vd 10 phút).

Phụ thuộc: `OrderService → CartService, CouponService`; `ReportService` độc lập. Inject qua constructor (container tự resolve, không cần đăng ký binding).

## 4. Package tối thiểu

**Composer**
| Package | Lý do |
|---|---|
| `laravel/framework` | lõi (bản ổn định mới nhất khi cài) |
| `laravel/pint` (dev, có sẵn skeleton) | định dạng code theo CLAUDE.md |
| `pestphp/pest`, `pestphp/pest-plugin-laravel` (dev) | kiểm thử; cú pháp gọn hơn PHPUnit, chọn khi tạo project |

Cố ý **không** cài: Breeze/Jetstream/Fortify (tự viết auth, xem quyết định 1), spatie/laravel-permission (chỉ 3 role → cột `role` + Enum + Policy), slug package (`Str::slug` đã chuyển tiếng Việt → ASCII, sẽ có test xác nhận), Excel package (CSV bằng `fputcsv` + streamed response), HTML purifier/WYSIWYG (xem quyết định 3), debugbar (dùng `Model::preventLazyLoading()` ở môi trường dev).

**npm**
| Package | Lý do |
|---|---|
| `vite`, `laravel-vite-plugin` | có sẵn skeleton, build asset |
| `bootstrap`, `@popperjs/core` | UI theo CLAUDE.md; Popper cho dropdown/tooltip |
| `sass` | biên dịch Sass tuỳ biến Bootstrap |
| `alpinejs` | tương tác nhẹ (giỏ hàng AJAX, mini-cart, toggle) |
| `chart.js` | biểu đồ thống kê admin |

Gỡ khỏi skeleton: `tailwindcss`, `@tailwindcss/vite` (skeleton mặc định có), `axios` (dùng `fetch` native).

---

## 5. Ba điểm cần quyết định

1. **Xác thực: tự viết hay dùng Fortify?**
   → **Khuyến nghị: tự viết** (~4 controller + FormRequest) bằng `Auth`, `Password` broker, `RateLimiter` có sẵn. Lý do: 0 package, URL tiếng Việt (`/dang-nhap`) tự nhiên, đúng khuôn controller mỏng, dễ giải thích khi bảo vệ đồ án. Fortify buộc tắt route mặc định và vẫn phải tự làm view → lợi ích ít. Không làm xác minh email (có thể thêm sau).

2. **Phương thức thanh toán**
   → **Khuyến nghị: COD + chuyển khoản thủ công** (hiển thị STK/QR tĩnh, admin xác nhận đã nhận tiền khi chuyển `pending → confirmed`). Không cần package hay tích hợp ngoài, OrderService đơn giản. VNPay sandbox để làm phần mở rộng nếu còn thời gian (chỉ thêm 1 cổng, không ảnh hưởng kiến trúc vì đã có enum `PaymentMethod`).

3. **Định dạng nội dung tin tức / mô tả sản phẩm**
   → **Khuyến nghị: Markdown**, render bằng `Str::markdown(..., ['html_input' => 'strip', 'allow_unsafe_links' => false])` (league/commonmark đã có sẵn trong framework) → an toàn XSS, 0 package. Phương án WYSIWYG (TinyMCE/Trix) cần thêm `mews/purifier` để lọc HTML do staff nhập — nhiều phụ thuộc hơn.

(Ghi chú: yêu cầu ghi “Controllers chia Web/Admin” nhưng CLAUDE.md quy định controller khách ở `App\Http\Controllers` gốc — đề xuất theo CLAUDE.md. Nếu bạn muốn thư mục `Web/` riêng thì cập nhật luôn CLAUDE.md.)

