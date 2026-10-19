<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cod = 'cod';
    case BankTransfer = 'bank_transfer';
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
