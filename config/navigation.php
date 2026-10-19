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

    'cache_key' => 'navigation.menu',

    'cache_ttl' => 60 * 60 * 24,

    'groups' => [
        ['label' => 'Chăm sóc da', 'roots' => ['cham-soc-da-mat', 'chong-nang']],
        ['label' => 'Trang điểm', 'roots' => ['trang-diem', 'dung-cu-lam-dep']],
        ['label' => 'Chăm sóc tóc & cơ thể', 'roots' => ['cham-soc-toc', 'cham-soc-co-the']],
        ['label' => 'Nước hoa', 'roots' => ['nuoc-hoa', 'bo-qua-tang']],
        ['label' => 'Thương hiệu', 'brands' => true],
    ],

];
