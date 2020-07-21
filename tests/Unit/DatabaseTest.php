<?php

namespace Stickee\Sync\Test\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Stickee\Sync\Interfaces\TableDescriberInterface;
use Stickee\Sync\Interfaces\TableHasherInterface;
use Stickee\Sync\Models\SyncTest;
use Stickee\Sync\TableDescribers\MySqlTableDescriber;
use Stickee\Sync\TableDescribers\SqliteTableDescriber;
use Stickee\Sync\TableHashers\MySqlTableHasher;
use Stickee\Sync\TableHashers\SqliteTableHasher;
use Stickee\Sync\Test\TestCase;

class DatabaseTest extends TestCase
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
     *
     * @return void
     */
    public function test_table_hasher_factory_sqlite()
    {
        $this->useSqlite();

        $tableHasher = app(TableHasherInterface::class);

        $this->assertEquals(SqliteTableHasher::class, get_class($tableHasher), 'Wrong class created for SQLite');
    }

    /**
     *
     * @return void
     */
    public function test_table_hasher_factory_mysql()
    {
        $this->useMysql();

        $tableHasher = app(TableHasherInterface::class);

        $this->assertEquals(MySqlTableHasher::class, get_class($tableHasher), 'Wrong class created for MySQL');
    }

    /**
     *
     * @return void
     */
    public function test_table_hasher_mysql()
    {
        $this->useMysql();

        $tableHasher = app(TableHasherInterface::class);
        $hash = $tableHasher->hash('sync_tests');

        $this->assertEquals(MySqlTableHasher::class, get_class($tableHasher), 'Wrong class created for MySQL');
        $this->assertEquals('e022fe6a4e3352603ce26d4c13792f431ab21282', $hash, 'Wrong hash for MySQL');
    }

    /**
     *
     * @return void
     */
    public function test_table_hasher_sqlite()
    {
        $this->useSqlite();

        $tableHasher = app(TableHasherInterface::class);
        $hash = $tableHasher->hash('sync_tests');

        $this->assertEquals(SqliteTableHasher::class, get_class($tableHasher), 'Wrong class created for SQLite');
        $this->assertEquals('e022fe6a4e3352603ce26d4c13792f431ab21282', $hash, 'Wrong hash for SQLite');
    }
}
