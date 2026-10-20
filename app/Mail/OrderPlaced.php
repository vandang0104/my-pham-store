<?php

namespace App\Mail;

use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\Setting;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mail xác nhận đơn hàng (Markdown, tiếng Việt). Driver mặc định: log (storage/logs/laravel.log).
 * Mọi URL trong mail phải tuyệt đối: route() đã tuyệt đối; ảnh disk public thì bọc url(...) (xem config/filesystems.php).
 */
class OrderPlaced extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('order.mail_subject', ['code' => $this->order->code, 'app' => config('app.name')]));
    }

    public function content(): Content
    {
        $order = $this->order->loadMissing('items');

        return new Content(markdown: 'emails.orders.placed', with: [
            'order' => $order,
            'money' => fn (int $amount) => Money::format($amount),
            'bank' => $order->payment_method === PaymentMethod::BankTransfer ? [
                'name' => Setting::get('bank_name'),
                'number' => Setting::get('bank_account_number'),
                'holder' => Setting::get('bank_account_name'),
            ] : null,
            'hotline' => Setting::get('store_hotline'),
            // Khách có tài khoản xem đơn ở trang tài khoản (S4); khách vãng lai không có link xem lại
            'orderUrl' => $order->user_id ? url('/tai-khoan/don-hang/'.$order->code) : null,
            'shopUrl' => route('home'),
        ]);
    }
}
