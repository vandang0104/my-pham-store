<?php

namespace App\Http\Requests\Account;

use App\Enums\SkinType;
use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Hồ sơ khách: họ tên, SĐT, ngày sinh, loại da. Email KHÔNG đổi ở đây (không có trong rules → bị bỏ qua).
 * Avatar đã cắt khỏi phạm vi rút gọn.
 */
class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => Phone::rules(),
            'birthday' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
            'skin_type' => ['nullable', Rule::enum(SkinType::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'phone' => Phone::normalize($this->input('phone')),
            'birthday' => $this->input('birthday') ?: null,
            'skin_type' => $this->input('skin_type') ?: null,
        ]);
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['birthday' => 'ngày sinh', 'skin_type' => 'loại da'];
    }
}
