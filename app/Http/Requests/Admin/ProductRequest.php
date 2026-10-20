<?php

namespace App\Http\Requests\Admin;

use App\Enums\SkinType;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Thêm / sửa sản phẩm (staff được thêm/sửa — ProductPolicy). Giá KM phải nhỏ hơn giá; 1 ảnh chính. */
class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $product = $this->route('product');

        return $product ? $this->user()->can('update', $product) : $this->user()->can('create', Product::class);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $id = $this->route('product')?->id;

        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
            'name' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:220', 'alpha_dash', Rule::unique('products', 'slug')->ignore($id)],
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($id)],
            'price' => ['required', 'integer', 'min:1000', 'max:100000000'],
            'sale_price' => ['nullable', 'integer', 'min:0', 'lt:price'],
            'stock' => ['required', 'integer', 'min:0', 'max:100000'],
            'volume' => ['nullable', 'string', 'max:50'],
            'skin_type' => ['nullable', Rule::enum(SkinType::class)],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:20000'],
            'ingredients' => ['nullable', 'string', 'max:5000'],
            'usage_guide' => ['nullable', 'string', 'max:5000'],
            'is_featured' => ['boolean'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'slug' => trim((string) $this->input('slug')) ?: null,
            'sku' => strtoupper(trim((string) $this->input('sku'))),
            'sale_price' => $this->input('sale_price') === '' ? null : $this->input('sale_price'),
            'skin_type' => $this->input('skin_type') ?: null,
            'is_featured' => $this->boolean('is_featured'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['sale_price.lt' => 'Giá khuyến mãi phải nhỏ hơn giá bán.'];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'category_id' => 'danh mục', 'brand_id' => 'thương hiệu', 'name' => 'tên sản phẩm', 'slug' => 'đường dẫn',
            'sku' => 'mã SKU', 'price' => 'giá', 'sale_price' => 'giá khuyến mãi', 'stock' => 'tồn kho', 'volume' => 'dung tích',
            'skin_type' => 'loại da', 'short_description' => 'mô tả ngắn', 'description' => 'mô tả', 'ingredients' => 'thành phần',
            'usage_guide' => 'hướng dẫn', 'image' => 'ảnh',
        ];
    }
}
