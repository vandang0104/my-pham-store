<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tài khoản bị khoá (is_active = false) khi đang đăng nhập sẽ bị đăng xuất ở request kế tiếp.
 */
class EnsureAccountIsActive
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && ! $user->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                abort(401, __('auth.locked'));
            }

            return redirect()->route('login')->withErrors(['email' => __('auth.locked')]);
        }

        return $next($request);
    }
}
