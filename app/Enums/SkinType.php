<?php

namespace App\Enums;

enum SkinType: string
{
    case Oily = 'oily';
    case Dry = 'dry';
    case Combination = 'combination';
    case Sensitive = 'sensitive';
    case All = 'all';

    public function label(): string
    {
        return match ($this) {
            self::Oily => 'Da dầu',
            self::Dry => 'Da khô',
            self::Combination => 'Da hỗn hợp',
            self::Sensitive => 'Da nhạy cảm',
            self::All => 'Mọi loại da',
        };
    }
}
