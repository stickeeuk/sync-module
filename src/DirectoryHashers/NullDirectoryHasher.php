<?php

namespace Stickee\Sync\DirectoryHashers;

use Illuminate\Support\Facades\Storage;
use Stickee\Sync\Interfaces\DirectoryHasherInterface;
use Stickee\Sync\Traits\UsesDirectories;

/**
 */
class NullDirectoryHasher implements DirectoryHasherInterface
{
    use UsesDirectories;

    /**
     * Hash a directory specified in config('sync.directories')
     *
     * @param string $configName The key in config('sync.directories')
     *
     * @return array A map of file => hash
     */
    public function hash(string $configName): array
    {
        $config = $this->getDirectoryInfo($configName);

        $disk = Storage::disk($config['disk']);
        $files = $disk->allFiles('');
        $result = [];

        foreach ($files as $file) {
            $result[$file] = '';
        }

        return $result;
    }
}
