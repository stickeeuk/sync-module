<?php

namespace Stickee\Sync;

use Illuminate\Support\Facades\Storage;
use Stickee\Sync\Traits\UsesDirectories;

/**
 */
class FileExporter
{
    use UsesDirectories;

    /**
     * Export files to a stream
     *
     * @param mixed $stream The stream to write to
     * @param string $configName The key in config('sync-server.directories')
     * @param array $files The files to copy to the stream
     */
    public function export($stream, string $configName, array $files): void
    {
        $config = $this->getDirectoryInfo('sync-server', $configName);

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
