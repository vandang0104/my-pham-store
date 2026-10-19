<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->cha = Category::factory()->create(['name' => 'Chăm sóc da mặt', 'slug' => 'cham-soc-da-mat']);
    $this->danhMuc = Category::factory()->childOf($this->cha)->create(['name' => 'Serum', 'slug' => 'serum']);
    $this->brand = Brand::factory()->create(['name' => 'Dermalis Lab', 'slug' => 'dermalis-lab']);

    $this->product = Product::factory()->for($this->danhMuc, 'category')->for($this->brand)->create([
        'name' => 'Serum Niacinamide 10%',
        'slug' => 'serum-niacinamide-10',
        'sku' => 'DML-001',
        'price' => 345000,
        'sale_price' => 279000,
        'stock' => 12,
        'volume' => '30ml',
        'short_description' => 'Tinh chất mỏng nhẹ cho da dầu.',
        'description' => "Tinh chất **đậm đặc**.\n\n<script>alert('x')</script>\n\n[bấm](javascript:alert(1))",
        'ingredients' => 'Nước tinh khiết, Niacinamide 10%.',
        'usage_guide' => "1. Làm sạch da.\n2. Thoa 2–3 giọt.",
    ]);
    ProductImage::factory()->for($this->product)->create(['path' => 'placeholders/a.svg', 'is_primary' => true, 'sort_order' => 0]);
    ProductImage::factory()->for($this->product)->create(['path' => 'placeholders/b.svg', 'is_primary' => false, 'sort_order' => 1]);
});

describe('chi tiết sản phẩm', function () {
    it('hiện đúng thông tin: tên, thương hiệu, SKU, giá khuyến mãi, tồn kho, thành phần, hướng dẫn', function () {
        $this->get('/san-pham/serum-niacinamide-10')
            ->assertOk()
            ->assertSee('<title>', false)
            ->assertSee('Serum Niacinamide 10% · Mộc Lam')
            ->assertSee('<h1 class="t-title mb-3">Serum Niacinamide 10%</h1>', false)
            ->assertSee('href="'.route('brands.show', $this->brand).'"', false)
            ->assertSee('Mã SP: DML-001')
            ->assertSeeInOrder(['279.000', '345.000'])
            ->assertSee('−19%')
            ->assertSee('Còn hàng')
            ->assertSee('Nước tinh khiết, Niacinamide 10%.')
            ->assertSee('<li>Thoa 2–3 giọt.</li>', false);
    });

    it('breadcrumb: Trang chủ → danh mục cha → danh mục → tên sản phẩm', function () {
        $this->get('/san-pham/serum-niacinamide-10')->assertSeeInOrder([
            'Trang chủ', 'Chăm sóc da mặt', 'Serum', 'Serum Niacinamide 10%',
        ]);
    });

    it('có ảnh chính và ảnh phụ để đổi ảnh', function () {
        $html = $this->get('/san-pham/serum-niacinamide-10')->getContent();

        expect($html)->toContain('data-gallery-main', 'alt="Serum Niacinamide 10%"')
            ->and(substr_count($html, 'data-gallery-thumb'))->toBe(2)
            ->and($html)->toContain('src="/storage/placeholders/a.svg"');
    });

    it('mô tả Markdown được lọc: giữ định dạng, bỏ HTML thô và link javascript:', function () {
        $html = $this->get('/san-pham/serum-niacinamide-10')->getContent();

        expect($html)->toContain('<strong>đậm đặc</strong>')
            ->not->toContain("<script>alert('x')</script>")
            ->not->toContain('href="javascript:');
    });

    it('hết hàng: báo "Hết hàng" và khoá ô số lượng', function () {
        $this->product->update(['stock' => 0]);

        $this->get('/san-pham/serum-niacinamide-10')
            ->assertSee('Hết hàng')
            ->assertDontSee('Còn hàng')
            ->assertSee('class="form-control product-detail__qty" disabled', false);
    });

    it('nút thêm vào giỏ trỏ POST /gio-hang (tạm khoá tới S2)', function () {
        $this->get('/san-pham/serum-niacinamide-10')
            ->assertSee('action="'.url('/gio-hang').'"', false)
            ->assertSee('name="quantity"', false);
    });

    it('tối đa 4 sản phẩm liên quan cùng danh mục, không gồm chính nó và sản phẩm ẩn', function () {
        Product::factory()->count(5)->for($this->danhMuc, 'category')->create();
        $an = Product::factory()->inactive()->for($this->danhMuc, 'category')->create(['name' => 'Liên quan đang ẩn']);
        $khac = Product::factory()->create(['name' => 'Khác danh mục']);

        $html = $this->get('/san-pham/serum-niacinamide-10')->getContent();
        $section = str($html)->after('id="lien-quan"')->toString();

        expect(substr_count($section, '<article class="product-card'))->toBe(4)
            ->and($section)->not->toContain('Serum Niacinamide 10%</a>', $an->name, $khac->name);
    });

    it('sản phẩm đang ẩn, đã xoá mềm hoặc không tồn tại → 404', function () {
        $this->get('/san-pham/khong-ton-tai')->assertNotFound();

        $this->product->update(['is_active' => false]);
        $this->get('/san-pham/serum-niacinamide-10')->assertNotFound();

        $this->product->update(['is_active' => true]);
        $this->product->delete();
        $this->get('/san-pham/serum-niacinamide-10')->assertNotFound();
    });
});
