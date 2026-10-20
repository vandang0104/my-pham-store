<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Support\Money;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Thống kê doanh thu (CHỈ admin — route role:admin): theo ngày trong khoảng lọc (mặc định 30 ngày gần nhất),
 * theo tháng (12 tháng), top 10 bán chạy. Chỉ đơn đã giao, theo orders.created_at.
 */
class ReportController extends Controller
{
    /** Khoảng lọc tối đa (ngày) để biểu đồ không quá dài */
    private const MAX_DAYS = 366;

    public function __invoke(Request $request, ReportService $reports): View
    {
        [$from, $to] = $this->range($request);

        $daily = $reports->revenueByDay($from, $to);
        $monthly = $reports->revenueByMonth(12);

        return view('admin.reports.index', [
            'from' => $from,
            'to' => $to,
            'daily' => $daily,
            'monthly' => $monthly,
            'dailyTotal' => Money::format($daily['total']),
            'monthlyTotal' => Money::format($monthly['total']),
            'top' => $reports->topProducts($from, $to, 10)->map(fn ($row) => (object) [...(array) $row, 'formatted_revenue' => Money::format($row->revenue)]),
            'money' => fn (int $amount) => Money::format($amount),
        ]);
    }

    /** @return array{0: Carbon, 1: Carbon} — ngày sai / đảo ngược / quá dài thì về mặc định 30 ngày */
    private function range(Request $request): array
    {
        $parse = function (mixed $value): ?Carbon {
            try {
                return is_string($value) && $value !== '' ? Carbon::createFromFormat('Y-m-d', $value)->startOfDay() : null;
            } catch (InvalidFormatException) {
                return null; // ngày gõ sai định dạng → dùng mặc định
            }
        };

        $to = $parse($request->query('den-ngay')) ?? today();
        $from = $parse($request->query('tu-ngay')) ?? $to->copy()->subDays(29);

        if ($from->gt($to) || $from->diffInDays($to) > self::MAX_DAYS) {
            $to = today();
            $from = $to->copy()->subDays(29);
        }

        return [$from, $to];
    }
}
