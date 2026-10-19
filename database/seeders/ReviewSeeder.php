<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\ReviewStatus;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Database\Seeder;
use LogicException;

/**
 * 30 đánh giá, chỉ từ sản phẩm trong đơn đã giao của khách có tài khoản (quy tắc 6), không trùng user+sản phẩm.
 * rating_avg/rating_count của sản phẩm chỉ tính đánh giá đã duyệt.
 */
class ReviewSeeder extends Seeder
{
    private const TOTAL = 30;

    private const APPROVED = 24;

    private const PENDING = 4;

    public function run(): void
    {
        $candidates = OrderItem::query()
            ->with('order')
            ->whereHas('order', fn ($q) => $q->where('status', OrderStatus::Delivered)->whereNotNull('user_id'))
            ->get()
            ->unique(fn (OrderItem $item) => $item->order->user_id.'-'.$item->product_id)
            ->values();

        if ($candidates->count() < self::TOTAL) {
            throw new LogicException('Không đủ cặp khách–sản phẩm từ đơn đã giao để tạo '.self::TOTAL.' đánh giá.');
        }

        $comments = require __DIR__.'/data/reviews.php';

        foreach (fake()->randomElements($candidates->all(), self::TOTAL) as $i => $item) {
            $status = match (true) {
                $i < self::APPROVED => ReviewStatus::Approved,
                $i < self::APPROVED + self::PENDING => ReviewStatus::Pending,
                default => ReviewStatus::Rejected,
            };
            $rating = $status === ReviewStatus::Rejected
                ? fake()->randomElement([2, 3])
                : fake()->randomElement([5, 5, 5, 5, 4, 4, 4, 3, 2]);
            $writtenAt = $item->order->delivered_at->copy()->addDays(fake()->numberBetween(1, 5))->min(now());

            Review::query()->forceCreate([
                'product_id' => $item->product_id,
                'user_id' => $item->order->user_id,
                'order_id' => $item->order_id,
                'rating' => $rating,
                'comment' => fake()->randomElement($comments[$rating]),
                'images' => null,
                'status' => $status,
                'created_at' => $writtenAt,
                'updated_at' => $writtenAt,
            ]);
        }

        Review::approved()
            ->selectRaw('product_id, AVG(rating) as avg_rating, COUNT(*) as total')
            ->groupBy('product_id')
            ->get()
            ->each(fn ($row) => Product::query()->whereKey($row->product_id)->update([
                'rating_avg' => round((float) $row->avg_rating, 2),
                'rating_count' => (int) $row->total,
            ]));
    }
}
