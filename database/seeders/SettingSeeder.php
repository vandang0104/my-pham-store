<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Cấu hình cửa hàng. Thông tin ngân hàng là DỮ LIỆU MẪU HƯ CẤU — thay bằng tài khoản thật khi dùng.
 */
class SettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Setting::DEFAULTS as $key => $value) {
            Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
