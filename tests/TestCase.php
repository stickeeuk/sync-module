<?php

namespace Stickee\Sync\Test;

use Faker\Factory;
use Illuminate\Foundation\Auth\User;
use Orchestra\Testbench\TestCase as Orchestra;
use Stickee\Sync\Seeds\TestSeeder;
use Stickee\Sync\ServiceProvider;

abstract class TestCase extends Orchestra
{
    protected $connectionsToTransact = ['test_mysql'];
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        \DB::listen(function($sql) {
            dump($sql->connection->getName(), $sql->sql);

            if ($sql->bindings)
                ;//dump($sql->bindings);
            //dump($sql->time);
        });

        $faker = Factory::create();
        $faker->seed(1234);

        $this->useSqlite();
        $this->setUpDatabase();

        $this->useMysql();
        $this->setUpDatabase();
    }

    private function setUpDatabase()
    {
        dump('Migrating ' . config('database.default'));
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
