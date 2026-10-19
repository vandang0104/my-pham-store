<?php

namespace App\Policies;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /** Danh sách đơn trong khu quản trị */
    public function viewAny(User $user): bool
    {
        return $user->isBackOffice();
    }

    /** Admin/staff xem mọi đơn; khách chỉ xem đơn của mình */
    public function view(User $user, Order $order): bool
    {
        return $user->isBackOffice() || $this->owns($user, $order);
    }

    /** Đổi trạng thái theo luồng (xác nhận, giao hàng, đã giao); việc kiểm tra luồng nằm ở OrderService */
    public function updateStatus(User $user, Order $order): bool
    {
        return $user->isBackOffice();
    }

    /** Đánh dấu đã thanh toán (chuyển khoản) */
    public function updatePayment(User $user, Order $order): bool
    {
        return $user->isBackOffice();
    }

    /** Huỷ đơn: chỉ khi đơn còn ở pending/confirmed; khách chỉ huỷ đơn của chính mình */
    public function cancel(User $user, Order $order): bool
    {
        if (! $order->status->canTransitionTo(OrderStatus::Cancelled)) {
            return false;
        }

        return $user->isBackOffice() || $this->owns($user, $order);
    }

    /** Không xoá đơn còn hiệu lực để giữ số liệu doanh thu; admin chỉ xoá được đơn đã huỷ */
    public function delete(User $user, Order $order): bool
    {
        return $user->isAdmin() && $order->status === OrderStatus::Cancelled;
    }

    private function owns(User $user, Order $order): bool
    {
        return $order->user_id !== null && $order->user_id === $user->id;
    }
}
