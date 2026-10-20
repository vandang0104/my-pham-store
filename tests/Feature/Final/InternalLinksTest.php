<?php

use App\Models\Order;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->seed(DatabaseSeeder::class);
});

/**
 * Lấy mọi link nội bộ (href cùng host hoặc bắt đầu bằng "/") trong HTML; bỏ neo "#", mailto/tel, file /storage và /build.
 *
 * @return list<string> đường dẫn (path + query)
 */
function linkNoiBo(string $html): array
{
    preg_match_all('/<a\s[^>]*href="([^"]+)"/i', $html, $m);
    $host = parse_url(config('app.url'), PHP_URL_HOST);

    return collect($m[1])
        ->map(fn (string $href) => html_entity_decode($href))
        ->filter(function (string $href) use ($host) {
            if (str_starts_with($href, '#') || preg_match('/^(mailto|tel|javascript):/i', $href)) {
                return false;
            }
            $linkHost = parse_url($href, PHP_URL_HOST);

            return $linkHost === null || $linkHost === $host;
        })
        ->map(function (string $href) {
            $path = parse_url($href, PHP_URL_PATH) ?: '/';
            $query = parse_url($href, PHP_URL_QUERY);

            return $path.($query ? '?'.$query : '');
        })
        ->reject(fn (string $path) => str_starts_with($path, '/storage/') || str_starts_with($path, '/build/'))
        ->unique()
        ->values()
        ->all();
}

/** Mở các trang gốc, gom link nội bộ rồi GET từng link: không link nào 404 / 500 */
function kiemLink($test, array $startPages): array
{
    $links = collect($startPages)
        ->flatMap(fn (string $page) => linkNoiBo($test->get($page)->assertOk()->getContent()))
        ->unique()
        ->values();

    $broken = $links->mapWithKeys(fn (string $link) => [$link => $test->get($link)->getStatusCode()])
        ->filter(fn (int $status) => in_array($status, [404, 500], true));

    expect($links)->not->toBeEmpty()->and($broken->all())->toBe([]);

    return $links->all();
}

describe('link nội bộ không gãy', function () {
    it('khách vãng lai: trang chủ, header, footer, danh sách và chi tiết sản phẩm', function () {
        $links = kiemLink($this, ['/', '/san-pham', '/cau-hoi-thuong-gap', '/lien-he']);

        expect($links)->toContain('/gio-hang', '/cau-hoi-thuong-gap', '/lien-he');
    });

    it('khách hàng đăng nhập: khu tài khoản và chi tiết đơn của mình', function () {
        $user = User::query()->whereHas('orders')->where('role', 'customer')->firstOrFail();
        $this->actingAs($user);

        kiemLink($this, ['/', '/tai-khoan', '/tai-khoan/don-hang', '/tai-khoan/thong-tin', '/tai-khoan/mat-khau']);
    });

    it('nhân viên: sidebar và các trang quản trị được phép', function () {
        $this->actingAs(User::query()->where('role', 'staff')->firstOrFail());

        kiemLink($this, ['/admin', '/admin/products', '/admin/categories', '/admin/orders', '/admin/chats', '/admin/contacts']);
    });

    it('admin: sidebar đầy đủ (tài khoản, thống kê) và chi tiết đơn', function () {
        $this->actingAs(User::query()->where('role', 'admin')->firstOrFail());
        $order = Order::query()->firstOrFail();

        kiemLink($this, ['/admin', '/admin/users', '/admin/reports', '/admin/orders/'.$order->code]);
    });
});
