<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Shipping = 'shipping';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Chờ xác nhận',
            self::Confirmed => 'Đã xác nhận',
            self::Shipping => 'Đang giao hàng',
            self::Delivered => 'Đã giao hàng',
            self::Cancelled => 'Đã huỷ',
        };
    }

    /**
     * Các trạng thái được phép chuyển tới từ trạng thái hiện tại.
     * pending → confirmed → shipping → delivered; chỉ huỷ được khi pending/confirmed.
     *
     * @return list<self>
     */
    public function transitions(): array
    {
        return match ($this) {
            self::Pending => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::Shipping, self::Cancelled],
            self::Shipping => [self::Delivered],
            self::Delivered, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->transitions(), true);
    }

    public function isFinal(): bool
    {
        return $this->transitions() === [];
    }
}
