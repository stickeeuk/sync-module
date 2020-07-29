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
