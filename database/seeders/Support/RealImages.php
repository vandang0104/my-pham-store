<?php

namespace Database\Seeders\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Ảnh thật nằm trong repo ở database/seeders/images/{san-pham,danh-muc,thuong-hieu,banner}/, đặt tên theo slug.
 * Khi seed: chép sang disk public (thư mục anh-that/) và trả đường dẫn để lưu DB; không có file thì trả null
 * để seeder dùng placeholder SVG.
 */
final class RealImages
{
    public const DIR = 'anh-that';

    private const EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    public static function reset(): void
    {
        Storage::disk('public')->deleteDirectory(self::DIR);
    }

    /** Ảnh theo tên (không đuôi) trong một nhóm, vd find('san-pham', 'serum-niacinamide-10-sang-da') */
    public static function find(string $group, string $name): ?string
    {
        foreach (self::EXTENSIONS as $ext) {
            $source = self::sourceDir()."/{$group}/{$name}.{$ext}";
            if (File::exists($source)) {
                $path = self::DIR."/{$group}/{$name}.{$ext}";
                Storage::disk('public')->put($path, File::get($source));

                return $path;
            }
        }

        return null;
    }

    /**
     * Ảnh sản phẩm: ảnh chính <slug> + ảnh phụ <slug>-2, <slug>-3… (dừng ở số đầu tiên bị thiếu).
     *
     * @return list<string>
     */
    public static function product(string $slug): array
    {
        $primary = self::find('san-pham', $slug);
        if ($primary === null) {
            return [];
        }

        $paths = [$primary];
        for ($k = 2; ($extra = self::find('san-pham', "{$slug}-{$k}")) !== null; $k++) {
            $paths[] = $extra;
        }

        return $paths;
    }

    private static function sourceDir(): string
    {
        return database_path('seeders/images');
    }
}
