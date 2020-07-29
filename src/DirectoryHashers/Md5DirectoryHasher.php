<?php

namespace Stickee\Sync\DirectoryHashers;

use Illuminate\Support\Facades\Storage;
use League\Flysystem\Adapter\Local;
use Stickee\Sync\Interfaces\DirectoryHasherInterface;
use Stickee\Sync\Traits\UsesDirectories;

/**
 */
class Md5DirectoryHasher implements DirectoryHasherInterface
{
    use UsesDirectories;

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
