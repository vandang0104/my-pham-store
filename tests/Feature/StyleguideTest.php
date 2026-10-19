<?php

use App\Http\Controllers\StyleguideController;
use App\View\Components\Icon;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// Trang styleguide dùng layout chung (menu đọc từ DB) và sản phẩm mẫu (thẻ, phân trang)
uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->seed(CatalogSeeder::class);
});

function renderStyleguide(): string
{
    return app(StyleguideController::class)()->render();
}

it('trả 404 ngoài môi trường local (route không được đăng ký)', function () {
    expect(app()->environment('local'))->toBeFalse()
        ->and(Route::has('styleguide'))->toBeFalse();

    $this->get('/styleguide')->assertNotFound();
});

it('có mục lục và đủ các mục theo thứ tự', function () {
    $html = renderStyleguide();

    foreach (StyleguideController::SECTIONS as $id => $label) {
        expect($html)->toContain('href="#'.$id.'"', 'id="'.$id.'"', e($label));
    }

    // Thứ tự: id các mục xuất hiện đúng như SECTIONS
    $positions = array_map(fn (string $id) => strpos($html, 'id="'.$id.'"'), array_keys(StyleguideController::SECTIONS));
    expect($positions)->toBe(collect($positions)->sort()->values()->all());
});

it('hiển thị đủ bảng màu, thang chữ, nút (kể cả disabled) và form có lỗi/gợi ý', function () {
    $html = renderStyleguide();

    foreach (StyleguideController::colors() as $color) {
        expect($html)->toContain($color['hex'], $color['token']);
    }

    foreach (StyleguideController::typeSteps() as $step) {
        expect($html)->toContain('class="'.$step['class'].' mb-0"');
    }

    expect($html)
        ->toContain('Hệ thống thiết kế', 'btn btn-primary', 'btn btn-champagne', 'btn btn-text', 'btn btn-danger-text')
        ->toMatch('/class="btn btn-primary" disabled/')
        ->toContain('form-label', 'form-required', 'is-invalid', 'invalid-feedback', 'form-text', 'form-check-input')
        ->toContain('fonts.googleapis.com/css2?family=Be+Vietnam+Pro', 'Cormorant+Garamond', 'display=swap')
        ->not->toContain('form-floating');
});

it('thẻ sản phẩm đủ trạng thái: giảm giá, giá thường, hết hàng', function () {
    $html = renderStyleguide();

    expect(substr_count($html, '<article class="product-card'))->toBe(4)
        ->and($html)->toContain('product-card__discount', 'price__current--sale', 'is-out-of-stock', 'product-card--tone-2')
        ->not->toContain('product-card__wish');
});

it('badge: giỏ ở các mức 0 (ẩn), 3, 12, 99+ và nhãn −20% đứng riêng', function () {
    $html = renderStyleguide();

    expect(substr_count($html, 'class="cart-badge'))->toBe(3)
        ->and($html)->toContain('3 sản phẩm trong giỏ', '12 sản phẩm trong giỏ', '120 sản phẩm trong giỏ', '>99+<', 'cart-badge cart-badge--wide')
        ->not->toContain('visually-hidden">0 sản phẩm trong giỏ')
        ->and($html)->toMatch('/<span class="discount-badge"\s*>−20%<\/span>/');
});

it('flash là "alert" của dự án: đủ ba loại, không dùng .alert của Bootstrap', function () {
    $html = renderStyleguide();

    expect($html)
        ->toContain('flash flash--success', 'flash flash--error', 'flash flash--info', 'role="alert"', 'role="status"')
        ->not->toContain('class="alert');
});

it('breadcrumb, phân trang, skeleton, trạng thái trống và bộ icon dùng component thật', function () {
    $html = renderStyleguide();

    expect($html)
        ->toContain('class="breadcrumb"', 'application/ld+json')
        ->toContain('class="pagination-nav"', 'class="pagination d-none d-md-flex"', 'Trang 1/')
        ->toContain('skeleton-card', 'skeleton-row', 'aria-busy="true"', 'Đang tải sản phẩm…')
        ->toContain('empty-state', 'Giỏ hàng của bạn đang trống');

    foreach (array_keys(Icon::PATHS) as $icon) {
        expect($html)->toContain('>'.$icon.'</figcaption>');
    }

    expect(Icon::PATHS)->not->toHaveKey('heart');
});
