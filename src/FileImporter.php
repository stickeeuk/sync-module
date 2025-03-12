<?php

namespace Stickee\Sync;

use Exception;
use Illuminate\Support\Facades\Storage;
use Stickee\Sync\Traits\UsesDirectories;

class FileImporter
{
    use UsesDirectories;

    /**
     * Import files from a stream
     *
     * @param mixed $stream The stream to read from
     * @param mixed $callback A callback for each file
     */
    public function import($stream, $callback): void
    {
        while (!feof($stream)) {
            $packedSize = fread($stream, 4);

            if ($packedSize === false) {
                throw new Exception('fread error');
            }

            if ($packedSize === '') {
                break;
            }

            $metaSize = unpack('Nsize', $packedSize)['size'];
            $metaJson = fread($stream, $metaSize);

            if ($metaJson === false) {
                throw new Exception('fread error');
            }

            $meta = json_decode($metaJson);
            $data = $meta->size ? fread($stream, $meta->size) : '';

            if ($data === false) {
                throw new Exception('fread error');
            }

            $allMeta[] = $metaSize;
            $allMeta[] = $metaJson;

            call_user_func($callback, $meta, $data);
        }
    }

    /**
     * Import a file stream to a directory
     *
     * @param mixed $stream The stream to read from
     * @param string $configName The key in config('sync-client.directories')
     */
    public function importToDirectory($stream, string $configName): void
    {
        $config = $this->getDirectoryInfo(Helpers::CLIENT_CONFIG, $configName);

        $disk = Storage::disk($config['disk']);

        $callback = function ($meta, $data) use ($disk): void {
            $disk->put($meta->file, $data);
        };

        $this->import($stream, $callback);
    }
}
