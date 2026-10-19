<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

/** Dữ liệu đăng ký hợp lệ, ghi đè từng trường khi cần */
function duLieuDangKy(array $overrides = []): array
{
    return $overrides + [
        'name' => 'Nguyễn Thị Lan',
        'email' => 'lan@example.com',
        'phone' => '0912345678',
        'password' => 'MatKhau123',
        'password_confirmation' => 'MatKhau123',
    ];
}

it('hiển thị trang đăng ký', function () {
    $this->get('/dang-ky')->assertOk()->assertSee('Đăng ký tài khoản');
});

it('đăng ký thành công thì tạo tài khoản khách hàng, tự đăng nhập và về trang chủ', function () {
    $this->post('/dang-ky', duLieuDangKy(['email' => 'Lan@Example.com ']))
        ->assertRedirect(route('home'))
        ->assertSessionHas('status', 'Đăng ký thành công. Chào mừng bạn đến với '.config('app.name').'!');

    $user = User::query()->where('email', 'lan@example.com')->firstOrFail();
    $this->assertAuthenticatedAs($user);
    expect($user->role)->toBe(UserRole::Customer)
        ->and(Hash::check('MatKhau123', $user->password))->toBeTrue();
});

it('chuẩn hoá số điện thoại dạng +84 và có dấu cách về dạng 0xxxxxxxxx', function (string $input) {
    $this->post('/dang-ky', duLieuDangKy(['phone' => $input]))->assertSessionHasNoErrors();

    expect(User::query()->where('email', 'lan@example.com')->value('phone'))->toBe('0912345678');
})->with(['+84912345678', '+84 912 345 678', '0912.345.678', '091-234-5678']);

it('báo lỗi khi email đã được đăng ký', function () {
    User::factory()->create(['email' => 'lan@example.com']);

    $this->post('/dang-ky', duLieuDangKy())
        ->assertSessionHasErrors(['email' => 'Email này đã được đăng ký.']);
    $this->assertGuest();
    expect(User::query()->where('email', 'lan@example.com')->count())->toBe(1);
});

it('báo lỗi khi mật khẩu quá ngắn hoặc nhập lại không khớp', function (array $input, string $message) {
    $this->post('/dang-ky', duLieuDangKy($input))->assertSessionHasErrors(['password' => $message]);
    $this->assertGuest();
})->with([
    'ít hơn 8 ký tự' => [['password' => 'abc123', 'password_confirmation' => 'abc123'], 'Mật khẩu phải có ít nhất 8 ký tự.'],
    'nhập lại không khớp' => [['password_confirmation' => 'KhacHoanToan1'], 'Xác nhận mật khẩu không khớp.'],
]);

it('báo lỗi khi số điện thoại không hợp lệ', function (string $phone) {
    $this->post('/dang-ky', duLieuDangKy(['phone' => $phone]))
        ->assertSessionHasErrors(['phone' => 'Số điện thoại phải gồm 10 chữ số, bắt đầu bằng 03, 05, 07, 08, 09 (hoặc +84).']);
})->with([
    'đầu số 01' => ['0123456789'],
    'đầu số 02' => ['0241234567'],
    'thiếu số' => ['091234567'],
    'thừa số' => ['09123456789'],
    'có chữ' => ['09123abcde'],
    '+84 sai đầu số' => ['+84212345678'],
]);

it('báo lỗi tiếng Việt khi bỏ trống các trường bắt buộc', function () {
    $this->post('/dang-ky', [])->assertSessionHasErrors([
        'name' => 'Họ tên không được để trống.',
        'email' => 'Email không được để trống.',
        'phone' => 'Số điện thoại không được để trống.',
        'password' => 'Mật khẩu không được để trống.',
    ]);
});

it('bỏ qua role=admin gửi kèm, tài khoản mới vẫn là customer', function () {
    $this->post('/dang-ky', duLieuDangKy(['role' => 'admin', 'is_active' => false]))->assertSessionHasNoErrors();

    $user = User::query()->where('email', 'lan@example.com')->firstOrFail();
    expect($user->role)->toBe(UserRole::Customer)
        ->and($user->is_active)->toBeTrue();
});
