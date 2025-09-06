<?php

namespace App\Providers;

use App\Contracts\LlmServiceInterface;
use App\Contracts\WeatherServiceInterface;
use App\Services\LlmService;
use App\Services\WeatherService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(WeatherServiceInterface::class, WeatherService::class);
        $this->app->bind(LlmServiceInterface::class, LlmService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
