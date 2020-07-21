<?php

namespace Stickee\Sync\Traits;

use InvalidArgumentException;

/**
 */
trait ChecksTables
{
    protected function checkTable(string $table)
    {
        if (!in_array($table, config('sync.allowed_tables'))) {
            throw new InvalidArgumentException('Table "' . $table . '" is not in sync.allowed_tables');
        }
    }
}
