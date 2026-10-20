<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->staff = User::factory()->staff()->create();
});

describe('đơn hàng', function () {
    it('staff đổi trạng thái theo luồng: ghi mốc thời gian và lịch sử kèm người thực hiện', function () {
        $order = Order::factory()->status(OrderStatus::Pending)->create();

        $this->actingAs($this->staff)->patch(route('admin.orders.status', $order), ['status' => 'confirmed', 'note' => 'Đã gọi khách'])
            ->assertRedirect(route('admin.orders.show', $order))->assertSessionHas('status');
        $this->patch(route('admin.orders.status', $order), ['status' => 'shipping']);
        $this->patch(route('admin.orders.status', $order), ['status' => 'delivered']);

        $order->refresh();
        $last = $order->statusHistories()->get()->last();
        expect($order->status)->toBe(OrderStatus::Delivered)
            ->and($order->confirmed_at)->not->toBeNull()
            ->and($order->shipped_at)->not->toBeNull()
            ->and($order->delivered_at)->not->toBeNull()
            ->and($last->changed_by)->toBe($this->staff->id);
    });

    it('chuyển sai luồng bị chặn (vd chờ xác nhận → đã giao, đã giao → huỷ)', function (OrderStatus $from, string $to) {
        $order = Order::factory()->status($from)->create();

        $this->actingAs($this->staff)->from(route('admin.orders.show', $order))->patch(route('admin.orders.status', $order), ['status' => $to])
            ->assertSessionHas('error');
        expect($order->fresh()->status)->toBe($from);
    })->with([
        'chờ xác nhận → đã giao' => [OrderStatus::Pending, 'delivered'],
        'đã giao → huỷ' => [OrderStatus::Delivered, 'cancelled'],
        'đã huỷ → xác nhận' => [OrderStatus::Cancelled, 'confirmed'],
    ]);

    it('huỷ đơn đã xác nhận: hoàn kho; đã thanh toán thì chuyển "đã hoàn tiền"', function () {
        $product = Product::factory()->create(['stock' => 4, 'sold_count' => 3]);
        $order = Order::factory()->status(OrderStatus::Confirmed)->create(['payment_status' => PaymentStatus::Paid]);
        OrderItem::factory()->for($order)->for($product)->create(['quantity' => 2]);

        $this->actingAs($this->staff)->patch(route('admin.orders.status', $order), ['status' => 'cancelled'])->assertRedirect();

        expect($order->fresh()->status)->toBe(OrderStatus::Cancelled)
            ->and($order->fresh()->payment_status)->toBe(PaymentStatus::Refunded)
            ->and($product->fresh()->stock)->toBe(6);
    });

    it('đánh dấu đã thanh toán; không có route xoá đơn', function () {
        $order = Order::factory()->status(OrderStatus::Pending)->create(['payment_status' => PaymentStatus::Unpaid]);

        $this->actingAs($this->admin)->patch(route('admin.orders.paid', $order))->assertRedirect();
        expect($order->fresh()->payment_status)->toBe(PaymentStatus::Paid);

        $this->delete('/admin/orders/'.$order->code)->assertStatus(405);
        expect(Order::query()->whereKey($order->id)->exists())->toBeTrue();
    });

    it('danh sách lọc trạng thái và tìm theo mã', function () {
        $a = Order::factory()->status(OrderStatus::Pending)->create();
        $b = Order::factory()->status(OrderStatus::Delivered)->create();

        $this->actingAs($this->staff)->get(route('admin.orders.index', ['trang-thai' => 'pending']))->assertSee($a->code)->assertDontSee($b->code);
        $this->get(route('admin.orders.index', ['q' => $b->code]))->assertSee($b->code)->assertDontSee($a->code);
        $this->get(route('admin.orders.show', $a))->assertOk()->assertSee($a->receiver_name);
    });
});

describe('tài khoản và thống kê chỉ dành cho admin', function () {
    it('staff vào /admin/users và /admin/reports → 403', function () {
        $this->actingAs($this->staff)->get(route('admin.users.index'))->assertForbidden();
        $this->get(route('admin.reports.index'))->assertForbidden();
    });

    it('admin đổi vai trò và khoá / mở khoá tài khoản khách', function () {
        $khach = User::factory()->customer()->create();

        $this->actingAs($this->admin)->patch(route('admin.users.role', $khach), ['role' => 'staff'])->assertSessionHas('status');
        expect($khach->fresh()->role)->toBe(UserRole::Staff);

        $this->patch(route('admin.users.toggle', $khach));
        expect($khach->fresh()->is_active)->toBeFalse();
        $this->patch(route('admin.users.toggle', $khach));
        expect($khach->fresh()->is_active)->toBeTrue();
    });

    it('chặn tự hạ quyền / tự khoá mình và hạ quyền admin đang hoạt động cuối cùng', function () {
        $this->actingAs($this->admin)->from(route('admin.users.show', $this->admin))
            ->patch(route('admin.users.role', $this->admin), ['role' => 'customer'])->assertSessionHasErrors('delete');
        $this->patch(route('admin.users.toggle', $this->admin))->assertSessionHasErrors('delete');

        // Admin thứ hai muốn hạ quyền admin cuối cùng còn hoạt động (admin hiện tại đã bị khoá)
        $admin2 = User::factory()->admin()->create();
        $this->admin->forceFill(['is_active' => false])->save();
        $this->actingAs($admin2)->patch(route('admin.users.role', $admin2), ['role' => 'staff'])->assertSessionHasErrors('delete');

        expect($this->admin->fresh()->role)->toBe(UserRole::Admin)->and($admin2->fresh()->role)->toBe(UserRole::Admin);
    });

    it('chi tiết tài khoản: số đơn và tổng chi tiêu (đơn đã giao)', function () {
        $khach = User::factory()->customer()->create();
        Order::factory()->for($khach)->status(OrderStatus::Delivered)->create(['total' => 300000]);
        Order::factory()->for($khach)->status(OrderStatus::Pending)->create(['total' => 999000]);

        $this->actingAs($this->admin)->get(route('admin.users.show', $khach))
            ->assertSee('data-orders-count>2<', false)
            ->assertSee('300.000');
    });
});

describe('ReportService', function () {
    it('doanh thu theo ngày / tháng và top bán chạy chỉ tính đơn đã giao, theo ngày đặt', function () {
        $this->travelTo('2026-10-20 10:00:00');
        $sp = Product::factory()->create();

        $giao1 = Order::factory()->status(OrderStatus::Delivered)->create(['total' => 200000, 'created_at' => '2026-10-18 09:00:00']);
        $giao2 = Order::factory()->status(OrderStatus::Delivered)->create(['total' => 300000, 'created_at' => '2026-10-20 08:00:00']);
        Order::factory()->status(OrderStatus::Cancelled)->create(['total' => 999000, 'created_at' => '2026-10-20 08:00:00']);
        Order::factory()->status(OrderStatus::Delivered)->create(['total' => 100000, 'created_at' => '2026-09-05 08:00:00']);
        OrderItem::factory()->for($giao1)->for($sp)->create(['product_name' => 'Serum A', 'quantity' => 2, 'line_total' => 200000]);
        OrderItem::factory()->for($giao2)->for($sp)->create(['product_name' => 'Serum A', 'quantity' => 3, 'line_total' => 300000]);

        $reports = app(ReportService::class);
        $daily = $reports->revenueByDay(now()->subDays(2), now());
        $monthly = $reports->revenueByMonth(12);
        $top = $reports->topProducts(now()->subDays(2), now());

        expect($daily['labels'])->toBe(['18/10', '19/10', '20/10'])
            ->and($daily['data'])->toBe([200000, 0, 300000])
            ->and($daily['total'])->toBe(500000)
            ->and(array_slice($monthly['data'], -2))->toBe([100000, 500000])
            ->and($top->first()->product_name)->toBe('Serum A')
            ->and($top->first()->quantity)->toBe(5);

        $this->actingAs($this->admin)->get(route('admin.reports.index', ['tu-ngay' => '2026-10-18', 'den-ngay' => '2026-10-20']))
            ->assertOk()->assertSee('data-daily-total>500.000', false)->assertSee('data-chart', false);
    });
});

describe('tin liên hệ', function () {
    it('staff xem danh sách (chưa xử lý lên đầu), mở tin và đánh dấu đã xử lý', function () {
        $daXuLy = ContactMessage::query()->create(['name' => 'An', 'email' => 'an@example.com', 'subject' => 'Cũ', 'message' => 'Đã xong rồi ạ.']);
        $daXuLy->forceFill(['is_handled' => true])->save();
        $moi = ContactMessage::query()->create(['name' => 'Bình', 'email' => 'binh@example.com', 'subject' => 'Hỏi giao hàng', 'message' => 'Bao lâu thì nhận hàng?']);

        $this->actingAs($this->staff)->get(route('admin.contacts.index'))->assertSeeInOrder(['Hỏi giao hàng', 'Cũ']);
        $this->get(route('admin.contacts.show', $moi))->assertOk()->assertSee('Bao lâu thì nhận hàng?');

        $this->patch(route('admin.contacts.handled', $moi))->assertRedirect(route('admin.contacts.index'));
        expect($moi->fresh()->is_handled)->toBeTrue();
    });
});
