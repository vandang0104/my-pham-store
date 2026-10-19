<?php

use App\Enums\SkinType;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function sanPham(array $attrs = []): Product
{
    return Product::query()->findOrFail(taoSanPham($attrs));
}

describe('accessor giá', function () {
    it('lấy giá khuyến mãi khi nhỏ hơn giá gốc', function () {
        $p = sanPham(['price' => 400000, 'sale_price' => 300000]);

        expect($p->current_price)->toBe(300000)
            ->and($p->is_on_sale)->toBeTrue()
            ->and($p->discount_percent)->toBe(25)
            ->and($p->formatted_price)->toBe("300.000\u{00A0}₫")
            ->and($p->formatted_original_price)->toBe("400.000\u{00A0}₫");
    });

    it('lấy giá gốc khi không có hoặc giá khuyến mãi không thấp hơn', function (?int $sale) {
        $p = sanPham(['price' => 400000, 'sale_price' => $sale]);

        expect($p->current_price)->toBe(400000)
            ->and($p->is_on_sale)->toBeFalse()
            ->and($p->discount_percent)->toBe(0);
    })->with([
        'không có giá KM' => [null],
        'giá KM bằng giá gốc' => [400000],
        'giá KM cao hơn giá gốc' => [450000],
    ]);

    it('làm tròn phần trăm giảm', function () {
        expect(sanPham(['price' => 300000, 'sale_price' => 199000])->discount_percent)->toBe(34);
    });
});

describe('ảnh chính', function () {
    it('ưu tiên ảnh được đánh dấu chính', function () {
        $p = sanPham();
        $p->images()->create(['path' => 'products/phu.jpg', 'sort_order' => 0]);
        $p->images()->create(['path' => 'products/chinh.jpg', 'is_primary' => true, 'sort_order' => 5]);

        expect($p->primary_image->path)->toBe('products/chinh.jpg');
    });

    it('lấy ảnh đầu tiên theo thứ tự khi không có ảnh chính', function () {
        $p = sanPham();
        $p->images()->create(['path' => 'products/b.jpg', 'sort_order' => 2]);
        $p->images()->create(['path' => 'products/a.jpg', 'sort_order' => 1]);

        expect($p->primary_image->path)->toBe('products/a.jpg');
    });

    it('trả về null khi sản phẩm chưa có ảnh', function () {
        expect(sanPham()->primary_image)->toBeNull();
    });
});

describe('scopes', function () {
    it('lọc sản phẩm đang bán, nổi bật, đang giảm giá', function () {
        $thuong = sanPham(['price' => 200000]);
        $noiBat = sanPham(['price' => 200000, 'is_featured' => true]);
        $giamGia = sanPham(['price' => 200000, 'sale_price' => 150000]);
        $an = sanPham(['price' => 200000, 'is_active' => false]);
        sanPham(['price' => 200000, 'sale_price' => 250000]); // giá KM không hợp lệ

        expect(Product::active()->pluck('id'))->not->toContain($an->id)->toContain($thuong->id)
            ->and(Product::featured()->pluck('id')->all())->toBe([$noiBat->id])
            ->and(Product::onSale()->pluck('id')->all())->toBe([$giamGia->id]);
    });

    it('lọc khoảng giá theo giá hiện hành', function () {
        $re = sanPham(['price' => 100000]);
        $giamVaoKhoang = sanPham(['price' => 500000, 'sale_price' => 250000]);
        $dat = sanPham(['price' => 500000]);

        expect(Product::priceBetween(200000, 300000)->pluck('id')->all())->toBe([$giamVaoKhoang->id])
            ->and(Product::priceBetween(null, 260000)->pluck('id')->sort()->values()->all())->toBe([$re->id, $giamVaoKhoang->id])
            ->and(Product::priceBetween(400000)->pluck('id')->all())->toBe([$dat->id])
            ->and(Product::priceBetween()->count())->toBe(3);
    });

    it('tìm theo tên, SKU, mô tả và thương hiệu', function () {
        $brand = Brand::create(['name' => 'Cocoon', 'slug' => 'cocoon']);
        $theoTen = sanPham(['name' => 'Toner hoa hồng']);
        $theoSku = sanPham(['sku' => 'BHA-2PCT']);
        $theoMoTa = sanPham(['description' => 'Chứa niacinamide giúp sáng da']);
        $theoHang = sanPham(['brand_id' => $brand->id]);

        expect(Product::search('hoa hồng')->pluck('id')->all())->toBe([$theoTen->id])
            ->and(Product::search('bha-2pct')->pluck('id')->all())->toBe([$theoSku->id])
            ->and(Product::search('niacinamide')->pluck('id')->all())->toBe([$theoMoTa->id])
            ->and(Product::search('cocoon')->pluck('id')->all())->toBe([$theoHang->id])
            ->and(Product::search('  ')->count())->toBe(4);
    });

    it('lấy sản phẩm của danh mục kèm mọi danh mục con cháu', function () {
        $chamSocDa = Category::create(['name' => 'Chăm sóc da', 'slug' => 'cs-da']);
        $lamSach = Category::create(['name' => 'Làm sạch', 'slug' => 'lam-sach', 'parent_id' => $chamSocDa->id]);
        $tayTrang = Category::create(['name' => 'Tẩy trang', 'slug' => 'tay-trang', 'parent_id' => $lamSach->id]);
        $trangDiem = Category::create(['name' => 'Trang điểm', 'slug' => 'trang-diem']);

        $a = sanPham(['category_id' => $chamSocDa->id]);
        $b = sanPham(['category_id' => $lamSach->id]);
        $c = sanPham(['category_id' => $tayTrang->id]);
        sanPham(['category_id' => $trangDiem->id]);

        expect(Product::inCategory($chamSocDa)->pluck('id')->sort()->values()->all())->toBe([$a->id, $b->id, $c->id])
            ->and(Product::inCategory($lamSach->id)->pluck('id')->sort()->values()->all())->toBe([$b->id, $c->id])
            ->and(Product::inCategory($tayTrang)->pluck('id')->all())->toBe([$c->id]);
    });

    it('kết hợp được nhiều scope', function () {
        sanPham(['name' => 'Serum vitamin C', 'price' => 500000, 'sale_price' => 390000]);
        sanPham(['name' => 'Serum vitamin C mini', 'price' => 200000, 'is_active' => false]);

        expect(Product::active()->onSale()->search('vitamin')->priceBetween(300000, 400000)->count())->toBe(1);
    });
});

it('cast loại da sang enum', function () {
    expect(sanPham(['skin_type' => 'oily'])->skin_type)->toBe(SkinType::Oily);
});
