<?php

namespace Stickee\Sync\Traits;

use InvalidArgumentException;

trait UsesDirectories
{
    /**
     * Check if a config name exists
     *
     * @param string $configType The config type - 'sync-client' or 'sync-server'
     * @param string $configName The key in config('sync-client.directories') or config('sync-server.directories')
     */
    protected function checkDirectoryConfig(string $configType, string $configName): void
    {
        if (!isset(config($configType . '.directories')[$configName])) {
            throw new InvalidArgumentException('"' . $configName . '" is not in ' . $configType . '.directories');
        }
    }

    /**
     * Get information about a directory
     *
     * @param string $configType The config type - 'sync-client' or 'sync-server'
     * @param string $configName The key in config('sync-client.directories') or config('sync-server.directories')
     */
    protected function getDirectoryInfo(string $configType, string $configName): array
    {
        $this->checkDirectoryConfig($configType, $configName);

        $config = config($configType . '.directories')[$configName];

        return [
            'config' => $config,
            'disk' => $config['disk'] ?? config('filesystems.default'),
            'hasher' => $config['hasher'] ?? config($configType . '.default_file_hasher'),
        ];
    }
}
