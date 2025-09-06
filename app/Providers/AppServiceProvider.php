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
        // Force HTTPS in production
        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
            
            // Auto-setup SQLite database in production
            $this->setupSqliteDatabase();
        }
    }

    /**
     * Setup SQLite database for production deployment
     */
    private function setupSqliteDatabase(): void
    {
        if (config('database.default') === 'sqlite') {
            $databasePath = config('database.connections.sqlite.database');
            
            // Create database file if it doesn't exist
            if ($databasePath !== ':memory:' && !file_exists($databasePath)) {
                $directory = dirname($databasePath);
                if (!is_dir($directory)) {
                    mkdir($directory, 0755, true);
                }
                touch($databasePath);
                chmod($databasePath, 0666);
                
                // Run migrations after creating database
                try {
                    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('Auto-migration failed: ' . $e->getMessage());
                }
            }
        }
    }
}
