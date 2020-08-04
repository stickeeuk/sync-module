<?php

namespace Stickee\Sync\DirectoryHashers;

use Illuminate\Support\Facades\Storage;
use League\Flysystem\Adapter\Local;
use Stickee\Sync\Interfaces\DirectoryHasherInterface;
use Stickee\Sync\Traits\UsesDirectories;

/**
 * Hash all files in a directory using MD5
 */
class Md5DirectoryHasher implements DirectoryHasherInterface
{
    use UsesDirectories;

    /**
     * Hash a directory specified in config('sync.directories')
     *
     * @param string $configName The key from config('sync.directories')
     *
     * @return array A map of file => hash
     */
    public function hash(string $configName): array
    {
        $config = $this->getDirectoryInfo($configName);

        $disk = Storage::disk($config['disk']);
        $isLocal = $disk->getDriver()->getAdapter() instanceof Local;
        $files = $disk->allFiles('');
        $result = [];
        $path = $disk->path('');

        foreach ($files as $file) {
            $result[$file] = $isLocal ? md5_file($path . $file) : md5($disk->get($file));
        }

        return $result;
    }
}
