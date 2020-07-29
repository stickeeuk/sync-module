<?php

namespace Stickee\Sync\Traits;

use InvalidArgumentException;

/**
 */
trait UsesDirectories
{
    protected function checkDirectoryConfig(string $configName)
    {
        if (!isset(config('sync.directories')[$configName])) {
            throw new InvalidArgumentException('"' . $configName . '" is not in sync.directories');
        }
    }

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
