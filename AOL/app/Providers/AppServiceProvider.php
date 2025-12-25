<?php

namespace App\Providers;

use App\Models\ProductRating;
use App\Observers\ProductRatingObserver;
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
        ProductRating::observe(ProductRatingObserver::class);
    }
}
