<?php

namespace App\Providers;

use App\View\Composers\CartComposer;
use App\View\Composers\NavigationComposer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
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
        // Bắt N+1 (architecture.md §4): khi chạy test thì ném lỗi; ở local chỉ ghi log cảnh báo
        // (một lazy load sót lúc demo không thành lỗi 500); production tắt hẳn.
        Model::preventLazyLoading(! $this->app->isProduction());
        if (! $this->app->runningUnitTests()) {
            Model::handleLazyLoadingViolationUsing(function (Model $model, string $relation) {
                Log::warning('Lazy loading (N+1): '.$model::class.'::'.$relation);
            });
        }

        // Header: menu chính (cache) và số lượng giỏ hàng
        View::composer('components.header', NavigationComposer::class);
        View::composer('components.header', CartComposer::class);

        // Phân trang theo DESIGN.md §9; controller nhớ gọi ->withQueryString() để giữ bộ lọc
        Paginator::defaultView('pagination.default');
        Paginator::defaultSimpleView('pagination.simple');
    }
}
