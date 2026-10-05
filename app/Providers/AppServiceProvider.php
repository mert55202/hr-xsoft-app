<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Company;
use App\Support\Navigation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer(['layouts.app', 'auth.login'], function ($view): void {
            $view->with('company', Company::query()->first());
        });

        View::composer('layouts.app', function ($view): void {
            $view->with('navItems', auth()->check() ? Navigation::for(auth()->user()) : []);
        });
    }
}
