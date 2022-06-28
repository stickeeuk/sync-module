<?php

namespace Stickee\Sync\Test\Unit;

use Illuminate\Support\Facades\Storage;
use Stickee\Sync\FileImporter;
use Stickee\Sync\Test\TestCase;

class FileImporterTest extends TestCase
{
    /**
     * Test the file importer
     */
    public function test_file_importer(): void
    {
        $fileImporter = app(FileImporter::class);
        $disk = Storage::disk(config('sync-client.directories.sync_test.disk'));
        $stream = $disk->readStream('test-stream.bin');
        $allMeta = [
            [
               'file' => '0.png',
               'size' => 4340,
            ],
            [
               'file' => '1/1a.png',
               'size' => 4340,
            ],
            [
               'file' => '1/1b.png',
               'size' => 3077,
            ],
            [
               'file' => '1/2/2.png',
               'size' => 4340,
            ],
        ];

        $fileImporter->import($stream, function ($meta, $data) use (&$allMeta, $disk) {
            $expected = array_shift($allMeta);
            $this->assertEquals($expected, (array)$meta, 'Wrong meta data returned');
            $this->assertEquals($disk->read($meta->file), $data, 'Incorrect file data for ' . $meta->file);
        });

        $this->assertEquals([], $allMeta, 'Some files were not read');

        fclose($stream);
    }
}
