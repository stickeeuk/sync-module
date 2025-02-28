<?php

namespace Stickee\Sync;

use Exception;
use InvalidArgumentException;
use Stickee\Sync\Interfaces\TableDescriberInterface;
use Stickee\Sync\Interfaces\TableHasherInterface;
use Stickee\Sync\TableHashers\MySqlTableHasher;
use Stickee\Sync\TableHashers\SqliteTableHasher;

class TableHasherFactory
{
    /**
     * Create a table hasher
     *
     * @param string $connection The database connection name
     *
     * @return \Stickee\Sync\Interfaces\TableHasherInterface
     */
    public function create(string $connection): TableHasherInterface
    {
        $class = '';
        $tableDescriber = app(TableDescriberInterface::class);
        $driver = config('database.connections.' . $connection . '.driver');

        if (!$driver) {
            throw new InvalidArgumentException('Invalid connection "' . $connection . '"');
        }

        switch ($driver) {
            case 'mysql':
                $class = MySqlTableHasher::class;

                break;

            case 'sqlite':
                $class = SqliteTableHasher::class;

                break;

            default:
                throw new Exception('No TableHasher for "' . $driver . '"');
        }

        return app()->makeWith($class, ['tableDescriber' => $tableDescriber]);
    }
}
