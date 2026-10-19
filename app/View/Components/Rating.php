<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Điểm đánh giá (DESIGN.md §9): 5 sao SVG (đầy / nửa / rỗng) + "4,7" + "(23 đánh giá)".
 * Chưa có đánh giá → chỉ ghi "Chưa có đánh giá", không vẽ sao.
 */
class Rating extends Component
{
    /** @var list<string> tên icon cho 5 sao */
    public array $stars = [];

    public string $score;

    public function __construct(public float $value = 0, public int $count = 0)
    {
        $this->value = max(0, min(5, $value));
        $this->score = number_format($this->value, 1, ',', '');

        for ($i = 1; $i <= 5; $i++) {
            $fraction = $this->value - ($i - 1);
            $this->stars[] = match (true) {
                $fraction >= .75 => 'star-filled',
                $fraction >= .25 => 'star-half',
                default => 'star',
            };
        }
    }

    public function hasReviews(): bool
    {
        return $this->count > 0;
    }

    public function render(): View
    {
        return view('components.rating');
    }
}
