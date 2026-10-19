<?php

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->staff = User::factory()->staff()->create();
    $this->customer = User::factory()->customer()->create();
});

/** Kết quả quyền cho 3 vai trò theo thứ tự admin, staff, customer */
function quyen(string $ability, mixed $arguments): array
{
    $test = test();

    return array_map(
        fn (User $user) => Gate::forUser($user)->allows($ability, $arguments),
        [$test->admin, $test->staff, $test->customer],
    );
}

describe('ProductPolicy', function () {
    it('cho staff xem, tạo, sửa nhưng không xoá', function () {
        $product = Product::factory()->create();

        expect(quyen('viewAny', Product::class))->toBe([true, true, false])
            ->and(quyen('create', Product::class))->toBe([true, true, false])
            ->and(quyen('update', $product))->toBe([true, true, false])
            ->and(quyen('delete', $product))->toBe([true, false, false])
            ->and(quyen('restore', $product))->toBe([true, false, false]);
    });

    it('chỉ cho xoá cứng sản phẩm chưa có trong đơn', function () {
        $chuaBan = Product::factory()->create();
        $daBan = Product::factory()->create();
        OrderItem::factory()->for($daBan)->create();

        expect(quyen('forceDelete', $chuaBan))->toBe([true, false, false])
            ->and(quyen('forceDelete', $daBan))->toBe([false, false, false]);
    });
});

describe('OrderPolicy', function () {
    it('cho admin/staff quản lý đơn, khách chỉ xem đơn của mình', function () {
        $donCuaKhach = Order::factory()->for($this->customer)->create();
        $donNguoiKhac = Order::factory()->create();

        expect(quyen('viewAny', Order::class))->toBe([true, true, false])
            ->and(quyen('view', $donCuaKhach))->toBe([true, true, true])
            ->and(quyen('view', $donNguoiKhac))->toBe([true, true, false])
            ->and(quyen('updateStatus', $donCuaKhach))->toBe([true, true, false])
            ->and(quyen('updatePayment', $donCuaKhach))->toBe([true, true, false]);
    });

    it('chỉ cho huỷ khi đơn đang chờ xác nhận hoặc đã xác nhận, khách chỉ huỷ đơn của mình', function (OrderStatus $status, array $expected) {
        $order = Order::factory()->for($this->customer)->status($status)->create();

        expect(quyen('cancel', $order))->toBe($expected);
    })->with([
        'chờ xác nhận' => [OrderStatus::Pending, [true, true, true]],
        'đã xác nhận' => [OrderStatus::Confirmed, [true, true, true]],
        'đang giao' => [OrderStatus::Shipping, [false, false, false]],
        'đã giao' => [OrderStatus::Delivered, [false, false, false]],
        'đã huỷ' => [OrderStatus::Cancelled, [false, false, false]],
    ]);

    it('không cho khách huỷ đơn của người khác hoặc đơn khách vãng lai', function () {
        expect(quyen('cancel', Order::factory()->create()))->toBe([true, true, false])
            ->and(quyen('cancel', Order::factory()->guest()->create()))->toBe([true, true, false]);
    });

    it('chỉ admin xoá được và chỉ đơn đã huỷ', function () {
        expect(quyen('delete', Order::factory()->status(OrderStatus::Cancelled)->create()))->toBe([true, false, false])
            ->and(quyen('delete', Order::factory()->delivered()->create()))->toBe([false, false, false]);
    });
});

describe('ReviewPolicy', function () {
    it('chỉ cho đánh giá khi đã có đơn đã giao chứa sản phẩm và chưa đánh giá', function () {
        $product = Product::factory()->create();
        $canReview = fn () => Gate::forUser($this->customer)->allows('create', [Review::class, $product]);

        // Chưa mua
        expect($canReview())->toBeFalse();

        // Có đơn chứa sản phẩm nhưng chưa giao
        $order = Order::factory()->for($this->customer)->status(OrderStatus::Shipping)->create();
        OrderItem::factory()->for($order)->for($product)->create();
        expect($canReview())->toBeFalse();

        // Đơn đã giao
        $order->forceFill(['status' => OrderStatus::Delivered])->save();
        expect($canReview())->toBeTrue();

        // Đã đánh giá rồi
        Review::factory()->for($this->customer)->for($product)->create();
        expect($canReview())->toBeFalse();
    });

    it('cho admin/staff duyệt, chỉ admin xoá', function () {
        $review = Review::factory()->create();

        expect(quyen('viewAny', Review::class))->toBe([true, true, false])
            ->and(quyen('moderate', $review))->toBe([true, true, false])
            ->and(quyen('delete', $review))->toBe([true, false, false]);
    });
});

describe('UserPolicy', function () {
    it('cho staff xem khách hàng nhưng không xem/sửa nhân viên khác', function () {
        $nhanVienKhac = User::factory()->staff()->create();

        expect(quyen('viewAny', User::class))->toBe([true, true, false])
            ->and(quyen('view', $this->customer))->toBe([true, true, false])
            ->and(quyen('view', $nhanVienKhac))->toBe([true, false, false])
            ->and(quyen('create', User::class))->toBe([true, false, false])
            ->and(quyen('update', $this->customer))->toBe([true, false, false])
            ->and(quyen('toggleActive', $this->customer))->toBe([true, false, false]);
    });

    it('cấm tự xoá và tự khoá mình', function () {
        expect(Gate::forUser($this->admin)->allows('delete', $this->admin))->toBeFalse()
            ->and(Gate::forUser($this->admin)->allows('toggleActive', $this->admin))->toBeFalse();
    });

    it('bảo vệ admin đang hoạt động cuối cùng', function () {
        $adminKhac = User::factory()->admin()->create();
        $gate = Gate::forUser($adminKhac);

        // Còn 2 admin: được hạ quyền / khoá / xoá admin kia
        expect($gate->allows('changeRole', [$this->admin, UserRole::Staff]))->toBeTrue()
            ->and($gate->allows('toggleActive', $this->admin))->toBeTrue()
            ->and($gate->allows('delete', $this->admin))->toBeTrue();

        // Khoá admin kia → $adminKhac là admin hoạt động cuối cùng: tự hạ quyền bị chặn
        $this->admin->forceFill(['is_active' => false])->save();
        expect($gate->allows('changeRole', [$adminKhac, UserRole::Staff]))->toBeFalse()
            ->and($gate->allows('changeRole', [$adminKhac, UserRole::Admin]))->toBeTrue()
            // Admin đã bị khoá không còn là "admin hoạt động cuối cùng": xoá được
            ->and($gate->allows('delete', $this->admin))->toBeTrue();
    });

    it('không cho staff đổi vai trò', function () {
        expect(Gate::forUser($this->staff)->allows('changeRole', [$this->customer, UserRole::Staff]))->toBeFalse();
    });
});

describe('CouponPolicy', function () {
    it('cho staff xem, chỉ admin tạo/sửa/xoá coupon chưa dùng', function () {
        $chuaDung = Coupon::factory()->create();
        $daDung = Coupon::factory()->create();
        Order::factory()->create(['coupon_id' => $daDung->id]);

        expect(quyen('viewAny', Coupon::class))->toBe([true, true, false])
            ->and(quyen('create', Coupon::class))->toBe([true, false, false])
            ->and(quyen('update', $chuaDung))->toBe([true, false, false])
            ->and(quyen('delete', $chuaDung))->toBe([true, false, false])
            ->and(quyen('delete', $daDung))->toBe([false, false, false]);
    });
});

describe('SettingPolicy', function () {
    it('chỉ admin xem và sửa cài đặt', function () {
        expect(quyen('viewAny', Setting::class))->toBe([true, false, false])
            ->and(quyen('update', Setting::class))->toBe([true, false, false]);
    });
});
