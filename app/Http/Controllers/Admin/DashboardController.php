<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Tổng quan quản trị: số liệu vận hành cho mọi người; doanh thu chỉ admin (quy tắc 9) */
class DashboardController extends Controller
{
    public function index(Request $request, ReportService $reports): View
    {
        $revenue = $request->user()->isAdmin() ? $reports->revenueSummary() : null;

        return view('admin.dashboard', [
            'user' => $request->user(),
            'ops' => $reports->operations(),
            'revenue' => $revenue ? array_map(fn (int $amount) => Money::format($amount), $revenue) : null,
            'lowStockThreshold' => ReportService::LOW_STOCK,
        ]);
    }
}
