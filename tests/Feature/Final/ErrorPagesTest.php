<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

describe('trang lỗi tiếng Việt', function () {
    it('404: thông báo tiếng Việt, giữ header/footer và nút về trang chủ', function () {
        $this->get('/khong-co-trang-nay')
            ->assertNotFound()
            ->assertSee('Không tìm thấy trang')
            ->assertSee('Về trang chủ')
            ->assertSee('site-footer', false);
    });

    it('403: khách hàng vào khu quản trị', function () {
        $this->actingAs(User::factory()->customer()->create())->get('/admin')
            ->assertForbidden()
            ->assertSee('Bạn không có quyền truy cập');
    });

    it('419: phiên hết hạn (CSRF — test tự bỏ kiểm CSRF nên dựng bằng abort)', function () {
        Route::get('/_test/het-han', fn () => abort(419))->middleware('web');

        $this->get('/_test/het-han')->assertStatus(419)->assertSee('Phiên làm việc đã hết hạn');
    });

    it('500: trang đứng riêng tiếng Việt, không lộ chi tiết lỗi', function () {
        config(['app.debug' => false]);
        Route::get('/_test/loi', fn () => throw new RuntimeException('bí mật nội bộ'))->middleware('web');

        $this->get('/_test/loi')->assertStatus(500)->assertSee('Đã có lỗi xảy ra')->assertDontSee('bí mật nội bộ');
    });
});
