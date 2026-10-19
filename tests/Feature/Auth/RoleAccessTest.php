<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('middleware khu quản trị', function () {
    it('chuyển khách vãng lai về trang đăng nhập', function () {
        $this->get('/admin')->assertRedirect(route('login'));
    });

    it('trả 403 cho khách hàng', function () {
        $this->actingAs(User::factory()->customer()->create())->get('/admin')->assertForbidden();
    });

    it('cho admin và staff vào dashboard, hiện tên và vai trò', function (string $role, string $label) {
        $user = User::factory()->{$role}()->create(['name' => 'Lê Thu Hà']);

        $this->actingAs($user)->get('/admin')->assertOk()->assertSee('Lê Thu Hà')->assertSee($label);
    })->with([
        'admin' => ['admin', 'Quản trị viên'],
        'staff' => ['staff', 'Nhân viên'],
    ]);

    it('chỉ admin vào được mục nhân viên và cài đặt', function (string $uri) {
        $this->actingAs(User::factory()->staff()->create())->get($uri)->assertForbidden();
        $this->actingAs(User::factory()->admin()->create())->get($uri)->assertOk();
    })->with(['/admin/staff', '/admin/settings']);
});

describe('tài khoản bị khoá', function () {
    it('bị đăng xuất ở request kế tiếp', function () {
        $user = User::factory()->customer()->create();
        $this->actingAs($user)->get('/')->assertOk();

        $user->forceFill(['is_active' => false])->save();

        $this->get('/')->assertRedirect(route('login'))->assertSessionHasErrors('email');
        $this->assertGuest();
    });
});

describe('chuyển hướng sau đăng nhập', function () {
    it('đưa từng vai trò về đúng khu vực', function (string $role, string $expected) {
        $user = User::factory()->{$role}()->create(['password' => 'MatKhau123']);

        $this->post('/dang-nhap', ['email' => $user->email, 'password' => 'MatKhau123'])
            ->assertRedirect($expected);
    })->with([
        'admin' => ['admin', '/admin'],
        'staff' => ['staff', '/admin'],
        'customer' => ['customer', '/'],
    ]);

    it('đưa người đã đăng nhập vào trang đăng nhập về đúng khu vực', function () {
        $this->actingAs(User::factory()->staff()->create())->get('/dang-nhap')->assertRedirect(route('admin.dashboard'));
        $this->actingAs(User::factory()->customer()->create())->get('/dang-nhap')->assertRedirect(route('home'));
    });
});
