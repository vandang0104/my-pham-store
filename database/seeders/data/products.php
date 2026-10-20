<?php

// 21 sản phẩm chăm sóc da mặt (hư cấu). dm: slug danh mục con, th: slug thương hiệu, loai: khoá trong product_types.php,
// km: giá khuyến mãi (null nếu không giảm), da: SkinType, hl: điểm nhấn, nb: nổi bật.
return [
    // Làm sạch
    ['ten' => 'Sữa rửa mặt Trà Xanh Dịu Nhẹ', 'dm' => 'sua-rua-mat', 'th' => 'thao-moc-lam-vien', 'loai' => 'sua-rua-mat', 'gia' => 159000, 'km' => null, 'dt' => '150ml', 'da' => 'oily', 'hl' => 'chiết xuất trà xanh Bảo Lộc và BHA 0,5%', 'nb' => false],
    ['ten' => 'Gel rửa mặt Rau Má Làm Dịu', 'dm' => 'sua-rua-mat', 'th' => 'thao-moc-lam-vien', 'loai' => 'sua-rua-mat', 'gia' => 189000, 'km' => 159000, 'dt' => '200ml', 'da' => 'combination', 'hl' => 'chiết xuất rau má và madecassoside', 'nb' => false],
    ['ten' => 'Sữa rửa mặt Yến Mạch Cho Da Nhạy Cảm', 'dm' => 'sua-rua-mat', 'th' => 'diu-em', 'loai' => 'sua-rua-mat', 'gia' => 179000, 'km' => null, 'dt' => '150ml', 'da' => 'sensitive', 'hl' => 'yến mạch keo và ceramide NP', 'nb' => false],
    ['ten' => 'Nước tẩy trang Hoa Cúc Không Cồn', 'dm' => 'tay-trang', 'th' => 'diu-em', 'loai' => 'tay-trang', 'gia' => 219000, 'km' => null, 'dt' => '400ml', 'da' => 'sensitive', 'hl' => 'chiết xuất hoa cúc La Mã', 'nb' => false],
    ['ten' => 'Dầu tẩy trang Hạt Mắc Ca', 'dm' => 'tay-trang', 'th' => 'thao-moc-lam-vien', 'loai' => 'tay-trang', 'gia' => 289000, 'km' => null, 'dt' => '200ml', 'da' => 'dry', 'hl' => 'dầu hạt mắc ca ép lạnh', 'nb' => false],
    ['ten' => 'Dung dịch tẩy tế bào chết BHA 2%', 'dm' => 'tay-te-bao-chet-da-mat', 'th' => 'dermalis-lab', 'loai' => 'tay-da-chet-mat', 'gia' => 365000, 'km' => null, 'dt' => '100ml', 'da' => 'oily', 'hl' => 'BHA (salicylic acid) 2% và chiết xuất trà xanh', 'nb' => true],
    ['ten' => 'Gel tẩy tế bào chết Enzyme Đu Đủ', 'dm' => 'tay-te-bao-chet-da-mat', 'th' => 'hat-suong-spa', 'loai' => 'tay-da-chet-mat', 'gia' => 215000, 'km' => 179000, 'dt' => '100ml', 'da' => 'all', 'hl' => 'enzyme đu đủ và PHA 3%', 'nb' => false],

    // Dưỡng da
    ['ten' => 'Nước cân bằng Diếp Cá Se Khít', 'dm' => 'toner', 'th' => 'thao-moc-lam-vien', 'loai' => 'toner', 'gia' => 175000, 'km' => null, 'dt' => '200ml', 'da' => 'oily', 'hl' => 'diếp cá và tràm trà', 'nb' => false],
    ['ten' => 'Toner Gạo Sữa Cấp Ẩm', 'dm' => 'toner', 'th' => 'thao-moc-lam-vien', 'loai' => 'toner', 'gia' => 210000, 'km' => null, 'dt' => '150ml', 'da' => 'dry', 'hl' => 'nước cám gạo lên men', 'nb' => false],
    ['ten' => 'Serum Niacinamide 10% Sáng Da', 'dm' => 'serum-tinh-chat', 'th' => 'dermalis-lab', 'loai' => 'serum', 'gia' => 345000, 'km' => 279000, 'dt' => '30ml', 'da' => 'combination', 'hl' => 'niacinamide 10% và kẽm PCA 1%', 'nb' => true],
    ['ten' => 'Tinh chất Vitamin C 15% Rạng Rỡ', 'dm' => 'serum-tinh-chat', 'th' => 'dermalis-lab', 'loai' => 'serum', 'gia' => 459000, 'km' => 389000, 'dt' => '30ml', 'da' => 'all', 'hl' => 'vitamin C 15% kết hợp vitamin E và ferulic acid', 'nb' => true],
    ['ten' => 'Serum Hyaluronic Acid B5 Cấp Nước', 'dm' => 'serum-tinh-chat', 'th' => 'dermalis-lab', 'loai' => 'serum', 'gia' => 389000, 'km' => null, 'dt' => '30ml', 'da' => 'dry', 'hl' => 'hyaluronic acid đa phân tử và vitamin B5', 'nb' => false],
    ['ten' => 'Kem dưỡng Ceramide Phục Hồi Hàng Rào', 'dm' => 'kem-duong-am', 'th' => 'dermalis-lab', 'loai' => 'kem-duong', 'gia' => 420000, 'km' => 349000, 'dt' => '50ml', 'da' => 'sensitive', 'hl' => 'phức hợp 3 ceramide và cholesterol', 'nb' => true],
    ['ten' => 'Gel dưỡng Nha Đam Kiềm Dầu', 'dm' => 'kem-duong-am', 'th' => 'dermalis-lab', 'loai' => 'kem-duong', 'gia' => 265000, 'km' => null, 'dt' => '50ml', 'da' => 'oily', 'hl' => 'nha đam Ninh Thuận và niacinamide', 'nb' => false],
    ['ten' => 'Kem dưỡng đêm Lựu Đỏ Săn Chắc', 'dm' => 'kem-duong-am', 'th' => 'thao-moc-lam-vien', 'loai' => 'kem-duong', 'gia' => 395000, 'km' => null, 'dt' => '50ml', 'da' => 'dry', 'hl' => 'chiết xuất lựu đỏ và peptide', 'nb' => false],

    // Mặt nạ
    ['ten' => 'Mặt nạ đất sét Bí Đao', 'dm' => 'mat-na-dat-set', 'th' => 'thao-moc-lam-vien', 'loai' => 'mat-na', 'gia' => 199000, 'km' => null, 'dt' => '100g', 'da' => 'oily', 'hl' => 'chiết xuất bí đao và tràm trà', 'nb' => false],
    ['ten' => 'Mặt nạ đất sét Nghệ Hưng Yên', 'dm' => 'mat-na-dat-set', 'th' => 'hat-suong-spa', 'loai' => 'mat-na', 'gia' => 209000, 'km' => null, 'dt' => '100g', 'da' => 'combination', 'hl' => 'tinh bột nghệ Hưng Yên và kaolin', 'nb' => false],
    ['ten' => 'Mặt nạ giấy Hyaluronic Cấp Ẩm (hộp 5 miếng)', 'dm' => 'mat-na-giay', 'th' => 'hat-suong-spa', 'loai' => 'mat-na-giay', 'gia' => 175000, 'km' => null, 'dt' => '5 miếng', 'da' => 'dry', 'hl' => 'hyaluronic acid và chiết xuất lô hội', 'nb' => false],

    // Chống nắng
    ['ten' => 'Kem chống nắng Nâng Tông SPF50+ PA++++', 'dm' => 'chong-nang-da-mat', 'th' => 'sunveil-lab', 'loai' => 'chong-nang', 'gia' => 329000, 'km' => 279000, 'dt' => '50ml', 'da' => 'combination', 'hl' => 'bột ngọc trai nâng tông nhẹ', 'nb' => true],
    ['ten' => 'Sữa chống nắng Kiềm Dầu SPF50+', 'dm' => 'chong-nang-da-mat', 'th' => 'sunveil-lab', 'loai' => 'chong-nang', 'gia' => 299000, 'km' => null, 'dt' => '60ml', 'da' => 'oily', 'hl' => 'bột silica kiềm dầu', 'nb' => false],
    ['ten' => 'Kem chống nắng Vật Lý Dịu Nhẹ SPF50', 'dm' => 'chong-nang-da-mat', 'th' => 'diu-em', 'loai' => 'chong-nang', 'gia' => 359000, 'km' => 309000, 'dt' => '50ml', 'da' => 'sensitive', 'hl' => 'zinc oxide và chiết xuất rau má', 'nb' => true],
];
