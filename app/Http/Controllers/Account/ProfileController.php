<?php

namespace App\Http\Controllers\Account;

use App\Enums\SkinType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\UpdatePasswordRequest;
use App\Http\Requests\Account\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

/** Hồ sơ và đổi mật khẩu của chính người đang đăng nhập */
class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('account.profile', ['user' => $request->user(), 'skinTypes' => SkinType::cases()]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return redirect()->route('account.profile.edit')->with('status', __('account.profile_updated'));
    }

    public function editPassword(): View
    {
        return view('account.password');
    }

    /**
     * Đổi mật khẩu, giữ phiên hiện tại; đăng xuất thiết bị khác bằng cách đổi remember_token (cookie "ghi nhớ")
     * và xoá các phiên khác trong bảng sessions (SESSION_DRIVER=database).
     */
    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->forceFill(['password' => $request->validated('password'), 'remember_token' => Str::random(60)])->save();

        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $user->id)
                ->where('id', '!=', $request->session()->getId())
                ->delete();
        }
        $request->session()->regenerate();

        return redirect()->route('account.password.edit')->with('status', __('account.password_updated'));
    }
}
