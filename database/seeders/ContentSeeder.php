<?php

namespace Database\Seeders;

use App\Enums\PostStatus;
use App\Enums\UserRole;
use App\Models\Banner;
use App\Models\Faq;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\Support\PlaceholderImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * 4 banner, 8 FAQ, 6 bài tin tức (Markdown).
 */
class ContentSeeder extends Seeder
{
    /** [nền, màu khối] theo bảng màu DESIGN.md §3 */
    private const POST_COLORS = [
        [PlaceholderImage::SURFACE, PlaceholderImage::ACCENT],
        [PlaceholderImage::FIELD, PlaceholderImage::INK],
        [PlaceholderImage::SURFACE, PlaceholderImage::CHAMPAGNE],
    ];

    public function run(): void
    {
        $content = require __DIR__.'/data/content.php';

        foreach ($content['banners'] as $i => $data) {
            $slug = Str::slug($data['title']);
            Banner::factory()->create([
                'title' => $data['title'],
                'image_desktop' => PlaceholderImage::banner("banners/{$slug}-desktop.svg", $data['mau']),
                'image_mobile' => PlaceholderImage::banner("banners/{$slug}-mobile.svg", $data['mau'], mobile: true),
                'link' => $data['link'],
                'position' => $data['position'],
                'sort_order' => $i + 1,
            ]);
        }

        foreach ($content['faqs'] as $i => $data) {
            Faq::factory()->create($data + ['sort_order' => $i + 1]);
        }

        $authors = User::query()->whereIn('role', [UserRole::Admin, UserRole::Staff])->orderBy('id')->pluck('id');
        foreach ($content['posts'] as $i => $data) {
            $slug = Str::slug($data['title']);
            Post::factory()->create([
                'title' => $data['title'],
                'slug' => $slug,
                'excerpt' => $data['excerpt'],
                'content' => $data['content'],
                'thumbnail' => PlaceholderImage::cover("posts/{$slug}.svg", self::POST_COLORS[$i % count(self::POST_COLORS)]),
                'status' => PostStatus::Published,
                // Bài đầu mới nhất
                'published_at' => now()->subDays(3 + $i * 6)->setTime(9, 0),
                'author_id' => $authors[$i % $authors->count()],
            ]);
        }
    }
}
