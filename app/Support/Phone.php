<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Số điện thoại di động Việt Nam: chuẩn hoá về dạng 0xxxxxxxxx (10 số, đầu số 03/05/07/08/09).
 * Dùng chung cho đăng ký và thanh toán.
 */
final class Phone
{
    public const PATTERN = '/^0[35789]\d{8}$/';

    /** Bỏ khoảng trắng / dấu chấm / gạch / ngoặc; +84 → 0 */
    public static function normalize(mixed $phone): string
    {
        $phone = preg_replace('/[\s.\-()]/', '', (string) $phone);

        return Str::startsWith($phone, '+84') ? '0'.substr($phone, 3) : $phone;
    }

    /** @return list<string> */
    public static function rules(): array
    {
        return ['required', 'string', 'regex:'.self::PATTERN];
    }
}
