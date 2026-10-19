<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;
use InvalidArgumentException;

/**
 * Icon SVG inline (DESIGN.md §7): lưới 24×24, nét 1.5px, đầu tròn, ăn màu chữ (currentColor).
 * Có $label → role="img" + aria-label; không có → aria-hidden (icon trang trí).
 */
class Icon extends Component
{
    /** Nội dung SVG của từng icon (nét; 'fill' trong path là phần tô đặc) */
    public const PATHS = [
        'search' => '<circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/>',
        'bag' => '<path d="M5.5 8.5h13l-1 11.5h-11z"/><path d="M9 8.5V7a3 3 0 0 1 6 0v1.5"/>',
        'user' => '<circle cx="12" cy="8.5" r="3.5"/><path d="M5 20c.8-3.6 3.6-5.5 7-5.5s6.2 1.9 7 5.5"/>',
        'heart' => '<path d="M12 19.5s-7.5-4.4-7.5-10A4 4 0 0 1 12 7.2a4 4 0 0 1 7.5 2.3c0 5.6-7.5 10-7.5 10z"/>',
        'star' => '<path d="m12 4 2.35 4.76 5.25.77-3.8 3.7.9 5.23L12 16l-4.7 2.46.9-5.23-3.8-3.7 5.25-.77z"/>',
        'star-filled' => '<path fill="currentColor" d="m12 4 2.35 4.76 5.25.77-3.8 3.7.9 5.23L12 16l-4.7 2.46.9-5.23-3.8-3.7 5.25-.77z"/>',
        'star-half' => '<path d="m12 4 2.35 4.76 5.25.77-3.8 3.7.9 5.23L12 16l-4.7 2.46.9-5.23-3.8-3.7 5.25-.77z"/><path fill="currentColor" d="M12 4v12l-4.7 2.46.9-5.23-3.8-3.7 5.25-.77z"/>',
        'arrow-left' => '<path d="M19 12H5m6-6-6 6 6 6"/>',
        'arrow-right' => '<path d="M5 12h14m-6-6 6 6-6 6"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'close' => '<path d="M6 6l12 12M18 6 6 18"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'minus' => '<path d="M5 12h14"/>',
        'check' => '<path d="m5 12.5 4.5 4.5L19 7.5"/>',
        'info' => '<circle cx="12" cy="12" r="8.5"/><path d="M12 11v5M12 8h.01"/>',
    ];

    public function __construct(
        public string $name,
        public int $size = 24,
        public ?string $label = null,
    ) {
        if (! array_key_exists($name, self::PATHS)) {
            throw new InvalidArgumentException("Không có icon [{$name}]. Có: ".implode(', ', array_keys(self::PATHS)));
        }
    }

    public function paths(): string
    {
        return self::PATHS[$this->name];
    }

    public function render(): View
    {
        return view('components.icon');
    }
}
