<?php

namespace App\Providers;

use App\View\Composers\CartComposer;
use App\View\Composers\NavigationComposer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Bắt N+1 ngay khi dev/test (architecture.md §4): lazy load quan hệ trên một tập model sẽ ném lỗi.
        // Production chỉ chạy bình thường.
        Model::preventLazyLoading(! $this->app->isProduction());

        // Header: menu chính (cache) và số lượng giỏ hàng
        View::composer('components.header', NavigationComposer::class);
        View::composer('components.header', CartComposer::class);

        // Phân trang theo DESIGN.md §9; controller nhớ gọi ->withQueryString() để giữ bộ lọc
        Paginator::defaultView('pagination.default');
        Paginator::defaultSimpleView('pagination.simple');
    }
}
