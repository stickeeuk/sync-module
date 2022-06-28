<?php

namespace Stickee\Sync\Test;

use Faker\Factory;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase as Orchestra;
use PDO;
use Stickee\Sync\Seeds\TestSeeder;
use Stickee\Sync\ServiceProvider;

abstract class TestCase extends Orchestra
{
    use DatabaseMigrations {
        runDatabaseMigrations as originalRunDatabaseMigrations;
    }

    /**
     * Run migrations
     */
    public function runDatabaseMigrations(): void
    {
        $this->useSqlite();
        $this->originalRunDatabaseMigrations();

        $this->useMysql();
        $this->originalRunDatabaseMigrations();
    }

    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        ServiceProvider::routes();

        $faker = Factory::create();
        $faker->seed(1234);

        $this->useSqlite();
        $this->setUpDatabase();

        $faker->seed(1234);

        $this->useMysql();
        $this->setUpDatabase();
    }

    /**
     * Set up the database
     */
    private function setUpDatabase()
    {
        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__ . '/../src/database/migrations');
        $this->artisan('migrate');

        $this->withFactories(__DIR__ . '/../src/database/factories');

        $this->seed(TestSeeder::class);
    }

    /**
     * Define environment setup
     *
     * @param \Illuminate\Foundation\Application $app The application
     */
    protected function getEnvironmentSetUp($app)
    {
        $this->useSqlite();

        $app['config']->set('database.connections.test_sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => 'test_',
        ]);

        $app['config']->set('database.connections.test_mysql', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => 'test_',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),

                // SQLite always stringifies fetches, so set it on mysql
                // so that the tests get the same data regardless of the DB
                PDO::ATTR_STRINGIFY_FETCHES => true,
            ]),
        ]);

        $app['config']->set('sync-client.tables', ['test_table' => ['table' => 'sync_tests_client']]);
        $app['config']->set('sync-server.tables', ['test_table' => ['table' => 'sync_tests']]);
        $app['config']->set('filesystems.disks.sync_test', [
            'driver' => 'local',
            'root' => __DIR__ . '/../test-data',
        ]);
        $app['config']->set('sync-client.directories', [
            'sync_test' => [
                'disk' => 'sync_test',
            ],
        ]);
        $app['config']->set('sync-server.directories', [
            'sync_test' => [
                'disk' => 'sync_test',
            ],
        ]);
    }

    /**
     * Get the package's providers
     *
     * @param \Illuminate\Foundation\Application $app The app container
     *
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            ServiceProvider::class,
        ];
    }

    /**
     * Set the SQLite connection as the default
     */
    protected function useSqlite(): void
    {
        app()['config']->set('database.default', 'test_sqlite');
    }

    /**
     * Set the MySQL connection as the default
     */
    protected function useMysql(): void
    {
        app()['config']->set('database.default', 'test_mysql');
    }
}
