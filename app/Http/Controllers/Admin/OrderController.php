<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\OrderService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/** Quản lý đơn (admin + staff): xem, đổi trạng thái theo luồng, huỷ (hoàn kho), đánh dấu đã thanh toán. Không xoá đơn. */
class OrderController extends Controller
{
    public const PER_PAGE = 20;

    public function __construct(private readonly OrderService $orders) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Order::class);
        $status = OrderStatus::tryFrom((string) $request->query('trang-thai'));
        $q = trim((string) $request->query('q'));

        return view('admin.orders.index', [
            'orders' => Order::query()
                ->when($status, fn ($query) => $query->where('status', $status))
                ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                    ->where('code', 'like', "%{$q}%")->orWhere('receiver_name', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%")))
                ->latest()->latest('id')
                ->paginate(self::PER_PAGE)
                ->withQueryString(),
            'statuses' => OrderStatus::cases(),
            'filters' => ['trang-thai' => $status, 'q' => $q],
        ]);
    }

    public function show(Order $order): View
    {
        Gate::authorize('view', $order);
        $order->load(['items', 'user', 'statusHistories.changedBy']);

        return view('admin.orders.show', [
            'order' => $order,
            'next' => $order->status->transitions(),
            'money' => [
                'subtotal' => Money::format($order->subtotal),
                'shipping' => Money::format($order->shipping_fee),
                'total' => Money::format($order->total),
            ],
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $to = OrderStatus::from($request->validated('status'));
        $this->orders->changeStatus($order, $to, $request->user(), $request->validated('note'));

        return redirect()->route('admin.orders.show', $order)->with('status', "Đơn {$order->code}: chuyển sang “{$to->label()}”.");
    }

    public function markPaid(Order $order): RedirectResponse
    {
        Gate::authorize('updatePayment', $order);
        $this->orders->markPaid($order);

        return redirect()->route('admin.orders.show', $order)->with('status', "Đơn {$order->code}: đã đánh dấu đã thanh toán.");
    }
}
