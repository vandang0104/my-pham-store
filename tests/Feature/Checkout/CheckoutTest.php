<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Exceptions\OrderException;
use App\Mail\OrderPlaced;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\CartLine;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
    // Settings mặc định (Setting::DEFAULTS): phí ship 30.000, miễn phí từ 500.000
    $this->serum = Product::factory()->create(['name' => 'Serum Niacinamide', 'sku' => 'DML-001', 'price' => 345000, 'sale_price' => 279000, 'stock' => 5, 'sold_count' => 10]);
    $this->kem = Product::factory()->create(['name' => 'Kem dưỡng ẩm', 'sku' => 'VLV-001', 'price' => 250000, 'stock' => 10, 'sold_count' => 0]);
});

function themVaoGio($test, Product $product, int $quantity = 1): void
{
    $test->post(route('cart.store'), ['product_id' => $product->id, 'quantity' => $quantity]);
}

/** Mở trang thanh toán và lấy token một lần của form */
function layToken($test): string
{
    preg_match('/name="checkout_token" value="([^"]+)"/', $test->get(route('checkout.create'))->getContent(), $m);

    return $m[1] ?? '';
}

/** @param array<string, mixed> $overrides */
function duLieuDatHang(string $token, array $overrides = []): array
{
    return [
        'checkout_token' => $token,
        'receiver_name' => 'Nguyễn Thị Lan',
        'phone' => '0912 345 678',
        'email' => 'lan@example.com',
        'province' => 'TP. Hồ Chí Minh',
        'district' => 'Quận 1',
        'ward' => 'Phường Bến Thành',
        'detail' => '12 Lê Lợi',
        'note' => 'Giao giờ hành chính.',
        'payment_method' => 'cod',
        ...$overrides,
    ];
}

describe('trang thanh toán', function () {
    it('giỏ trống → về giỏ hàng kèm lỗi', function () {
        $this->get(route('checkout.create'))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('error', 'Giỏ hàng đang trống, hãy chọn sản phẩm trước khi đặt hàng.');
    });

    it('hiện form, tóm tắt đơn (tạm tính, phí ship theo settings, tổng) và một nút Đặt hàng', function () {
        themVaoGio($this, $this->serum);   // 279.000 < 500.000 → ship 30.000

        $html = $this->get(route('checkout.create'))->assertOk()->getContent();

        $form = str($html)->after('action="'.route('checkout.store').'"')->before('</form>')->toString();

        expect($html)->toContain('name="checkout_token"', 'Serum Niacinamide', '279.000', '30.000', 'data-checkout-total>309.000')
            ->and(substr_count($form, 'type="submit"'))->toBe(1)
            ->and($html)->toContain('value="cod"', 'value="bank_transfer"')
            ->not->toContain('value="vnpay"');
    });

    it('user đăng nhập: điền sẵn họ tên, SĐT, email từ hồ sơ', function () {
        $user = User::factory()->customer()->create(['name' => 'Trần Minh Anh', 'phone' => '0987654321', 'email' => 'anh@example.com']);
        $this->actingAs($user);
        themVaoGio($this, $this->kem);

        $this->get(route('checkout.create'))
            ->assertSee('value="Trần Minh Anh"', false)
            ->assertSee('value="0987654321"', false)
            ->assertSee('value="anh@example.com"', false);
    });

    it('nút "Tiến hành đặt hàng" ở giỏ trỏ tới trang thanh toán, có dòng miễn phí giao hàng', function () {
        themVaoGio($this, $this->serum);

        $this->get(route('cart.index'))
            ->assertSee('href="'.route('checkout.create').'"', false)
            ->assertSee('Còn 221.000');
    });
});

describe('đặt hàng thành công', function () {
    it('khách vãng lai: tạo đơn pending/unpaid, snapshot, lịch sử, trừ kho, tăng sold_count, xoá giỏ, gửi mail', function () {
        themVaoGio($this, $this->serum, 2);
        themVaoGio($this, $this->kem, 1);

        $response = $this->post(route('checkout.store'), duLieuDatHang(layToken($this)));

        $order = Order::query()->with(['items', 'statusHistories'])->sole();
        $response->assertRedirect(route('checkout.success', $order));

        expect($order->user_id)->toBeNull()
            ->and($order->code)->toMatch('/^MP-\d{8}-[A-Z2-9]{4}$/')
            ->and($order->status)->toBe(OrderStatus::Pending)
            ->and($order->payment_status)->toBe(PaymentStatus::Unpaid)
            ->and($order->payment_method)->toBe(PaymentMethod::Cod)
            ->and($order->coupon_id)->toBeNull()
            ->and($order->discount)->toBe(0)
            ->and($order->subtotal)->toBe(2 * 279000 + 250000)
            ->and($order->shipping_fee)->toBe(0)               // 808.000 ≥ 500.000
            ->and($order->total)->toBe(808000)
            ->and($order->phone)->toBe('0912345678')           // đã chuẩn hoá
            ->and($order->items)->toHaveCount(2);

        $line = $order->items->firstWhere('product_id', $this->serum->id);
        expect($line->only(['product_name', 'sku', 'price', 'quantity', 'line_total']))
            ->toBe(['product_name' => 'Serum Niacinamide', 'sku' => 'DML-001', 'price' => 279000, 'quantity' => 2, 'line_total' => 558000]);

        expect($order->statusHistories)->toHaveCount(1)
            ->and($order->statusHistories->first()->from_status)->toBeNull()
            ->and($order->statusHistories->first()->to_status)->toBe(OrderStatus::Pending)
            ->and($order->statusHistories->first()->changed_by)->toBeNull();

        expect($this->serum->fresh()->stock)->toBe(3)
            ->and($this->serum->fresh()->sold_count)->toBe(12)
            ->and($this->kem->fresh()->stock)->toBe(9)
            ->and(session(CartService::SESSION_KEY, []))->toBe([]);

        Mail::assertSent(OrderPlaced::class, fn (OrderPlaced $mail) => $mail->hasTo('lan@example.com') && $mail->order->is($order));
    });

    it('user đăng nhập: đơn gắn user, lịch sử ghi người đặt, email trống thì dùng email tài khoản, giỏ DB bị xoá', function () {
        $user = User::factory()->customer()->create(['email' => 'anh@example.com']);
        $this->actingAs($user);
        themVaoGio($this, $this->kem, 2);

        $this->post(route('checkout.store'), duLieuDatHang(layToken($this), ['email' => '', 'payment_method' => 'bank_transfer']))
            ->assertRedirect();

        $order = Order::query()->sole();
        expect($order->user_id)->toBe($user->id)
            ->and($order->email)->toBe('anh@example.com')
            ->and($order->payment_method)->toBe(PaymentMethod::BankTransfer)
            ->and($order->statusHistories()->first()->changed_by)->toBe($user->id)
            ->and($user->cart()->first()->items()->count())->toBe(0);

        Mail::assertSent(OrderPlaced::class, fn (OrderPlaced $mail) => $mail->hasTo('anh@example.com'));
    });

    it('tổng = tạm tính + phí ship (dưới ngưỡng); phí ship và ngưỡng đọc từ settings', function () {
        Setting::query()->create(['key' => 'shipping_fee', 'value' => '25000']);
        themVaoGio($this, $this->serum);

        $this->post(route('checkout.store'), duLieuDatHang(layToken($this)));

        $order = Order::query()->sole();
        expect($order->subtotal)->toBe(279000)
            ->and($order->shipping_fee)->toBe(25000)
            ->and($order->total)->toBe(304000);
    });

    it('miễn phí giao hàng khi tạm tính đạt đúng ngưỡng', function () {
        $sp = Product::factory()->create(['price' => 500000, 'stock' => 3]);
        themVaoGio($this, $sp);

        $this->post(route('checkout.store'), duLieuDatHang(layToken($this)));

        expect(Order::query()->sole()->shipping_fee)->toBe(0);
    });

    it('order_items là snapshot: đổi tên/giá sản phẩm sau khi đặt, đơn không đổi', function () {
        themVaoGio($this, $this->serum);
        $this->post(route('checkout.store'), duLieuDatHang(layToken($this)));

        $this->serum->update(['name' => 'Serum đổi tên', 'price' => 999000, 'sale_price' => null]);

        $item = Order::query()->sole()->items()->sole();
        expect($item->product_name)->toBe('Serum Niacinamide')
            ->and($item->price)->toBe(279000)
            ->and($item->line_total)->toBe(279000);
    });

    it('mã đơn duy nhất theo dạng MP-YYYYMMDD-XXXX', function () {
        foreach (range(1, 5) as $i) {
            themVaoGio($this, $this->kem);
            $this->post(route('checkout.store'), duLieuDatHang(layToken($this)));
        }
        $codes = Order::query()->pluck('code');

        expect($codes)->toHaveCount(5)
            ->and($codes->unique())->toHaveCount(5)
            ->and($codes->every(fn (string $c) => str_starts_with($c, 'MP-'.now()->format('Ymd').'-')))->toBeTrue();
    });
});

describe('dữ liệu không hợp lệ', function () {
    it('báo lỗi từng trường và không tạo đơn', function (array $overrides, string $field) {
        themVaoGio($this, $this->kem);

        $this->post(route('checkout.store'), duLieuDatHang(layToken($this), $overrides))->assertSessionHasErrors($field);
        expect(Order::count())->toBe(0);
    })->with([
        'SĐT sai định dạng' => [['phone' => '012345'], 'phone'],
        'SĐT đầu số không hợp lệ' => [['phone' => '0212345678'], 'phone'],
        'email sai' => [['email' => 'lan@'], 'email'],
        'khách vãng lai thiếu email' => [['email' => ''], 'email'],
        'thiếu họ tên' => [['receiver_name' => ''], 'receiver_name'],
        'thiếu tỉnh/thành' => [['province' => ''], 'province'],
        'thiếu phường/xã' => [['ward' => ''], 'ward'],
        'thiếu địa chỉ chi tiết' => [['detail' => ''], 'detail'],
        'phương thức VNPay (đã cắt)' => [['payment_method' => 'vnpay'], 'payment_method'],
    ]);

    it('quận/huyện được bỏ trống (địa chỉ 2 cấp)', function () {
        themVaoGio($this, $this->kem);

        $this->post(route('checkout.store'), duLieuDatHang(layToken($this), ['district' => '']))->assertSessionHasNoErrors();
        expect(Order::query()->sole()->district)->toBeNull();
    });
});

describe('tồn kho và giỏ thay đổi', function () {
    it('tồn kho giảm sau khi thêm giỏ: không tạo đơn, kho không đổi, về giỏ kèm thông báo', function () {
        themVaoGio($this, $this->serum, 4);
        $token = layToken($this);
        $this->serum->update(['stock' => 2]);

        $this->post(route('checkout.store'), duLieuDatHang($token))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('error');

        expect(Order::count())->toBe(0)->and($this->serum->fresh()->stock)->toBe(2);
        Mail::assertNothingSent();
    });

    it('thiếu hàng lúc khoá dòng trong transaction: rollback toàn bộ, báo rõ sản phẩm thiếu, kho không đổi', function () {
        // Giỏ "nhìn thấy" 4 Serum + 1 Kem, nhưng trong DB Serum chỉ còn 2 (đơn khác vừa mua) → kiểm lại khi khoá dòng
        $cart = Mockery::mock(CartService::class);
        $cart->shouldReceive('items')->andReturn(collect([
            new CartLine($this->kem, 1),
            new CartLine(tap($this->serum->replicate())->forceFill(['id' => $this->serum->id, 'stock' => 5]), 4),
        ]));
        $cart->shouldReceive('notices')->andReturn([]);
        $cart->shouldNotReceive('clear');
        $this->serum->update(['stock' => 2]);

        expect(fn () => (new OrderService($cart))->placeOrder(duLieuDatHang('x'), null))
            ->toThrow(OrderException::class, '“Serum Niacinamide” (còn 2)');

        expect(Order::count())->toBe(0)
            ->and($this->serum->fresh()->stock)->toBe(2)
            ->and($this->kem->fresh()->stock)->toBe(10);
    });

    it('sản phẩm bị ẩn trước khi đặt: không tạo đơn', function () {
        themVaoGio($this, $this->kem);
        $token = layToken($this);
        $this->kem->update(['is_active' => false]);

        $this->post(route('checkout.store'), duLieuDatHang($token))->assertRedirect(route('cart.index'));
        expect(Order::count())->toBe(0);
    });
});

describe('chống gửi trùng', function () {
    it('gửi lại cùng form (double-click / F5) không tạo đơn thứ hai, về trang xác nhận đơn vừa đặt', function () {
        themVaoGio($this, $this->kem);
        $data = duLieuDatHang(layToken($this));

        $first = $this->post(route('checkout.store'), $data);
        $order = Order::query()->sole();
        $first->assertRedirect(route('checkout.success', $order));

        themVaoGio($this, $this->kem);   // kể cả giỏ lại có hàng
        $this->post(route('checkout.store'), $data)
            ->assertRedirect(route('checkout.success', $order))
            ->assertSessionHas('info');

        expect(Order::count())->toBe(1)->and($this->kem->fresh()->stock)->toBe(9);
    });

    it('token lạ (không do trang thanh toán cấp) bị từ chối', function () {
        themVaoGio($this, $this->kem);

        $this->post(route('checkout.store'), duLieuDatHang('token-gia'))->assertRedirect(route('cart.index'));
        expect(Order::count())->toBe(0);
    });
});

describe('trang xác nhận đơn', function () {
    it('người vừa đặt (cùng phiên) xem được: mã, trạng thái, sản phẩm, tổng, địa chỉ; chuyển khoản có thông tin tài khoản', function () {
        themVaoGio($this, $this->serum);
        $this->post(route('checkout.store'), duLieuDatHang(layToken($this), ['payment_method' => 'bank_transfer']));
        $order = Order::query()->sole();

        $this->get(route('checkout.success', $order))
            ->assertOk()
            ->assertSee($order->code)
            ->assertSee('Chờ xác nhận')
            ->assertSee('Serum Niacinamide')
            ->assertSee('309.000')
            ->assertSee('12 Lê Lợi, Phường Bến Thành, Quận 1, TP. Hồ Chí Minh')
            ->assertSee(Setting::get('bank_account_number'))
            ->assertSee('Nội dung chuyển khoản: <strong>'.$order->code.'</strong>', false);
    });

    it('COD: không hiện thông tin chuyển khoản', function () {
        themVaoGio($this, $this->kem);
        $this->post(route('checkout.store'), duLieuDatHang(layToken($this)));

        $this->get(route('checkout.success', Order::query()->sole()))->assertOk()->assertDontSee('Thông tin chuyển khoản');
    });

    it('chủ đơn (user) xem được kể cả ở phiên khác; người khác và phiên khác → 404', function () {
        $chu = User::factory()->customer()->create();
        $order = Order::factory()->for($chu)->create();
        $donKhach = Order::factory()->guest()->create();

        $this->actingAs($chu)->get(route('checkout.success', $order))->assertOk();
        $this->actingAs(User::factory()->customer()->create())->get(route('checkout.success', $order))->assertNotFound();
        $this->get(route('checkout.success', $donKhach))->assertNotFound();
    });
});

describe('email xác nhận', function () {
    it('nội dung tiếng Việt: mã đơn, sản phẩm, tổng, phương thức, hướng dẫn chuyển khoản; link tuyệt đối', function () {
        $order = Order::factory()->guest()->create(['payment_method' => PaymentMethod::BankTransfer, 'email' => 'lan@example.com']);
        $order->items()->create(['product_id' => $this->serum->id, 'product_name' => 'Serum Niacinamide', 'sku' => 'DML-001', 'price' => 279000, 'quantity' => 1, 'line_total' => 279000]);

        $mail = new OrderPlaced($order);
        $html = $mail->render();

        expect($mail->envelope()->subject)->toBe('Xác nhận đơn hàng '.$order->code.' — '.config('app.name'))
            ->and($html)->toContain($order->code, 'Serum Niacinamide', 'Chuyển khoản ngân hàng', 'Thông tin chuyển khoản', Setting::get('bank_account_number'))
            ->and($html)->toContain('href="'.url('/').'"');
    });
});
