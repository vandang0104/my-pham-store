<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Lỗi nghiệp vụ giỏ hàng (hết hàng, vượt tồn kho, sản phẩm ngừng bán).
 * Tự render: request thường → quay lại trang trước + flash lỗi; JSON → 422. Controller không cần try/catch.
 * ShouldntReport: lỗi nghiệp vụ bình thường, không ghi vào log lỗi.
 */
class CartException extends Exception implements ShouldntReport
{
    /**
     * @param  array<string, mixed>  $replace
     */
    public static function because(string $key, array $replace = []): self
    {
        return new self(__('cart.'.$key, $replace));
    }

    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $this->getMessage()], 422);
        }

        return back()->with('error', $this->getMessage());
    }
}
