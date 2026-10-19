<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Thông báo flash dưới header (DESIGN.md §9). Đọc session: 'error' → lỗi; 'status' / 'success' → thành công;
 * 'info' → thông tin. Mỗi dải vẽ bằng <x-flash-message> (role và tự ẩn quyết định theo loại).
 */
class Flash extends Component
{
    private const KEYS = ['error' => 'error', 'status' => 'success', 'success' => 'success', 'info' => 'info'];

    /** @var list<array{type: string, message: string}> */
    public array $messages = [];

    public function __construct()
    {
        foreach (self::KEYS as $key => $type) {
            $message = session($key);
            if (is_string($message) && $message !== '') {
                $this->messages[] = ['type' => $type, 'message' => $message];
            }
        }
    }

    public function shouldRender(): bool
    {
        return $this->messages !== [];
    }

    public function render(): View
    {
        return view('components.flash');
    }
}
