<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

/**
 * Số liệu cho khu quản trị. Doanh thu chỉ tính đơn ĐÃ GIAO, theo orders.created_at (CLAUDE.md mục CSDL).
 * Thống kê theo ngày/tháng + top bán chạy bổ sung ở bước thống kê.
 */
class ReportService
{
    public const LOW_STOCK = 5;

    /**
     * Số liệu vận hành (admin + staff).
     *
     * @return array{pending: int, today: int, lowStock: Collection<int, Product>, latestOrders: Collection<int, Order>}
     */
    public function operations(): array
    {
        return [
            'pending' => Order::query()->where('status', OrderStatus::Pending)->count(),
            'today' => Order::query()->whereDate('created_at', today())->count(),
            'lowStock' => Product::query()->where('stock', '<=', self::LOW_STOCK)->orderBy('stock')->orderBy('name')->limit(5)->get(['id', 'name', 'sku', 'stock', 'is_active']),
            'latestOrders' => Order::query()->latest()->latest('id')->limit(5)->get(),
        ];
    }

    /**
     * Doanh thu hôm nay / tháng này (chỉ admin).
     *
     * @return array{today: int, month: int}
     */
    public function revenueSummary(): array
    {
        $delivered = fn () => Order::query()->where('status', OrderStatus::Delivered);

        return [
            'today' => (int) $delivered()->whereDate('created_at', today())->sum('total'),
            'month' => (int) $delivered()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('total'),
        ];
    }
}
