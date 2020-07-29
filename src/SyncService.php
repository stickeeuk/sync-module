<?php

namespace Stickee\Sync;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Stickee\Sync\Interfaces\TableHasherInterface;
use Stickee\Sync\Models\Property;
use Stickee\Sync\Traits\UsesTables;

/**
 * Property Service
 */
class SyncService
{
    use UsesTables;

    public function getTableHash(string $configName)
    {
        $config = $this->getTableInfo($configName);

        $tableHasher = app()->makeWith(
            TableHasherInterface::class,
            ['connection' => $config['connection']]
        );

        return $tableHasher->hash($configName);
    }
}
