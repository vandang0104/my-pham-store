<?php

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('tạo đủ các bảng giỏ hàng, đơn hàng, coupon, đánh giá và nội dung', function () {
    foreach (['carts', 'cart_items', 'coupons', 'orders', 'order_items', 'order_status_histories',
        'reviews', 'banners', 'posts', 'faqs', 'contact_messages', 'settings'] as $bang) {
        expect(Schema::hasTable($bang))->toBeTrue("Thiếu bảng {$bang}");
    }

    // Địa chỉ giao hàng của đơn dùng cùng tên cột với addresses
    expect(Schema::hasColumns('orders', ['receiver_name', 'phone', 'province', 'district', 'ward', 'detail']))->toBeTrue();
});

it('gán trạng thái mặc định cho đơn mới', function () {
    $order = DB::table('orders')->find(taoDonHang());

    expect($order->status)->toBe('pending')
        ->and($order->payment_status)->toBe('unpaid')
        ->and($order->payment_method)->toBe('cod')
        ->and((int) $order->discount)->toBe(0)
        ->and((int) $order->shipping_fee)->toBe(0);
});

it('chỉ cho mỗi khách đánh giá một sản phẩm một lần', function () {
    $userId = taoUser();
    $productId = taoSanPham();
    $review = ['user_id' => $userId, 'product_id' => $productId, 'rating' => 5, 'comment' => 'Rất tốt'];

    DB::table('reviews')->insert($review);

    expect(fn () => DB::table('reviews')->insert($review))->toThrow(QueryException::class);
    expect(DB::table('reviews')->first()->status)->toBe('pending');
});

it('không cho trùng sản phẩm trong cùng một giỏ', function () {
    $cartId = DB::table('carts')->insertGetId(['user_id' => taoUser()]);
    $item = ['cart_id' => $cartId, 'product_id' => taoSanPham(), 'quantity' => 1];

    DB::table('cart_items')->insert($item);

    expect(fn () => DB::table('cart_items')->insert($item))->toThrow(QueryException::class);
});

it('giữ nguyên snapshot order_items khi sản phẩm đổi tên, giá', function () {
    $productId = taoSanPham(['name' => 'Kem chống nắng', 'sku' => 'KCN-50', 'price' => 450000]);
    $orderId = taoDonHang();
    DB::table('order_items')->insert([
        'order_id' => $orderId, 'product_id' => $productId,
        'product_name' => 'Kem chống nắng', 'sku' => 'KCN-50',
        'price' => 450000, 'quantity' => 2, 'line_total' => 900000,
    ]);

    DB::table('products')->where('id', $productId)->update(['name' => 'Kem chống nắng 2026', 'sku' => 'KCN-50-V2', 'price' => 520000]);

    $item = DB::table('order_items')->where('order_id', $orderId)->first();
    expect($item->product_name)->toBe('Kem chống nắng')
        ->and($item->sku)->toBe('KCN-50')
        ->and((int) $item->price)->toBe(450000)
        ->and((int) $item->line_total)->toBe(900000);
});

it('không cho xoá cứng sản phẩm đã có trong đơn', function () {
    $productId = taoSanPham();
    DB::table('order_items')->insert([
        'order_id' => taoDonHang(), 'product_id' => $productId,
        'product_name' => 'Sữa rửa mặt', 'sku' => 'SRM', 'price' => 350000, 'quantity' => 1, 'line_total' => 350000,
    ]);

    expect(fn () => DB::table('products')->delete($productId))->toThrow(QueryException::class);
});

it('giữ đơn hàng khi xoá user, chỉ bỏ liên kết', function () {
    $userId = taoUser();
    $orderId = taoDonHang(['user_id' => $userId]);
    DB::table('order_status_histories')->insert(['order_id' => $orderId, 'to_status' => 'confirmed', 'changed_by' => $userId]);

    DB::table('users')->delete($userId);

    expect(DB::table('orders')->find($orderId))->not->toBeNull()
        ->and(DB::table('orders')->find($orderId)->user_id)->toBeNull()
        ->and(DB::table('order_status_histories')->where('order_id', $orderId)->value('changed_by'))->toBeNull();
});

it('không cho xoá coupon đã được dùng trong đơn', function () {
    $couponId = DB::table('coupons')->insertGetId(['code' => 'GIAM10', 'type' => 'percent', 'value' => 10]);
    taoDonHang(['coupon_id' => $couponId]);

    expect(fn () => DB::table('coupons')->delete($couponId))->toThrow(QueryException::class);
});

it('xoá đơn thì xoá chi tiết và nhật ký trạng thái, đánh giá vẫn còn', function () {
    $userId = taoUser();
    $productId = taoSanPham();
    $orderId = taoDonHang(['user_id' => $userId]);
    DB::table('order_items')->insert([
        'order_id' => $orderId, 'product_id' => $productId,
        'product_name' => 'Sữa rửa mặt', 'sku' => 'SRM', 'price' => 350000, 'quantity' => 1, 'line_total' => 350000,
    ]);
    DB::table('order_status_histories')->insert(['order_id' => $orderId, 'to_status' => 'pending']);
    DB::table('reviews')->insert(['user_id' => $userId, 'product_id' => $productId, 'order_id' => $orderId, 'rating' => 4, 'comment' => 'Ổn']);

    DB::table('orders')->delete($orderId);

    expect(DB::table('order_items')->count())->toBe(0)
        ->and(DB::table('order_status_histories')->count())->toBe(0)
        ->and(DB::table('reviews')->value('order_id'))->toBeNull();
});

it('xoá user thì xoá giỏ hàng của user', function () {
    $userId = taoUser();
    $cartId = DB::table('carts')->insertGetId(['user_id' => $userId]);
    DB::table('cart_items')->insert(['cart_id' => $cartId, 'product_id' => taoSanPham(), 'quantity' => 2]);

    DB::table('users')->delete($userId);

    expect(DB::table('carts')->count())->toBe(0)
        ->and(DB::table('cart_items')->count())->toBe(0);
});

it('không cho trùng mã đơn, mã coupon, slug bài viết, key cài đặt', function (string $bang, array $dong) {
    DB::table($bang)->insert($dong);

    expect(fn () => DB::table($bang)->insert($dong))->toThrow(QueryException::class);
})->with([
    'mã đơn' => fn () => ['orders', ['code' => 'DH0001', 'receiver_name' => 'A', 'phone' => '1', 'province' => 'P', 'ward' => 'W', 'detail' => 'D', 'subtotal' => 1, 'total' => 1]],
    'mã coupon' => fn () => ['coupons', ['code' => 'FREESHIP', 'type' => 'fixed', 'value' => 30000]],
    'slug bài viết' => fn () => ['posts', ['title' => 'Bài 1', 'slug' => 'bai-1', 'content' => '# Nội dung']],
    'key cài đặt' => fn () => ['settings', ['key' => 'shipping_fee', 'value' => '30000']],
]);
