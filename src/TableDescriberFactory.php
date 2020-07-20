<?php

namespace Stickee\Sync;

use Exception;
use InvalidArgumentException;
use Stickee\Sync\Interfaces\TableDescriberInterface;
use Stickee\Sync\TableDescribers\MySqlTableDescriber;
use Stickee\Sync\TableDescribers\SqliteTableDescriber;

/**
 */
class TableDescriberFactory
{
    public function create($connection = null): TableDescriberInterface
    {
        $connection = $connection ?: config('database.default');
        $driver = config('database.connections.' . $connection . '.driver');

        if (!$driver) {
            throw new InvalidArgumentException('Invalid connection "' . $connection . '"');
        }

        switch ($driver) {
            case 'mysql':
                return app(MySqlTableDescriber::class);

            case 'sqlite':
                return app(SqliteTableDescriber::class);

            default:
                throw new Exception('No TableDescriber for "' . $driver . '"');
        }
    }
}
