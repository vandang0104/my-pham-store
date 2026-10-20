<?php

namespace App\Http\Requests;

use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Form liên hệ. Chống spam: ô honeypot "website" (ẩn với người thật; bot điền vào → coi như gửi xong, không lưu)
 * + giới hạn MAX_ATTEMPTS lượt gửi / DECAY_SECONDS theo IP.
 */
class ContactRequest extends FormRequest
{
    public const HONEYPOT = 'website';

    private const MAX_ATTEMPTS = 3;

    private const DECAY_SECONDS = 600;

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255'],
            // Không bắt buộc; có nhập thì phải đúng SĐT di động VN
            'phone' => ['nullable', 'string', 'regex:'.Phone::PATTERN],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $phone = trim((string) $this->input('phone'));

        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => Str::lower(trim((string) $this->input('email'))),
            'phone' => $phone === '' ? null : Phone::normalize($phone),
            'subject' => trim((string) $this->input('subject')),
            'message' => trim((string) $this->input('message')),
        ]);
    }

    public function isBot(): bool
    {
        return filled($this->input(self::HONEYPOT));
    }

    /**
     * Ghi một lượt gửi; vượt giới hạn thì báo lỗi (cả lượt của bot cũng tính).
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        $key = 'contact|'.$this->ip();

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            throw ValidationException::withMessages([
                'throttle' => __('contact.throttle', ['minutes' => (int) ceil(RateLimiter::availableIn($key) / 60)]),
            ]);
        }

        RateLimiter::hit($key, self::DECAY_SECONDS);
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['subject' => 'chủ đề', 'message' => 'nội dung'];
    }
}
