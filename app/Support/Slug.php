<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Slug duy nhất từ tiếng Việt (Str::slug chuyển dấu → ASCII, "đ" → "d"): trùng thì thêm -2, -3…
 * Tính cả bản ghi đã xoá mềm (cột slug unique ở DB).
 */
final class Slug
{
    /** @param class-string<Model> $model */
    public static function unique(string $model, string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source) ?: 'muc';
        $slug = $base;
        $i = 2;

        while (self::taken($model, $slug, $ignoreId)) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    /** @param class-string<Model> $model */
    private static function taken(string $model, string $slug, ?int $ignoreId): bool
    {
        $query = in_array(SoftDeletes::class, class_uses_recursive($model), true)
            ? $model::withTrashed()
            : $model::query();

        return $query->where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists();
    }
}
