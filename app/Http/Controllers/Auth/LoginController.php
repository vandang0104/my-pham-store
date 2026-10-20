<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('pages.auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // Chống session fixation
        $request->session()->regenerate();

        // Admin/staff vào khu quản trị, khách hàng về trang chủ (trừ khi đang được chuyển hướng tới trang khác)
        $default = $request->user()->role->isBackOffice() ? route('admin.dashboard') : route('home');

        return redirect()->intended($default)->with('status', __('auth.logged_in'));
    }

    /** Trang đăng nhập quản trị /admin/login (dùng chung LoginRequest) */
    public function createAdmin(): View
    {
        return view('admin.auth.login');
    }

    /**
     * Chỉ admin/staff: tài khoản khách hàng đăng nhập đúng mật khẩu ở đây vẫn bị từ chối bằng
     * cùng thông báo chung (không lộ tài khoản có tồn tại hay không) và không giữ phiên.
     */
    public function storeAdmin(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        if (! $request->user()->role->isBackOffice()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'))->with('status', __('auth.logged_in'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', __('auth.logged_out'));
    }
}
