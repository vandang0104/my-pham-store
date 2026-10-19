<?php

use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Khu quản trị: chỉ admin và staff; nhóm role:admin khai báo bên trong routes/admin.php
            Route::middleware(['web', 'auth', 'role:admin,staff'])
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['role' => EnsureUserHasRole::class]);
        $middleware->web(append: [EnsureAccountIsActive::class]);

        // Người đã đăng nhập vào trang dành cho khách (đăng nhập, đăng ký…): về đúng khu vực theo vai trò
        $middleware->redirectUsersTo(fn (Request $request) => $request->user()?->role->isBackOffice()
            ? route('admin.dashboard')
            : route('home'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
