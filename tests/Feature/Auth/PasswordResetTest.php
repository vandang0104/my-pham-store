<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

const THONG_BAO_GUI = 'Nếu email tồn tại trong hệ thống, chúng tôi đã gửi hướng dẫn đặt lại mật khẩu. Vui lòng kiểm tra hộp thư.';
const THONG_BAO_TOKEN = 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.';

beforeEach(function () {
    Notification::fake();
    $this->user = User::factory()->create(['email' => 'lan@example.com', 'password' => 'MatKhauCu1']);
});

describe('quên mật khẩu', function () {
    it('hiển thị trang quên mật khẩu', function () {
        $this->get('/quen-mat-khau')->assertOk()->assertSee('Quên mật khẩu');
    });

    it('email tồn tại thì gửi mail đặt lại mật khẩu có liên kết tiếng Việt', function () {
        $this->from('/quen-mat-khau')->post('/quen-mat-khau', ['email' => 'lan@example.com'])
            ->assertRedirect('/quen-mat-khau')
            ->assertSessionHas('status', THONG_BAO_GUI);

        Notification::assertSentTo($this->user, ResetPassword::class, function (ResetPassword $notification) {
            $mail = $notification->toMail($this->user);

            return $mail->subject === 'Đặt lại mật khẩu'
                && str_contains($mail->actionUrl, '/dat-lai-mat-khau/'.$notification->token);
        });
    });

    it('email tồn tại và không tồn tại nhận cùng một phản hồi (chống dò tài khoản)', function () {
        $coThat = $this->from('/quen-mat-khau')->post('/quen-mat-khau', ['email' => 'lan@example.com']);
        $khongCo = $this->from('/quen-mat-khau')->post('/quen-mat-khau', ['email' => 'khongco@example.com']);

        expect($khongCo->status())->toBe($coThat->status())
            ->and($khongCo->headers->get('Location'))->toBe($coThat->headers->get('Location'));
        $khongCo->assertSessionHas('status', THONG_BAO_GUI)->assertSessionHasNoErrors();
        Notification::assertSentTimes(ResetPassword::class, 1);
    });
});

describe('đặt lại mật khẩu', function () {
    it('hiển thị form đặt lại kèm email từ liên kết', function () {
        $this->get('/dat-lai-mat-khau/token-mau?email=lan@example.com')
            ->assertOk()
            ->assertSee('value="lan@example.com"', false)
            ->assertSee('value="token-mau"', false);
    });

    it('token đúng thì đổi mật khẩu, chuyển về đăng nhập và đăng nhập được bằng mật khẩu mới', function () {
        $token = Password::createToken($this->user);

        $this->post('/dat-lai-mat-khau', [
            'token' => $token, 'email' => 'lan@example.com',
            'password' => 'MatKhauMoi2', 'password_confirmation' => 'MatKhauMoi2',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Mật khẩu của bạn đã được đặt lại. Vui lòng đăng nhập bằng mật khẩu mới.');

        expect(Hash::check('MatKhauMoi2', $this->user->fresh()->password))->toBeTrue();

        $this->post('/dang-nhap', ['email' => 'lan@example.com', 'password' => 'MatKhauMoi2']);
        $this->assertAuthenticatedAs($this->user);
    });

    it('token chỉ dùng được một lần', function () {
        $token = Password::createToken($this->user);
        $data = ['token' => $token, 'email' => 'lan@example.com', 'password' => 'MatKhauMoi2', 'password_confirmation' => 'MatKhauMoi2'];

        $this->post('/dat-lai-mat-khau', $data)->assertSessionHasNoErrors();
        $this->post('/dat-lai-mat-khau', ['password' => 'MatKhauKhac3', 'password_confirmation' => 'MatKhauKhac3'] + $data)
            ->assertSessionHasErrors(['email' => THONG_BAO_TOKEN]);
    });

    it('token sai, hết hạn hoặc email không khớp thì báo lỗi và giữ nguyên mật khẩu', function (string $case) {
        $token = Password::createToken($this->user);
        $data = ['token' => $token, 'email' => 'lan@example.com', 'password' => 'MatKhauMoi2', 'password_confirmation' => 'MatKhauMoi2'];

        match ($case) {
            'token sai' => $data['token'] = 'token-gia-mao',
            'email không tồn tại' => $data['email'] = 'khongco@example.com',
            'hết hạn' => $this->travel(61)->minutes(),
        };

        $this->post('/dat-lai-mat-khau', $data)->assertSessionHasErrors(['email' => THONG_BAO_TOKEN]);

        expect(Hash::check('MatKhauCu1', $this->user->fresh()->password))->toBeTrue();
    })->with(['token sai', 'email không tồn tại', 'hết hạn']);

    it('báo lỗi khi mật khẩu mới quá ngắn hoặc không khớp', function () {
        $token = Password::createToken($this->user);

        $this->post('/dat-lai-mat-khau', ['token' => $token, 'email' => 'lan@example.com', 'password' => 'ngan', 'password_confirmation' => 'khac'])
            ->assertSessionHasErrors('password');
    });
});
