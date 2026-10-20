<?php

use App\Enums\OrderStatus;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('kịch bản demo: xem → tìm/lọc → giỏ → đặt COD → admin xác nhận → giao → đã giao → thống kê; khách huỷ đơn chờ khác', function () {
    Mail::fake();
    $category = Category::factory()->create(['name' => 'Chăm sóc da', 'slug' => 'cham-soc-da']);
    $brand = Brand::factory()->create(['name' => 'Dermalis Lab', 'slug' => 'dermalis-lab']);
    $serum = Product::factory()->for($category)->for($brand)->create([
        'name' => 'Serum Niacinamide', 'slug' => 'serum-niacinamide', 'price' => 345000, 'sale_price' => 279000, 'stock' => 10,
    ]);
    $admin = User::factory()->admin()->create();
    $khach = User::factory()->customer()->create(['name' => 'Nguyễn Thị Lan', 'phone' => '0912345678', 'email' => 'lan@example.com']);

    // 1. Xem, tìm, lọc sản phẩm
    $this->get('/')->assertOk();
    $this->get('/san-pham?q=niacinamide')->assertSee('Serum Niacinamide');
    $this->get('/san-pham?danh-muc=cham-soc-da&thuong-hieu[]=dermalis-lab&khuyen-mai=1')->assertSee('Serum Niacinamide');
    $this->get('/san-pham/serum-niacinamide')->assertOk()->assertSee('279.000');

    // 2. Khách đăng nhập, thêm giỏ, đặt hàng COD
    $this->actingAs($khach);
    $this->post(route('cart.store'), ['product_id' => $serum->id, 'quantity' => 2])->assertSessionHas('status');
    preg_match('/name="checkout_token" value="([^"]+)"/', $this->get(route('checkout.create'))->getContent(), $m);
    $this->post(route('checkout.store'), [
        'checkout_token' => $m[1], 'receiver_name' => 'Nguyễn Thị Lan', 'phone' => '0912345678', 'email' => 'lan@example.com',
        'province' => 'TP. Hồ Chí Minh', 'ward' => 'Phường Bến Thành', 'detail' => '12 Lê Lợi', 'payment_method' => 'cod',
    ])->assertRedirect();
    $order = Order::query()->sole();
    expect($order->total)->toBe(558000)->and($serum->fresh()->stock)->toBe(8);

    // 3. Admin xác nhận → đang giao → đã giao
    $this->actingAs($admin);
    foreach (['confirmed', 'shipping', 'delivered'] as $status) {
        $this->patch(route('admin.orders.status', $order), ['status' => $status])->assertSessionHas('status');
    }
    expect($order->fresh()->status)->toBe(OrderStatus::Delivered);

    // 4. Thống kê có doanh thu đơn đó
    expect(app(ReportService::class)->revenueByDay(today(), today())['total'])->toBe(558000);
    $this->get(route('admin.reports.index'))->assertSee('558.000');
    $this->get(route('admin.dashboard'))->assertSee('data-stat="revenue-today">558.000', false);

    // 5. Khách đặt thêm đơn rồi tự huỷ đơn đang chờ → hoàn kho
    $this->actingAs($khach);
    $this->post(route('cart.store'), ['product_id' => $serum->id, 'quantity' => 1]);
    preg_match('/name="checkout_token" value="([^"]+)"/', $this->get(route('checkout.create'))->getContent(), $m);
    $this->post(route('checkout.store'), [
        'checkout_token' => $m[1], 'receiver_name' => 'Nguyễn Thị Lan', 'phone' => '0912345678',
        'province' => 'TP. Hồ Chí Minh', 'ward' => 'Phường Bến Thành', 'detail' => '12 Lê Lợi', 'payment_method' => 'bank_transfer',
    ]);
    $second = Order::query()->whereKeyNot($order->id)->sole();
    expect($serum->fresh()->stock)->toBe(7);

    $this->post(route('account.orders.cancel', $second))->assertSessionHas('status');
    expect($second->fresh()->status)->toBe(OrderStatus::Cancelled)->and($serum->fresh()->stock)->toBe(8);
});
