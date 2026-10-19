<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cod = 'cod';
    case BankTransfer = 'bank_transfer';
    // ĐÃ CẮT (phạm vi rút gọn 19/10): giữ case vì cột orders.payment_method và test còn dùng; thanh toán chỉ COD + chuyển khoản
    case Vnpay = 'vnpay';

    public function label(): string
    {
        return match ($this) {
            self::Cod => 'Thanh toán khi nhận hàng (COD)',
            self::BankTransfer => 'Chuyển khoản ngân hàng',
            self::Vnpay => 'VNPay',
        };
    }
}
