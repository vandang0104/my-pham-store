<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Tài khoản (khách + nhân viên gộp một màn, CHỈ admin — quy tắc 9): xem, đổi vai trò, khoá/mở khoá.
 * Chặn tự hạ quyền/khoá mình và hạ quyền/khoá admin đang hoạt động cuối cùng (UserPolicy). Không sửa mật khẩu.
 */
class UserController extends Controller
{
    public const PER_PAGE = 20;

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);
        $role = UserRole::tryFrom((string) $request->query('vai-tro'));
        $q = trim((string) $request->query('q'));

        return view('admin.users.index', [
            'users' => User::query()
                ->withCount('orders')
                ->when($role, fn ($query) => $query->where('role', $role))
                ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")))
                ->orderBy('name')
                ->paginate(self::PER_PAGE)
                ->withQueryString(),
            'roles' => UserRole::cases(),
            'filters' => ['vai-tro' => $role, 'q' => $q],
        ]);
    }

    public function show(User $user): View
    {
        Gate::authorize('view', $user);

        return view('admin.users.show', [
            'user' => $user,
            'ordersCount' => $user->orders()->count(),
            // Tổng chi tiêu = tổng tiền các đơn đã giao
            'totalSpent' => Money::format((int) $user->orders()->where('status', OrderStatus::Delivered)->sum('total')),
            'recentOrders' => $user->orders()->latest()->latest('id')->limit(10)->get(),
            'roles' => UserRole::cases(),
        ]);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(['role' => ['required', Rule::enum(UserRole::class)]]);
        $role = UserRole::from($data['role']);

        if (Gate::denies('changeRole', [$user, $role])) {
            return back()->withErrors(['delete' => 'Không thể đổi vai trò: không được tự hạ quyền chính mình hoặc hạ quyền quản trị viên đang hoạt động cuối cùng.']);
        }

        $user->forceFill(['role' => $role])->save();

        return back()->with('status', "Đã đổi vai trò của {$user->name} thành “{$role->label()}”.");
    }

    public function toggleActive(User $user): RedirectResponse
    {
        if (Gate::denies('toggleActive', $user)) {
            return back()->withErrors(['delete' => 'Không thể khoá: không được tự khoá chính mình hoặc khoá quản trị viên đang hoạt động cuối cùng.']);
        }

        $user->forceFill(['is_active' => ! $user->is_active])->save();

        return back()->with('status', $user->is_active ? "Đã mở khoá tài khoản {$user->name}." : "Đã khoá tài khoản {$user->name}.");
    }
}
