<?php

namespace App\Policies;

use App\Enums\OrderStatus;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /** Danh sách đánh giá trong khu quản trị (lọc chờ duyệt…) */
    public function viewAny(User $user): bool
    {
        return $user->isBackOffice();
    }

    /**
     * Viết đánh giá cho một sản phẩm (quy tắc 6): đã có đơn ĐÃ GIAO chứa sản phẩm và chưa từng đánh giá sản phẩm này.
     * Gọi: Gate::allows('create', [Review::class, $product]).
     */
    public function create(User $user, Product $product): bool
    {
        $hasDeliveredOrder = $user->orders()
            ->where('status', OrderStatus::Delivered)
            ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
            ->exists();

        return $hasDeliveredOrder
            && ! $user->reviews()->where('product_id', $product->id)->exists();
    }

    /** Duyệt hoặc từ chối đánh giá */
    public function moderate(User $user, Review $review): bool
    {
        return $user->isBackOffice();
    }

    /** Staff không xoá dữ liệu */
    public function delete(User $user, Review $review): bool
    {
        return $user->isAdmin();
    }
}
