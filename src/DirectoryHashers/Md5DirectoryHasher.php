<?php

namespace Stickee\Sync\DirectoryHashers;

use Illuminate\Support\Facades\Storage;
use League\Flysystem\Adapter\Local;
use Stickee\Sync\Interfaces\DirectoryHasherInterface;
use Stickee\Sync\Traits\ChecksDirectories;

/**
 */
class Md5DirectoryHasher implements DirectoryHasherInterface
{
    use ChecksDirectories;

    public function hash(string $directory): array
    {
        $this->checkDirectory($directory);

        $diskName = 'sync_test'; // todo
        $disk = Storage::disk($diskName);
        $driver = $disk->getDriver();
        $isLocal = $driver->getAdapter() instanceof Local;
        $files = $disk->allFiles($directory);
        $result = [];
        $path = $disk->path('');

        foreach ($files as $file) {
            $result[$file] = $isLocal ? md5_file($path . $file) : md5($disk->get($file));
        }

        return $result;
    }
}
