<?php

namespace Stickee\Sync\Traits;

use InvalidArgumentException;

/**
 */
trait UsesTables
{
    protected function checkTableConfig(string $configName)
    {
        if (!isset(config('sync.tables')[$configName])) {
            throw new InvalidArgumentException('"' . $configName . '" is not in sync.tables');
        }
    }

    protected function getTableInfo(string $configName): array
    {
        $this->checkTableConfig($configName);

        $config = config('sync.tables')[$configName];

        return [
            'config' => $config,
            'connection' => $config['connection'] ?? config('database.default'),
            'table' => $config['table'] ?? $configName,
            'primary' => $config['primary'] ?? ['id'],
        ];
    }
}
