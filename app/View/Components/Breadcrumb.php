<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Breadcrumb (DESIGN.md §9) + dữ liệu có cấu trúc schema.org BreadcrumbList (JSON-LD).
 * $items: [['label' => 'Chăm sóc da mặt', 'url' => '...'], ..., ['label' => 'Trang hiện tại']]
 * "Trang chủ" được thêm tự động ở đầu; mục cuối là trang hiện tại (không link).
 */
class Breadcrumb extends Component
{
    /** @var list<array{label: string, url: string|null}> */
    public array $trail;

    /**
     * @param  list<array{label: string, url?: string|null}>  $items
     */
    public function __construct(array $items = [])
    {
        $this->trail = [
            ['label' => 'Trang chủ', 'url' => route('home')],
            ...array_map(fn (array $item) => ['label' => $item['label'], 'url' => $item['url'] ?? null], $items),
        ];
    }

    public function isLast(int $index): bool
    {
        return $index === count($this->trail) - 1;
    }

    public function jsonLd(): string
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(fn (array $item, int $i) => array_filter([
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item['label'],
                'item' => $item['url'] ?? ($this->isLast($i) ? url()->current() : null),
            ]), $this->trail, array_keys($this->trail)),
        ];

        // JSON_HEX_* để chuỗi không thể đóng thẻ <script>
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }

    public function render(): View
    {
        return view('components.breadcrumb');
    }
}
