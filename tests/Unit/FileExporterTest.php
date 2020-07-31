<?php

namespace Stickee\Sync\Test\Unit;

use Exception;
use Illuminate\Support\Facades\Storage;
use Stickee\Sync\FileExporter;
use Stickee\Sync\Test\TestCase;

class FileExporterTest extends TestCase
{
    /**
     * Test the file exporter
     */
    public function test_file_exporter(): void
    {
        $stream = fopen('php://memory', 'w+');
        $fileExporter = app(FileExporter::class);
        $disk = Storage::disk(config('sync.directories.sync_test.disk'));
        $files = $disk->allFiles('');
        $allMeta = [];

        $fileExporter->export($stream, 'sync_test', $files);

        fseek($stream, 0);

        // Use this to update test-stream.bin. Don't forget to delete the file first.
        // $disk->put('test-stream.bin', fread($stream, 999999)); die('File updated');

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
            '{"file":"0.png","size":4340}',
            32,
            '{"file":"1\\/1a.png","size":4340}',
            32,
            '{"file":"1\\/1b.png","size":3077}',
            34,
            '{"file":"1\\/2\\/2.png","size":4340}',
            39,
            '{"file":"test-stream.bin","size":16239}',

        ];


        $this->assertEquals($expected, $allMeta, 'Wrong data returned');
    }
}
