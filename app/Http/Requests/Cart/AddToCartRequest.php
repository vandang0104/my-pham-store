<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Còn bán / còn hàng / vượt tồn kho do CartService kiểm (báo lỗi nghiệp vụ bằng flash).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:999'],
        ];
    }

    public function quantity(): int
    {
        return (int) ($this->validated('quantity') ?? 1);
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['product_id' => 'sản phẩm', 'quantity' => 'số lượng'];
    }
}
