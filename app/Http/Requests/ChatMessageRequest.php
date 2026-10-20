<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Một tin chat (khách hoặc nhân viên). Tên chỉ cần với khách vãng lai ở tin đầu tiên (controller kiểm). */
class ChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:1000'],
            'name' => ['nullable', 'string', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'body' => trim((string) $this->input('body')),
            'name' => trim((string) $this->input('name')) ?: null,
        ]);
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['body' => 'tin nhắn', 'name' => 'tên của bạn'];
    }
}
