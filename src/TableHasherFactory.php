<?php

namespace Stickee\Sync;

use Exception;
use InvalidArgumentException;
use Stickee\Sync\Interfaces\TableDescriberInterface;
use Stickee\Sync\Interfaces\TableHasherInterface;
use Stickee\Sync\TableHashers\MySqlTableHasher;
use Stickee\Sync\TableHashers\SqliteTableHasher;

/**
 */
class TableHasherFactory
{
    public function create(?string $connection = null): TableHasherInterface
    {
        $connection = $connection ?: config('database.default');
        $driver = config('database.connections.' . $connection . '.driver');

        if (!$driver) {
            throw new InvalidArgumentException('Invalid connection "' . $connection . '"');
        }

        $tableDescriber = app()->makeWith(TableDescriberInterface::class, ['connection' => $connection]);

        switch ($driver) {
            case 'mysql':
                return app()->makeWith(MySqlTableHasher::class, ['tableDescriber' => $tableDescriber]);

            case 'sqlite':
                return app()->makeWith(SqliteTableHasher::class, ['tableDescriber' => $tableDescriber]);

            default:
                throw new Exception('No TableHasher for "' . $driver . '"');
        }
    }
}
