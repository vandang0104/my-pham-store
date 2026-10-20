<?php

use App\Exceptions\CartException;
use App\Exceptions\InvalidOrderTransitionException;
use App\Exceptions\OrderException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Support\Money;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Giá hiện hành: serum giảm còn 279.000 (giá gốc 345.000); kem 250.000
    $this->serum = Product::factory()->create(['name' => 'Serum Niacinamide', 'price' => 345000, 'sale_price' => 279000, 'stock' => 5]);
    $this->kem = Product::factory()->create(['name' => 'Kem dưỡng ẩm', 'price' => 250000, 'stock' => 10]);
});

function themGio($test, Product $product, int $quantity = 1)
{
    return $test->from(route('products.show', $product))
        ->post(route('cart.store'), ['product_id' => $product->id, 'quantity' => $quantity]);
}

/** Giỏ session hiện tại của khách vãng lai */
function gioSession($test): array
{
    return session()->get(CartService::SESSION_KEY, []);
}

describe('thêm vào giỏ', function () {
    it('thêm sản phẩm mới: quay lại trang trước với thông báo thành công', function () {
        themGio($this, $this->serum, 2)
            ->assertRedirect(route('products.show', $this->serum))
            ->assertSessionHas('status', 'Đã thêm “Serum Niacinamide” vào giỏ hàng.');

        expect(gioSession($this))->toBe([$this->serum->id => 2]);
    });

    it('thêm sản phẩm đã có thì cộng dồn số lượng', function () {
        themGio($this, $this->serum, 2);
        themGio($this, $this->serum, 1);

        expect(gioSession($this))->toBe([$this->serum->id => 3]);
    });

    it('không cho vượt tồn kho (tính cả số đã có trong giỏ), giỏ giữ nguyên', function () {
        themGio($this, $this->serum, 4);

        themGio($this, $this->serum, 2)
            ->assertRedirect(route('products.show', $this->serum))
            ->assertSessionHas('error', 'Chỉ còn 5 sản phẩm “Serum Niacinamide” trong kho (giỏ của bạn đã có 4).');

        expect(gioSession($this))->toBe([$this->serum->id => 4]);
    });

    it('không thêm được sản phẩm hết hàng, đang ẩn hoặc đã xoá mềm', function (string $state, string $message) {
        $product = match ($state) {
            'hết hàng' => Product::factory()->outOfStock()->create(['name' => 'SP hết hàng']),
            'đang ẩn' => Product::factory()->inactive()->create(),
            'đã xoá' => tap(Product::factory()->create())->delete(),
        };

        $this->post(route('cart.store'), ['product_id' => $product->id])->assertSessionHas('error', $message);
        expect(gioSession($this))->toBe([]);
    })->with([
        'hết hàng' => ['hết hàng', '“SP hết hàng” đã hết hàng.'],
        'đang ẩn' => ['đang ẩn', 'Sản phẩm này hiện không còn bán.'],
        'đã xoá mềm' => ['đã xoá', 'Sản phẩm này hiện không còn bán.'],
    ]);

    it('lỗi nghiệp vụ (giỏ, đặt hàng, sai luồng trạng thái) chỉ báo cho khách, không ghi vào log lỗi', function () {
        $handler = app(ExceptionHandler::class);

        expect($handler->shouldReport(new CartException('x')))->toBeFalse()
            ->and($handler->shouldReport(new OrderException('x')))->toBeFalse()
            ->and($handler->shouldReport(new InvalidOrderTransitionException('x')))->toBeFalse()
            ->and($handler->shouldReport(new RuntimeException('x')))->toBeTrue();
    });

    it('dữ liệu không hợp lệ bị từ chối (sản phẩm không tồn tại, số lượng < 1)', function () {
        $this->post(route('cart.store'), ['product_id' => 999999])->assertSessionHasErrors('product_id');
        $this->post(route('cart.store'), ['product_id' => $this->serum->id, 'quantity' => 0])->assertSessionHasErrors('quantity');
    });

    it('khách vãng lai không tạo bản ghi carts', function () {
        themGio($this, $this->serum);

        expect(Cart::count())->toBe(0)->and(CartItem::count())->toBe(0);
    });

    it('user đăng nhập: lưu giỏ trong DB', function () {
        $user = User::factory()->customer()->create();
        $this->actingAs($user);

        themGio($this, $this->serum, 2);
        themGio($this, $this->kem);

        expect($user->cart()->first()->items()->pluck('quantity', 'product_id')->all())
            ->toEqual([$this->serum->id => 2, $this->kem->id => 1])
            ->and(gioSession($this))->toBe([]);
    });
});

describe('trang giỏ hàng', function () {
    it('giỏ trống: hiện trạng thái trống', function () {
        $this->get(route('cart.index'))->assertOk()->assertSee('Giỏ hàng của bạn đang trống');
    });

    it('hiện từng dòng và tạm tính đúng theo giá hiện hành (có sản phẩm giảm giá)', function () {
        themGio($this, $this->serum, 2);   // 2 × 279.000
        themGio($this, $this->kem, 1);     // 1 × 250.000

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Serum Niacinamide')
            ->assertSee('558.000')
            ->assertSee('data-cart-subtotal>'.Money::format(808000), false)
            ->assertSee('href="'.url('/thanh-toan').'"', false);
    });

    it('cập nhật số lượng; vượt tồn kho bị chặn', function () {
        themGio($this, $this->serum, 1);

        $this->patch(route('cart.update', $this->serum->id), ['quantity' => 3])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('status');
        expect(gioSession($this))->toBe([$this->serum->id => 3]);

        $this->from(route('cart.index'))->patch(route('cart.update', $this->serum->id), ['quantity' => 6])
            ->assertSessionHas('error');
        expect(gioSession($this))->toBe([$this->serum->id => 3]);

        $this->patch(route('cart.update', $this->serum->id), ['quantity' => 0])->assertSessionHasErrors('quantity');
    });

    it('xoá một dòng khỏi giỏ', function () {
        themGio($this, $this->serum);
        themGio($this, $this->kem);

        $this->delete(route('cart.destroy', $this->serum->id))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('status', 'Đã xoá “Serum Niacinamide” khỏi giỏ hàng.');

        expect(gioSession($this))->toBe([$this->kem->id => 1]);
    });

    it('sản phẩm bị ẩn / xoá sau khi thêm: tự bỏ khỏi giỏ và báo cho khách', function () {
        themGio($this, $this->serum);
        themGio($this, $this->kem);
        $this->serum->update(['is_active' => false]);

        $this->get(route('cart.index'))
            ->assertSee('“Serum Niacinamide” không còn bán nên đã được bỏ khỏi giỏ hàng.')
            ->assertSee('data-cart-subtotal>'.Money::format(250000), false);

        expect(gioSession($this))->toBe([$this->kem->id => 1]);
    });

    it('tồn kho giảm dưới số lượng trong giỏ: hạ số lượng và báo', function () {
        themGio($this, $this->kem, 8);
        $this->kem->update(['stock' => 3]);

        $this->get(route('cart.index'))->assertSee('Số lượng “Kem dưỡng ẩm” đã giảm còn 3 theo tồn kho hiện tại.');
        expect(gioSession($this))->toBe([$this->kem->id => 3]);
    });
});

describe('badge header', function () {
    it('hiện tổng số lượng trong giỏ, ẩn khi giỏ trống', function () {
        $this->get('/')->assertDontSee('class="cart-badge', false);

        themGio($this, $this->serum, 2);
        themGio($this, $this->kem, 3);

        $this->get('/')->assertSee('5 sản phẩm trong giỏ');
    });

    it('user đăng nhập: badge đọc từ DB', function () {
        $user = User::factory()->customer()->create();
        Cart::create(['user_id' => $user->id])->items()->create(['product_id' => $this->kem->id, 'quantity' => 4]);

        $this->actingAs($user)->get('/')->assertSee('4 sản phẩm trong giỏ');
    });
});

describe('gộp giỏ khi đăng nhập / đăng ký', function () {
    it('đăng nhập: gộp giỏ session vào DB, cộng dồn và cắt theo tồn kho, xoá giỏ session', function () {
        $user = User::factory()->customer()->create(['email' => 'lan@example.com', 'password' => 'MatKhau123']);
        Cart::create(['user_id' => $user->id])->items()->create(['product_id' => $this->serum->id, 'quantity' => 3]);

        themGio($this, $this->serum, 4);   // 3 + 4 = 7 > tồn kho 5 → 5
        themGio($this, $this->kem, 2);

        $this->post(route('login'), ['email' => 'lan@example.com', 'password' => 'MatKhau123'])->assertRedirect();

        expect($user->cart()->first()->items()->pluck('quantity', 'product_id')->all())
            ->toEqual([$this->serum->id => 5, $this->kem->id => 2])
            ->and(gioSession($this))->toBe([]);
    });

    it('đăng ký thành công: giỏ session chuyển vào giỏ DB của tài khoản mới', function () {
        themGio($this, $this->kem, 2);

        $this->post(route('register'), [
            'name' => 'Nguyễn Thị Lan', 'email' => 'moi@example.com', 'phone' => '0912345678',
            'password' => 'MatKhau123', 'password_confirmation' => 'MatKhau123',
        ])->assertRedirect(route('home'));

        $user = User::query()->where('email', 'moi@example.com')->firstOrFail();
        expect($user->cart()->first()->items()->pluck('quantity', 'product_id')->all())->toEqual([$this->kem->id => 2]);
    });

    it('bỏ qua sản phẩm đã ngừng bán khi gộp', function () {
        $user = User::factory()->customer()->create(['email' => 'lan@example.com', 'password' => 'MatKhau123']);
        themGio($this, $this->serum);
        $this->serum->update(['is_active' => false]);

        $this->post(route('login'), ['email' => 'lan@example.com', 'password' => 'MatKhau123']);

        expect($user->cart()->first()->items()->count())->toBe(0);
    });
});

describe('CartService', function () {
    it('count và subtotal tính theo giá hiện hành', function () {
        $user = User::factory()->customer()->create();
        $this->actingAs($user);
        $cart = app(CartService::class);

        $cart->add($this->serum, 2);
        $cart->add($this->kem);

        expect($cart->count())->toBe(3)
            ->and($cart->subtotal())->toBe(2 * 279000 + 250000)
            ->and($cart->items()->first()->unitPrice)->toBe(279000);

        $cart->clear();
        expect($cart->count())->toBe(0);
    });
});
