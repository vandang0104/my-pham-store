<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Cây danh mục: Chăm sóc da (cha) → Serum (con); Trang điểm (cha khác)
    $this->chamSocDa = Category::factory()->create(['name' => 'Chăm sóc da', 'slug' => 'cham-soc-da']);
    $this->serum = Category::factory()->childOf($this->chamSocDa)->create(['name' => 'Serum', 'slug' => 'serum']);
    $this->trangDiem = Category::factory()->create(['name' => 'Trang điểm', 'slug' => 'trang-diem']);

    $this->dermalis = Brand::factory()->create(['name' => 'Dermalis Lab', 'slug' => 'dermalis-lab']);
    $this->velvetine = Brand::factory()->create(['name' => 'Velvetine', 'slug' => 'velvetine']);

    $this->serumVitC = Product::factory()->for($this->serum)->for($this->dermalis)
        ->create(['name' => 'Serum Vitamin C', 'sku' => 'DML-001', 'price' => 400000, 'sale_price' => 320000]);
    $this->kemDuong = Product::factory()->for($this->chamSocDa)->for($this->velvetine)
        ->create(['name' => 'Kem dưỡng ẩm', 'sku' => 'VLV-001', 'price' => 250000]);
    $this->son = Product::factory()->for($this->trangDiem)->for($this->velvetine)
        ->create(['name' => 'Son kem lì', 'sku' => 'VLV-002', 'price' => 180000]);
});

/** Tên sản phẩm có trong trang danh sách (theo thứ tự xuất hiện) */
function tenTrongDanhSach(string $html): array
{
    preg_match_all('/class="product-card__link">([^<]+)</', $html, $m);

    return array_map('html_entity_decode', $m[1]);
}

describe('danh sách sản phẩm', function () {
    it('trả 200, hiện số kết quả và chỉ sản phẩm đang bán', function () {
        Product::factory()->inactive()->create(['name' => 'Sản phẩm đang ẩn']);
        Product::factory()->create(['name' => 'Sản phẩm đã xoá'])->delete();

        $html = $this->get('/san-pham')->assertOk()->assertSee('3 sản phẩm')->getContent();

        expect(tenTrongDanhSach($html))->toEqualCanonicalizing(['Serum Vitamin C', 'Kem dưỡng ẩm', 'Son kem lì']);
    });

    it('sắp xếp mới nhất trước', function () {
        $moi = Product::factory()->create(['name' => 'Sản phẩm mới nhất', 'created_at' => now()->addMinute()]);

        expect(tenTrongDanhSach($this->get('/san-pham')->getContent())[0])->toBe($moi->name);
    });

    it('phân trang 12 sản phẩm/trang và giữ tham số lọc trên link trang', function () {
        Product::factory()->count(20)->for($this->velvetine)->create(['price' => 100000]);

        $response = $this->get('/san-pham?thuong-hieu[]=velvetine&gia-den=200000');
        $html = $response->assertOk()->getContent();

        preg_match('/href="([^"]*page=2[^"]*)"/', $html, $link);
        $query = [];
        parse_str(parse_url(html_entity_decode($link[1] ?? ''), PHP_URL_QUERY) ?? '', $query);

        expect(tenTrongDanhSach($html))->toHaveCount(12)
            // 20 SP mới + Son kem lì (180.000); Kem dưỡng 250.000 vượt giá đến
            ->and($html)->toContain('21 sản phẩm')
            ->and($query)->toMatchArray(['thuong-hieu' => ['velvetine'], 'gia-den' => '200000', 'page' => '2']);

        expect(tenTrongDanhSach($this->get('/san-pham?thuong-hieu[]=velvetine&gia-den=200000&page=2')->getContent()))->toHaveCount(9);
    });

    it('không có kết quả → hiện trạng thái trống với nút xoá bộ lọc', function () {
        $this->get('/san-pham?q=khongtontai')
            ->assertOk()
            ->assertSee('0 sản phẩm')
            ->assertSee('Không tìm thấy sản phẩm phù hợp')
            ->assertSee('Xoá bộ lọc');
    });
});

describe('tìm kiếm', function () {
    it('tìm theo tên, mã SKU và tên thương hiệu', function (string $q, array $expected) {
        expect(tenTrongDanhSach($this->get('/san-pham?q='.urlencode($q))->getContent()))->toEqualCanonicalizing($expected);
    })->with([
        'theo tên' => ['vitamin', ['Serum Vitamin C']],
        'theo SKU' => ['VLV-002', ['Son kem lì']],
        'theo thương hiệu' => ['velvetine', ['Kem dưỡng ẩm', 'Son kem lì']],
    ]);

    it('đổi tiêu đề thành "Kết quả tìm kiếm" và giữ từ khoá trong ô tìm', function () {
        $this->get('/san-pham?q=serum')
            ->assertSee('Kết quả tìm kiếm')
            ->assertSee('value="serum"', false);
    });
});

describe('lọc', function () {
    it('lọc danh mục cha gồm cả sản phẩm của danh mục con', function () {
        expect(tenTrongDanhSach($this->get('/san-pham?danh-muc=cham-soc-da')->getContent()))
            ->toEqualCanonicalizing(['Serum Vitamin C', 'Kem dưỡng ẩm']);
    });

    it('lọc một hoặc nhiều thương hiệu', function () {
        expect(tenTrongDanhSach($this->get('/san-pham?thuong-hieu[]=dermalis-lab')->getContent()))->toBe(['Serum Vitamin C'])
            ->and(tenTrongDanhSach($this->get('/san-pham?thuong-hieu=velvetine')->getContent()))->toEqualCanonicalizing(['Kem dưỡng ẩm', 'Son kem lì'])
            ->and(tenTrongDanhSach($this->get('/san-pham?thuong-hieu[]=dermalis-lab&thuong-hieu[]=velvetine')->getContent()))->toHaveCount(3);
    });

    it('lọc khoảng giá theo giá hiện hành (đã tính khuyến mãi)', function () {
        // Serum Vitamin C: giá gốc 400.000, đang bán 320.000
        expect(tenTrongDanhSach($this->get('/san-pham?gia-tu=200000&gia-den=330000')->getContent()))
            ->toEqualCanonicalizing(['Serum Vitamin C', 'Kem dưỡng ẩm'])
            ->and(tenTrongDanhSach($this->get('/san-pham?gia-den=200000')->getContent()))->toBe(['Son kem lì'])
            ->and(tenTrongDanhSach($this->get('/san-pham?gia-tu=300000')->getContent()))->toBe(['Serum Vitamin C']);
    });

    it('khuyen-mai=1 chỉ ra sản phẩm đang giảm giá', function () {
        expect(tenTrongDanhSach($this->get('/san-pham?khuyen-mai=1')->getContent()))->toBe(['Serum Vitamin C']);
    });

    it('kết hợp nhiều bộ lọc', function () {
        expect(tenTrongDanhSach($this->get('/san-pham?thuong-hieu[]=velvetine&danh-muc=trang-diem&q=son')->getContent()))->toBe(['Son kem lì']);
    });

    it('tham số sai bị bỏ qua, không lỗi', function (string $query, int $count) {
        $this->get('/san-pham?'.$query)->assertOk()->assertSee($count.' sản phẩm');
    })->with([
        'giá chữ' => ['gia-tu=abc&gia-den=xyz', 3],
        'giá âm' => ['gia-tu=-5', 3],
        'giá từ lớn hơn giá đến → bỏ giá đến' => ['gia-tu=300000&gia-den=100000', 1],
        'khuyến mãi khác 1' => ['khuyen-mai=2', 3],
        'từ khoá dạng mảng' => ['q[]=serum', 3],
        'thương hiệu dạng chuỗi lạ' => ['thuong-hieu[][]=x', 3],
    ]);

    it('slug danh mục / thương hiệu không tồn tại → không có kết quả', function () {
        $this->get('/san-pham?danh-muc=khong-co')->assertOk()->assertSee('0 sản phẩm');
        $this->get('/san-pham?thuong-hieu[]=khong-co')->assertOk()->assertSee('0 sản phẩm');
    });
});

describe('trang danh mục và thương hiệu', function () {
    it('trang danh mục cha: breadcrumb, tên, danh mục con, sản phẩm của cả con', function () {
        $html = $this->get('/danh-muc/cham-soc-da')->assertOk()->getContent();

        expect($html)->toContain('<h1 class="t-title mb-2">Chăm sóc da</h1>', 'href="'.route('categories.show', $this->serum).'"', 'class="breadcrumb"')
            ->and(tenTrongDanhSach($html))->toEqualCanonicalizing(['Serum Vitamin C', 'Kem dưỡng ẩm']);
    });

    it('trang danh mục con có breadcrumb về danh mục cha và vẫn lọc thêm được', function () {
        $this->get('/danh-muc/serum?gia-den=100000')
            ->assertOk()
            ->assertSee('href="'.route('categories.show', $this->chamSocDa).'"', false)
            ->assertSee('0 sản phẩm');
    });

    it('trang thương hiệu: tên, mô tả, chỉ sản phẩm của thương hiệu', function () {
        $html = $this->get('/thuong-hieu/velvetine')->assertOk()->getContent();

        expect($html)->toContain('<h1 class="t-title mb-2">Velvetine</h1>', e($this->velvetine->description))
            ->and(tenTrongDanhSach($html))->toEqualCanonicalizing(['Kem dưỡng ẩm', 'Son kem lì']);
    });

    it('danh mục / thương hiệu đang ẩn hoặc không tồn tại → 404', function () {
        $this->trangDiem->update(['is_active' => false]);
        $this->velvetine->update(['is_active' => false]);

        $this->get('/danh-muc/trang-diem')->assertNotFound();
        $this->get('/thuong-hieu/velvetine')->assertNotFound();
        $this->get('/danh-muc/khong-co')->assertNotFound();
    });
});

describe('hiệu năng', function () {
    it('không lazy load và số truy vấn không tăng theo số sản phẩm', function () {
        $count = function (string $uri) {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->get($uri)->assertOk();

            return count(DB::getQueryLog());
        };

        $before = $count('/san-pham?danh-muc=cham-soc-da');
        Product::factory()->count(8)->for($this->serum)->create();
        $after = $count('/san-pham?danh-muc=cham-soc-da');

        expect($after)->toBe($before);
    });
});
