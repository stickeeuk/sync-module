<?php

namespace Stickee\Sync\Traits;

use InvalidArgumentException;

/**
 */
trait UsesDirectories
{
    /**
     * Check if a config name exists
     *
     * @param string $configName The name in config('sync.directories')
     */
    protected function checkDirectoryConfig(string $configName): void
    {
        if (!isset(config('sync.directories')[$configName])) {
            throw new InvalidArgumentException('"' . $configName . '" is not in sync.directories');
        }
    }

    /**
     * Get information about a directory
     *
     * @param string $configName The name in config('sync.directories')
     *
     * @return array
     */
    protected function getDirectoryInfo(string $configName): array
    {
        $this->checkDirectoryConfig($configName);

        $config = config('sync.directories')[$configName];

        return [
            'config' => $config,
            'disk' => $config['disk'] ?? config('filesystems.default'),
            'hasher' => $config['hasher'] ?? config('sync.default_file_hasher'),
        ];
    }
}
