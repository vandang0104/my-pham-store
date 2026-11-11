<?php

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->seed();
});

it('mọi sản phẩm dùng ảnh thật theo slug, ảnh chính đứng đầu và file có trên disk', function () {
    $products = Product::query()->with('images')->get();

    expect($products)->toHaveCount(21);
    foreach ($products as $product) {
        $primary = $product->images->firstWhere('is_primary', true);
        expect($primary->path)->toBe("anh-that/san-pham/{$product->slug}.jpg");
        Storage::disk('public')->assertExists($primary->path);
    }
});

it('sản phẩm có ảnh phụ <slug>-2 thì nhận thêm ảnh thứ hai', function () {
    $product = Product::query()->where('slug', 'dung-dich-tay-te-bao-chet-bha-2')->with('images')->firstOrFail();

    expect($product->images->sortBy('sort_order')->pluck('path')->all())->toBe([
        'anh-that/san-pham/dung-dich-tay-te-bao-chet-bha-2.jpg',
        'anh-that/san-pham/dung-dich-tay-te-bao-chet-bha-2-2.jpg',
    ]);
});

it('danh mục cha và thương hiệu dùng ảnh thật', function () {
    Category::query()->whereNull('parent_id')->get()
        ->each(fn (Category $c) => expect($c->image)->toBe("anh-that/danh-muc/{$c->slug}.jpg"));
    Brand::query()->get()
        ->each(fn (Brand $b) => expect($b->logo)->toBe("anh-that/thuong-hieu/{$b->slug}.png"));
});

it('banner dùng ảnh thật; thiếu bản mobile thì để trống để trang dùng ảnh desktop', function () {
    $first = Banner::query()->where('position', 'home_hero')->orderBy('sort_order')->firstOrFail();
    $second = Banner::query()->where('position', 'home_hero')->orderBy('sort_order')->skip(1)->firstOrFail();

    expect($first->image_desktop)->toBe('anh-that/banner/home-1-desktop.jpg')
        ->and($first->image_mobile)->toBe('anh-that/banner/home-1-mobile.jpg')
        ->and($second->image_desktop)->toBe('anh-that/banner/home-2-desktop.jpg')
        ->and($second->image_mobile)->toBeNull()
        ->and($second->mobile_url)->toBe($second->desktop_url);
});
