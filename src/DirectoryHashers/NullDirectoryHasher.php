<?php

namespace Stickee\Sync\DirectoryHashers;

use Illuminate\Support\Facades\Storage;
use Stickee\Sync\Interfaces\DirectoryHasherInterface;
use Stickee\Sync\Traits\UsesDirectories;

class NullDirectoryHasher implements DirectoryHasherInterface
{
    use UsesDirectories;

    /**
     * Hash a directory specified in config('sync-client.directories') or config('sync-server.directories')
     *
     * @param string $configType The config type - 'sync-client' or 'sync-server'
     * @param string $configName The key in config('sync-client.directories') or config('sync-server.directories')
     *
     * @return array A map of file => hash
     */
    public function hash(string $configType, string $configName): array
    {
        $config = $this->getDirectoryInfo($configType, $configName);

        $disk = Storage::disk($config['disk']);
        $files = $disk->allFiles('');
        $result = [];

        foreach ($files as $file) {
            $result[$file] = '';
        }

        return $result;
    }
}
