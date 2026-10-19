<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Đăng ký tài khoản khách hàng. Không nhận role từ request: role do DB mặc định là customer
 * và không nằm trong $fillable của User.
 */
class RegisterRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            // Đã chuẩn hoá về dạng 0xxxxxxxxx: 10 số, đầu số di động 03/05/07/08/09
            'phone' => ['required', 'string', 'regex:/^0[35789]\d{8}$/'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ];
    }

    /**
     * Chuẩn hoá trước khi kiểm tra: email viết thường; số điện thoại bỏ khoảng trắng/dấu chấm/gạch, +84 → 0.
     */
    protected function prepareForValidation(): void
    {
        $phone = preg_replace('/[\s.\-()]/', '', (string) $this->input('phone'));

        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => Str::lower(trim((string) $this->input('email'))),
            'phone' => Str::startsWith($phone, '+84') ? '0'.substr($phone, 3) : $phone,
        ]);
    }
}
