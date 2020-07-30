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
        $primary = $config['primary'] ?? ['id'];

        if (!is_array($primary)) {
            $primary = [$primary];
        }

        $importIndexes = $config['importIndexes'] ?? ['PRIMARY'];

        if (!is_array($importIndexes)) {
            $importIndexes = [$importIndexes];
        }

        return [
            'config' => $config,
            'connection' => $config['connection'] ?? config('database.default'),
            'table' => $config['table'] ?? $configName,
            'primary' => $primary,
            'importIndexes' => $importIndexes,
        ];
    }
}
