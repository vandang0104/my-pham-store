<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Lỗi nghiệp vụ khi đặt hàng (giỏ trống, giỏ đã thay đổi, thiếu hàng lúc trừ kho).
 * Tự render: request thường → về trang giỏ hàng + flash lỗi (khách xem lại giỏ); JSON → 422.
 */
class OrderException extends Exception implements ShouldntReport
{
    /**
     * @param  array<string, mixed>  $replace
     */
    public static function because(string $key, array $replace = []): self
    {
        return new self(__('order.'.$key, $replace));
    }

    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $this->getMessage()], 422);
        }

        return redirect()->route('cart.index')->with('error', $this->getMessage());
    }
}
