<?php

namespace Stickee\Sync\Test\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Stickee\Sync\Interfaces\TableHasherInterface;
use Stickee\Sync\TableHashers\MySqlTableHasher;
use Stickee\Sync\TableHashers\SqliteTableHasher;
use Stickee\Sync\Test\TestCase;

class DatabaseTest extends TestCase
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
     * Test the factory makes an SQLite table hasher
     */
    public function test_table_hasher_factory_sqlite(): void
    {
        $this->useSqlite();

        $tableHasher = app(TableHasherInterface::class);

        $this->assertEquals(SqliteTableHasher::class, get_class($tableHasher), 'Wrong class created for SQLite');
    }

    /**
     * Test the factory makes a MySQL table hasher
     */
    public function test_table_hasher_factory_mysql(): void
    {
        $this->useMysql();

        $tableHasher = app(TableHasherInterface::class);

        $this->assertEquals(MySqlTableHasher::class, get_class($tableHasher), 'Wrong class created for MySQL');
    }

    /**
     * Test the SQLite table hasher
     */
    public function test_table_hasher_sqlite(): void
    {
        $this->useSqlite();

        $tableHasher = app(TableHasherInterface::class);
        $hash = $tableHasher->hash('sync-server', 'test_table');

        $this->assertEquals(SqliteTableHasher::class, get_class($tableHasher), 'Wrong class created for SQLite');
        $this->assertEquals('da937ef9a2991114184c12039310c558ec4ab24c', $hash, 'Wrong hash for SQLite');
    }

    /**
     * Test the MySQL table hasher
     */
    public function test_table_hasher_mysql(): void
    {
        $this->useMysql();

        $tableHasher = app(TableHasherInterface::class);
        $hash = $tableHasher->hash('sync-server', 'test_table');

        $this->assertEquals(MySqlTableHasher::class, get_class($tableHasher), 'Wrong class created for MySQL');
        $this->assertEquals('da937ef9a2991114184c12039310c558ec4ab24c', $hash, 'Wrong hash for MySQL');
    }
}
