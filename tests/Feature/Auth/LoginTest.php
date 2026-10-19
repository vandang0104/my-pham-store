<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

const THONG_BAO_SAI = 'Email hoặc mật khẩu không đúng.';

beforeEach(function () {
    $this->user = User::factory()->customer()->create(['email' => 'lan@example.com', 'password' => 'MatKhau123']);
});

it('hiển thị trang đăng nhập', function () {
    $this->get('/dang-nhap')->assertOk()->assertSee('Đăng nhập')->assertSee('Ghi nhớ đăng nhập');
});

it('đăng nhập đúng email và mật khẩu thì vào được hệ thống', function () {
    $this->post('/dang-nhap', ['email' => 'lan@example.com', 'password' => 'MatKhau123'])
        ->assertRedirect(route('home'))
        ->assertSessionHas('status', 'Đăng nhập thành công.');

    $this->assertAuthenticatedAs($this->user);
});

it('không phân biệt hoa thường và khoảng trắng ở email', function () {
    $this->post('/dang-nhap', ['email' => '  LAN@Example.COM ', 'password' => 'MatKhau123']);

    $this->assertAuthenticatedAs($this->user);
});

it('sai mật khẩu thì báo lỗi chung và không đăng nhập', function () {
    $this->post('/dang-nhap', ['email' => 'lan@example.com', 'password' => 'SaiMatKhau'])
        ->assertSessionHasErrors(['email' => THONG_BAO_SAI]);

    $this->assertGuest();
});

it('email không tồn tại thì báo đúng câu lỗi như sai mật khẩu (không lộ tài khoản)', function () {
    $this->post('/dang-nhap', ['email' => 'khongco@example.com', 'password' => 'MatKhau123'])
        ->assertSessionHasErrors(['email' => THONG_BAO_SAI]);

    $this->assertGuest();
});

it('tài khoản bị khoá không đăng nhập được dù đúng mật khẩu', function () {
    $this->user->forceFill(['is_active' => false])->save();

    $this->post('/dang-nhap', ['email' => 'lan@example.com', 'password' => 'MatKhau123'])
        ->assertSessionHasErrors(['email' => THONG_BAO_SAI]);

    $this->assertGuest();
});

it('sai quá 5 lần trong 1 phút thì bị chặn, kể cả lần sau nhập đúng mật khẩu', function () {
    foreach (range(1, 5) as $i) {
        $this->post('/dang-nhap', ['email' => 'lan@example.com', 'password' => "Sai{$i}"])
            ->assertSessionHasErrors(['email' => THONG_BAO_SAI]);
    }

    $this->post('/dang-nhap', ['email' => 'lan@example.com', 'password' => 'MatKhau123'])
        ->assertSessionHasErrors('email');

    expect(session('errors')->first('email'))->toStartWith('Bạn đã thử quá nhiều lần. Vui lòng thử lại sau');
    $this->assertGuest();
});

it('được đăng nhập lại sau khi hết thời gian chặn', function () {
    foreach (range(1, 5) as $i) {
        $this->post('/dang-nhap', ['email' => 'lan@example.com', 'password' => "Sai{$i}"]);
    }

    $this->travel(61)->seconds();

    $this->post('/dang-nhap', ['email' => 'lan@example.com', 'password' => 'MatKhau123']);
    $this->assertAuthenticatedAs($this->user);
});

it('ghi nhớ đăng nhập thì tạo cookie remember và remember_token', function () {
    $this->post('/dang-nhap', ['email' => 'lan@example.com', 'password' => 'MatKhau123', 'remember' => '1'])
        ->assertCookie(Auth::guard('web')->getRecallerName());

    expect($this->user->fresh()->remember_token)->not->toBeNull();
});

it('không chọn ghi nhớ thì không tạo cookie remember', function () {
    $this->post('/dang-nhap', ['email' => 'lan@example.com', 'password' => 'MatKhau123'])
        ->assertCookieMissing(Auth::guard('web')->getRecallerName());
});

it('tạo session mới sau khi đăng nhập (chống session fixation)', function () {
    $this->get('/dang-nhap');
    $before = session()->getId();

    $this->post('/dang-nhap', ['email' => 'lan@example.com', 'password' => 'MatKhau123']);

    expect(session()->getId())->not->toBe($before);
});
