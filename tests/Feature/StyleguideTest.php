<?php

use App\Http\Controllers\StyleguideController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

// Trang styleguide dùng layout chung (menu đọc từ DB) và sản phẩm mẫu
uses(RefreshDatabase::class);

it('không mở trang styleguide ngoài môi trường local', function () {
    expect(app()->environment('local'))->toBeFalse()
        ->and(Route::has('styleguide'))->toBeFalse();

    $this->get('/styleguide')->assertNotFound();
});

it('hiển thị đủ bảng màu, thang chữ và các thành phần dùng chung', function () {
    $html = app(StyleguideController::class)()->render();

    foreach (StyleguideController::colors() as $color) {
        expect($html)->toContain($color['hex'], $color['token']);
    }

    foreach (StyleguideController::typeSteps() as $step) {
        expect($html)->toContain('class="'.$step['class'].' mb-0"');
    }

    expect($html)
        ->toContain('Hệ thống thiết kế', 'btn btn-primary', 'btn btn-champagne', 'btn btn-text')
        ->toContain('form-label', 'form-required', 'invalid-feedback', 'form-check-input')
        ->toContain('flash flash--success', 'flash flash--error', 'flash flash--info')
        ->toContain('class="pagination"', 'class="breadcrumb"', 'link-nav')
        ->toContain('fonts.googleapis.com/css2?family=Be+Vietnam+Pro', 'Cormorant+Garamond', 'display=swap')
        ->not->toContain('form-floating');
});
