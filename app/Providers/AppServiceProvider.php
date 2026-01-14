<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ThemeService;
use Illuminate\Support\Facades\View;


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
    // Share admin theme to admin layouts only
    View::composer(['layouts.admin', 'layouts.admin-auth'], function ($view) {
        $theme = app(ThemeService::class)->payload();

        $view->with('adminThemeMode', $theme['mode']);
        $view->with('adminThemeCss', $theme['css']);
        $view->with('adminThemeVars', $theme['vars']); // optional (useful later)
    });
}

}
