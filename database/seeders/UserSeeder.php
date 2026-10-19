<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Address;
use App\Models\User;
use Database\Factories\Support\Vn;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * 1 admin, 2 staff, 20 customer — tất cả có địa chỉ mẫu.
 */
class UserSeeder extends Seeder
{
    /** Mật khẩu chung của mọi tài khoản demo (ghi trong README, CHỈ dùng để demo) */
    public const DEMO_PASSWORD = 'Demo@123';

    public function run(): void
    {
        $password = Hash::make(self::DEMO_PASSWORD);

        $admin = User::factory()->admin()->create([
            'name' => 'Trần Minh Anh', 'email' => 'admin@mypham.test', 'password' => $password,
        ]);
        $staff = collect([
            ['name' => 'Lê Thu Hà', 'email' => 'nhanvien1@mypham.test'],
            ['name' => 'Phạm Quốc Huy', 'email' => 'nhanvien2@mypham.test'],
        ])->map(fn (array $attrs) => User::factory()->staff()->create($attrs + ['password' => $password]));

        $customers = collect(range(1, 20))->map(fn (int $i) => User::factory()->customer()->create([
            'name' => Vn::name(),
            'email' => sprintf('khachhang%02d@mypham.test', $i),
            'password' => $password,
        ]));

        foreach ([$admin, ...$staff, ...$customers] as $i => $user) {
            $this->addAddresses($user, extra: $user->role === UserRole::Customer && $i % 3 === 0);
        }
    }

    /** Địa chỉ mặc định đứng tên chủ tài khoản; một số khách có thêm địa chỉ thứ hai (người nhận khác) */
    private function addAddresses(User $user, bool $extra): void
    {
        Address::factory()->default()->create([
            'user_id' => $user->id,
            'receiver_name' => $user->name,
            'phone' => $user->phone,
        ]);

        if ($extra) {
            Address::factory()->create(['user_id' => $user->id]);
        }
    }
}
