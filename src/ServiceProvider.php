<?php

namespace Stickee\Sync;

use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Stickee\Sync\Console\Commands\Sync;
use Stickee\Sync\Exceptions\PropertyNotFoundException;
use Stickee\Sync\Http\Controllers\SyncController;
use Stickee\Sync\Interfaces\TableDescriberInterface;
use Stickee\Sync\Interfaces\TableHasherInterface;
use Stickee\Sync\Models\Affiliate;
use Stickee\Sync\Models\Property;
use Stickee\Sync\PropertyService;
use Stickee\Sync\TableDescriber;
use Stickee\Sync\TableHasherFactory;

/**
 * Sync service provider
 */
class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register the service provider
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/sync.php', 'sync'
        );

        $this->app->bind(TableDescriberInterface::class, TableDescriber::class);

        $this->app->bind(TableHasherInterface::class, function ($app, $arguments) {
            return app(TableHasherFactory::class)->create($arguments['connection'] ?? null);
        });

        $this->commands([Sync::class]);
    }

    /**
     * Bootstrap any application services
     */
    public function boot()
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/sync.php' => config_path('sync.php'),
        ]);

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        $this->app->make(Factory::class)->load(__DIR__ . '/database/factories');
    }

    public static function routes()
    {
        Route::post(config('sync.url') . '/getTableHash', '\\' . SyncController::class . '@getTableHash')->name('sync.getTableHash');
        Route::post(config('sync.url') . '/getTable', '\\' . SyncController::class . '@getTable')->name('sync.getTable');
        Route::post(config('sync.url') . '/getFileHashes', '\\' . SyncController::class . '@getFileHashes')->name('sync.getFileHashes');
        Route::post(config('sync.url') . '/getFiles', '\\' . SyncController::class . '@getFiles')->name('sync.getFiles');
    }
}
