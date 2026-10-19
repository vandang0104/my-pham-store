<?php

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use Database\Seeders\CatalogSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->seed(CatalogSeeder::class);
});

/** Số truy vấn của một lần GET / */
function homeQueryCount($test): int
{
    DB::flushQueryLog();
    DB::enableQueryLog();
    $test->get('/')->assertOk();
    $count = count(DB::getQueryLog());
    DB::disableQueryLog();

    return $count;
}

describe('trang chủ', function () {
    it('trả 200 và có đủ các khối theo thứ tự: hero, nổi bật, danh mục, đang ưu đãi, giới thiệu', function () {
        Banner::factory()->create();

        $html = $this->get('/')->assertOk()->getContent();

        $ids = ['home-title', 'featured-title', 'categories-title', 'sale-title', 'about-title'];
        $positions = array_map(fn (string $id) => strpos($html, 'id="'.$id.'"'), $ids);

        expect($positions)->not->toContain(false)
            ->and($positions)->toBe(collect($positions)->sort()->values()->all())
            ->and($html)->toContain('Sản phẩm nổi bật', 'Danh mục', 'Đang ưu đãi', 'Về Mộc Lam', 'site-footer');
    });

    it('chỉ có một h1, nằm trong hero', function () {
        Banner::factory()->create();

        $html = $this->get('/')->getContent();

        expect(substr_count($html, '<h1'))->toBe(1)
            ->and($html)->toMatch('/<section class="home-hero"[^>]*>.*<h1 id="home-title"/s');
    });

    it('không còn hiệu ứng đã cắt: crossfade, tab nổi bật, reveal, đánh giá sao', function () {
        Banner::factory()->count(2)->create();

        $this->get('/')
            ->assertDontSee('data-hero-slide', false)
            ->assertDontSee('data-hero-pause', false)
            ->assertDontSee('data-tabs', false)
            ->assertDontSee('data-reveal', false)
            ->assertDontSee('class="rating', false);
    });
});

describe('hero', function () {
    it('không có banner → mảng ngọc nhạt phẳng và tiêu đề mặc định', function () {
        $this->get('/')
            ->assertSee('home-hero__media--empty', false)
            ->assertSee('Mỹ phẩm thảo mộc Việt')
            ->assertDontSee('<picture', false);
    });

    it('nhiều banner → chỉ hiện banner đầu theo sort_order, ảnh desktop/mobile qua <picture>', function () {
        Banner::factory()->create(['title' => 'Banner thứ hai', 'sort_order' => 2]);
        Banner::factory()->create(['title' => 'Banner thứ nhất', 'sort_order' => 1, 'link' => '/danh-muc/cham-soc-da-mat']);

        $html = $this->get('/')->getContent();

        expect(substr_count($html, '<picture'))->toBe(1)
            ->and($html)->toContain('<h1 id="home-title" class="home-hero__title">Banner thứ nhất</h1>')
            ->and($html)->toContain('<source media="(min-width: 992px)"', 'fetchpriority="high"', 'href="'.url('/danh-muc/cham-soc-da-mat').'"')
            ->not->toContain('Banner thứ hai');
    });

    it('bỏ banner đang tắt, hết hạn, chưa tới giờ hoặc khác vị trí', function () {
        Banner::factory()->inactive()->create(['title' => 'Tắt']);
        Banner::factory()->create(['title' => 'Hết hạn', 'ends_at' => now()->subDay()]);
        Banner::factory()->create(['title' => 'Chưa chạy', 'starts_at' => now()->addDay()]);
        Banner::factory()->create(['title' => 'Đầu danh mục', 'position' => 'category_top']);

        $this->get('/')->assertSee('home-hero__media--empty', false);
    });
});

describe('sản phẩm nổi bật', function () {
    it('lưới tối đa 8 thẻ sản phẩm is_featured', function () {
        Product::factory()->count(10)->featured()->create();

        $html = $this->get('/')->getContent();
        $section = str($html)->after('id="featured-title"')->before('id="categories-title"')->toString();

        expect(substr_count($section, '<article class="product-card'))->toBe(8);
    });

    it('không có sản phẩm nổi bật → ẩn khối', function () {
        Product::query()->update(['is_featured' => false]);

        $this->get('/')->assertOk()->assertDontSee('id="featured-title"', false);
    });
});

describe('danh mục', function () {
    it('liệt kê danh mục cha đang bật, danh mục con và số sản phẩm đang bán', function () {
        $root = Category::query()->roots()->orderBy('sort_order')->first();
        $child = $root->children->first();
        $expected = Product::query()->active()->where('category_id', $child->id)->count();

        $html = $this->get('/')->getContent();

        expect(substr_count($html, 'class="home-category"'))->toBe(Category::query()->active()->roots()->count())
            ->and($html)->toContain('href="'.url('/danh-muc/'.$root->slug).'"', 'href="'.url('/danh-muc/'.$child->slug).'"')
            ->and($html)->toMatch('/'.preg_quote(e($child->name), '/').'<\/a>\s*<span class="home-category__count">'.$expected.'<\/span>/');
    });
});

describe('đang ưu đãi', function () {
    it('tối đa 8 sản phẩm, sắp theo % giảm giảm dần, link Xem tất cả lọc khuyến mãi', function () {
        $html = $this->get('/')->getContent();
        $section = str($html)->after('id="sale-title"')->before('id="about-title"')->toString();

        preg_match_all('/−(\d+)%/u', $section, $matches);
        $percents = array_map('intval', $matches[1]);

        expect(count($percents))->toBe(min(8, Product::query()->active()->onSale()->count()))
            ->and($percents)->toBe(collect($percents)->sortDesc()->values()->all())
            ->and($html)->toContain('href="'.url('/san-pham?khuyen-mai=1').'"');
    });

    it('không có sản phẩm giảm giá → ẩn cả section', function () {
        Product::query()->update(['sale_price' => null]);

        $this->get('/')->assertOk()->assertDontSee('id="sale-title"', false);
    });
});

describe('hiệu năng và ảnh', function () {
    it('không N+1: không lazy load và số truy vấn không tăng theo số sản phẩm', function () {
        Banner::factory()->create();
        Model::preventLazyLoading();

        $before = homeQueryCount($this);
        Product::factory()->count(6)->onSale()->featured()->create();
        $after = homeQueryCount($this);

        expect($after)->toBe($before)->toBeLessThanOrEqual(15);
    });

    it('ảnh sản phẩm và banner là đường dẫn tương đối /storage, không dính http://localhost', function () {
        Banner::factory()->create();

        $html = $this->get('https://demo.trycloudflare.com/')->getContent();

        expect($html)->toContain('src="/storage/', 'srcset="/storage/')
            ->not->toContain('http://localhost');
    });
});
