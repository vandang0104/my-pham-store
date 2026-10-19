<?php

use App\Enums\OrderStatus;
use App\Enums\ReviewStatus;
use App\Enums\SkinType;
use App\Enums\UserRole;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Faq;
use App\Models\Order;
use App\Models\Post;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->seed();
});

it('tạo đúng số tài khoản theo vai trò, ai cũng có đúng một địa chỉ mặc định', function () {
    $users = User::query()->withCount(['addresses as so_mac_dinh' => fn ($q) => $q->where('is_default', true)])->get();

    expect($users->countBy(fn (User $u) => $u->role->value)->all())->toBe(['admin' => 1, 'staff' => 2, 'customer' => 20])
        ->and($users->pluck('so_mac_dinh')->unique()->values()->all())->toBe([1]);
});

it('tạo catalog đúng yêu cầu', function () {
    $products = Product::query()->with('images')->get();

    expect(Category::roots()->count())->toBe(8)
        ->and(Category::query()->whereNotNull('parent_id')->count())->toBeGreaterThan(8)
        ->and($products)->toHaveCount(48)
        ->and($products->where('is_featured', true))->toHaveCount(12)
        ->and($products->where('is_on_sale', true)->count())->toBeBetween(13, 15)
        ->and($products->filter(fn (Product $p) => $p->sale_price !== null && $p->sale_price >= $p->price))->toBeEmpty()
        ->and($products->filter(fn (Product $p) => $p->price % 1000 !== 0 || ($p->sale_price ?? 0) % 1000 !== 0))->toBeEmpty()
        ->and($products->every(fn (Product $p) => in_array($p->images->count(), [3, 4], true)))->toBeTrue()
        ->and($products->every(fn (Product $p) => $p->images->where('is_primary', true)->count() === 1))->toBeTrue();

    // Đủ mọi loại da
    $skinTypes = $products->pluck('skin_type')->filter()->unique()->values();
    expect($skinTypes)->toHaveCount(count(SkinType::cases()));
});

it('dùng ảnh cục bộ, không hotlink', function () {
    $paths = Product::query()->with('images')->get()->flatMap->images->pluck('path');

    expect($paths->filter(fn (string $p) => str_starts_with($p, 'http')))->toBeEmpty();
    $paths->each(fn (string $p) => Storage::disk('public')->assertExists($p));
});

it('tạo đơn hàng đủ trạng thái với tổng tiền và snapshot nhất quán', function () {
    $orders = Order::query()->with(['items', 'statusHistories'])->get();

    expect($orders)->toHaveCount(15)
        ->and($orders->pluck('status')->unique())->toHaveCount(count(OrderStatus::cases()));

    foreach ($orders as $order) {
        expect($order->total)->toBe($order->subtotal - $order->discount + $order->shipping_fee)
            ->and($order->subtotal)->toBe($order->items->sum('line_total'));

        foreach ($order->items as $item) {
            expect($item->line_total)->toBe($item->price * $item->quantity)
                ->and($item->product_name)->not->toBeEmpty()
                ->and($item->sku)->not->toBeEmpty();
        }

        // Nhật ký đi đúng luồng và kết thúc ở trạng thái hiện tại của đơn
        $histories = $order->statusHistories;
        expect($histories->first()->from_status)->toBeNull()
            ->and($histories->last()->to_status)->toBe($order->status);
        $histories->skip(1)->each(fn ($h) => expect($h->from_status->canTransitionTo($h->to_status))->toBeTrue());
    }
});

it('đếm lượt dùng coupon theo đơn chưa huỷ và có đủ tình huống coupon', function () {
    foreach (Coupon::all() as $coupon) {
        expect($coupon->used_count)->toBe($coupon->orders()->where('status', '!=', OrderStatus::Cancelled)->count());
    }

    expect(Coupon::query()->where('expires_at', '<', now())->exists())->toBeTrue()
        ->and(Coupon::query()->where('starts_at', '>', now())->exists())->toBeTrue()
        ->and(Coupon::query()->whereNotNull('usage_limit')->whereColumn('used_count', '>=', 'usage_limit')->exists())->toBeTrue();
});

it('chỉ tạo đánh giá từ đơn đã giao, không trùng và khớp điểm trung bình', function () {
    $reviews = Review::query()->with('order.items')->get();

    expect($reviews)->toHaveCount(30)
        ->and($reviews->unique(fn (Review $r) => $r->user_id.'-'.$r->product_id))->toHaveCount(30);

    foreach ($reviews as $review) {
        expect($review->order->status)->toBe(OrderStatus::Delivered)
            ->and($review->order->user_id)->toBe($review->user_id)
            ->and($review->order->items->pluck('product_id'))->toContain($review->product_id);
    }

    foreach (Product::all() as $product) {
        $approved = Review::approved()->where('product_id', $product->id);
        expect($product->rating_count)->toBe($approved->count())
            ->and($product->rating_avg)->toEqualWithDelta(round((float) $approved->avg('rating'), 2), 0.001);
    }

    expect($reviews->pluck('status')->unique()->count())->toBe(count(ReviewStatus::cases()));
});

it('tạo nội dung tiếng Việt: banner, FAQ, bài viết đã xuất bản', function () {
    expect(Banner::count())->toBe(4)
        ->and(Faq::count())->toBe(8)
        ->and(Post::published()->count())->toBe(6)
        ->and(User::query()->where('role', UserRole::Admin)->first()->posts()->exists())->toBeTrue();
});
