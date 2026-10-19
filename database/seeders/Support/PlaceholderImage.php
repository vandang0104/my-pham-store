<?php

namespace Database\Seeders\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Sinh ảnh placeholder SVG cục bộ trên disk public (không hotlink) theo DESIGN.md §6:
 * mảng màu PHẲNG theo bảng màu (không gradient), góc vuông, đúng tỉ lệ ảnh thật.
 * Mỗi hàm ghi file và trả về đường dẫn tương đối để lưu vào DB.
 *
 * Ảnh sản phẩm KHÔNG in chữ: SVG nạp qua <img> không dùng được web font của trang nên chữ rơi về Georgia
 * và vỡ dấu tiếng Việt ("Hồng" → "Hồ ng"). Thẻ sản phẩm vẽ tên bằng Cormorant lên trên ảnh giữ chỗ (HTML).
 */
final class PlaceholderImage
{
    public const DIR = 'placeholders';

    // Bảng màu DESIGN.md §3
    public const GROUND = '#F5F2EC';

    public const SURFACE = '#EBE5DA';

    public const FIELD = '#D3DDD4';

    public const CHAMPAGNE = '#C2A878';

    public const INK = '#1E1B18';

    public const ACCENT = '#2E5A4C';

    private const SERIF = "'Cormorant Garamond', Georgia, 'Times New Roman', serif";

    /** Xoá toàn bộ placeholder cũ trước khi seed lại */
    public static function reset(): void
    {
        Storage::disk('public')->deleteDirectory(self::DIR);
    }

    /**
     * Ảnh sản phẩm 4:5 (800×1000): nền phẳng + dáng sản phẩm phẳng (chai, hũ, tuýp, chai xịt), không chữ.
     *
     * @param  array{0: string, 1: string}  $colors  [nền, màu dáng sản phẩm]
     */
    public static function product(string $path, array $colors, int $variant): string
    {
        [$bg, $shape] = $colors;
        $cap = $shape === self::INK ? self::CHAMPAGNE : self::INK;

        $object = match ($variant % 4) {
            // Chai serum có nắp nhỏ giọt
            0 => '<rect x="350" y="250" width="100" height="60" fill="'.$cap.'"/>'
                .'<rect x="318" y="310" width="164" height="300" fill="'.$shape.'"/>',
            // Hũ kem
            1 => '<rect x="262" y="330" width="276" height="56" fill="'.$cap.'"/>'
                .'<rect x="248" y="386" width="304" height="196" fill="'.$shape.'"/>',
            // Tuýp
            2 => '<path d="M318 250 h164 l-24 330 h-116 z" fill="'.$shape.'"/>'
                .'<rect x="364" y="580" width="72" height="40" fill="'.$cap.'"/>',
            // Chai xịt
            default => '<rect x="380" y="232" width="40" height="40" fill="'.$cap.'"/>'
                .'<rect x="360" y="272" width="80" height="40" fill="'.$cap.'"/>'
                .'<rect x="298" y="312" width="204" height="300" fill="'.$shape.'"/>',
        };

        $svg = self::open(800, 1000, $bg).$object.'</svg>';

        return self::put($path, $svg);
    }

    /**
     * Banner: chỉ mảng màu và hình khối — KHÔNG in chữ vào ảnh (chữ đặt cạnh ảnh trên trang, DESIGN.md §6).
     *
     * @param  array{0: string, 1: string}  $colors  [nền, màu khối]
     */
    public static function banner(string $path, array $colors, bool $mobile = false): string
    {
        [$bg, $shape] = $colors;
        [$w, $h] = $mobile ? [800, 1000] : [1920, 720];

        $svg = self::open($w, $h, $bg)
            .($mobile
                ? '<rect x="160" y="300" width="480" height="560" fill="'.$shape.'"/><rect x="300" y="200" width="200" height="100" fill="'.self::INK.'"/>'
                : '<rect x="1180" y="140" width="460" height="580" fill="'.$shape.'"/><rect x="1320" y="60" width="180" height="80" fill="'.self::INK.'"/>')
            .'</svg>';

        return self::put($path, $svg);
    }

    /**
     * Ảnh bìa (danh mục 1:1, bài viết 16:9): nền phẳng + một khối màu, không chữ (tên hiện bằng HTML cạnh ảnh).
     *
     * @param  array{0: string, 1: string}  $colors  [nền, màu khối]
     */
    public static function cover(string $path, array $colors, int $w = 1200, int $h = 675): string
    {
        [$bg, $shape] = $colors;

        $svg = self::open($w, $h, $bg)
            .'<rect x="'.($w - $w * .32).'" y="0" width="'.($w * .32).'" height="'.($h * .58).'" fill="'.$shape.'"/>'
            .'<rect x="'.($w * .08).'" y="'.($h * .7).'" width="'.($w * .24).'" height="'.($h * .3).'" fill="'.self::INK.'"/>'
            .'</svg>';

        return self::put($path, $svg);
    }

    public static function logo(string $path, string $name): string
    {
        $svg = self::open(400, 200, self::GROUND)
            .self::text(200, 112, $name, mb_strlen($name) > 14 ? 26 : 32, self::SERIF, 600, 'middle')
            .'</svg>';

        return self::put($path, $svg);
    }

    private static function open(int $w, int $h, string $bg): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 '.$w.' '.$h.'">'
            .'<rect width="'.$w.'" height="'.$h.'" fill="'.$bg.'"/>';
    }

    private static function text(float $x, float $y, string $content, int $size, string $font, int $weight, string $anchor): string
    {
        return '<text x="'.$x.'" y="'.$y.'" font-family="'.self::esc($font).'" font-size="'.$size.'" font-weight="'.$weight
            .'" fill="'.self::INK.'" text-anchor="'.$anchor.'">'.self::esc($content).'</text>';
    }

    private static function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private static function put(string $path, string $svg): string
    {
        $path = self::DIR.'/'.$path;
        Storage::disk('public')->put($path, $svg);

        return $path;
    }
}
