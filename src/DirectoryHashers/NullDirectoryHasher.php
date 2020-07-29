<?php

namespace Stickee\Sync\DirectoryHashers;

use Illuminate\Support\Facades\Storage;
use Stickee\Sync\Interfaces\DirectoryHasherInterface;
use Stickee\Sync\Traits\ChecksDirectories;

/**
 */
class NullDirectoryHasher implements DirectoryHasherInterface
{
    use ChecksDirectories;

    public function hash(string $directory): array
    {
        $this->checkDirectory($directory);

        $disk = 'sync_test'; // todo

        $files = Storage::disk($disk)->allFiles($directory);
        $result = [];

        foreach ($files as $file) {
            $result[$file] = '';
        }

        return $result;
    }
}
