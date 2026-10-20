# CHƯƠNG 4. XÂY DỰNG HỆ THỐNG

Chương này trình bày cách hiện thực các thiết kế ở Chương 3 bằng Laravel. Mỗi mục gồm cách làm, một đoạn mã trích từ dự án (ghi đường dẫn tệp; dấu `// …` đánh dấu phần lược bớt) và vị trí ảnh minh hoạ. Danh sách ảnh cần chụp nằm ở cuối chương (mục 4.12).

## 4.1. Cài đặt môi trường và khởi tạo dự án

**Môi trường phát triển:** Windows 11; PHP 8.4 (bật `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`); Composer 2; Node.js 20+ và npm; MySQL 8 chạy trong container Docker (`compose.yaml`, cơ sở dữ liệu `mypham_shop`, bảng mã `utf8mb4`); Git; trình soạn thảo Visual Studio Code.

**Các bước cài đặt** (theo `README.md`):

```bash
git clone <url-repo> mypham-shop && cd mypham-shop
composer install                 # cài thư viện PHP
cp .env.example .env             # cấu hình: DB_*, MAIL_MAILER=log
php artisan key:generate
docker compose up -d             # khởi động MySQL 8
php artisan migrate --seed       # tạo bảng + dữ liệu mẫu
php artisan storage:link         # ảnh tải lên hiển thị qua /storage
npm install && npm run build     # biên dịch Sass/JS bằng Vite
php artisan serve                # http://localhost:8000
```

Dự án được khởi tạo bằng `composer create-project laravel/laravel`, không dùng starter kit (Breeze/Jetstream); chức năng xác thực được tự viết (mục 4.7). Ngoài Laravel, dự án chỉ thêm Bootstrap, Sass và Chart.js (phía giao diện), Pest và Pint (phía phát triển).

[HÌNH 4.1: Cấu trúc thư mục dự án trong VS Code (app/, database/, resources/, routes/, tests/)]

[HÌNH 4.2: Terminal chạy `php artisan migrate --seed` thành công]

## 4.2. Cơ sở dữ liệu: migration và seeder

Lược đồ CSDL được định nghĩa hoàn toàn bằng **migration** (25 tệp trong `database/migrations`: 3 tệp mặc định của Laravel, 22 tệp của dự án), nên có thể dựng lại trên máy bất kỳ bằng một lệnh. Mỗi thay đổi lược đồ là một migration mới (ví dụ `2026_10_19_120100_add_status_created_at_index_to_orders_table.php` thêm chỉ mục cho thống kê, `2026_10_20_100000_create_chat_tables.php` thêm hai bảng chat).

Đoạn trích migration bảng `products`:

```php
// database/migrations/2026_10_19_100300_create_products_table.php (trích)
Schema::create('products', function (Blueprint $table) {
    $table->id();
    // restrict: không xoá được danh mục/thương hiệu khi còn sản phẩm (kể cả sản phẩm đã xoá mềm)
    $table->foreignId('category_id')->constrained()->restrictOnDelete();
    $table->foreignId('brand_id')->constrained()->restrictOnDelete();
    $table->string('name', 200);
    $table->string('slug', 220)->unique();
    $table->string('sku', 50)->unique();
    // Tiền VND: số nguyên không dấu, không phần thập phân
    $table->unsignedInteger('price')->index();
    $table->unsignedInteger('sale_price')->nullable();
    $table->unsignedInteger('stock')->default(0);
    $table->boolean('is_active')->default(true)->index();
    $table->unsignedInteger('sold_count')->default(0);
    $table->timestamps();
    $table->softDeletes();
});
```

Khoá ngoại khai báo hành vi khi xoá ngay trong migration (`restrictOnDelete`, `cascadeOnDelete`, `nullOnDelete`), hiện thực các ràng buộc đã thiết kế ở mục 3.7. `softDeletes()` thêm cột `deleted_at` cho xoá mềm.

**Dữ liệu mẫu** do các seeder sinh ra, gọi theo thứ tự trong `DatabaseSeeder`: `SettingSeeder` → `UserSeeder` → `CatalogSeeder` → `ContentSeeder` → `CouponSeeder` → `OrderSeeder` → `ReviewSeeder`. Faker được cố định hạt giống (`fake()->seed(20261019)`) để mỗi lần seed cho cùng một bộ dữ liệu. Kết quả: 1 admin, 2 nhân viên, 20 khách hàng; 8 danh mục cha và 26 danh mục con; 10 thương hiệu hư cấu; 48 sản phẩm; 4 banner; 8 câu hỏi thường gặp; 15 đơn hàng đủ các trạng thái. Model có **factory** tương ứng (`database/factories`) dùng cho kiểm thử.

[HÌNH 4.3: Bảng `products` trong công cụ quản lý MySQL (vd. phpMyAdmin / DBeaver / MySQL Workbench) sau khi seed]

## 4.3. Model

Mỗi bảng nghiệp vụ có một model Eloquent trong `app/Models`. Model khai báo ba loại thành phần chính:

- **Quan hệ:** `belongsTo`, `hasMany`, `hasOne` (vd. `Product::category()`, `Product::images()`, `Order::items()`, `User::cart()`).
- **Scope truy vấn** (thuộc tính `#[Scope]`): `active()`, `featured()`, `onSale()`, `priceBetween()`, `search()`, `filter()`, `inCategory()` — giúp controller viết truy vấn ngắn và nhất quán.
- **Accessor:** giá trị suy ra từ cột, đặc biệt là **giá hiện hành** (quy tắc 1).

```php
// app/Models/Product.php (trích)
/** Giá hiện hành: sale_price nếu có và nhỏ hơn price, ngược lại price (quy tắc 1) */
protected function currentPrice(): Attribute
{
    return Attribute::get(fn (): int => $this->is_on_sale ? $this->sale_price : $this->price);
}

protected function isOnSale(): Attribute
{
    return Attribute::get(fn (): bool => $this->sale_price !== null && $this->sale_price < $this->price);
}

/** Phần trăm giảm, làm tròn; 0 nếu không giảm giá */
protected function discountPercent(): Attribute
{
    return Attribute::get(fn (): int => $this->is_on_sale && $this->price > 0
        ? (int) round(($this->price - $this->sale_price) * 100 / $this->price)
        : 0);
}
```

Mọi nơi cần giá (thẻ sản phẩm, giỏ hàng, đặt hàng) đều đọc `$product->current_price`, nên quy tắc giá chỉ nằm ở một chỗ. Trạng thái đơn, phương thức/tình trạng thanh toán, vai trò và loại da được khai báo bằng **enum PHP** (`OrderStatus`, `PaymentMethod`, `PaymentStatus`, `UserRole`, `SkinType`) và ép kiểu qua `casts()`, tránh so sánh chuỗi rời rạc. Để phát hiện truy vấn N+1, `AppServiceProvider` bật `Model::preventLazyLoading()` ngoài môi trường production.

## 4.4. Controller, FormRequest và Service

Controller được giữ **mỏng**: nhận request đã được **FormRequest** kiểm tra (quy tắc dữ liệu, thông báo lỗi tiếng Việt, kiểm quyền trong `authorize()`), gọi **Service** chứa nghiệp vụ, rồi trả về view hoặc chuyển hướng. Ví dụ thêm sản phẩm vào giỏ:

```php
// app/Http/Controllers/CartController.php (trích)
public function store(AddToCartRequest $request): RedirectResponse
{
    // withTrashed: sản phẩm đã xoá mềm báo "không còn bán" (CartService) thay vì trang 404
    $product = Product::withTrashed()->findOrFail($request->validated('product_id'));
    $this->cart->add($product, $request->quantity());

    return back()->with('status', __('cart.added', ['name' => $product->name]));
}
```

Bốn service trong `app/Services` gom toàn bộ nghiệp vụ: `CartService` (giỏ hàng), `OrderService` (đặt hàng, đổi trạng thái, huỷ, thanh toán), `ReportService` (thống kê), `ChatService` (chat). Service được Laravel tự tiêm qua constructor (service container). Lỗi nghiệp vụ được ném thành ngoại lệ riêng (`CartException`, `OrderException`, `InvalidOrderTransitionException`) tự hiển thị thành thông báo cho người dùng và không ghi vào log lỗi hệ thống (giao diện `ShouldntReport`), nên controller không cần khối `try/catch`.

## 4.5. Định tuyến (Route)

Route chia làm hai tệp:

- `routes/web.php` — phía khách, URL tiếng Việt không dấu: `/san-pham`, `/danh-muc/{slug}`, `/gio-hang`, `/thanh-toan`, `/tai-khoan/...`, `/lien-he`, `/chat/tin-nhan`.
- `routes/admin.php` — khu quản trị, nạp trong `bootstrap/app.php` với tiền tố `/admin`, tên `admin.` và middleware `web`, `auth`, `role:admin,staff`.

```php
// bootstrap/app.php (trích)
then: function () {
    // Khu quản trị: chỉ admin và staff; nhóm role:admin khai báo bên trong routes/admin.php
    Route::middleware(['web', 'auth', 'role:admin,staff'])
        ->prefix('admin')
        ->name('admin.')
        ->group(base_path('routes/admin.php'));
},
```

```php
// routes/admin.php (trích)
Route::patch('orders/{order:code}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
// Chỉ admin: quản lý tài khoản và thống kê doanh thu (quy tắc 9)
Route::middleware('role:admin')->group(function () {
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('reports', ReportController::class)->name('reports.index');
});
```

**Route model binding** tự tìm bản ghi theo khoá chỉ định trong tham số: sản phẩm phía khách theo slug (`/san-pham/{product:slug}`), đơn hàng theo mã (`/admin/orders/{order:code}`, `/tai-khoan/don-hang/{order:code}`), sản phẩm ở quản trị theo id (`{product:id}`, kèm `->withTrashed()` để sửa/khôi phục được sản phẩm đã xoá mềm). Không tìm thấy thì trả 404. Tên route theo dạng `nhóm.chức-năng` (`cart.store`, `admin.orders.status`), view và controller chỉ tham chiếu qua `route('…')`.

## 4.6. Giao diện (View)

Giao diện dùng **Blade** với ba layout trong `resources/views/layouts`: `app` (trang khách: header, thông báo flash, nội dung, footer, nút chat), `auth` (trang đăng nhập/đăng ký, kế thừa `app`) và `admin` (khu quản trị). Các phần lặp lại được tách thành **component** (`resources/views/components`, lớp xử lý ở `app/View/Components`): `x-header`, `x-footer`, `x-product-card`, `x-price`, `x-breadcrumb`, `x-flash`, `x-empty-state`, `x-order-status`, `x-chat-widget`…

```blade
{{-- resources/views/layouts/app.blade.php (trích) --}}
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body id="top">
    <a href="#main" class="skip-link">Bỏ qua đến nội dung chính</a>
    <x-header />
    <x-flash />
    <main id="main" tabindex="-1">
        @yield('content')
    </main>
    <x-footer />
    <x-chat-widget />
    @stack('scripts')
</body>
```

Lớp component chuẩn bị dữ liệu hiển thị (vd. `ProductCard` tính URL, ảnh chính, trạng thái hết hàng) để Blade không chứa logic. Giao diện dựa trên **Bootstrap 5** biên dịch từ Sass bằng Vite: biến Sass được tuỳ biến (màu chủ đạo, góc vuông, tắt bóng và gradient, thang khoảng cách 4/8 px); các trang làm sau ngày 19/10/2026 (tài khoản, quản trị) dùng trực tiếp lớp Bootstrap (`card`, `table`, `form-control`, `btn`, `badge`…). Giao diện responsive, ảnh có `alt` và `loading="lazy"`, form có nhãn và báo lỗi tại từng trường.

[HÌNH 4.4: Trang chủ trên máy tính]

[HÌNH 4.5: Trang chủ trên điện thoại (rộng 390px) — menu thu gọn]

[HÌNH 4.6: Trang danh sách sản phẩm có bộ lọc]

[HÌNH 4.7: Trang chi tiết sản phẩm]

## 4.7. Xác thực và phân quyền

Chức năng đăng ký, đăng nhập, đăng xuất, quên/đặt lại mật khẩu được **tự viết** bằng các thành phần sẵn có của Laravel (`Auth`, `Password` broker, `RateLimiter`, cast `hashed` cho mật khẩu). `LoginRequest::authenticate()` kiểm tra thông tin đăng nhập, tài khoản còn hoạt động và giới hạn 5 lần sai mỗi phút. Sau khi đăng nhập, phiên được tạo lại (`session()->regenerate()`) để chống session fixation.

Khu quản trị có trang đăng nhập riêng `/admin/login` dùng chung `LoginRequest`, nhưng từ chối tài khoản khách hàng:

```php
// app/Http/Controllers/Auth/LoginController.php (trích)
public function storeAdmin(LoginRequest $request): RedirectResponse
{
    $request->authenticate();

    if (! $request->user()->role->isBackOffice()) {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        throw ValidationException::withMessages(['email' => __('auth.failed')]);
    }

    $request->session()->regenerate();

    return redirect()->intended(route('admin.dashboard'))->with('status', __('auth.logged_in'));
}
```

**Phân quyền hai tầng:**

1. **Middleware theo nhóm route:** `EnsureUserHasRole` (bí danh `role`) chặn theo vai trò — `role:admin,staff` cho toàn khu quản trị, `role:admin` cho tài khoản và thống kê. Middleware `EnsureAccountIsActive` (gắn vào nhóm `web`) đăng xuất ngay tài khoản bị khoá giữa phiên. Khách chưa đăng nhập vào `/admin/*` được chuyển tới `/admin/login`.
2. **Policy theo bản ghi/hành động:** `ProductPolicy`, `CategoryPolicy`, `OrderPolicy`, `UserPolicy` quyết định chi tiết (staff thêm/sửa sản phẩm nhưng không xoá; không ai xoá được đơn hàng; không tự hạ quyền mình hoặc hạ quyền admin cuối cùng). Policy được gọi qua `Gate::authorize()` hoặc `FormRequest::authorize()`.

Ma trận quyền đầy đủ ở `docs/permissions.md`.

[HÌNH 4.8: Trang đăng nhập khách hàng `/dang-nhap` (có thông báo lỗi khi nhập sai)]

[HÌNH 4.9: Trang đăng nhập quản trị `/admin/login`]

[HÌNH 4.10: Trang lỗi 403 khi nhân viên mở `/admin/reports`]

## 4.8. Quản lý sản phẩm (CRUD)

`Admin\ProductController` cung cấp danh sách (tìm theo tên/SKU, lọc danh mục và trạng thái *đang bán / ẩn / đã xoá*), thêm, sửa, xoá mềm và khôi phục. `ProductRequest` kiểm tra dữ liệu (SKU duy nhất và viết hoa, `sale_price` phải nhỏ hơn `price`, ảnh `jpg/png/webp` ≤ 2 MB); slug được sinh tự động không dấu, duy nhất bằng `Slug::unique()`. Thêm/sửa chạy trong giao dịch cùng việc lưu ảnh.

```php
// app/Http/Controllers/Admin/ProductController.php (trích)
/** Xoá mềm (quy tắc 10: không xoá cứng — đơn cũ vẫn tham chiếu được) */
public function destroy(Product $product): RedirectResponse
{
    Gate::authorize('delete', $product);
    $product->delete();
    // … chuyển về danh sách kèm thông báo
}

/** Ảnh mới thay ảnh chính: cập nhật đường dẫn ảnh chính (xoá file cũ nếu không phải ảnh mẫu) */
private function saveImage(ProductRequest $request, Product $product): void
{
    if (! $request->file('image') instanceof UploadedFile) {
        return;
    }
    $path = $request->file('image')->store('products', 'public');
    $primary = $product->images()->where('is_primary', true)->first();
    if ($primary === null) {
        $product->images()->create(['path' => $path, 'is_primary' => true, 'sort_order' => 0]);
        return;
    }
    if (! $primary->is_placeholder) {
        Storage::disk('public')->delete($primary->path);
    }
    $primary->update(['path' => $path]);
}
```

Vì model `Product` dùng trait `SoftDeletes`, `delete()` chỉ ghi `deleted_at`; sản phẩm biến mất khỏi mọi truy vấn phía khách nhưng dòng đơn cũ vẫn giữ tham chiếu, và quản trị viên khôi phục được bằng `restore()`. Ảnh lưu ở disk `public` (`storage/app/public/products`) và hiển thị qua đường dẫn tương đối `/storage/...`. Quản lý danh mục (`Admin\CategoryController`) làm theo cùng mẫu, cây hai cấp, chặn xoá khi danh mục còn sản phẩm hoặc danh mục con.

[HÌNH 4.11: Danh sách sản phẩm trong khu quản trị]

[HÌNH 4.12: Form thêm/sửa sản phẩm (có lỗi "Giá khuyến mãi phải nhỏ hơn giá bán.")]

[HÌNH 4.13: Quản lý danh mục]

## 4.9. Giỏ hàng

`CartService` ẩn đi việc giỏ được lưu ở đâu (quy tắc 8): khách vãng lai lưu mảng `[product_id => quantity]` trong session (khoá `cart`); người dùng đăng nhập lưu ở bảng `carts`/`cart_items`. Controller và view chỉ gọi `add()`, `updateQuantity()`, `remove()`, `items()`, `subtotal()`, `count()`. Mỗi lần thêm/cập nhật, service kiểm tra sản phẩm còn bán và tổng số lượng không vượt tồn kho; khi đọc giỏ, dòng của sản phẩm đã ngừng bán bị bỏ, số lượng vượt tồn kho bị hạ xuống, kèm thông báo (`notices()`). Giỏ không lưu giá: thành tiền luôn tính theo `current_price` tại thời điểm xem.

**Gộp giỏ khi đăng nhập:** listener `MergeGuestCart` lắng nghe sự kiện `Illuminate\Auth\Events\Login` (phát ra cả khi đăng nhập lẫn khi đăng ký thành công) và gọi:

```php
// app/Services/CartService.php (trích)
public function mergeGuestCart(User $user): void
{
    $guest = $this->sessionLines();
    if ($guest === []) {
        return;
    }

    $cart = Cart::query()->firstOrCreate(['user_id' => $user->id]);
    $existing = $cart->items()->pluck('quantity', 'product_id')->all();
    $products = Product::query()->active()->where('stock', '>', 0)->whereKey(array_keys($guest))->get(['id', 'stock']);

    foreach ($products as $product) {
        $quantity = min(($existing[$product->id] ?? 0) + $guest[$product->id], $product->stock);
        $cart->items()->updateOrCreate(['product_id' => $product->id], ['quantity' => $quantity]);
    }

    $this->session->forget(self::SESSION_KEY);
}
```

Số lượng được cộng dồn và cắt theo tồn kho; sản phẩm đã ngừng bán bị bỏ qua; giỏ session bị xoá sau khi gộp. Header hiển thị tổng số lượng trong giỏ (badge) qua `CartService::count()`.

[HÌNH 4.14: Trang giỏ hàng có sản phẩm giảm giá và dòng "Còn … để được miễn phí giao hàng"]

## 4.10. Đặt hàng

**Luồng:** trang `/thanh-toan` (`CheckoutController@create`) hiển thị form người nhận, phương thức thanh toán (COD, chuyển khoản) và tóm tắt đơn; phí giao hàng đọc từ bảng `settings` (`Shipping::fee()`: 30.000 ₫, miễn phí khi tạm tính từ 500.000 ₫). Khi gửi form, `PlaceOrderRequest` kiểm tra dữ liệu (số điện thoại Việt Nam được chuẩn hoá về dạng `0xxxxxxxxx`, email bắt buộc với khách vãng lai), sau đó `OrderService::placeOrder()` tạo đơn.

**Giao dịch và khoá dòng (quy tắc 4) + snapshot (quy tắc 2):**

```php
// app/Services/OrderService.php (trích)
$order = DB::transaction(function () use ($quantities, $data, $user) {
    // Khoá theo thứ tự id tăng dần để hai đơn đồng thời không khoá chéo nhau
    $products = Product::query()
        ->whereKey(array_keys($quantities))
        ->orderBy('id')
        ->lockForUpdate()
        ->get()
        ->keyBy('id');
    $this->ensureStock($products, $quantities);
    $items = collect($quantities)->map(function (int $quantity, int $productId) use ($products) {
        $product = $products[$productId];
        return [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'price' => $product->current_price,
            'quantity' => $quantity,
            'line_total' => $product->current_price * $quantity,
        ];
    })->values();
    // … tạo orders (mã MP-YYYYMMDD-XXXX), order_items, order_status_histories; trừ stock, tăng sold_count
```

`lockForUpdate()` sinh câu `SELECT … FOR UPDATE`: hai khách cùng đặt sản phẩm cuối cùng sẽ phải chờ nhau, người sau thấy tồn kho đã giảm và nhận thông báo thiếu hàng; mọi lỗi trong khối đều khiến toàn bộ giao dịch rollback. Dòng đơn lưu tên, SKU và giá tại thời điểm đặt nên không đổi khi sản phẩm bị sửa về sau.

**Chống gửi trùng:** mỗi lần mở trang thanh toán sinh một token (UUID) lưu ở session và trường ẩn của form. Khi đặt hàng, token phải khớp session và giành được khoá nguyên tử `Cache::add()`:

```php
// app/Http/Controllers/CheckoutController.php (trích)
$token = $request->validated('checkout_token');
$session = $request->session();

if ($token !== $session->get(self::TOKEN_KEY) || ! Cache::add('checkout-token:'.$token, true, now()->addHour())) {
    return $this->duplicate($request);
}
$session->forget(self::TOKEN_KEY);

$order = $this->orders->placeOrder($request->orderData(), $request->user());
```

Bấm "Đặt hàng" hai lần hay nhấn F5 sau khi gửi đều không tạo đơn thứ hai; người dùng được đưa tới trang xác nhận của đơn vừa đặt.

**Email xác nhận:** sau khi giao dịch commit, giỏ hàng được xoá và mailable `OrderPlaced` (`resources/views/emails`) được gửi tới email người nhận: mã đơn, danh sách sản phẩm, tổng tiền, phương thức thanh toán và hướng dẫn chuyển khoản (nếu có). Khi demo, driver mail là `log` nên nội dung email nằm trong `storage/logs/laravel.log`. Trang `/dat-hang-thanh-cong/{mã}` chỉ chủ đơn hoặc phiên vừa đặt xem được; người khác nhận 404.

[HÌNH 4.15: Trang thanh toán]

[HÌNH 4.16: Trang xác nhận đặt hàng (chọn chuyển khoản, có số tài khoản và nội dung chuyển khoản)]

[HÌNH 4.17: Nội dung email xác nhận đơn trong `storage/logs/laravel.log`]

[HÌNH 4.18: Lịch sử đơn và chi tiết đơn trong khu tài khoản (có nút "Huỷ đơn")]

## 4.11. Chức năng quản trị

### 4.11.1. Quản lý đơn hàng — luồng trạng thái

Danh sách đơn (`/admin/orders`) lọc theo trạng thái, tìm theo mã/tên/SĐT. Trang chi tiết chỉ đưa ra các trạng thái kế tiếp hợp lệ do enum `OrderStatus::transitions()` quy định. Controller chỉ chuyển yêu cầu sang service:

```php
// app/Http/Controllers/Admin/OrderController.php (trích)
public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
{
    $to = OrderStatus::from($request->validated('status'));
    $this->orders->changeStatus($order, $to, $request->user(), $request->validated('note'));

    return redirect()->route('admin.orders.show', $order)->with('status', "Đơn {$order->code}: chuyển sang “{$to->label()}”.");
}
```

`OrderService::changeStatus()` khoá đơn, kiểm tra `canTransitionTo()`, ghi mốc thời gian (`confirmed_at`, `shipped_at`, `delivered_at`) và một dòng `order_status_histories` kèm người thực hiện; chuyển sang "Đã huỷ" thì gọi `cancel()` để hoàn kho (đơn đã thanh toán chuyển "Đã hoàn tiền"). Chuyển sai luồng ném `InvalidOrderTransitionException`, hiển thị thành thông báo lỗi. Chức năng "Đánh dấu đã thanh toán" gọi `markPaid()`. Hệ thống không có route xoá đơn (quy tắc 11).

[HÌNH 4.19: Danh sách đơn hàng trong khu quản trị (lọc "Chờ xác nhận")]

[HÌNH 4.20: Chi tiết đơn hàng — ô chọn trạng thái kế tiếp và lịch sử trạng thái]

### 4.11.2. Quản lý tài khoản

`Admin\UserController` (chỉ admin) gộp khách hàng và nhân viên vào một màn: tìm kiếm, lọc theo vai trò, xem chi tiết (số đơn, tổng chi tiêu), đổi vai trò, khoá/mở khoá. `UserPolicy` đảm bảo luôn còn ít nhất một admin đang hoạt động:

```php
// app/Http/Controllers/Admin/UserController.php (trích)
public function updateRole(Request $request, User $user): RedirectResponse
{
    $data = $request->validate(['role' => ['required', Rule::enum(UserRole::class)]]);
    $role = UserRole::from($data['role']);

    if (Gate::denies('changeRole', [$user, $role])) {
        return back()->withErrors(['delete' => 'Không thể đổi vai trò: không được tự hạ quyền chính mình hoặc hạ quyền quản trị viên đang hoạt động cuối cùng.']);
    }

    $user->forceFill(['role' => $role])->save();
    // … quay lại kèm thông báo
}
```

[HÌNH 4.21: Danh sách tài khoản (khách hàng + nhân viên) với nút đổi vai trò / khoá]

### 4.11.3. Thống kê doanh thu

`Admin\ReportController` (chỉ admin) nhận khoảng ngày (`tu-ngay`, `den-ngay`, mặc định 30 ngày gần nhất) và gọi `ReportService`. Doanh thu chỉ tính đơn **đã giao**, nhóm theo ngày đặt (`orders.created_at`, có chỉ mục ghép `status + created_at`); ngày không có đơn vẫn có giá trị 0 để biểu đồ liên tục:

```php
// app/Services/ReportService.php (trích)
public function revenueByDay(CarbonInterface $from, CarbonInterface $to): array
{
    $byDay = $this->deliveredBetween($from->copy()->startOfDay(), $to->copy()->endOfDay())
        ->groupBy(fn (Order $o) => $o->created_at->format('Y-m-d'))
        ->map(fn ($orders) => (int) $orders->sum('total'));

    $labels = [];
    $data = [];
    foreach (CarbonPeriod::create($from->copy()->startOfDay(), '1 day', $to->copy()->startOfDay()) as $day) {
        $labels[] = $day->format('d/m');
        $data[] = $byDay[$day->format('Y-m-d')] ?? 0;
    }

    return ['labels' => $labels, 'data' => $data, 'total' => array_sum($data)];
}
```

Dữ liệu `labels`/`data` được truyền sang view, `resources/js/charts.js` vẽ biểu đồ đường bằng **Chart.js** (chỉ nạp ở trang thống kê). Trang còn có biểu đồ 12 tháng (`revenueByMonth`) và bảng top 10 sản phẩm bán chạy (`topProducts`). Trang tổng quan `/admin` hiển thị số liệu vận hành (đơn chờ xác nhận, sản phẩm sắp hết hàng) cho cả staff, còn doanh thu chỉ hiện với admin.

[HÌNH 4.22: Trang tổng quan quản trị (đăng nhập admin)]

[HÌNH 4.23: Trang thống kê doanh thu — biểu đồ theo ngày, theo tháng và bảng top sản phẩm]

### 4.11.4. Chat trực tuyến và tin liên hệ

Chat dùng cơ chế **polling** thay vì WebSocket để không cần máy chủ thời gian thực. Ở phía khách, `resources/js/chat.js` gọi API mỗi 5 giây, chỉ khi khung chat đang mở, và chỉ lấy tin mới hơn tin cuối cùng đã nhận:

```js
// resources/js/chat.js (trích)
const poll = async () => {
    const res = await fetch(`${root.dataset.url}?sau=${lastId}`, { headers: { Accept: 'application/json' } });
    if (!res.ok) {
        return;
    }
    const data = await res.json();
    if (nameField) {
        nameField.hidden = !data.needs_name;
    }
    append(data.messages);
};
// …
start() {
    poll();
    timer = setInterval(poll, POLL_MS);
},
```

Phía máy chủ, `ChatController` và `Admin\ChatController` gọi `ChatService`: hội thoại xác định theo `user_id` (khách hàng) hoặc `guest_token` ngẫu nhiên lưu trong session (khách vãng lai, nhập tên ở tin đầu tiên); gửi tin giới hạn 20 tin/phút. Nhân viên xem danh sách hội thoại (có tin chưa đọc lên đầu), trả lời, đóng hội thoại; trang hội thoại ở quản trị cũng polling 5 giây. Chức năng bật/tắt bằng cài đặt `chat_enabled`. Tin liên hệ từ form `/lien-he` (có honeypot và giới hạn 3 lượt/10 phút) được xem và đánh dấu "đã xử lý" ở `/admin/contacts`.

[HÌNH 4.24: Khung chat phía khách (đã có tin trả lời của nhân viên)]

[HÌNH 4.25: Màn hình trả lời chat trong khu quản trị]

[HÌNH 4.26: Danh sách tin liên hệ trong khu quản trị]

## 4.12. Danh sách ảnh cần chụp

Chuẩn bị: `php artisan migrate:fresh --seed`, `npm run build`, `php artisan serve`; mở `http://localhost:8000`. Mật khẩu mọi tài khoản demo: `Demo@123`. Ảnh máy tính chụp ở độ rộng ~1280px, ảnh điện thoại dùng chế độ thiết bị của trình duyệt (390px).

| Hình | Nội dung | URL | Tài khoản |
|---|---|---|---|
| 4.1 | Cấu trúc thư mục trong VS Code | — | — |
| 4.2 | Terminal `php artisan migrate --seed` | — | — |
| 4.3 | Bảng `products` trong công cụ quản lý MySQL | — | DB `mypham_shop` |
| 4.4 | Trang chủ (desktop) | `/` | Khách vãng lai |
| 4.5 | Trang chủ (mobile 390px), mở menu | `/` | Khách vãng lai |
| 4.6 | Danh sách sản phẩm có lọc | `/san-pham?danh-muc=…&khuyen-mai=1` | Khách vãng lai |
| 4.7 | Chi tiết sản phẩm đang giảm giá | `/san-pham/{slug}` (chọn SP có nhãn −%) | Khách vãng lai |
| 4.8 | Đăng nhập khách, nhập sai mật khẩu | `/dang-nhap` | `khachhang01@mypham.test` |
| 4.9 | Đăng nhập quản trị | `/admin/login` | — |
| 4.10 | Trang 403 | `/admin/reports` | `nhanvien1@mypham.test` |
| 4.11 | Danh sách sản phẩm quản trị | `/admin/products` | `admin@mypham.test` |
| 4.12 | Form sản phẩm có lỗi giá KM | `/admin/products/create` (nhập giá KM ≥ giá rồi Lưu) | `nhanvien1@mypham.test` |
| 4.13 | Quản lý danh mục | `/admin/categories` | `admin@mypham.test` |
| 4.14 | Giỏ hàng | `/gio-hang` (thêm 1–2 SP, có SP giảm giá) | Khách vãng lai |
| 4.15 | Trang thanh toán | `/thanh-toan` | `khachhang01@mypham.test` (đã điền sẵn) |
| 4.16 | Trang xác nhận đơn chuyển khoản | `/dat-hang-thanh-cong/{mã}` (ngay sau khi đặt) | `khachhang01@mypham.test` |
| 4.17 | Email xác nhận trong log | `storage/logs/laravel.log` | — |
| 4.18 | Lịch sử đơn / chi tiết đơn | `/tai-khoan/don-hang`, `/tai-khoan/don-hang/{mã}` | `khachhang01@mypham.test` |
| 4.19 | Danh sách đơn lọc "Chờ xác nhận" | `/admin/orders?trang-thai=pending` | `nhanvien1@mypham.test` |
| 4.20 | Chi tiết đơn + lịch sử trạng thái | `/admin/orders/{mã}` | `nhanvien1@mypham.test` |
| 4.21 | Quản lý tài khoản | `/admin/users` | `admin@mypham.test` |
| 4.22 | Tổng quan quản trị | `/admin` | `admin@mypham.test` |
| 4.23 | Thống kê doanh thu | `/admin/reports` | `admin@mypham.test` |
| 4.24 | Khung chat phía khách | `/` → nút "Chat" | Khách vãng lai (nhập tên) |
| 4.25 | Trả lời chat | `/admin/chats/{id}` | `nhanvien1@mypham.test` |
| 4.26 | Tin liên hệ | `/admin/contacts` (gửi trước 1 tin ở `/lien-he`) | `nhanvien1@mypham.test` |
