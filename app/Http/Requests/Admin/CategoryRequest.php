<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Thêm / sửa danh mục. Cây 2 cấp: danh mục cha phải là danh mục gốc, không phải chính nó;
 * danh mục đang có danh mục con thì không được gắn vào cha khác.
 */
class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $category = $this->route('category');

        return $category ? $this->user()->can('update', $category) : $this->user()->can('create', Category::class);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var Category|null $category */
        $category = $this->route('category');

        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:170', 'alpha_dash', Rule::unique('categories', 'slug')->ignore($category?->id)],
            'parent_id' => [
                'nullable', 'integer',
                Rule::exists('categories', 'id')->whereNull('parent_id'),
                function (string $attribute, mixed $value, Closure $fail) use ($category) {
                    if ($category && $value !== null && (int) $value === $category->id) {
                        $fail('Danh mục cha không được là chính nó.');
                    }
                    if ($category && $value !== null && $category->children()->exists()) {
                        $fail('Danh mục đang có danh mục con nên không thể làm danh mục con.');
                    }
                },
            ],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'slug' => trim((string) $this->input('slug')) ?: null,
            'parent_id' => $this->input('parent_id') ?: null,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['name' => 'tên danh mục', 'slug' => 'đường dẫn', 'parent_id' => 'danh mục cha', 'sort_order' => 'thứ tự', 'image' => 'ảnh'];
    }
}
