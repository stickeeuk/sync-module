<?php

namespace Stickee\Sync\Traits;

use InvalidArgumentException;

/**
 */
trait ChecksDirectories
{
    protected function checkDirectory(string $directory)
    {
        // TODO
        // if (!in_array($directory, array_keys(config('sync.directories')))) {
        //     throw new InvalidArgumentException('Directory "' . $directory . '" is not in sync.directories');
        // }
    }
}
