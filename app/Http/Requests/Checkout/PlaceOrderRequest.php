<?php

namespace App\Http\Requests\Checkout;

use App\Enums\PaymentMethod;
use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Form thanh toán: người nhận, địa chỉ 2–3 cấp (ô chữ), ghi chú, phương thức (COD / chuyển khoản — VNPay đã cắt).
 * Email bắt buộc với khách vãng lai; user đăng nhập bỏ trống thì dùng email tài khoản.
 */
class PlaceOrderRequest extends FormRequest
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
            'receiver_name' => ['required', 'string', 'max:100'],
            'phone' => Phone::rules(),
            'email' => [Rule::requiredIf($this->user() === null), 'nullable', 'string', 'email', 'max:255'],
            'province' => ['required', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'ward' => ['required', 'string', 'max:100'],
            'detail' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', Rule::in([PaymentMethod::Cod->value, PaymentMethod::BankTransfer->value])],
            'checkout_token' => ['required', 'string', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $trim = fn (string $key) => is_string($this->input($key)) ? trim($this->input($key)) : $this->input($key);

        $this->merge([
            'receiver_name' => $trim('receiver_name'),
            'phone' => Phone::normalize($this->input('phone')),
            'email' => is_string($this->input('email')) && trim($this->input('email')) !== '' ? Str::lower(trim($this->input('email'))) : null,
            'province' => $trim('province'),
            'district' => $trim('district') ?: null,
            'ward' => $trim('ward'),
            'detail' => $trim('detail'),
            'note' => $trim('note') ?: null,
        ]);
    }

    /**
     * Dữ liệu cho OrderService::placeOrder() (bỏ token).
     *
     * @return array{receiver_name: string, phone: string, email: ?string, province: string, district: ?string, ward: string, detail: string, note: ?string, payment_method: string}
     */
    public function orderData(): array
    {
        return collect($this->validated())->except('checkout_token')->all();
    }
}
