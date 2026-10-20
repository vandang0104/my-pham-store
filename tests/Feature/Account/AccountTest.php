<?php

use App\Enums\OrderStatus;
use App\Enums\SkinType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->customer()->create([
        'name' => 'Nguyễn Thị Lan', 'email' => 'lan@example.com', 'phone' => '0912345678', 'password' => 'MatKhau123',
    ]);
});

/** Đơn của $user chứa $product × $qty, trạng thái $status */
function donCua(User $user, OrderStatus $status, Product $product, int $qty = 2): Order
{
    $order = Order::factory()->for($user)->status($status)->create();
    OrderItem::factory()->for($order)->for($product)->create(['quantity' => $qty]);
    $order->statusHistories()->create(['from_status' => null, 'to_status' => OrderStatus::Pending, 'note' => 'Khách đặt hàng.']);

    return $order;
}

describe('truy cập khu tài khoản', function () {
    it('khách chưa đăng nhập bị chuyển về trang đăng nhập', function (string $uri) {
        $this->get($uri)->assertRedirect(route('login'));
    })->with(['/tai-khoan', '/tai-khoan/thong-tin', '/tai-khoan/mat-khau', '/tai-khoan/don-hang']);

    it('trang tổng quan: lời chào, menu và tối đa 3 đơn gần nhất', function () {
        Order::factory()->count(4)->for($this->user)->create();

        $html = $this->actingAs($this->user)->get(route('account.dashboard'))->assertOk()
            ->assertSee('Xin chào, <strong>Nguyễn Thị Lan</strong>', false)
            ->assertSee('href="'.route('account.orders.index').'"', false)
            ->assertSee('href="'.route('account.password.edit').'"', false)
            ->getContent();

        expect(substr_count($html, 'href="'.url('/tai-khoan/don-hang').'/'))->toBe(3);
    });
});

describe('hồ sơ', function () {
    it('cập nhật họ tên, SĐT (chuẩn hoá), ngày sinh, loại da', function () {
        $this->actingAs($this->user)->put(route('account.profile.update'), [
            'name' => 'Nguyễn Thị Lan Anh', 'phone' => '+84 987 654 321', 'birthday' => '1998-05-20', 'skin_type' => 'oily',
        ])->assertRedirect(route('account.profile.edit'))->assertSessionHas('status', 'Đã cập nhật hồ sơ.');

        $user = $this->user->fresh();
        expect($user->name)->toBe('Nguyễn Thị Lan Anh')
            ->and($user->phone)->toBe('0987654321')
            ->and($user->birthday->format('Y-m-d'))->toBe('1998-05-20')
            ->and($user->skin_type)->toBe(SkinType::Oily);
    });

    it('báo lỗi khi SĐT sai, thiếu họ tên, ngày sinh trong tương lai, loại da lạ', function (array $data, string $field) {
        $this->actingAs($this->user)
            ->put(route('account.profile.update'), [...['name' => 'Lan', 'phone' => '0912345678'], ...$data])
            ->assertSessionHasErrors($field);
    })->with([
        'SĐT sai' => [['phone' => '012345'], 'phone'],
        'thiếu họ tên' => [['name' => ''], 'name'],
        'ngày sinh tương lai' => [['birthday' => now()->addDay()->format('Y-m-d')], 'birthday'],
        'loại da lạ' => [['skin_type' => 'xyz'], 'skin_type'],
    ]);

    it('không đổi được email (và vai trò) qua form hồ sơ', function () {
        $this->actingAs($this->user)->put(route('account.profile.update'), [
            'name' => 'Lan', 'phone' => '0912345678', 'email' => 'khac@example.com', 'role' => 'admin',
        ])->assertSessionHasNoErrors();

        expect($this->user->fresh()->email)->toBe('lan@example.com')
            ->and($this->user->fresh()->role->value)->toBe('customer');
    });

    it('trang hồ sơ hiện email dạng chỉ đọc và giá trị hiện tại', function () {
        $this->actingAs($this->user)->get(route('account.profile.edit'))
            ->assertOk()
            ->assertSee('value="lan@example.com" disabled', false)
            ->assertSee('value="0912345678"', false);
    });
});

describe('đổi mật khẩu', function () {
    it('đúng mật khẩu hiện tại: đổi được, vẫn đăng nhập, đăng nhập lại bằng mật khẩu mới', function () {
        $this->actingAs($this->user)->put(route('account.password.update'), [
            'current_password' => 'MatKhau123', 'password' => 'MatKhauMoi456', 'password_confirmation' => 'MatKhauMoi456',
        ])->assertRedirect(route('account.password.edit'))->assertSessionHas('status');

        $this->assertAuthenticatedAs($this->user);
        expect(Hash::check('MatKhauMoi456', $this->user->fresh()->password))->toBeTrue();
    });

    it('sai mật khẩu hiện tại hoặc mật khẩu mới không hợp lệ: không đổi', function (array $data, string $field) {
        $this->actingAs($this->user)->put(route('account.password.update'), $data)->assertSessionHasErrors($field);

        expect(Hash::check('MatKhau123', $this->user->fresh()->password))->toBeTrue();
    })->with([
        'sai mật khẩu hiện tại' => [['current_password' => 'SaiMatKhau', 'password' => 'MatKhauMoi456', 'password_confirmation' => 'MatKhauMoi456'], 'current_password'],
        'mật khẩu mới quá ngắn' => [['current_password' => 'MatKhau123', 'password' => 'abc', 'password_confirmation' => 'abc'], 'password'],
        'nhập lại không khớp' => [['current_password' => 'MatKhau123', 'password' => 'MatKhauMoi456', 'password_confirmation' => 'Khac'], 'password'],
        'trùng mật khẩu cũ' => [['current_password' => 'MatKhau123', 'password' => 'MatKhau123', 'password_confirmation' => 'MatKhau123'], 'password'],
    ]);
});

describe('đơn hàng của tôi', function () {
    it('danh sách chỉ gồm đơn của mình', function () {
        $cuaToi = Order::factory()->count(2)->for($this->user)->create();
        $nguoiKhac = Order::factory()->create();

        $this->actingAs($this->user)->get(route('account.orders.index'))
            ->assertOk()
            ->assertSee($cuaToi[0]->code)
            ->assertSee($cuaToi[1]->code)
            ->assertDontSee($nguoiKhac->code);
    });

    it('lọc theo trạng thái; trạng thái lạ thì bỏ qua bộ lọc; phân trang giữ bộ lọc', function () {
        $choXacNhan = Order::factory()->for($this->user)->status(OrderStatus::Pending)->create();
        $daGiao = Order::factory()->for($this->user)->status(OrderStatus::Delivered)->create();

        $this->actingAs($this->user)->get(route('account.orders.index', ['trang-thai' => 'delivered']))
            ->assertSee($daGiao->code)->assertDontSee($choXacNhan->code);

        $this->get(route('account.orders.index', ['trang-thai' => 'khong-co']))
            ->assertOk()->assertSee($daGiao->code)->assertSee($choXacNhan->code);

        Order::factory()->count(12)->for($this->user)->status(OrderStatus::Delivered)->create();
        $this->get(route('account.orders.index', ['trang-thai' => 'delivered']))
            ->assertSee('trang-thai=delivered&amp;page=2', false);
    });

    it('xem chi tiết đơn của mình: sản phẩm (snapshot), tổng, địa chỉ, thanh toán, lịch sử', function () {
        $sp = Product::factory()->create();
        $order = donCua($this->user, OrderStatus::Confirmed, $sp, 2);
        $item = $order->items()->first();

        $this->actingAs($this->user)->get(route('account.orders.show', $order))
            ->assertOk()
            ->assertSee($order->code)
            ->assertSee($item->product_name)
            ->assertSee($item->formatted_line_total)
            ->assertSee($order->formatted_total)
            ->assertSee($order->full_address)
            ->assertSee($order->payment_method->label())
            ->assertSee($order->payment_status->label())
            ->assertSee('Lịch sử trạng thái')
            ->assertSee('Huỷ đơn');
    });

    it('đơn của người khác → 404 (không lộ mã đơn)', function () {
        $order = Order::factory()->create();

        $this->actingAs($this->user)->get(route('account.orders.show', $order))->assertNotFound();
        $this->get('/tai-khoan/don-hang/MP-KHONG-CO')->assertNotFound();
    });
});

describe('huỷ đơn', function () {
    it('huỷ đơn chờ xác nhận / đã xác nhận: hoàn kho, trừ sold_count, ghi lịch sử, cancelled_at', function (OrderStatus $status) {
        $sp = Product::factory()->create(['stock' => 5, 'sold_count' => 7]);
        $order = donCua($this->user, $status, $sp, 2);

        $this->actingAs($this->user)->post(route('account.orders.cancel', $order))
            ->assertRedirect(route('account.orders.show', $order))
            ->assertSessionHas('status');

        $order->refresh();
        $last = $order->statusHistories()->get()->last();
        expect($order->status)->toBe(OrderStatus::Cancelled)
            ->and($order->cancelled_at)->not->toBeNull()
            ->and($sp->fresh()->stock)->toBe(7)
            ->and($sp->fresh()->sold_count)->toBe(5)
            ->and($last->from_status)->toBe($status)
            ->and($last->to_status)->toBe(OrderStatus::Cancelled)
            ->and($last->changed_by)->toBe($this->user->id);
    })->with([
        'chờ xác nhận' => [OrderStatus::Pending],
        'đã xác nhận' => [OrderStatus::Confirmed],
    ]);

    it('không huỷ được đơn đang giao / đã giao / đã huỷ: báo lỗi, kho không đổi, không có nút huỷ', function (OrderStatus $status) {
        $sp = Product::factory()->create(['stock' => 5]);
        $order = donCua($this->user, $status, $sp, 2);

        $this->actingAs($this->user)->get(route('account.orders.show', $order))->assertDontSee('Huỷ đơn');

        $this->from(route('account.orders.show', $order))->post(route('account.orders.cancel', $order))
            ->assertRedirect(route('account.orders.show', $order))
            ->assertSessionHas('error');

        expect($order->fresh()->status)->toBe($status)->and($sp->fresh()->stock)->toBe(5);
    })->with([
        'đang giao' => [OrderStatus::Shipping],
        'đã giao' => [OrderStatus::Delivered],
        'đã huỷ' => [OrderStatus::Cancelled],
    ]);

    it('khách không huỷ được đơn của người khác (404), đơn giữ nguyên', function () {
        $sp = Product::factory()->create(['stock' => 5]);
        $order = donCua(User::factory()->customer()->create(), OrderStatus::Pending, $sp, 2);

        $this->actingAs($this->user)->post(route('account.orders.cancel', $order))->assertNotFound();
        expect($order->fresh()->status)->toBe(OrderStatus::Pending)->and($sp->fresh()->stock)->toBe(5);
    });

    it('OrderService::cancel hoàn kho cả sản phẩm đã xoá mềm (dùng lại cho admin ở S9)', function () {
        $sp = Product::factory()->create(['stock' => 1, 'sold_count' => 2]);
        $order = donCua($this->user, OrderStatus::Pending, $sp, 2);
        $sp->delete();

        app(OrderService::class)->cancel($order, null, 'Admin huỷ.');

        expect(Product::withTrashed()->find($sp->id)->stock)->toBe(3)
            ->and($order->fresh()->statusHistories()->get()->last()->changed_by)->toBeNull();
    });
});

describe('header', function () {
    it('menu tài khoản trỏ tới trang tài khoản và đơn hàng', function () {
        $this->actingAs($this->user)->get('/')
            ->assertSee('href="'.route('account.dashboard').'"', false)
            ->assertSee('href="'.route('account.orders.index').'"', false);
    });
});
