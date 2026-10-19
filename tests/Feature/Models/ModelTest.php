<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PostStatus;
use App\Enums\ReviewStatus;
use App\Enums\UserRole;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Post;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

describe('scope hiển thị công khai', function () {
    it('chỉ lấy đánh giá đã duyệt', function () {
        $productId = taoSanPham();
        foreach (['pending', 'approved', 'rejected'] as $status) {
            DB::table('reviews')->insert(['user_id' => taoUser(), 'product_id' => $productId, 'rating' => 5, 'comment' => 'Tốt', 'status' => $status]);
        }

        expect(Review::approved()->get())->toHaveCount(1)
            ->and(Review::approved()->first()->status)->toBe(ReviewStatus::Approved);
    });

    it('chỉ lấy bài đã xuất bản và đã đến giờ đăng', function () {
        $bai = fn (string $slug, string $status, $publishedAt) => Post::create([
            'title' => $slug, 'slug' => $slug, 'content' => '# Bài', 'status' => $status, 'published_at' => $publishedAt,
        ]);
        $bai('da-dang', 'published', now()->subDay());
        $bai('nhap', 'draft', now()->subDay());
        $bai('hen-gio', 'published', now()->addDay());
        $bai('thieu-ngay', 'published', null);

        expect(Post::published()->pluck('slug')->all())->toBe(['da-dang'])
            ->and(Post::first()->status)->toBe(PostStatus::Published);
    });

    it('chỉ lấy banner đang bật và trong thời gian hiệu lực', function () {
        $banner = fn (string $title, array $attrs = []) => Banner::create($attrs + [
            'title' => $title, 'image_desktop' => 'banners/a.jpg', 'position' => 'home_hero',
        ]);
        $banner('khong-han');
        $banner('trong-han', ['starts_at' => now()->subDay(), 'ends_at' => now()->addDay()]);
        $banner('tat', ['is_active' => false]);
        $banner('chua-toi', ['starts_at' => now()->addDay()]);
        $banner('het-han', ['ends_at' => now()->subMinute()]);

        expect(Banner::visible()->orderBy('id')->pluck('title')->all())->toBe(['khong-han', 'trong-han']);
    });
});

describe('quan hệ', function () {
    it('liên kết danh mục cha - con hai chiều', function () {
        $cha = Category::create(['name' => 'Trang điểm', 'slug' => 'trang-diem']);
        $con2 = $cha->children()->create(['name' => 'Phấn', 'slug' => 'phan', 'sort_order' => 2]);
        $con1 = $cha->children()->create(['name' => 'Son', 'slug' => 'son', 'sort_order' => 1]);

        expect($cha->children->pluck('slug')->all())->toBe(['son', 'phan'])
            ->and($con2->parent->is($cha))->toBeTrue()
            ->and(Category::roots()->pluck('id')->all())->toBe([$cha->id])
            ->and($con1->selfAndDescendantIds())->toBe([$con1->id]);
    });

    it('liên kết sản phẩm với danh mục, thương hiệu và ngược lại', function () {
        $product = Product::query()->findOrFail(taoSanPham());

        expect($product->category->products->first()->is($product))->toBeTrue()
            ->and($product->brand)->toBeInstanceOf(Brand::class)
            ->and($product->brand->products)->toHaveCount(1);
    });

    it('liên kết đơn hàng với user, chi tiết, nhật ký và cast enum', function () {
        $user = User::query()->findOrFail(taoUser());
        $order = Order::query()->findOrFail(taoDonHang(['user_id' => $user->id, 'payment_method' => 'bank_transfer']));
        $order->items()->create(['product_id' => taoSanPham(), 'product_name' => 'Kem dưỡng', 'sku' => 'KD', 'price' => 300000, 'quantity' => 1, 'line_total' => 300000]);
        $order->statusHistories()->create(['to_status' => 'pending', 'changed_by' => $user->id]);

        $order->refresh();
        expect($order->status)->toBe(OrderStatus::Pending)
            ->and($order->payment_method)->toBe(PaymentMethod::BankTransfer)
            ->and($order->payment_status)->toBe(PaymentStatus::Unpaid)
            ->and($order->user->is($user))->toBeTrue()
            ->and($user->orders)->toHaveCount(1)
            ->and($order->items->first()->order->is($order))->toBeTrue()
            ->and($order->statusHistories->first()->to_status)->toBe(OrderStatus::Pending)
            ->and($order->statusHistories->first()->changedBy->is($user))->toBeTrue();
    });

    it('vẫn lấy được sản phẩm đã xoá mềm từ chi tiết đơn cũ', function () {
        $product = Product::query()->findOrFail(taoSanPham());
        $order = Order::query()->findOrFail(taoDonHang());
        $item = $order->items()->create(['product_id' => $product->id, 'product_name' => 'X', 'sku' => 'X', 'price' => 1, 'quantity' => 1, 'line_total' => 1]);

        $product->delete();

        expect(Product::find($product->id))->toBeNull()
            ->and($item->fresh()->product->is($product))->toBeTrue();
    });
});

describe('route model binding', function () {
    it('dùng slug cho sản phẩm, danh mục, thương hiệu, bài viết và mã đơn cho đơn hàng', function () {
        expect((new Product)->getRouteKeyName())->toBe('slug')
            ->and((new Category)->getRouteKeyName())->toBe('slug')
            ->and((new Brand)->getRouteKeyName())->toBe('slug')
            ->and((new Post)->getRouteKeyName())->toBe('slug')
            ->and((new Order)->getRouteKeyName())->toBe('code');

        $order = Order::query()->findOrFail(taoDonHang(['code' => 'DH20261019']));
        expect((new Order)->resolveRouteBinding('DH20261019')->is($order))->toBeTrue();
    });
});

it('không cho gán hàng loạt quyền và trạng thái khoá tài khoản', function () {
    $user = User::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'secret', 'role' => 'admin', 'is_active' => false])->refresh();

    expect($user->role)->toBe(UserRole::Customer)
        ->and($user->is_active)->toBeTrue();
});
