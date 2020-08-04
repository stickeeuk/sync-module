<?php

namespace Stickee\Sync;

use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Stickee\Sync\Console\Commands\Sync;
use Stickee\Sync\Http\Controllers\SyncController;
use Stickee\Sync\Interfaces\TableDescriberInterface;
use Stickee\Sync\Interfaces\TableHasherInterface;
use Stickee\Sync\TableDescriber;
use Stickee\Sync\TableHasherFactory;

/**
 * Sync service provider
 */
class ServiceProvider extends BaseServiceProvider
{
    /**
     * Fake value for hashing NULL, otherwise the whole row's hash will be NULL
     *
     * @var string NULL_VALUE
     */
    const NULL_VALUE = 'NULL9cf4-973a-4539-a5f2-8d4bde0aNULL';

    /**
     * Fake value for hashing an empty table, otherwise it will be empty string,
     * which will fail validation
     *
     * @var string EMPTY_TABLE_HASH
     */
    const EMPTY_TABLE_HASH = '--EMPTY--';

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
            return app(TableHasherFactory::class)->create($arguments['connection'] ?? config('database.default'));
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

        $this->app->make(Factory::class)->load(__DIR__ . '/database/factories');
    }

    /**
     * Register routes needed by the API
     */
    public static function routes(): void
    {
        Route::post(config('sync.slug') . '/getTableHash', '\\' . SyncController::class . '@getTableHash')->name('sync.getTableHash');
        Route::post(config('sync.slug') . '/getTable', '\\' . SyncController::class . '@getTable')->name('sync.getTable');
        Route::post(config('sync.slug') . '/getFileHashes', '\\' . SyncController::class . '@getFileHashes')->name('sync.getFileHashes');
        Route::post(config('sync.slug') . '/getFiles', '\\' . SyncController::class . '@getFiles')->name('sync.getFiles');
    }
}
