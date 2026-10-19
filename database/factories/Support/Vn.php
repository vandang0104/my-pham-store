<?php

namespace Database\Factories\Support;

/**
 * Sinh tên, số điện thoại, địa chỉ Việt Nam tự nhiên (Faker vi_VN cho kết quả lạ như "Em. Thôi Phụng").
 * Dùng fake() nên kết quả lặp lại được khi seed Faker.
 */
final class Vn
{
    private const SURNAMES = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Huỳnh', 'Phan', 'Vũ', 'Võ', 'Đặng', 'Bùi', 'Đỗ', 'Hồ', 'Ngô', 'Dương', 'Lý'];

    private const FEMALE_MIDDLE = ['Thị', 'Ngọc', 'Thu', 'Thanh', 'Bảo', 'Minh', 'Khánh', 'Phương', 'Hoài', 'Mỹ'];

    private const FEMALE_GIVEN = ['Lan', 'Hương', 'Linh', 'Trang', 'Hà', 'Mai', 'Ngân', 'Thảo', 'Vy', 'Anh', 'Nhi', 'Quỳnh', 'Trâm', 'Hạnh', 'Uyên', 'Giang', 'Yến', 'Châu', 'Diệp', 'My'];

    private const MALE_MIDDLE = ['Văn', 'Minh', 'Quốc', 'Đức', 'Hoàng', 'Gia', 'Thành', 'Anh', 'Hữu', 'Tuấn'];

    private const MALE_GIVEN = ['Huy', 'Nam', 'Khoa', 'Phúc', 'Long', 'Bảo', 'Đạt', 'Tùng', 'Quân', 'Hiếu', 'Sơn', 'Trung', 'Khang', 'Vinh'];

    private const PHONE_PREFIXES = ['090', '091', '093', '094', '096', '097', '098', '032', '033', '035', '070', '076', '077', '079', '081', '086'];

    private const STREETS = ['Lê Lợi', 'Nguyễn Huệ', 'Hai Bà Trưng', 'Trần Hưng Đạo', 'Lý Thường Kiệt', 'Nguyễn Trãi', 'Phan Đình Phùng', 'Điện Biên Phủ', 'Võ Văn Tần', 'Pasteur', 'Nguyễn Thị Minh Khai', 'Lê Duẩn', 'Trần Phú', 'Hùng Vương'];

    /** Tỉnh/thành → phường/xã theo mô hình 2 cấp (từ 01/7/2025 không còn cấp quận/huyện) */
    private const PROVINCES = [
        'TP. Hồ Chí Minh' => ['Phường Sài Gòn', 'Phường Bến Thành', 'Phường Tân Định', 'Phường Xuân Hòa', 'Phường Bình Thạnh', 'Phường Thủ Đức'],
        'TP. Hà Nội' => ['Phường Hoàn Kiếm', 'Phường Ba Đình', 'Phường Cầu Giấy', 'Phường Đống Đa', 'Phường Tây Hồ'],
        'TP. Đà Nẵng' => ['Phường Hải Châu', 'Phường Sơn Trà', 'Phường Ngũ Hành Sơn'],
        'TP. Cần Thơ' => ['Phường Ninh Kiều', 'Phường Cái Khế'],
        'TP. Hải Phòng' => ['Phường Hồng Bàng', 'Phường Lê Chân'],
        'TP. Huế' => ['Phường Thuận Hóa', 'Phường Phú Xuân'],
        'Tỉnh Khánh Hòa' => ['Phường Nha Trang', 'Phường Bắc Nha Trang'],
        'Tỉnh Lâm Đồng' => ['Phường Xuân Hương - Đà Lạt', 'Phường Lâm Viên - Đà Lạt'],
    ];

    public static function name(?bool $female = null): string
    {
        $female ??= fake()->boolean(75);

        return fake()->randomElement(self::SURNAMES).' '
            .fake()->randomElement($female ? self::FEMALE_MIDDLE : self::MALE_MIDDLE).' '
            .fake()->randomElement($female ? self::FEMALE_GIVEN : self::MALE_GIVEN);
    }

    public static function phone(): string
    {
        return fake()->randomElement(self::PHONE_PREFIXES).fake()->numerify('#######');
    }

    /**
     * @return array{province: string, district: null, ward: string, detail: string}
     */
    public static function address(): array
    {
        $province = fake()->randomElement(array_keys(self::PROVINCES));

        return [
            'province' => $province,
            'district' => null,
            'ward' => fake()->randomElement(self::PROVINCES[$province]),
            'detail' => fake()->numberBetween(1, 299).' '.fake()->randomElement(self::STREETS),
        ];
    }
}
