<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->seed(CatalogSeeder::class);
});

describe('khách vãng lai', function () {
    it('có skip link, các landmark và tên thương hiệu', function () {
        $this->get('/')
            ->assertOk()
            ->assertSee('href="#main"', false)
            ->assertSee('Bỏ qua đến nội dung chính')
            ->assertSee('<header class="site-header"', false)
            ->assertSee('<main id="main"', false)
            ->assertSee('<footer class="site-footer', false)
            ->assertSee('aria-label="Mộc Lam"', false)
            ->assertSee('favicon.svg', false)
            ->assertSee('Mọi quyền được bảo lưu');
    });

    it('hiển thị 5 nhóm menu với danh mục và thương hiệu lấy từ DB', function () {
        $response = $this->get('/');

        foreach (config('navigation.groups') as $group) {
            $response->assertSee($group['label']);
        }
        $response->assertSee('Serum – Tinh chất')
            ->assertSee('Chống nắng da mặt')
            ->assertSee(Brand::query()->first()->name)
            ->assertSee(url('/danh-muc/serum-tinh-chat'), false);
    });

    it('có link đăng nhập, đăng ký và không có menu tài khoản', function () {
        $this->get('/')
            ->assertSee(route('login'), false)
            ->assertSee(route('register'), false)
            ->assertDontSee('Xin chào,')
            ->assertDontSee('Đăng xuất');
    });

    it('trang đăng nhập dùng layout chung', function () {
        $this->get('/dang-nhap')
            ->assertOk()
            ->assertSee('<header class="site-header"', false)
            ->assertSee('<footer class="site-footer', false);
    });
});

describe('đã đăng nhập', function () {
    it('khách hàng thấy menu tài khoản và nút đăng xuất, không thấy trang quản trị', function () {
        $user = User::factory()->customer()->create(['name' => 'Nguyễn Thị Lan']);

        $this->actingAs($user)->get('/')
            ->assertOk()
            ->assertSee('Xin chào, Nguyễn Thị Lan')
            ->assertSee('Đăng xuất')
            ->assertSee(route('logout'), false)
            ->assertDontSee('Trang quản trị');
    });

    it('nhân viên thấy thêm lối vào trang quản trị', function () {
        $this->actingAs(User::factory()->staff()->create())->get('/')
            ->assertSee('Trang quản trị')
            ->assertSee(route('admin.dashboard'), false);
    });
});

describe('flash message', function () {
    it('thông báo thành công dùng role="status", lỗi dùng role="alert"', function () {
        $this->withSession(['status' => 'Đăng nhập thành công.', 'error' => 'Mã giảm giá đã hết lượt.'])
            ->get('/')
            ->assertSeeInOrder(['role="alert"', 'Mã giảm giá đã hết lượt.'], false)
            ->assertSeeInOrder(['role="status"', 'Đăng nhập thành công.'], false);
    });

    it('không có thông báo thì không có khối flash', function () {
        $this->get('/')->assertDontSee('flash-stack', false);
    });
});

describe('menu được cache', function () {
    it('lần đầu dựng menu bằng số truy vấn cố định, các lần sau không truy vấn danh mục', function () {
        Cache::flush();
        $count = function () {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->get('/')->assertOk();

            return collect(DB::getQueryLog())
                ->filter(fn (array $q) => preg_match('/from "(categories|brands)"/', $q['query']))
                ->count();
        };

        expect($count())->toBe(3)   // danh mục cha, danh mục con (nạp sẵn), thương hiệu
            ->and($count())->toBe(0);
    });

    it('xoá cache khi sửa danh mục', function () {
        $this->get('/');
        expect(Cache::has(config('navigation.cache_key')))->toBeTrue();

        Category::query()->first()->update(['name' => 'Chăm sóc da mặt mới']);

        expect(Cache::has(config('navigation.cache_key')))->toBeFalse();
    });
});

describe('badge giỏ hàng', function () {
    it('ẩn khi giỏ có 0 sản phẩm (View Composer tạm trả 0)', function () {
        $this->get('/')->assertDontSee('class="cart-badge"', false);
    });

    it('hiện số lượng và chữ cho trình đọc màn hình khi lớn hơn 0', function () {
        $this->blade('<x-cart-badge :count="3" />')
            ->assertSee('class="cart-badge"', false)
            ->assertSee('3 sản phẩm trong giỏ');

        $this->blade('<x-cart-badge :count="120" />')->assertSee('99+');
        $this->blade('<x-cart-badge :count="0" />')->assertDontSee('cart-badge');
    });
});
