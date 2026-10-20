<?php

namespace App\Http\Controllers\Account;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Đơn hàng của tôi: danh sách (lọc trạng thái), chi tiết, huỷ đơn.
 * Đơn của người khác trả 404 (không 403) để không lộ mã đơn có tồn tại.
 */
class OrderController extends Controller
{
    public const PER_PAGE = 10;

    public function __construct(private readonly OrderService $orders) {}

    public function index(Request $request): View
    {
        // Trạng thái lạ → bỏ qua bộ lọc (không lỗi)
        $status = OrderStatus::tryFrom((string) $request->query('trang-thai'));

        return view('account.orders.index', [
            'orders' => $request->user()->orders()
                ->when($status, fn ($q) => $q->where('status', $status))
                ->latest()
                ->latest('id')
                ->paginate(self::PER_PAGE)
                ->withQueryString(),
            'statuses' => OrderStatus::cases(),
            'currentStatus' => $status,
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        abort_if(Gate::denies('view', $order) || $order->user_id !== $request->user()->id, 404);
        $order->load(['items', 'statusHistories']);

        return view('account.orders.show', [
            'order' => $order,
            'money' => [
                'subtotal' => Money::format($order->subtotal),
                'shipping' => $order->shipping_fee > 0 ? Money::format($order->shipping_fee) : 'Miễn phí',
                'total' => Money::format($order->total),
            ],
            'canCancel' => Gate::allows('cancel', $order),
        ]);
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        // Chỉ đơn của chính mình (kể cả admin/staff: huỷ đơn người khác làm ở khu quản trị)
        abort_if($order->user_id !== $request->user()->id, 404);
        // Luồng trạng thái (shipping/delivered/cancelled không huỷ được) do OrderService kiểm, báo bằng flash
        $this->orders->cancel($order, $request->user(), __('order.history_cancelled_by_customer'));

        return redirect()->route('account.orders.show', $order)->with('status', __('order.cancelled', ['code' => $order->code]));
    }
}
