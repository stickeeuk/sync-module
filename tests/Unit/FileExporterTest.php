<?php

namespace Stickee\Sync\Test\Unit;

use Exception;
use Illuminate\Support\Facades\Storage;
use Stickee\Sync\FileExporter;
use Stickee\Sync\Test\TestCase;

class FileExporterTest extends TestCase
{
    /**
     *
     * @return void
     */
    public function test_file_exporter()
    {
        $stream = fopen('php://memory', 'w+');
        $fileExporter = app(FileExporter::class);
        $disk = Storage::disk(config('sync.directories.sync_test.disk'));
        $files = $disk->allFiles('');
        $allMeta = [];

        $fileExporter->export($stream, 'sync_test', $files);

        fseek($stream, 0);

        while (!feof($stream)) {
            $packedSize = fread($stream, 4);

            if ($packedSize === false) {
                throw new Exception('fread error');
            } elseif ($packedSize === '') {
                break;
            }

            $metaSize = unpack('Nsize', $packedSize)['size'];
            $metaJson = fread($stream, $metaSize);

            if ($metaJson === false) {
                throw new Exception('fread error');
            }

            $meta = json_decode($metaJson);
            $data = fread($stream, $meta->size);

            if ($data === false) {
                throw new Exception('fread error');
            }

            $allMeta[] = $metaSize;
            $allMeta[] = $metaJson;

            $this->assertEquals($disk->read($meta->file), $data, 'Incorrect file data');
        }

        fclose($stream);

        $expected = [
            28,
            '{"file":"0.png","size":9370}',
            32,
            '{"file":"1\\/1a.png","size":9370}',
            32,
            '{"file":"1\\/1b.png","size":9370}',
            34,
            '{"file":"1\\/2\\/2.png","size":9370}',
        ];


        $this->assertEquals($expected, $allMeta, 'Wrong data returned');
    }
}
