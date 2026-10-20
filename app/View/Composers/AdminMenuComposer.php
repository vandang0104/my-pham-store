<?php

namespace App\View\Composers;

use Illuminate\View\View;

/**
 * Menu sidebar khu quản trị theo quyền (docs/permissions.md): admin thấy thêm Tài khoản, Thống kê.
 */
class AdminMenuComposer
{
    private const ITEMS = [
        ['label' => 'Tổng quan', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
        ['label' => 'Sản phẩm', 'route' => 'admin.products.index', 'active' => 'admin.products.*'],
        ['label' => 'Danh mục', 'route' => 'admin.categories.index', 'active' => 'admin.categories.*'],
        ['label' => 'Đơn hàng', 'route' => 'admin.orders.index', 'active' => 'admin.orders.*'],
        ['label' => 'Chat', 'route' => 'admin.chats.index', 'active' => 'admin.chats.*'],
        ['label' => 'Liên hệ', 'route' => 'admin.contacts.index', 'active' => 'admin.contacts.*'],
        ['label' => 'Tài khoản', 'route' => 'admin.users.index', 'active' => 'admin.users.*', 'admin' => true],
        ['label' => 'Thống kê', 'route' => 'admin.reports.index', 'active' => 'admin.reports.*', 'admin' => true],
    ];

    public function compose(View $view): void
    {
        $isAdmin = (bool) auth()->user()?->isAdmin();

        $view->with('adminMenu', array_values(array_filter(self::ITEMS, fn (array $item) => $isAdmin || ! ($item['admin'] ?? false))));
    }
}
