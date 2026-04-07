<?php

namespace App\Providers;

use App\Models\HeaderFooterSetting;
use App\Models\LegalPage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
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
        //
        View::composer('*', function ($view) {
            // Fetch site settings
            $settings = HeaderFooterSetting::first();

            // Fetch all legal pages (we only need the title and slug for links)
            $legalPages = LegalPage::select('title', 'slug')->get();

            // Share both variables globally
            $view->with('siteSettings', $settings)
                ->with('legalPages', $legalPages);
        });
        Paginator::useBootstrapFive();
    }
}
