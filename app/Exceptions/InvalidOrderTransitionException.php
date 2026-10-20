<?php

namespace App\Exceptions;

use App\Enums\OrderStatus;
use Exception;
use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Chuyển trạng thái đơn sai luồng (quy tắc 3), vd huỷ đơn đang giao.
 * Tự render: request thường → quay lại trang trước + flash lỗi; JSON → 422.
 */
class InvalidOrderTransitionException extends Exception implements ShouldntReport
{
    public static function make(OrderStatus $from, OrderStatus $to): self
    {
        return new self(__('order.invalid_transition', ['from' => $from->label(), 'to' => $to->label()]));
    }

    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $this->getMessage()], 422);
        }

        return back()->with('error', $this->getMessage());
    }
}
