<?php

namespace Database\Factories;

use App\Enums\PostStatus;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->randomElement([
            'Bí quyết dưỡng ẩm cho da khô mùa hanh',
            'Chọn sữa rửa mặt phù hợp với làn da',
            'Ba bước trang điểm nhẹ nhàng đi làm',
        ]);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(6)),
            'excerpt' => "Gợi ý ngắn gọn: {$title}.",
            'content' => "## {$title}\n\nNội dung bài viết mẫu dạng **Markdown**.",
            'thumbnail' => null,
            'status' => PostStatus::Draft,
            'published_at' => null,
            'author_id' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(['status' => PostStatus::Published, 'published_at' => now()->subDay()]);
    }
}
