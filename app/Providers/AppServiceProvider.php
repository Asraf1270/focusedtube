<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->singleton(\App\Services\YouTube\YouTubeService::class, function ($app) {
    return new \App\Services\YouTube\YouTubeService(
        apiKey:  config('services.youtube.key'),
        baseUrl: config('services.youtube.base_url'),
        timeout: (int) config('services.youtube.timeout', 8),
        retries: (int) config('services.youtube.retries', 1),
    );
});
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
