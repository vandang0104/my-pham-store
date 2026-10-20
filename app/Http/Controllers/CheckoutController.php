<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Http\Requests\Checkout\PlaceOrderRequest;
use App\Models\Order;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\OrderService;
use App\Support\Money;
use App\Support\Shipping;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Thanh toán và trang xác nhận đơn.
 * Chống gửi trùng (double-click / F5 / gửi lại form): mỗi lần mở trang thanh toán sinh một token một lần
 * lưu trong session và form. Khi đặt hàng: token phải khớp session VÀ giành được khoá Cache::add(token) —
 * thao tác nguyên tử nên hai request song song cùng token chỉ một cái chạy tiếp; cái kia về trang xác nhận đơn vừa tạo.
 */
class CheckoutController extends Controller
{
    private const TOKEN_KEY = 'checkout.token';

    /** Mã các đơn đặt trong phiên này (khách vãng lai xem được trang xác nhận) */
    private const ORDERS_KEY = 'checkout.orders';

    private const LAST_KEY = 'checkout.last';

    public function __construct(private readonly CartService $cart, private readonly OrderService $orders) {}

    public function create(Request $request): View|RedirectResponse
    {
        $items = $this->cart->items();
        if ($notices = $this->cart->notices()) {
            return redirect()->route('cart.index')->with('info', implode(' ', $notices));
        }
        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', __('order.empty_cart'));
        }

        $token = (string) Str::uuid();
        $request->session()->put(self::TOKEN_KEY, $token);

        $subtotal = $this->cart->subtotal($items);
        $shipping = Shipping::fee($subtotal);
        $user = $request->user();

        return view('pages.checkout', [
            'items' => $items,
            'token' => $token,
            'summary' => [
                'subtotal' => Money::format($subtotal),
                'shipping' => $shipping > 0 ? Money::format($shipping) : 'Miễn phí',
                'total' => Money::format($subtotal + $shipping),
                'remaining' => ($left = Shipping::remainingForFree($subtotal)) > 0 ? Money::format($left) : null,
            ],
            // User đăng nhập: điền sẵn từ hồ sơ (old() vẫn ưu tiên khi form bị trả về vì lỗi)
            'defaults' => [
                'receiver_name' => $user?->name,
                'phone' => $user?->phone,
                'email' => $user?->email,
            ],
            'paymentMethods' => [PaymentMethod::Cod, PaymentMethod::BankTransfer],
        ]);
    }

    public function store(PlaceOrderRequest $request): RedirectResponse
    {
        $token = $request->validated('checkout_token');
        $session = $request->session();

        if ($token !== $session->get(self::TOKEN_KEY) || ! Cache::add('checkout-token:'.$token, true, now()->addHour())) {
            return $this->duplicate($request);
        }
        $session->forget(self::TOKEN_KEY);

        $order = $this->orders->placeOrder($request->orderData(), $request->user());

        $session->push(self::ORDERS_KEY, $order->code);
        $session->put(self::LAST_KEY, $order->code);

        return redirect()->route('checkout.success', $order)->with('status', __('order.placed', ['code' => $order->code]));
    }

    /** Chỉ chủ đơn (user) hoặc phiên vừa đặt đơn này được xem; còn lại 404 (không lộ đơn có tồn tại) */
    public function success(Request $request, Order $order): View
    {
        $owns = $order->user_id !== null && $order->user_id === $request->user()?->id;
        $placedHere = in_array($order->code, $request->session()->get(self::ORDERS_KEY, []), true);
        abort_unless($owns || $placedHere, 404);

        $order->load('items');

        return view('pages.checkout-success', [
            'order' => $order,
            'money' => [
                'subtotal' => Money::format($order->subtotal),
                'shipping' => $order->shipping_fee > 0 ? Money::format($order->shipping_fee) : 'Miễn phí',
                'total' => Money::format($order->total),
            ],
            'bank' => $order->payment_method === PaymentMethod::BankTransfer ? [
                'name' => Setting::get('bank_name'),
                'number' => Setting::get('bank_account_number'),
                'holder' => Setting::get('bank_account_name'),
            ] : null,
        ]);
    }

    /** Gửi trùng: đã có đơn vừa đặt trong phiên → về trang xác nhận đơn đó; không thì về giỏ kèm thông báo */
    private function duplicate(Request $request): RedirectResponse
    {
        $last = $request->session()->get(self::LAST_KEY);
        $order = $last ? Order::query()->where('code', $last)->first() : null;

        return $order
            ? redirect()->route('checkout.success', $order)->with('info', __('order.duplicate'))
            : redirect()->route('cart.index')->with('error', __('order.duplicate'));
    }
}
