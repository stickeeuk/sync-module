<?php

namespace Stickee\Sync\Test\Unit;

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
    use RefreshDatabase {
        refreshDatabase as originalRefreshDatabase;
    }

    /**
     * Define hooks to migrate the database before and after each test.
     *
     * @return void
     */
    public function refreshDatabase()
    {
        $this->useSqlite();
        $this->originalRefreshDatabase();

        $this->useMysql();
        $this->originalRefreshDatabase();
    }

    /**
     *
     * @return void
     */
    public function test_table_describer_factory_sqlite()
    {
        $this->useSqlite();

        $tableDescriber = app(TableDescriberInterface::class);

        $this->assertEquals(SqliteTableDescriber::class, get_class($tableDescriber), 'Wrong class created for SQLite');
    }

    /**
     *
     * @return void
     */
    public function test_table_describer_factory_mysql()
    {
        $this->useMysql();

        $tableDescriber = app(TableDescriberInterface::class);

        $this->assertEquals(MySqlTableDescriber::class, get_class($tableDescriber), 'Wrong class created for MySQL');
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
        dd($hash);

        //$this->assertEquals(MySqlTableHasher::class, get_class($tableHasher), 'Wrong class created for MySQL');
    }
}
