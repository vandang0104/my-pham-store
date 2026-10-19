<?php

namespace Database\Seeders\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Sinh ảnh placeholder SVG cục bộ trên disk public (không hotlink ảnh ngoài).
 * Mỗi hàm ghi file và trả về đường dẫn tương đối để lưu vào DB.
 */
final class PlaceholderImage
{
    public const DIR = 'placeholders';

    private const SERIF = "Georgia, 'Times New Roman', serif";

    private const SANS = "'Segoe UI', Helvetica, Arial, sans-serif";

    private const INK = '#3B2F2A';

    /** Xoá toàn bộ placeholder cũ trước khi seed lại */
    public static function reset(): void
    {
        Storage::disk('public')->deleteDirectory(self::DIR);
    }

    /**
     * @param  array{0: string, 1: string}  $colors
     */
    public static function product(string $path, string $name, string $brand, array $colors, int $variant): string
    {
        [$light, $dark] = $colors;
        $shape = match ($variant % 4) {
            // Chai serum
            0 => '<rect x="350" y="250" width="100" height="40" rx="8" fill="'.self::INK.'" opacity=".85"/>'
                .'<rect x="320" y="285" width="160" height="230" rx="28" fill="#FFFFFF" opacity=".75"/>',
            // Hũ kem
            1 => '<rect x="285" y="300" width="230" height="45" rx="10" fill="'.self::INK.'" opacity=".85"/>'
                .'<rect x="270" y="340" width="260" height="150" rx="36" fill="#FFFFFF" opacity=".75"/>',
            // Tuýp
            2 => '<path d="M330 260 h140 l-20 250 h-100 z" fill="#FFFFFF" opacity=".75"/>'
                .'<rect x="370" y="505" width="60" height="30" rx="6" fill="'.self::INK.'" opacity=".85"/>',
            // Bố cục kết cấu sản phẩm
            default => '<circle cx="400" cy="380" r="120" fill="#FFFFFF" opacity=".55"/>'
                .'<circle cx="470" cy="330" r="55" fill="'.$dark.'" opacity=".55"/>',
        };

        $svg = self::open(800, 800, $light, $dark, 135 + 30 * $variant)
            .$shape
            .self::text(400, 110, mb_strtoupper($brand), 22, self::SANS, 6, .7)
            .self::lines(400, 610, self::wrap($name, 24), 34, 44)
            .'</svg>';

        return self::put($path, $svg);
    }

    /**
     * @param  array{0: string, 1: string}  $colors
     */
    public static function banner(string $path, string $title, string $subtitle, array $colors, bool $mobile = false): string
    {
        [$light, $dark] = $colors;
        [$w, $h] = $mobile ? [800, 1000] : [1920, 720];
        $x = $mobile ? 80 : 160;
        $titleLines = self::wrap($title, $mobile ? 18 : 28);

        $svg = self::open($w, $h, $light, $dark, 120)
            .'<circle cx="'.($mobile ? 600 : 1500).'" cy="'.($mobile ? 760 : 360).'" r="'.($mobile ? 260 : 300).'" fill="#FFFFFF" opacity=".35"/>'
            .self::lines($x, $mobile ? 260 : 270, $titleLines, $mobile ? 60 : 72, $mobile ? 76 : 88, 'start')
            .self::text($x, ($mobile ? 260 : 270) + count($titleLines) * ($mobile ? 76 : 88) + 20, $subtitle, $mobile ? 28 : 32, self::SANS, 0, .8, 'start')
            .'</svg>';

        return self::put($path, $svg);
    }

    /**
     * @param  array{0: string, 1: string}  $colors
     */
    public static function cover(string $path, string $title, array $colors, int $w = 1200, int $h = 675): string
    {
        [$light, $dark] = $colors;
        $lines = self::wrap($title, 30);

        $svg = self::open($w, $h, $light, $dark, 160)
            .'<rect x="60" y="60" width="'.($w - 120).'" height="'.($h - 120).'" fill="none" stroke="#FFFFFF" stroke-opacity=".6" stroke-width="2"/>'
            .self::lines($w / 2, $h / 2 - (count($lines) - 1) * 28, $lines, 46, 56)
            .'</svg>';

        return self::put($path, $svg);
    }

    public static function logo(string $path, string $name): string
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="200" viewBox="0 0 400 200">'
            .'<rect width="400" height="200" fill="#FFFFFF"/>'
            .self::text(200, 112, mb_strtoupper($name), mb_strlen($name) > 14 ? 24 : 30, self::SERIF, 4, 1)
            .'</svg>';

        return self::put($path, $svg);
    }

    private static function open(int $w, int $h, string $light, string $dark, int $angle): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 '.$w.' '.$h.'">'
            .'<defs><linearGradient id="g" gradientTransform="rotate('.($angle % 360).' .5 .5)">'
            .'<stop offset="0" stop-color="'.$light.'"/><stop offset="1" stop-color="'.$dark.'"/></linearGradient></defs>'
            .'<rect width="'.$w.'" height="'.$h.'" fill="url(#g)"/>';
    }

    private static function text(float $x, float $y, string $content, int $size, string $font, int $spacing = 0, float $opacity = 1, string $anchor = 'middle'): string
    {
        return '<text x="'.$x.'" y="'.$y.'" font-family="'.self::esc($font).'" font-size="'.$size.'" letter-spacing="'.$spacing
            .'" fill="'.self::INK.'" fill-opacity="'.$opacity.'" text-anchor="'.$anchor.'">'.self::esc($content).'</text>';
    }

    /**
     * @param  list<string>  $lines
     */
    private static function lines(float $x, float $y, array $lines, int $size, int $lineHeight, string $anchor = 'middle'): string
    {
        $out = '';
        foreach ($lines as $i => $line) {
            $out .= self::text($x, $y + $i * $lineHeight, $line, $size, self::SERIF, 0, 1, $anchor);
        }

        return $out;
    }

    /**
     * Ngắt dòng theo số ký tự (đếm đa byte cho tiếng Việt).
     *
     * @return list<string>
     */
    private static function wrap(string $text, int $max): array
    {
        $lines = [];
        $current = '';
        foreach (explode(' ', $text) as $word) {
            $candidate = $current === '' ? $word : "{$current} {$word}";
            if ($current !== '' && mb_strlen($candidate) > $max) {
                $lines[] = $current;
                $current = $word;
            } else {
                $current = $candidate;
            }
        }

        return [...$lines, $current];
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
