<?php

// Từ 20/10/2026 cửa hàng chỉ bán chăm sóc da mặt. Danh mục cha → danh mục con. 'mau' là [nền, màu dáng sản phẩm] của ảnh placeholder
// — bảng màu DESIGN.md §3: nền giấy dó #EBE5DA / ngọc nhạt #D3DDD4; dáng men ngọc, sơn mài hoặc champagne.
return [
    ['ten' => 'Làm sạch', 'slug' => 'lam-sach', 'mau' => ['#EBE5DA', '#2E5A4C'], 'con' => [
        ['ten' => 'Sữa rửa mặt', 'slug' => 'sua-rua-mat'],
        ['ten' => 'Tẩy trang', 'slug' => 'tay-trang'],
        ['ten' => 'Tẩy tế bào chết da mặt', 'slug' => 'tay-te-bao-chet-da-mat'],
    ]],
    ['ten' => 'Dưỡng da', 'slug' => 'duong-da', 'mau' => ['#D3DDD4', '#1E1B18'], 'con' => [
        ['ten' => 'Toner – Nước cân bằng', 'slug' => 'toner'],
        ['ten' => 'Serum – Tinh chất', 'slug' => 'serum-tinh-chat'],
        ['ten' => 'Kem dưỡng ẩm', 'slug' => 'kem-duong-am'],
    ]],
    ['ten' => 'Mặt nạ', 'slug' => 'mat-na', 'mau' => ['#EBE5DA', '#C2A878'], 'con' => [
        ['ten' => 'Mặt nạ đất sét', 'slug' => 'mat-na-dat-set'],
        ['ten' => 'Mặt nạ giấy', 'slug' => 'mat-na-giay'],
    ]],
    ['ten' => 'Chống nắng', 'slug' => 'chong-nang', 'mau' => ['#D3DDD4', '#2E5A4C'], 'con' => [
        ['ten' => 'Chống nắng da mặt', 'slug' => 'chong-nang-da-mat'],
    ]],
];
