<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function create(): View
    {
        return view('pages.auth.forgot-password');
    }

    public function store(ForgotPasswordRequest $request): RedirectResponse
    {
        $request->ensureIsNotRateLimited();

        // Bỏ qua kết quả (đã gửi / không có user / đang chờ) để không lộ email có tồn tại hay không
        Password::sendResetLink($request->only('email'));

        return back()->with('status', __('passwords.sent_if_exists'));
    }
}
