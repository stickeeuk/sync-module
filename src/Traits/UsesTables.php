<?php

namespace Stickee\Sync\Traits;

use InvalidArgumentException;

trait UsesTables
{
    /**
     * Check if a config name exists
     *
     * @param string $configType The config type - 'sync-client' or 'sync-server'
     * @param string $configName The key in config('sync-client.tables') or config('sync-server.tables')
     */
    protected function checkTableConfig(string $configType, string $configName)
    {
        if (!isset(config($configType . '.tables')[$configName])) {
            throw new InvalidArgumentException('"' . $configName . '" is not in ' . $configType . '.tables');
        }
    }

    /**
     * Get information about a table
     *
     * @param string $configType The config type - 'sync-client' or 'sync-server'
     * @param string $configName The key in config('sync-client.tables') or config('sync-server.tables')
     *
     * @return array
     */
    protected function getTableInfo(string $configType, string $configName): array
    {
        $this->checkTableConfig($configType, $configName);

        $config = config($configType . '.tables')[$configName];
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
