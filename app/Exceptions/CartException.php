<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Lỗi nghiệp vụ giỏ hàng (hết hàng, vượt tồn kho, sản phẩm ngừng bán).
 * Tự render: request thường → quay lại trang trước + flash lỗi; JSON → 422. Controller không cần try/catch.
 */
class CartException extends Exception
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
