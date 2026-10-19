<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('đăng xuất bằng POST thì thoát tài khoản và về trang chủ', function () {
    $this->actingAs(User::factory()->create());

    $this->post('/dang-xuat')
        ->assertRedirect(route('home'))
        ->assertSessionHas('status', 'Bạn đã đăng xuất.');

    $this->assertGuest();
});

it('không cho đăng xuất bằng GET', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/dang-xuat')->assertMethodNotAllowed();
    $this->assertAuthenticated();
});

it('làm mới CSRF token khi đăng xuất', function () {
    $this->actingAs(User::factory()->create());
    $this->get('/');
    $before = csrf_token();

    $this->post('/dang-xuat');

    expect(csrf_token())->not->toBe($before);
});
