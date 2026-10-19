<?php

use App\Enums\SkinType;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
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
    it('trả 200 và có đủ section bắt buộc theo thứ tự DESIGN.md §10.1', function () {
        Banner::factory()->create();

        $html = $this->get('/')->assertOk()->getContent();

        $ids = ['home-title', 'featured-title', 'categories-title', 'sale-title', 'story-title', 'invite-title'];
        $positions = array_map(fn (string $id) => strpos($html, 'id="'.$id.'"'), $ids);

        expect($positions)->not->toContain(false)
            ->and($positions)->toBe(collect($positions)->sort()->values()->all())
            ->and($html)->toContain('Sản phẩm nổi bật', 'Danh mục', 'Đang ưu đãi', 'Thư từ Mộc Lam', 'Xem sản phẩm theo loại da')
            ->and($html)->toContain('site-footer');
    });

    it('chỉ có một h1, nằm trong hero', function () {
        Banner::factory()->count(2)->create();

        $html = $this->get('/')->getContent();

        expect(substr_count($html, '<h1'))->toBe(1)
            ->and($html)->toMatch('/<section class="home-hero"[^>]*>.*<h1 id="home-title"/s');
    });

    it('không có ô đăng ký nhận tin (bản gọn của mục Câu chuyện)', function () {
        $this->get('/')->assertDontSee('type="email"', false)->assertDontSee('nhận tin');
    });
});

describe('hero', function () {
    it('không có banner → mảng ngọc nhạt phẳng và tiêu đề mặc định', function () {
        $this->get('/')
            ->assertSee('home-hero__media--empty', false)
            ->assertSee('Mỹ phẩm thảo mộc Việt')
            ->assertDontSee('<picture', false)
            ->assertDontSee('data-hero-pause', false);
    });

    it('một banner: ảnh <picture> desktop/mobile, tiêu đề banner là h1, không crossfade', function () {
        Banner::factory()->create(['title' => 'Dưỡng da mùa thu', 'link' => '/danh-muc/cham-soc-da-mat']);

        $this->get('/')
            ->assertSee('<h1 id="home-title" class="home-hero__title" data-hero-title>Dưỡng da mùa thu</h1>', false)
            ->assertSee('<source media="(min-width: 992px)"', false)
            ->assertSee('fetchpriority="high"', false)
            ->assertSee('href="'.url('/danh-muc/cham-soc-da-mat').'"', false)
            ->assertDontSee('data-hero-pause', false);
    });

    it('từ 2 banner: đủ slide theo sort_order, ảnh sau lazy, có nút dừng chuyển ảnh', function () {
        Banner::factory()->create(['title' => 'Thứ hai', 'sort_order' => 2]);
        Banner::factory()->create(['title' => 'Thứ nhất', 'sort_order' => 1]);

        $html = $this->get('/')->getContent();

        expect(substr_count($html, 'data-hero-slide'))->toBe(2)
            ->and(strpos($html, 'data-title="Thứ nhất"'))->toBeLessThan(strpos($html, 'data-title="Thứ hai"'))
            ->and($html)->toContain('loading="lazy"', 'data-hero-pause', 'Dừng chuyển ảnh');
    });

    it('bỏ banner đang tắt, hết hạn, chưa tới giờ hoặc khác vị trí', function () {
        Banner::factory()->inactive()->create(['title' => 'Tắt']);
        Banner::factory()->create(['title' => 'Hết hạn', 'ends_at' => now()->subDay()]);
        Banner::factory()->create(['title' => 'Chưa chạy', 'starts_at' => now()->addDay()]);
        Banner::factory()->create(['title' => 'Đầu danh mục', 'position' => 'category_top']);

        $this->get('/')
            ->assertSee('home-hero__media--empty', false)
            ->assertDontSee('Tắt')
            ->assertDontSee('Hết hạn')
            ->assertDontSee('Chưa chạy')
            ->assertDontSee('Đầu danh mục');
    });
});

describe('đang ưu đãi', function () {
    it('tối đa 8 sản phẩm, sắp theo % giảm giảm dần, link Xem tất cả lọc khuyến mãi', function () {
        $html = $this->get('/')->getContent();
        $section = str($html)->after('id="sale-title"')->before('id="cau-chuyen"')->toString();

        preg_match_all('/−(\d+)%/u', $section, $matches);
        $percents = array_map('intval', $matches[1]);

        expect(count($percents))->toBe(min(8, Product::query()->active()->onSale()->count()))
            ->and($percents)->toBe(collect($percents)->sortDesc()->values()->all())
            ->and($html)->toContain('href="'.url('/san-pham?khuyen-mai=1').'"');
    });

    it('không có sản phẩm giảm giá → ẩn cả section', function () {
        Product::query()->update(['sale_price' => null]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('id="sale-title"', false)
            ->assertDontSee('home-sale', false);
    });

    it('bỏ sản phẩm đang tắt', function () {
        $hidden = Product::query()->active()->onSale()->orderByDiscount()->first();
        $hidden->update(['is_active' => false]);

        $this->get('/')->assertDontSee($hidden->name);
    });
});

describe('sản phẩm nổi bật', function () {
    it('tối đa 4 sản phẩm nổi bật, mỗi danh mục một sản phẩm, chỉ sản phẩm đầu hiện khi chưa có JS', function () {
        $html = $this->get('/')->getContent();

        $tabs = substr_count($html, 'class="featured__tab"');
        $hiddenPanels = preg_match_all('/data-tab-panel[^>]*hidden/', $html);

        expect($tabs)->toBeGreaterThan(0)->toBeLessThanOrEqual(4)
            ->and($hiddenPanels)->toBe($tabs - 1);
    });

    it('không có sản phẩm nổi bật → ẩn khối', function () {
        Product::query()->update(['is_featured' => false]);

        $this->get('/')->assertOk()->assertDontSee('id="featured-title"', false);
    });
});

describe('danh mục', function () {
    it('8 thanh danh mục cha mở bằng details, có danh mục con và số sản phẩm đang bán', function () {
        $root = Category::query()->roots()->orderBy('sort_order')->first();
        $expected = Product::query()->active()->inCategory($root)->count();

        $html = $this->get('/')->getContent();

        expect(substr_count($html, '<details class="cat-bar'))->toBe(Category::query()->active()->roots()->count())
            ->and($html)->toContain($root->name, $expected.' sản phẩm', 'href="'.url('/danh-muc/'.$root->children->first()->slug).'"');
    });

    it('cache khối danh mục chỉ giữ dữ liệu thô (không URL tuyệt đối) và xoá khi sản phẩm/danh mục đổi', function () {
        $this->get('/');
        $key = config('home.cache.categories.key');

        expect(Cache::has($key))->toBeTrue()
            ->and(json_encode(Cache::get($key)))->not->toContain('http');

        Product::query()->first()->update(['is_active' => false]);
        expect(Cache::has($key))->toBeFalse();

        $this->get('/');
        Category::query()->first()->update(['name' => 'Đổi tên']);
        expect(Cache::has($key))->toBeFalse();
    });

    it('cache banner xoá khi banner đổi', function () {
        $banner = Banner::factory()->create(['title' => 'Cũ']);
        $this->get('/')->assertSee('Cũ');

        $banner->update(['title' => 'Mới']);

        $this->get('/')->assertSee('Mới')->assertDontSee('>Cũ<', false);
    });
});

describe('hiệu năng', function () {
    it('không N+1: không lazy load và số truy vấn không tăng theo số sản phẩm', function () {
        Banner::factory()->count(3)->create();
        Model::preventLazyLoading();

        $cold = homeQueryCount($this);
        $warm = homeQueryCount($this);

        // Thêm sản phẩm giảm giá + nổi bật ở danh mục mới: số truy vấn không đổi
        Product::factory()->count(6)->onSale()->featured()->create();
        Cache::flush();
        $coldMore = homeQueryCount($this);

        expect($warm)->toBeLessThan($cold)
            ->and($coldMore)->toBe($cold)
            ->and($cold)->toBeLessThanOrEqual(15);
    });
});

describe('lời mời đảo màu', function () {
    it('khách chưa lưu loại da → /san-pham; đã lưu → lọc theo loại da đó', function () {
        $this->get('/')->assertSee('href="'.url('/san-pham').'" class="btn btn-champagne"', false);

        $user = User::factory()->create(['skin_type' => SkinType::Oily]);

        $this->actingAs($user)->get('/')
            ->assertSee('href="'.url('/san-pham?loai-da='.SkinType::Oily->value).'" class="btn btn-champagne"', false);
    });
});
