<?php

// Danh mục cha → danh mục con. 'mau' là [nền, màu dáng sản phẩm] của ảnh placeholder
// — bảng màu DESIGN.md §3: nền giấy dó #EBE5DA / ngọc nhạt #D3DDD4; dáng men ngọc, sơn mài hoặc champagne.
return [
    ['ten' => 'Chăm sóc da mặt', 'slug' => 'cham-soc-da-mat', 'mau' => ['#EBE5DA', '#2E5A4C'], 'con' => [
        ['ten' => 'Sữa rửa mặt', 'slug' => 'sua-rua-mat'],
        ['ten' => 'Tẩy trang', 'slug' => 'tay-trang'],
        ['ten' => 'Toner – Nước cân bằng', 'slug' => 'toner'],
        ['ten' => 'Serum – Tinh chất', 'slug' => 'serum-tinh-chat'],
        ['ten' => 'Kem dưỡng ẩm', 'slug' => 'kem-duong-am'],
        ['ten' => 'Mặt nạ', 'slug' => 'mat-na'],
    ]],
    ['ten' => 'Trang điểm', 'slug' => 'trang-diem', 'mau' => ['#D3DDD4', '#1E1B18'], 'con' => [
        ['ten' => 'Trang điểm mặt', 'slug' => 'trang-diem-mat'],
        ['ten' => 'Trang điểm môi', 'slug' => 'trang-diem-moi'],
        ['ten' => 'Trang điểm mắt', 'slug' => 'trang-diem-mat-mi'],
    ]],
    ['ten' => 'Chăm sóc tóc', 'slug' => 'cham-soc-toc', 'mau' => ['#EBE5DA', '#C2A878'], 'con' => [
        ['ten' => 'Dầu gội', 'slug' => 'dau-goi'],
        ['ten' => 'Dầu xả – Ủ tóc', 'slug' => 'dau-xa-u-toc'],
        ['ten' => 'Dưỡng tóc', 'slug' => 'duong-toc'],
    ]],
    ['ten' => 'Chăm sóc cơ thể', 'slug' => 'cham-soc-co-the', 'mau' => ['#D3DDD4', '#2E5A4C'], 'con' => [
        ['ten' => 'Sữa tắm', 'slug' => 'sua-tam'],
        ['ten' => 'Dưỡng thể', 'slug' => 'duong-the'],
        ['ten' => 'Tẩy tế bào chết cơ thể', 'slug' => 'tay-te-bao-chet-co-the'],
    ]],
    ['ten' => 'Nước hoa', 'slug' => 'nuoc-hoa', 'mau' => ['#EBE5DA', '#1E1B18'], 'con' => [
        ['ten' => 'Nước hoa nữ', 'slug' => 'nuoc-hoa-nu'],
        ['ten' => 'Nước hoa nam', 'slug' => 'nuoc-hoa-nam'],
        ['ten' => 'Xịt thơm toàn thân', 'slug' => 'xit-thom-toan-than'],
    ]],
    ['ten' => 'Dụng cụ làm đẹp', 'slug' => 'dung-cu-lam-dep', 'mau' => ['#D3DDD4', '#C2A878'], 'con' => [
        ['ten' => 'Cọ trang điểm', 'slug' => 'co-trang-diem'],
        ['ten' => 'Mút – Bông phấn', 'slug' => 'mut-bong-phan'],
        ['ten' => 'Máy rửa mặt', 'slug' => 'may-rua-mat'],
    ]],
    ['ten' => 'Chống nắng', 'slug' => 'chong-nang', 'mau' => ['#EBE5DA', '#C2A878'], 'con' => [
        ['ten' => 'Chống nắng da mặt', 'slug' => 'chong-nang-da-mat'],
        ['ten' => 'Chống nắng toàn thân', 'slug' => 'chong-nang-toan-than'],
    ]],
    ['ten' => 'Bộ quà tặng', 'slug' => 'bo-qua-tang', 'mau' => ['#D3DDD4', '#2E5A4C'], 'con' => [
        ['ten' => 'Bộ dưỡng da', 'slug' => 'bo-duong-da'],
        ['ten' => 'Bộ trang điểm', 'slug' => 'bo-trang-diem'],
        ['ten' => 'Bộ chăm sóc cơ thể', 'slug' => 'bo-cham-soc-co-the'],
    ]],
];
