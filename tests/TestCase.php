<?php

namespace Stickee\Sync\Test;

use Faker\Factory;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Orchestra\Testbench\TestCase as Orchestra;
use PDO;
use Stickee\Sync\Seeds\TestSeeder;
use Stickee\Sync\ServiceProvider;

abstract class TestCase extends Orchestra
{
    use DatabaseMigrations {
        runDatabaseMigrations as originalRunDatabaseMigrations;
    }

    public function runDatabaseMigrations()
    {
        $this->useSqlite();
        $this->originalRunDatabaseMigrations();

        $this->useMysql();
        $this->originalRunDatabaseMigrations();
    }

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $faker = Factory::create();
        $faker->seed(1234);

        $this->useSqlite();
        $this->setUpDatabase();

        $faker->seed(1234);

        $this->useMysql();
        $this->setUpDatabase();
    }

    private function setUpDatabase()
    {
        // dump('Migrating ' . config('database.default'));
        $this->loadLaravelMigrations();
        $this->artisan('migrate');

        $this->withFactories(__DIR__ . '/../src/database/factories');

        $this->seed(TestSeeder::class);
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $this->useSqlite();

        $app['config']->set('database.connections.test_sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => 'test_',
        ]);

        // $app['config']->set('database.default', 'test_mysql');
        // TODO use env
        $app['config']->set('database.connections.test_mysql', [
            'driver'   => 'mysql',
            'host'   => 'localhost',
            'database' => 'sync_test',
            'prefix'   => 'test_',
            'username'   => 'root',
            'password'   => '',
            'options' => [
                // SQLite always stringifies fetches, so set it on mysql
                // so that the tests get the same data regardless of the DB
                PDO::ATTR_STRINGIFY_FETCHES => true,
            ],
        ]);

        $app['config']->set('sync.tables', ['sync_tests' => []]);
        $app['config']->set('filesystems.disks.sync_test', [
            'driver' => 'local',
            'root' => __DIR__ . '/../test-data',
        ]);
        $app['config']->set('sync.directories', [
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
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }

    protected function useSqlite()
    {
        app()['config']->set('database.default', 'test_sqlite');
    }

    protected function useMysql()
    {
        app()['config']->set('database.default', 'test_mysql');
    }
}
