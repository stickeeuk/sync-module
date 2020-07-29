<?php

namespace Stickee\Sync;

use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Stickee\Sync\Traits\UsesDirectories;

/**
 */
class FileExporter
{
    use UsesDirectories;

    public function export($stream, string $configName, array $files): void
    {
        // TODO check path of each file

        $config = $this->getDirectoryInfo($configName);

        // Export a stream in the format
        // 1. Metadata size (32 bits)
        // 2. Metadata json
        // 3. File data
        // ... repeat 1,2,3 for every file

        $disk = Storage::disk($config['disk']);

        foreach ($files as $file) {
            $meta = json_encode([
                'file' => $file,
                'size' => $disk->size($file),
            ]);
            fwrite($stream, pack('N', strlen($meta)));
            fwrite($stream, $meta);
            stream_copy_to_stream($disk->readStream($file), $stream);
        }
    }
}
