<?php

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Address;
use App\Models\Banner;
use App\Models\Coupon;
use App\Models\Faq;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('tạo được bản ghi hợp lệ từ mọi factory', function (string $model) {
    expect($model::factory()->create()->exists)->toBeTrue();
})->with([
    User::class, Address::class, Product::class, ProductImage::class, Coupon::class,
    Order::class, OrderItem::class, Review::class, Banner::class, Post::class, Faq::class,
]);

it('có state cho vai trò user', function () {
    expect(User::factory()->admin()->create()->role)->toBe(UserRole::Admin)
        ->and(User::factory()->staff()->create()->role)->toBe(UserRole::Staff)
        ->and(User::factory()->create()->role)->toBe(UserRole::Customer);
});

it('sinh giá khuyến mãi nhỏ hơn giá gốc và là bội số 1.000', function () {
    $products = Product::factory()->onSale()->count(10)->create();

    foreach ($products as $p) {
        expect($p->sale_price)->toBeLessThan($p->price)
            ->and($p->sale_price % 1000)->toBe(0)
            ->and($p->is_on_sale)->toBeTrue();
    }
});

it('snapshot chi tiết đơn lấy từ sản phẩm và tổng tiền đơn nhất quán', function () {
    $product = Product::factory()->onSale()->create();
    $item = OrderItem::factory()->for($product)->create(['quantity' => 2]);
    $order = Order::factory()->delivered()->create();

    expect($item->product_name)->toBe($product->name)
        ->and($item->price)->toBe($product->current_price)
        ->and($item->line_total)->toBe($product->current_price * 2)
        ->and($order->status)->toBe(OrderStatus::Delivered)
        ->and($order->total)->toBe($order->subtotal - $order->discount + $order->shipping_fee);
});

it('có state coupon hết hạn, chưa bắt đầu, hết lượt', function () {
    expect(Coupon::factory()->expired()->create()->expires_at->isPast())->toBeTrue()
        ->and(Coupon::factory()->upcoming()->create()->starts_at->isFuture())->toBeTrue()
        ->and(Coupon::factory()->exhausted(5)->create()->used_count)->toBe(5);
});
