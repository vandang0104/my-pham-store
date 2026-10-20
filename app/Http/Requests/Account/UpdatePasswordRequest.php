<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/** Đổi mật khẩu: phải đúng mật khẩu hiện tại; mật khẩu mới cùng luật với đăng ký (≥ 8 ký tự, nhập lại khớp) */
class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password:web'],
            'password' => ['required', 'string', 'confirmed', Password::min(8), 'different:current_password'],
        ];
    }
}
