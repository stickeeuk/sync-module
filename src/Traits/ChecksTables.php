<?php

namespace Stickee\Sync\Traits;

use InvalidArgumentException;

/**
 */
trait ChecksTables
{
    protected function checkTable(string $table)
    {
        if (!isset(config('sync.tables')[$table])) {
            throw new InvalidArgumentException('Table "' . $table . '" is not in sync.tables');
        }
    }
}
