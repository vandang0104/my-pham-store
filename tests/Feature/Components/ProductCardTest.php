<?php

use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

function sanPhamThe(array $attrs = []): Product
{
    $product = Product::factory()->for(Brand::factory()->state(['name' => 'Dermalis Lab']))->create($attrs + [
        'name' => 'Serum Niacinamide 10% Sáng Da',
        'price' => 345000,
        'sale_price' => null,
        'stock' => 20,
        'volume' => '30ml',
        'skin_type' => 'combination',
    ]);
    ProductImage::factory()->for($product)->primary()->create(['path' => 'placeholders/products/serum.svg']);

    return $product->load(['images', 'brand']);
}

/** Không có <button> nào nằm trong <a> */
function khongLongNutTrongLink(string $html): bool
{
    $dom = new DOMDocument;
    @$dom->loadHTML('<?xml encoding="utf-8"?>'.$html);

    return (new DOMXPath($dom))->query('//a//button | //a//form')->length === 0;
}

describe('product card', function () {
    it('đang giảm giá: hiện nhãn phần trăm, giá khuyến mãi và giá gốc gạch ngang', function () {
        $product = sanPhamThe(['sale_price' => 279000]);

        $this->blade('<x-product-card :product="$product" />', ['product' => $product])
            ->assertSee('−19%')
            ->assertSee('price__current--sale', false)
            ->assertSeeInOrder(['279.000', '345.000'])
            ->assertSee('<del class="price__original">', false)
            ->assertSee('Giá khuyến mãi');
    });

    it('không giảm giá: một mức giá, không nhãn phần trăm', function () {
        $product = sanPhamThe();

        $this->blade('<x-product-card :product="$product" />', ['product' => $product])
            ->assertSee('345.000')
            ->assertDontSee('product-card__discount', false)
            ->assertDontSee('<del', false);
    });

    it('hết hàng: ghi "Hết hàng" và vô hiệu nút thêm giỏ', function () {
        $product = sanPhamThe(['stock' => 0]);

        $this->blade('<x-product-card :product="$product" />', ['product' => $product])
            ->assertSee('is-out-of-stock', false)
            ->assertSee('Hết hàng')
            ->assertSee('đã hết hàng')
            ->assertSeeInOrder(['product-card__add', 'disabled'], false);
    });

    it('dùng stretched link: một link tới trang chi tiết, nút tim và nút giỏ không lồng trong thẻ a', function () {
        $product = sanPhamThe(['sale_price' => 279000]);
        $html = (string) $this->blade('<x-product-card :product="$product" />', ['product' => $product]);

        expect(substr_count($html, '<a '))->toBe(1)
            ->and($html)->toContain('href="'.url('/san-pham/'.$product->slug).'"', 'class="product-card__link"', 'product-card__wish', 'product-card__add')
            ->and(khongLongNutTrongLink($html))->toBeTrue();
    });

    it('hiện thương hiệu, dung tích, loại da và ảnh có alt là tên sản phẩm', function () {
        $product = sanPhamThe();

        $this->blade('<x-product-card :product="$product" />', ['product' => $product])
            ->assertSee('Dermalis Lab')
            ->assertSee('30ml · Da hỗn hợp')
            ->assertSee('alt="Serum Niacinamide 10% Sáng Da"', false)
            ->assertSee('loading="lazy"', false);
    });

    it('xoay vòng 3 tông dải nhãn theo vị trí', function (int $index, string $class) {
        $product = sanPhamThe();

        $this->blade('<x-product-card :product="$product" :index="$i" />', ['product' => $product, 'i' => $index])
            ->assertSee($class, false);
    })->with([[0, 'product-card--tone-0'], [1, 'product-card--tone-1'], [2, 'product-card--tone-2'], [3, 'product-card--tone-0']]);

    it('vẽ placeholder bằng HTML khi sản phẩm chưa có ảnh', function () {
        $product = Product::factory()->create(['name' => 'Kem dưỡng Ceramide'])->load(['images', 'brand']);

        $this->blade('<x-product-card :product="$product" />', ['product' => $product])
            ->assertSee('product-card__placeholder', false)
            ->assertDontSee('<img', false);
    });
});

describe('rating và breadcrumb', function () {
    it('hiển thị điểm kiểu Việt Nam và sao đầy/nửa', function () {
        $html = (string) $this->blade('<x-rating :value="4.7" :count="23" />');

        expect($html)->toContain('Đánh giá 4,7 trên 5', '(23 đánh giá)')
            ->and(substr_count($html, 'fill="currentColor"'))->toBe(5); // 4 sao đầy + 1 nửa sao
        $this->blade('<x-rating :value="0" :count="0" />')->assertSee('Chưa có đánh giá');
    });

    it('breadcrumb có Trang chủ, mục cuối không phải link và JSON-LD BreadcrumbList', function () {
        $html = (string) $this->blade('<x-breadcrumb :items="$items" />', ['items' => [
            ['label' => 'Chăm sóc da mặt', 'url' => url('/danh-muc/cham-soc-da-mat')],
            ['label' => 'Serum Niacinamide'],
        ]]);

        expect($html)->toContain('Trang chủ', 'aria-current="page">Serum Niacinamide', 'application/ld+json', '"@type":"BreadcrumbList"', '"position":3');
    });
});

describe('phân trang', function () {
    it('dùng view tuỳ biến và giữ query string của bộ lọc', function () {
        Product::factory()->count(5)->create();
        Route::get('/_test/phan-trang', fn () => Product::query()->paginate(2)->withQueryString()->links()->toHtml());

        $this->get('/_test/phan-trang?sap-xep=gia-tang&loai-da=oily')
            ->assertOk()
            ->assertSee('Trang 1/3')
            ->assertSee('aria-current="page"', false)
            ->assertSee('sap-xep=gia-tang&amp;loai-da=oily&amp;page=2', false)
            ->assertSee('Sau');
    });
});
