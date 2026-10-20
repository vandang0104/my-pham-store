# Kiến trúc dự án — Web bán mỹ phẩm (Laravel)

> Tài liệu định hướng cho các bước triển khai. Nguồn quy tắc: `CLAUDE.md`. Nguồn sự thật về schema: migrations.
> Trạng thái: 3 điểm ở mục 5 đã theo phương án khuyến nghị.
> **Phạm vi rút gọn (19/10/2026, xem CLAUDE.md):** đã cắt mã giảm giá (CouponService), đánh giá, CRUD banner/tin tức/FAQ/thương hiệu, cài đặt, sổ địa chỉ, tra cứu đơn khách vãng lai, VNPay, xuất CSV, mini-cart, gợi ý tìm kiếm. Thêm: chat trực tuyến tự làm (ChatService, polling), form liên hệ, FAQ chỉ hiển thị. Bảng/enum/model/policy của phần đã cắt **giữ nguyên** (schema không đổi), chỉ không có route/giao diện.

## 1. Cấu trúc thư mục

```
app/
├─ Enums/                     # PHP backed enum, cast thẳng vào Model
│  ├─ UserRole.php            # admin | staff | customer
│  ├─ OrderStatus.php         # pending|confirmed|shipping|delivered|cancelled + canTransitionTo()
│  ├─ CouponType.php          # (đã cắt) percent | fixed
│  ├─ PaymentMethod.php       # cod | bank_transfer (vnpay: đã cắt)
│  ├─ PaymentStatus.php       # unpaid | paid | refunded
│  ├─ ReviewStatus.php        # (đã cắt) pending | approved | rejected
│  ├─ PostStatus.php          # (đã cắt) draft | published
│  └─ SkinType.php            # oily | dry | combination | sensitive | all (chỉ hiển thị, không lọc)
├─ Exceptions/                # lỗi nghiệp vụ tự render (method render(): flash lỗi + redirect; JSON → 422)
│  ├─ CartException.php       # giỏ: hết hàng, vượt kho, SP ngừng bán → quay lại trang trước
│  ├─ OrderException.php      # đặt hàng: giỏ trống/đã thay đổi, thiếu hàng lúc khoá dòng → về giỏ
│  └─ InvalidOrderTransitionException.php   # (S9) đổi trạng thái sai luồng
├─ Http/
│  ├─ Controllers/
│  │  ├─ Controller.php
│  │  ├─ HomeController.php, ProductController.php, CategoryController.php,
│  │  │  CartController.php, CheckoutController.php, FaqController.php,
│  │  │  ContactController.php, ChatController.php
│  │  ├─ Auth/                # LoginController, RegisterController, ForgotPassword/ResetPasswordController
│  │  ├─ Account/             # DashboardController, OrderController, ProfileController
│  │  └─ Admin/               # DashboardController, ProductController, CategoryController,
│  │                          # OrderController, UserController (khách + nhân viên), ReportController,
│  │                          # ChatController, ContactController
│  ├─ Middleware/EnsureUserHasRole.php (alias 'role'), EnsureAccountIsActive.php
│  └─ Requests/               # cùng cấu trúc với Controllers
│     ├─ Cart/AddToCartRequest.php, Checkout/PlaceOrderRequest.php
│     ├─ Auth/…, Account/…
│     └─ Admin/StoreProductRequest.php, UpdateProductRequest.php, UpdateOrderStatusRequest.php…
├─ Listeners/MergeGuestCart.php          # nghe event Login → CartService::mergeGuestCart()
├─ Models/                    # User, Product, Category, Brand, Order, OrderItem, Cart… (+ 2 model chat ở S6)
├─ Policies/                  # ProductPolicy, OrderPolicy, UserPolicy (+ CouponPolicy, ReviewPolicy,
│                             #   SettingPolicy: đã cắt, giữ vì còn test)
├─ Services/
│  ├─ CartService.php
│  ├─ OrderService.php
│  ├─ ReportService.php
│  └─ ChatService.php
├─ Support/                   # Money (format VND), Phone (chuẩn hoá + rule SĐT VN), Shipping (phí ship theo settings)
├─ Mail/OrderPlaced.php       # mail xác nhận đơn (Markdown, driver log)
└─ View/Components/           # component có logic: ProductCard, Price, Breadcrumb, Flash, Icon
resources/
├─ views/
│  ├─ layouts/        app.blade.php, admin.blade.php, auth.blade.php
│  ├─ components/     component Blade (anonymous + view của class component)
│  ├─ pages/          home (+ home/*), products/{index,show}, categories/show, cart, checkout/{index,success},
│  │                  faq, contact, auth/{login,register,forgot,reset}
│  ├─ account/        dashboard, orders/{index,show}, profile
│  ├─ admin/          dashboard, products/…, categories/…, orders/…, users/…, reports/…, chats/…, contacts/…
│  └─ emails/         order-placed (mail xác nhận đơn, driver log)
├─ scss/              app.scss (Bootstrap tuỳ biến; từ 19/10 import đủ component chuẩn cho trang mới), _tokens.scss
└─ js/                app.js (header, flash; fetch cho giỏ/chat), admin.js (Chart.js)
lang/vi/              messages.php, order.php, cart.php, validation.php, auth.php, passwords.php, pagination.php
routes/               web.php (khách + tài khoản + auth), admin.php
tests/                Feature/{Web,Admin,Auth}, Unit/Services
docs/                 architecture.md, erd.mmd
```

Ghi chú:
- **Controller khách nằm ở namespace gốc `App\Http\Controllers`** (đúng CLAUDE.md), không tạo thư mục `Web/`; nhóm tài khoản tách `Account/`, quản trị tách `Admin/`.
- Không có thư mục Repository — Eloquent đủ dùng, thêm lớp repository chỉ là bọc thêm.
- Không có logic trong Blade: dữ liệu hiển thị phức tạp (giá, badge trạng thái) đi qua View Component.

## 2. Quy ước route

**Chung:** URL công khai tiếng Việt không dấu; route name dạng `nhom.chuc-nang`; bind model theo `slug` (sản phẩm, danh mục, bài viết) và theo `code` (đơn hàng — không lộ id tăng dần). Giữ tên route chuẩn của Laravel cho auth (`login`, `register`, `password.request`, `password.reset`) để middleware `auth` và mail reset tự hoạt động.

### routes/web.php
| Method | URL | Route name |
|---|---|---|
| GET | `/` | `home` |
| GET | `/san-pham` (tìm + lọc, xem chú thích dưới bảng) | `products.index` |
| GET | `/san-pham/{product:slug}` (ẩn / xoá mềm → 404) | `products.show` |
| GET | `/danh-muc/{category:slug}` (cùng view danh sách, danh mục cha gồm cả con) | `categories.show` |
| GET | `/thuong-hieu/{brand:slug}` (cùng view danh sách; thương hiệu chỉ từ seed, không CRUD) | `brands.show` |
| GET | `/gio-hang` | `cart.index` |
| POST | `/gio-hang` (`product_id`, `quantity`) | `cart.store` |
| PATCH / DELETE | `/gio-hang/{product:id}` (`withTrashed`) | `cart.update` / `cart.destroy` |
| GET / POST | `/thanh-toan` (giỏ trống → về giỏ; token một lần chống gửi trùng) | `checkout.create` / `checkout.store` |
| GET | `/dat-hang-thanh-cong/{order:code}` (chỉ chủ đơn hoặc phiên vừa đặt; còn lại 404) | `checkout.success` |
| GET | `/cau-hoi-thuong-gap` | `faq.index` |
| GET / POST | `/lien-he` | `contact.create` / `contact.store` |
| GET / POST | `/chat/tin-nhan` (polling, JSON) | `chat.messages` / `chat.send` |
| GET/POST | `/dang-nhap`, `/dang-ky`, `/dang-xuat`, `/quen-mat-khau`, `/dat-lai-mat-khau/{token}` | `login`, `register`, `logout`, `password.request`, `password.reset` |
| GET | `/tai-khoan` (auth; tổng quan: lời chào + 3 đơn gần nhất) | `account.dashboard` |
| GET | `/tai-khoan/don-hang` (`?trang-thai=`), `/tai-khoan/don-hang/{order:code}` (đơn người khác → 404) | `account.orders.index`, `account.orders.show` |
| POST | `/tai-khoan/don-hang/{order:code}/huy` | `account.orders.cancel` |
| GET / PUT | `/tai-khoan/thong-tin` | `account.profile.edit` / `account.profile.update` |
| GET / PUT | `/tai-khoan/mat-khau` | `account.password.edit` / `account.password.update` |

**Tham số lọc danh sách (chốt ở S1, `ProductFilterRequest`):** `q` (từ khoá: tên, SKU, mô tả, tên thương hiệu — LIKE), `danh-muc` (slug, danh mục cha gồm cả con), `thuong-hieu[]` (một hoặc nhiều slug; `?thuong-hieu=slug` cũng nhận), `gia-tu`, `gia-den` (số nguyên ≥ 0, so với **giá hiện hành**; giá đến < giá từ thì bỏ giá đến), `khuyen-mai=1` (đang giảm giá), `page`. Tham số sai bị bỏ qua (không redirect, không 500). Sắp xếp cố định: mới nhất trước; 12 SP/trang, `->withQueryString()`.
URL chat ở trên là dự kiến; S6 chốt tên cuối cùng. Đã cắt: đánh giá, mã giảm giá, `/tra-cuu-don-hang`, `/tin-tuc`, lọc loại da, sắp xếp khác.

Các endpoint giỏ hàng dùng chung cho form thường và AJAX: controller trả JSON khi `$request->expectsJson()`, ngược lại redirect + flash.

### routes/admin.php
Đăng ký trong `bootstrap/app.php` (`withRouting(then: …)`) với `prefix('admin')`, `name('admin.')`, middleware `['web','auth','role:admin,staff']`. **URL admin dùng tiếng Anh** (không công khai, khớp tên route `admin.products.index`).
- `/admin/login` (khách chưa đăng nhập, ngoài nhóm `auth`) — trang đăng nhập quản trị (S7).
- `dashboard`: `/admin` (staff chỉ thấy số liệu vận hành).
- `Route::resource`: `products` (1 ảnh/SP), `categories`.
- `orders`: index, show, `PATCH orders/{order}/status` → `admin.orders.status`.
- `chats` (hội thoại + trả lời), `contacts` (tin nhắn liên hệ, đánh dấu đã xử lý).
- Nhóm thêm `role:admin`: `users` (tài khoản khách + nhân viên gộp một màn: đổi vai trò, khoá/mở khoá), `reports` (doanh thu theo ngày/tháng + top SP bán chạy, không CSV) — staff không vào được (quy tắc 9). Ma trận chi tiết: `docs/permissions.md`.

**Phân quyền 2 tầng:** middleware `role` chặn theo nhóm route; Policy chặn theo đối tượng (vd `UserPolicy::delete` cấm xoá/hạ quyền admin cuối cùng; `OrderPolicy::cancel` khách chỉ huỷ đơn của mình khi pending/confirmed). FormRequest gọi policy trong `authorize()`.

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
- **CartService** (đã làm ở S2) — `add()`, `updateQuantity()`, `remove()`, `clear()`, `items()` (trả `CartLine`: sản phẩm + số lượng + giá hiện hành + thành tiền; tự bỏ dòng SP ẩn/xoá/hết hàng, hạ số lượng vượt kho và ghi `notices()`), `count()`, `subtotal()`, `mergeGuestCart(User)`. Khách: session `cart` = `[product_id => số lượng]`; user: `carts`/`cart_items`; nhánh nằm trong private method. Kiểm tra tồn kho “mềm” (không khoá — khoá dòng ở OrderService khi đặt). Lỗi nghiệp vụ ném `CartException` (tự render: flash lỗi + quay lại). Gộp giỏ qua listener `MergeGuestCart` nghe event `Login` — event này bắn cả khi đăng ký (`Auth::login`), cộng dồn số lượng, cắt theo tồn kho, bỏ SP ngừng bán.
- **OrderService** (đã làm ở S3) — `placeOrder(array $data, ?User)`: đọc giỏ (giỏ vừa bị chuẩn hoá → `OrderException` để khách xem lại; trống → lỗi) → `DB::transaction` → `lockForUpdate` các product **sắp theo id** (tránh deadlock) → kiểm lại active/tồn kho (thiếu → ném lỗi liệt kê SP thiếu, rollback) → tạo `orders` (mã `MP-YYYYMMDD-XXXX`, pending, unpaid, `coupon_id` null, `discount` 0, `total = subtotal + shipping_fee` theo settings) + `order_items` **snapshot** tên/sku/giá hiện hành + `order_status_histories` (null → pending) → trừ `stock`, tăng `sold_count` → commit → xoá giỏ → gửi mail `OrderPlaced` (lỗi gửi mail chỉ `report`, không hỏng đơn). Chống gửi trùng ở `CheckoutController`: token một lần trong session + `Cache::add(token)` nguyên tử.
- **Setting** — `Setting::get(key)` / `Setting::integer(key)`: cả bảng cache một khoá (`settings.all`), xoá khi lưu/xoá; thiếu khoá dùng `Setting::DEFAULTS`. Khoá: `shipping_fee`, `free_shipping_from`, `bank_name`, `bank_account_number`, `bank_account_name`, `store_hotline` (SettingSeeder).
- **OrderService::cancel(Order, ?User $by, ?string $note)** (đã làm ở S4, admin dùng lại ở S9): transaction → khoá dòng đơn (đọc lại trạng thái) → sai luồng (`OrderStatus::canTransitionTo`) thì ném `InvalidOrderTransitionException` → khoá sản phẩm theo id tăng dần (kể cả đã xoá mềm) → hoàn `stock`, trừ `sold_count` → `status = cancelled`, `cancelled_at` → ghi `order_status_histories` (from → cancelled, `changed_by`). `transition(order, OrderStatus)` cho các bước xác nhận/giao làm ở S9.
- Exception nghiệp vụ (`CartException`, `OrderException`, `InvalidOrderTransitionException`) cài `ShouldntReport`: chỉ báo cho người dùng, không ghi vào log lỗi.
- **ReportService** — doanh thu theo ngày/tháng (chỉ đơn delivered), top sản phẩm bán chạy; số liệu vận hành cho dashboard (đơn mới, đơn chờ xử lý, sắp hết hàng); trả mảng `labels/datasets` sẵn cho Chart.js.
- **ChatService** — tạo/lấy hội thoại của khách (user hoặc session khách vãng lai), gửi tin, lấy tin mới sau một id (polling); nhân viên trả lời từ admin. Schema 2 bảng mới chốt ở S6.
- ~~CouponService~~ — đã cắt (mã giảm giá).

Phụ thuộc: `OrderService → CartService`; `ReportService`, `ChatService` độc lập. Inject qua constructor (container tự resolve, không cần đăng ký binding).

## 4. Package tối thiểu

**Composer**
| Package | Lý do |
|---|---|
| `laravel/framework` | lõi (bản ổn định mới nhất khi cài) |
| `laravel/pint` (dev, có sẵn skeleton) | định dạng code theo CLAUDE.md |
| `pestphp/pest`, `pestphp/pest-plugin-laravel` (dev) | kiểm thử; cú pháp gọn hơn PHPUnit, chọn khi tạo project |

Cố ý **không** cài: Breeze/Jetstream/Fortify (tự viết auth, xem quyết định 1), spatie/laravel-permission (chỉ 3 role → cột `role` + Enum + Policy), slug package (`Str::slug` đã chuyển tiếng Việt → ASCII, sẽ có test xác nhận), Excel package (xuất CSV đã cắt), HTML purifier/WYSIWYG (xem quyết định 3), debugbar (dùng `Model::preventLazyLoading()` ở môi trường dev).

**npm**
| Package | Lý do |
|---|---|
| `vite`, `laravel-vite-plugin` | có sẵn skeleton, build asset |
| `bootstrap`, `@popperjs/core` | UI theo CLAUDE.md; Popper cho dropdown/tooltip |
| `sass` | biên dịch Sass tuỳ biến Bootstrap |
| `chart.js` | biểu đồ thống kê admin — **chưa cài**, thêm ở S10 |

Không dùng `alpinejs` (JS thuần + `fetch` đủ cho giỏ/chat; mini-cart đã cắt).

Gỡ khỏi skeleton: `tailwindcss`, `@tailwindcss/vite` (skeleton mặc định có), `axios` (dùng `fetch` native).

---

## 5. Ba điểm cần quyết định

1. **Xác thực: tự viết hay dùng Fortify?**
   → **Khuyến nghị: tự viết** (~4 controller + FormRequest) bằng `Auth`, `Password` broker, `RateLimiter` có sẵn. Lý do: 0 package, URL tiếng Việt (`/dang-nhap`) tự nhiên, đúng khuôn controller mỏng, dễ giải thích khi bảo vệ đồ án. Fortify buộc tắt route mặc định và vẫn phải tự làm view → lợi ích ít. Không làm xác minh email (có thể thêm sau).

2. **Phương thức thanh toán**
   → **Khuyến nghị: COD + chuyển khoản thủ công** (hiển thị STK/QR tĩnh, admin xác nhận đã nhận tiền khi chuyển `pending → confirmed`). Không cần package hay tích hợp ngoài, OrderService đơn giản. VNPay: đã cắt (phạm vi rút gọn 19/10).

3. **Định dạng nội dung tin tức / mô tả sản phẩm**
   → **Khuyến nghị: Markdown**, render bằng `Str::markdown(..., ['html_input' => 'strip', 'allow_unsafe_links' => false])` (league/commonmark đã có sẵn trong framework) → an toàn XSS, 0 package. Phương án WYSIWYG (TinyMCE/Trix) cần thêm `mews/purifier` để lọc HTML do staff nhập — nhiều phụ thuộc hơn.

(Ghi chú: yêu cầu ghi “Controllers chia Web/Admin” nhưng CLAUDE.md quy định controller khách ở `App\Http\Controllers` gốc — đề xuất theo CLAUDE.md. Nếu bạn muốn thư mục `Web/` riêng thì cập nhật luôn CLAUDE.md.)

