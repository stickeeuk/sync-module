<?php

namespace Stickee\Sync\Traits;

use InvalidArgumentException;

/**
 */
trait ChecksTables
{
    protected function checkTable(string $table)
    {
        if (!in_array($table, array_keys(config('sync.tables')))) {
            throw new InvalidArgumentException('Table "' . $table . '" is not in sync.tables');
        }
    }
}
