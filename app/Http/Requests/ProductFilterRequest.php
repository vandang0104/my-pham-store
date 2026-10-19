<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Tham số tìm kiếm / lọc danh sách sản phẩm (GET):
 * ?q=&danh-muc=<slug>&thuong-hieu[]=<slug>&gia-tu=&gia-den=&khuyen-mai=1
 * Tham số sai KHÔNG báo lỗi (không redirect, không 500): bị bỏ qua, các tham số đúng vẫn áp dụng.
 */
class ProductFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'danh-muc' => ['nullable', 'string', 'max:170'],
            'thuong-hieu' => ['nullable', 'array', 'max:20'],
            'thuong-hieu.*' => ['string', 'max:170'],
            'gia-tu' => ['nullable', 'integer', 'min:0'],
            // Chỉ so với "giá từ" khi giá từ là số hợp lệ (gte với ô trống luôn trượt → giá đến bị bỏ oan)
            'gia-den' => ['nullable', 'integer', 'min:0', ...($this->validPriceFrom() ? ['gte:gia-tu'] : [])],
            'khuyen-mai' => ['nullable', 'in:1'],
        ];
    }

    /** Một chữ (?thuong-hieu=slug) cũng nhận như mảng; bỏ khoảng trắng thừa */
    protected function prepareForValidation(): void
    {
        $brands = $this->input('thuong-hieu');

        $this->merge([
            'q' => is_string($this->input('q')) ? trim($this->input('q')) : $this->input('q'),
            'thuong-hieu' => is_string($brands) ? [$brands] : $brands,
        ]);
    }

    private function validPriceFrom(): bool
    {
        $from = $this->input('gia-tu');

        return is_string($from) && ctype_digit($from) || is_int($from) && $from >= 0;
    }

    /** Không ném lỗi: filters() chỉ lấy các tham số hợp lệ */
    protected function failedValidation(Validator $validator): void {}

    /**
     * Bộ lọc đã chuẩn hoá cho scope Product::filter().
     *
     * @return array{q: ?string, category: ?string, brands: list<string>, price_from: ?int, price_to: ?int, on_sale: bool}
     */
    public function filters(): array
    {
        $valid = $this->getValidatorInstance()->valid();
        $int = fn (string $key) => isset($valid[$key]) && $valid[$key] !== '' ? (int) $valid[$key] : null;

        return [
            'q' => ($valid['q'] ?? '') !== '' ? $valid['q'] : null,
            'category' => ($valid['danh-muc'] ?? '') !== '' ? $valid['danh-muc'] : null,
            'brands' => array_values(array_filter((array) ($valid['thuong-hieu'] ?? []), 'is_string')),
            'price_from' => $int('gia-tu'),
            'price_to' => $int('gia-den'),
            'on_sale' => ($valid['khuyen-mai'] ?? null) === '1',
        ];
    }
}
