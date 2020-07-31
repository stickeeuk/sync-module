<?php

namespace Stickee\Sync\Traits;

use InvalidArgumentException;

/**
 */
trait UsesTables
{
    /**
     * Check if a config name exists
     *
     * @param string $configName The name in config('sync.tables')
     */
    protected function checkTableConfig(string $configName)
    {
        if (!isset(config('sync.tables')[$configName])) {
            throw new InvalidArgumentException('"' . $configName . '" is not in sync.tables');
        }
    }

    /**
     * Get information about a table
     *
     * @param string $configName The name in config('sync.tables')
     *
     * @return array
     */
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
