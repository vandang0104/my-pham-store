<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ForgotPasswordRequest extends FormRequest
{
    /** Số lần gửi yêu cầu tối đa trong 1 phút (theo email + IP) */
    private const MAX_ATTEMPTS = 3;

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
            'email' => ['required', 'string', 'email'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['email' => Str::lower(trim((string) $this->input('email')))]);
    }

    /**
     * Ghi nhận một lượt gửi; vượt giới hạn thì báo lỗi (áp dụng như nhau dù email có tồn tại hay không).
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        $key = 'forgot-password|'.Str::transliterate($this->string('email').'|'.$this->ip());

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            throw ValidationException::withMessages([
                'email' => __('auth.throttle', ['seconds' => RateLimiter::availableIn($key)]),
            ]);
        }

        RateLimiter::hit($key, 60);
    }
}
