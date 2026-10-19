<?php

namespace Database\Seeders\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Ảnh thật do người dùng đặt trong storage/app/public/seed/ (chỉ đọc thư mục gốc, không đệ quy).
 * Có ảnh thì gán luân phiên cho sản phẩm; không có thì seeder dùng placeholder SVG.
 */
final class SeedImages
{
    public const DIR = 'seed';

    private const EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'avif', 'svg'];

    /** @var list<string> */
    private array $files;

    private int $cursor = 0;

    public function __construct()
    {
        $this->files = collect(Storage::disk('public')->files(self::DIR))
            ->filter(fn (string $file) => in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), self::EXTENSIONS, true))
            ->sort()
            ->values()
            ->all();
    }

    public function available(): bool
    {
        return $this->files !== [];
    }

    public function count(): int
    {
        return count($this->files);
    }

    /** Ảnh kế tiếp theo vòng tròn */
    public function next(): string
    {
        return $this->files[$this->cursor++ % count($this->files)];
    }
}
