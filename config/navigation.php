<?php

/*
|--------------------------------------------------------------------------
| Điều hướng chính của trang khách (DESIGN.md §9 — header 5 link)
|--------------------------------------------------------------------------
|
| Mỗi nhóm là một link trên header và một panel mega menu. 'roots' là slug các danh mục cha
| hiển thị trong panel (cột = danh mục cha, dòng = danh mục con). 'brands' => true thì panel
| liệt kê thương hiệu. Danh sách đã dựng được cache (khoá 'cache_key'), xoá khi sửa danh mục/thương hiệu.
|
*/

return [

    // v2: cache giữ đường dẫn tương đối thay vì URL tuyệt đối; v3: catalog chỉ còn chăm sóc da mặt — đổi khoá để bỏ menu cũ
    'cache_key' => 'navigation.menu.v3',

    'cache_ttl' => 60 * 60 * 24,

    'groups' => [
        ['label' => 'Làm sạch', 'roots' => ['lam-sach']],
        ['label' => 'Dưỡng da', 'roots' => ['duong-da']],
        ['label' => 'Mặt nạ', 'roots' => ['mat-na']],
        ['label' => 'Chống nắng', 'roots' => ['chong-nang']],
        ['label' => 'Thương hiệu', 'brands' => true],
    ],

];
