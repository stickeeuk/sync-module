<?php

namespace Stickee\Sync\DirectoryHashers;

use Illuminate\Support\Facades\Storage;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Stickee\Sync\Interfaces\DirectoryHasherInterface;
use Stickee\Sync\Traits\UsesDirectories;

/**
 * Hash all files in a directory using MD5
 */
class Md5DirectoryHasher implements DirectoryHasherInterface
{
    use UsesDirectories;

    /**
     * Hash a directory specified in config('sync-client.directories') or config('sync-server.directories')
     *
     * @param string $configType The config type - 'sync-client' or 'sync-server'
     * @param string $configName The key from config('sync-client.directories') or config('sync-server.directories')
     *
     * @return array A map of file => hash
     */
    public function hash(string $configType, string $configName): array
    {
        $config = $this->getDirectoryInfo($configType, $configName);
        $disk = Storage::disk($config['disk']);
        $isLocal = $disk->getAdapter() instanceof LocalFilesystemAdapter;
        $files = $disk->allFiles('');
        $result = [];
        $path = $disk->path('');

        foreach ($files as $file) {
            $result[$file] = $isLocal ? md5_file($path . $file) : md5($disk->get($file));
        }

        return $result;
    }
}
