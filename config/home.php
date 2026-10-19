<?php

/*
| Trang chủ (DESIGN.md §10.1) — cache các khối ít đổi.
|
| - Danh mục (cây + số sản phẩm): xoá cache khi Category/Product lưu, xoá, khôi phục (sự kiện model),
|   TTL 1 ngày làm lưới an toàn.
| - Banner hero: xoá cache khi Banner lưu/xoá; TTL ngắn 10 phút vì Banner::visible() phụ thuộc giờ hiện tại
|   (starts_at / ends_at) — banner tự hết hạn hoặc bắt đầu chạy mà không có sự kiện nào.
| - Sản phẩm nổi bật / đang ưu đãi KHÔNG cache: giá, tồn kho ("Hết hàng") phải luôn đúng; chỉ 2 truy vấn có eager load.
*/

return [
    'cache' => [
        'categories' => ['key' => 'home.categories', 'ttl' => 60 * 60 * 24],
        'banners' => ['key' => 'home.banners', 'ttl' => 60 * 10],
    ],

    // Số sản phẩm mỗi khối
    'featured_limit' => 4,
    'on_sale_limit' => 8,
];
