<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
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
     * Doanh thu đơn đã giao theo từng ngày trong khoảng [from, to] (ngày không có đơn = 0).
     * Gom nhóm ở PHP để chạy được cả MySQL và SQLite (test); dữ liệu đồ án nhỏ.
     *
     * @return array{labels: list<string>, data: list<int>, total: int}
     */
    public function revenueByDay(CarbonInterface $from, CarbonInterface $to): array
    {
        $byDay = $this->deliveredBetween($from->copy()->startOfDay(), $to->copy()->endOfDay())
            ->groupBy(fn (Order $o) => $o->created_at->format('Y-m-d'))
            ->map(fn ($orders) => (int) $orders->sum('total'));

        $labels = [];
        $data = [];
        foreach (CarbonPeriod::create($from->copy()->startOfDay(), '1 day', $to->copy()->startOfDay()) as $day) {
            $labels[] = $day->format('d/m');
            $data[] = $byDay[$day->format('Y-m-d')] ?? 0;
        }

        return ['labels' => $labels, 'data' => $data, 'total' => array_sum($data)];
    }

    /**
     * Doanh thu đơn đã giao theo tháng, $months tháng gần nhất (tính cả tháng hiện tại).
     *
     * @return array{labels: list<string>, data: list<int>, total: int}
     */
    public function revenueByMonth(int $months = 12): array
    {
        $start = now()->startOfMonth()->subMonths($months - 1);
        $byMonth = $this->deliveredBetween($start, now()->endOfMonth())
            ->groupBy(fn (Order $o) => $o->created_at->format('Y-m'))
            ->map(fn ($orders) => (int) $orders->sum('total'));

        $labels = [];
        $data = [];
        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i);
            $labels[] = $month->format('m/Y');
            $data[] = $byMonth[$month->format('Y-m')] ?? 0;
        }

        return ['labels' => $labels, 'data' => $data, 'total' => array_sum($data)];
    }

    /**
     * Top sản phẩm bán chạy (theo số lượng) trong các đơn đã giao trong khoảng ngày.
     *
     * @return \Illuminate\Support\Collection<int, object{product_id: ?int, product_name: string, quantity: int, revenue: int}>
     */
    public function topProducts(CarbonInterface $from, CarbonInterface $to, int $limit = 10)
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', OrderStatus::Delivered->value)
            ->whereBetween('orders.created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->selectRaw('order_items.product_id, order_items.product_name, SUM(order_items.quantity) AS quantity, SUM(order_items.line_total) AS revenue')
            ->orderByDesc('quantity')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->toBase()
            ->get()
            ->map(fn ($row) => (object) [
                'product_id' => $row->product_id,
                'product_name' => $row->product_name,
                'quantity' => (int) $row->quantity,
                'revenue' => (int) $row->revenue,
            ]);
    }

    /** @return \Illuminate\Support\Collection<int, Order> */
    private function deliveredBetween(CarbonInterface $from, CarbonInterface $to)
    {
        return Order::query()
            ->where('status', OrderStatus::Delivered)
            ->whereBetween('created_at', [$from, $to])
            ->get(['id', 'total', 'created_at']);
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
