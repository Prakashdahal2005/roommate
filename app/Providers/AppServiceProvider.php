<?php

namespace App\Providers;

use App\Contracts\KMeanBatchUpdateAdminInterface;
use App\Contracts\RoommateMatchServiceInterface;
use App\Services\RoommateMatchService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(RoommateMatchServiceInterface::class, RoommateMatchService::class);
        $this->app->bind(KMeanBatchUpdateAdminInterface::class, RoommateMatchService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $view->with('username', Auth::check() ? Auth::user()->name : null);
         });
    }
}
