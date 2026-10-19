<?php

// 48 sản phẩm hư cấu. dm: slug danh mục con, th: slug thương hiệu, loai: khoá trong product_types.php,
// km: giá khuyến mãi (null nếu không giảm), da: SkinType (null với tóc, nước hoa, dụng cụ), hl: điểm nhấn, nb: nổi bật.
return [
    // Chăm sóc da mặt
    ['ten' => 'Sữa rửa mặt Trà Xanh Dịu Nhẹ', 'dm' => 'sua-rua-mat', 'th' => 'thao-moc-lam-vien', 'loai' => 'sua-rua-mat', 'gia' => 159000, 'km' => null, 'dt' => '150ml', 'da' => 'oily', 'hl' => 'chiết xuất trà xanh Bảo Lộc và BHA 0,5%', 'nb' => false],
    ['ten' => 'Gel rửa mặt Rau Má Làm Dịu', 'dm' => 'sua-rua-mat', 'th' => 'thao-moc-lam-vien', 'loai' => 'sua-rua-mat', 'gia' => 189000, 'km' => 159000, 'dt' => '200ml', 'da' => 'combination', 'hl' => 'chiết xuất rau má và madecassoside', 'nb' => false],
    ['ten' => 'Nước tẩy trang Hoa Cúc Không Cồn', 'dm' => 'tay-trang', 'th' => 'diu-em', 'loai' => 'tay-trang', 'gia' => 219000, 'km' => null, 'dt' => '400ml', 'da' => 'sensitive', 'hl' => 'chiết xuất hoa cúc La Mã', 'nb' => false],
    ['ten' => 'Dầu tẩy trang Hạt Mắc Ca', 'dm' => 'tay-trang', 'th' => 'thao-moc-lam-vien', 'loai' => 'tay-trang', 'gia' => 289000, 'km' => null, 'dt' => '200ml', 'da' => 'dry', 'hl' => 'dầu hạt mắc ca ép lạnh', 'nb' => false],
    ['ten' => 'Nước cân bằng Diếp Cá Se Khít', 'dm' => 'toner', 'th' => 'thao-moc-lam-vien', 'loai' => 'toner', 'gia' => 175000, 'km' => null, 'dt' => '200ml', 'da' => 'oily', 'hl' => 'diếp cá và tràm trà', 'nb' => false],
    ['ten' => 'Toner Gạo Sữa Cấp Ẩm', 'dm' => 'toner', 'th' => 'thao-moc-lam-vien', 'loai' => 'toner', 'gia' => 210000, 'km' => null, 'dt' => '150ml', 'da' => 'dry', 'hl' => 'nước cám gạo lên men', 'nb' => false],
    ['ten' => 'Serum Niacinamide 10% Sáng Da', 'dm' => 'serum-tinh-chat', 'th' => 'dermalis-lab', 'loai' => 'serum', 'gia' => 345000, 'km' => 279000, 'dt' => '30ml', 'da' => 'combination', 'hl' => 'niacinamide 10% và kẽm PCA 1%', 'nb' => true],
    ['ten' => 'Tinh chất Vitamin C 15% Rạng Rỡ', 'dm' => 'serum-tinh-chat', 'th' => 'dermalis-lab', 'loai' => 'serum', 'gia' => 459000, 'km' => 389000, 'dt' => '30ml', 'da' => 'all', 'hl' => 'vitamin C 15% kết hợp vitamin E và ferulic acid', 'nb' => true],
    ['ten' => 'Serum Hyaluronic Acid B5 Cấp Nước', 'dm' => 'serum-tinh-chat', 'th' => 'dermalis-lab', 'loai' => 'serum', 'gia' => 389000, 'km' => null, 'dt' => '30ml', 'da' => 'dry', 'hl' => 'hyaluronic acid đa phân tử và vitamin B5', 'nb' => false],
    ['ten' => 'Kem dưỡng Ceramide Phục Hồi Hàng Rào', 'dm' => 'kem-duong-am', 'th' => 'dermalis-lab', 'loai' => 'kem-duong', 'gia' => 420000, 'km' => 349000, 'dt' => '50ml', 'da' => 'sensitive', 'hl' => 'phức hợp 3 ceramide và cholesterol', 'nb' => true],
    ['ten' => 'Gel dưỡng Nha Đam Kiềm Dầu', 'dm' => 'kem-duong-am', 'th' => 'dermalis-lab', 'loai' => 'kem-duong', 'gia' => 265000, 'km' => null, 'dt' => '50ml', 'da' => 'oily', 'hl' => 'nha đam Ninh Thuận và niacinamide', 'nb' => false],
    ['ten' => 'Mặt nạ đất sét Bí Đao', 'dm' => 'mat-na', 'th' => 'thao-moc-lam-vien', 'loai' => 'mat-na', 'gia' => 199000, 'km' => null, 'dt' => '100g', 'da' => 'oily', 'hl' => 'chiết xuất bí đao và tràm trà', 'nb' => false],

    // Trang điểm
    ['ten' => 'Kem nền Lụa Mỏng SPF20', 'dm' => 'trang-diem-mat', 'th' => 'velvetine', 'loai' => 'kem-nen', 'gia' => 395000, 'km' => null, 'dt' => '30ml', 'da' => 'combination', 'hl' => 'hyaluronic acid', 'nb' => true],
    ['ten' => 'Cushion Ẩm Mịn Căng Bóng', 'dm' => 'trang-diem-mat', 'th' => 'velvetine', 'loai' => 'kem-nen', 'gia' => 450000, 'km' => 379000, 'dt' => '15g', 'da' => 'dry', 'hl' => 'tinh chất nước hoa hồng và squalane', 'nb' => true],
    ['ten' => 'Phấn phủ Kiềm Dầu Hạt Gạo', 'dm' => 'trang-diem-mat', 'th' => 'velvetine', 'loai' => 'phan-phu', 'gia' => 285000, 'km' => null, 'dt' => '10g', 'da' => 'oily', 'hl' => 'bột gạo mịn', 'nb' => false],
    ['ten' => 'Son kem lì Nhung Đỏ Gạch', 'dm' => 'trang-diem-moi', 'th' => 'velvetine', 'loai' => 'son', 'gia' => 239000, 'km' => 199000, 'dt' => '3.5g', 'da' => 'all', 'hl' => 'dầu jojoba', 'nb' => true],
    ['ten' => 'Son dưỡng có màu Hồng Đào', 'dm' => 'trang-diem-moi', 'th' => 'velvetine', 'loai' => 'son', 'gia' => 169000, 'km' => null, 'dt' => '3.2g', 'da' => 'dry', 'hl' => 'bơ hạt mỡ và sáp ong', 'nb' => false],
    ['ten' => 'Son thỏi Satin Cam Cháy', 'dm' => 'trang-diem-moi', 'th' => 'velvetine', 'loai' => 'son', 'gia' => 279000, 'km' => null, 'dt' => '3.5g', 'da' => 'all', 'hl' => 'dầu hạt mâm xôi', 'nb' => false],
    ['ten' => 'Mascara Dày Mi Không Lem', 'dm' => 'trang-diem-mat-mi', 'th' => 'velvetine', 'loai' => 'mascara', 'gia' => 229000, 'km' => null, 'dt' => '8ml', 'da' => 'all', 'hl' => 'panthenol và peptide dưỡng mi', 'nb' => false],
    ['ten' => 'Bảng phấn mắt 9 ô Nâu Đất', 'dm' => 'trang-diem-mat-mi', 'th' => 'velvetine', 'loai' => 'phan-mat', 'gia' => 359000, 'km' => null, 'dt' => '9g', 'da' => 'all', 'hl' => 'vitamin E', 'nb' => false],

    // Chăm sóc tóc
    ['ten' => 'Dầu gội Bưởi Giảm Gãy Rụng', 'dm' => 'dau-goi', 'th' => 'thao-moc-lam-vien', 'loai' => 'dau-goi', 'gia' => 189000, 'km' => 149000, 'dt' => '500ml', 'da' => null, 'hl' => 'tinh dầu vỏ bưởi và bồ kết', 'nb' => false],
    ['ten' => 'Dầu gội Sạch Gàu Bạc Hà', 'dm' => 'dau-goi', 'th' => 'thao-moc-lam-vien', 'loai' => 'dau-goi', 'gia' => 165000, 'km' => null, 'dt' => '400ml', 'da' => null, 'hl' => 'tinh dầu bạc hà và kẽm pyrithione', 'nb' => false],
    ['ten' => 'Dầu xả Mềm Mượt Dầu Dừa', 'dm' => 'dau-xa-u-toc', 'th' => 'moc-suong', 'loai' => 'dau-xa', 'gia' => 159000, 'km' => null, 'dt' => '400ml', 'da' => null, 'hl' => 'dầu dừa Bến Tre', 'nb' => false],
    ['ten' => 'Kem ủ tóc Keratin Phục Hồi', 'dm' => 'dau-xa-u-toc', 'th' => 'moc-suong', 'loai' => 'dau-xa', 'gia' => 249000, 'km' => null, 'dt' => '250ml', 'da' => null, 'hl' => 'keratin thuỷ phân và dầu argan', 'nb' => false],
    ['ten' => 'Tinh dầu dưỡng tóc Argan', 'dm' => 'duong-toc', 'th' => 'moc-suong', 'loai' => 'duong-toc', 'gia' => 299000, 'km' => null, 'dt' => '50ml', 'da' => null, 'hl' => 'dầu argan nguyên chất', 'nb' => false],

    // Chăm sóc cơ thể
    ['ten' => 'Sữa tắm Hoa Nhài Thư Giãn', 'dm' => 'sua-tam', 'th' => 'hat-suong-spa', 'loai' => 'sua-tam', 'gia' => 179000, 'km' => 149000, 'dt' => '500ml', 'da' => 'all', 'hl' => 'tinh dầu hoa nhài', 'nb' => false],
    ['ten' => 'Sữa tắm Than Tre Sạch Thoáng', 'dm' => 'sua-tam', 'th' => 'hat-suong-spa', 'loai' => 'sua-tam', 'gia' => 169000, 'km' => null, 'dt' => '500ml', 'da' => 'oily', 'hl' => 'bột than tre hoạt tính', 'nb' => false],
    ['ten' => 'Sữa dưỡng thể Bơ Hạt Mỡ', 'dm' => 'duong-the', 'th' => 'hat-suong-spa', 'loai' => 'duong-the', 'gia' => 229000, 'km' => null, 'dt' => '250ml', 'da' => 'dry', 'hl' => 'bơ hạt mỡ (shea butter)', 'nb' => false],
    ['ten' => 'Lotion dưỡng thể Yến Mạch Dịu Da', 'dm' => 'duong-the', 'th' => 'diu-em', 'loai' => 'duong-the', 'gia' => 199000, 'km' => null, 'dt' => '300ml', 'da' => 'sensitive', 'hl' => 'yến mạch keo', 'nb' => false],
    ['ten' => 'Tẩy tế bào chết Cà Phê Đắk Lắk', 'dm' => 'tay-te-bao-chet-co-the', 'th' => 'hat-suong-spa', 'loai' => 'tay-da-chet', 'gia' => 185000, 'km' => null, 'dt' => '200g', 'da' => 'all', 'hl' => 'bột cà phê Robusta Đắk Lắk', 'nb' => false],

    // Nước hoa
    ['ten' => 'Nước hoa nữ Hoa Sứ Hội An EDP', 'dm' => 'nuoc-hoa-nu', 'th' => 'maison-le-anh', 'loai' => 'nuoc-hoa', 'gia' => 890000, 'km' => 759000, 'dt' => '50ml', 'da' => null, 'hl' => 'hoa sứ, xạ hương trắng và gỗ đàn hương', 'nb' => true],
    ['ten' => 'Nước hoa nữ Sương Mai Đà Lạt EDP', 'dm' => 'nuoc-hoa-nu', 'th' => 'maison-le-anh', 'loai' => 'nuoc-hoa', 'gia' => 950000, 'km' => null, 'dt' => '50ml', 'da' => null, 'hl' => 'hoa hồng, lá thông và rêu sồi', 'nb' => false],
    ['ten' => 'Nước hoa nam Gỗ Trầm Sài Gòn EDP', 'dm' => 'nuoc-hoa-nam', 'th' => 'maison-le-anh', 'loai' => 'nuoc-hoa', 'gia' => 1050000, 'km' => null, 'dt' => '50ml', 'da' => null, 'hl' => 'trầm hương, da thuộc và tiêu đen', 'nb' => true],
    ['ten' => 'Nước hoa nam Biển Xanh Côn Đảo EDT', 'dm' => 'nuoc-hoa-nam', 'th' => 'maison-le-anh', 'loai' => 'nuoc-hoa', 'gia' => 820000, 'km' => null, 'dt' => '100ml', 'da' => null, 'hl' => 'hương biển, cam bergamot và cỏ hương bài', 'nb' => false],
    ['ten' => 'Xịt thơm toàn thân Đào Hồng', 'dm' => 'xit-thom-toan-than', 'th' => 'maison-le-anh', 'loai' => 'xit-thom', 'gia' => 249000, 'km' => null, 'dt' => '150ml', 'da' => null, 'hl' => 'đào chín và hoa mộc lan', 'nb' => false],

    // Dụng cụ làm đẹp
    ['ten' => 'Bộ cọ trang điểm 8 cây Lông Mềm', 'dm' => 'co-trang-diem', 'th' => 'tinh-xao', 'loai' => 'co-trang-diem', 'gia' => 459000, 'km' => 389000, 'dt' => '8 cây', 'da' => null, 'hl' => 'lông tơ tổng hợp siêu mềm', 'nb' => false],
    ['ten' => 'Cọ nền đầu bằng Chuyên Nghiệp', 'dm' => 'co-trang-diem', 'th' => 'tinh-xao', 'loai' => 'co-trang-diem', 'gia' => 189000, 'km' => null, 'dt' => '1 cây', 'da' => null, 'hl' => 'lông cọ mật độ dày', 'nb' => false],
    ['ten' => 'Mút tán nền Giọt Nước (bộ 2)', 'dm' => 'mut-bong-phan', 'th' => 'tinh-xao', 'loai' => 'mut-trang-diem', 'gia' => 129000, 'km' => null, 'dt' => '2 chiếc', 'da' => null, 'hl' => 'mút polyurethane không chứa latex', 'nb' => false],
    ['ten' => 'Máy rửa mặt Silicone Sóng Âm', 'dm' => 'may-rua-mat', 'th' => 'tinh-xao', 'loai' => 'may-rua-mat', 'gia' => 690000, 'km' => null, 'dt' => '1 máy', 'da' => null, 'hl' => 'đầu silicone y tế và 3 chế độ rung', 'nb' => true],

    // Chống nắng
    ['ten' => 'Kem chống nắng Nâng Tông SPF50+ PA++++', 'dm' => 'chong-nang-da-mat', 'th' => 'sunveil-lab', 'loai' => 'chong-nang', 'gia' => 329000, 'km' => 279000, 'dt' => '50ml', 'da' => 'combination', 'hl' => 'bột ngọc trai nâng tông nhẹ', 'nb' => true],
    ['ten' => 'Sữa chống nắng Kiềm Dầu SPF50+', 'dm' => 'chong-nang-da-mat', 'th' => 'sunveil-lab', 'loai' => 'chong-nang', 'gia' => 299000, 'km' => null, 'dt' => '60ml', 'da' => 'oily', 'hl' => 'bột silica kiềm dầu', 'nb' => false],
    ['ten' => 'Kem chống nắng Vật Lý Dịu Nhẹ SPF50', 'dm' => 'chong-nang-da-mat', 'th' => 'diu-em', 'loai' => 'chong-nang', 'gia' => 359000, 'km' => 309000, 'dt' => '50ml', 'da' => 'sensitive', 'hl' => 'zinc oxide và chiết xuất rau má', 'nb' => true],
    ['ten' => 'Xịt chống nắng Cơ Thể SPF50+', 'dm' => 'chong-nang-toan-than', 'th' => 'sunveil-lab', 'loai' => 'chong-nang-co-the', 'gia' => 279000, 'km' => null, 'dt' => '150ml', 'da' => 'all', 'hl' => 'tinh chất lô hội', 'nb' => false],
    ['ten' => 'Lotion chống nắng Dưỡng Ẩm SPF30', 'dm' => 'chong-nang-toan-than', 'th' => 'sunveil-lab', 'loai' => 'chong-nang-co-the', 'gia' => 259000, 'km' => null, 'dt' => '200ml', 'da' => 'dry', 'hl' => 'glycerin và dầu hướng dương', 'nb' => false],

    // Bộ quà tặng
    ['ten' => 'Bộ quà Dưỡng Da Cơ Bản 3 Bước', 'dm' => 'bo-duong-da', 'th' => 'qua-xinh-atelier', 'loai' => 'bo-qua', 'gia' => 690000, 'km' => 590000, 'dt' => '3 món', 'da' => 'combination', 'hl' => 'sữa rửa mặt, toner và kem dưỡng kích thước tiêu chuẩn', 'nb' => true],
    ['ten' => 'Bộ quà Phục Hồi Da Nhạy Cảm', 'dm' => 'bo-duong-da', 'th' => 'diu-em', 'loai' => 'bo-qua', 'gia' => 790000, 'km' => null, 'dt' => '4 món', 'da' => 'sensitive', 'hl' => 'nước tẩy trang, kem dưỡng ceramide, kem chống nắng vật lý và túi vải', 'nb' => false],
    ['ten' => 'Bộ son Mini 4 Màu', 'dm' => 'bo-trang-diem', 'th' => 'qua-xinh-atelier', 'loai' => 'bo-qua', 'gia' => 499000, 'km' => 429000, 'dt' => '4 x 1.5g', 'da' => 'all', 'hl' => '4 thỏi son mini tông đỏ gạch, cam cháy, hồng đất và nude', 'nb' => false],
    ['ten' => 'Bộ quà Tắm Thư Giãn Hoa Nhài', 'dm' => 'bo-cham-soc-co-the', 'th' => 'qua-xinh-atelier', 'loai' => 'bo-qua', 'gia' => 459000, 'km' => null, 'dt' => '3 món', 'da' => 'all', 'hl' => 'sữa tắm, dưỡng thể hoa nhài và bông tắm sợi tre', 'nb' => false],
];
