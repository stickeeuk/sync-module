<?php

namespace Stickee\Sync\Test\Unit;

use Stickee\Sync\DirectoryHashers\Md5DirectoryHasher;
use Stickee\Sync\DirectoryHashers\NullDirectoryHasher;
use Stickee\Sync\Test\TestCase;

class DirectoryHasherTest extends TestCase
{
    /**
     * Test the NullDirectoryHasher
     */
    public function test_null_directory_hasher(): void
    {
        $directoryHasher = app(NullDirectoryHasher::class);
        $hashes = $directoryHasher->hash('sync-server', 'sync_test');

        $expected = [
            '0.png' => '',
            '1/1a.png' => '',
            '1/1b.png' => '',
            '1/2/2.png' => '',
            'test-stream.bin' => '',
        ];

        $this->assertEquals($expected, $hashes, 'Wrong result for hashed directory');
    }

    /**
     * Test the Md5DirectoryHasher
     */
    public function test_md5_directory_hasher(): void
    {
        $directoryHasher = app(Md5DirectoryHasher::class);
        $hashes = $directoryHasher->hash('sync-server', 'sync_test');

        $expected = [
            '0.png' => '49d1e469707577ed310e09f89b0848bf',
            '1/1a.png' => '49d1e469707577ed310e09f89b0848bf',
            '1/1b.png' => 'd7eba7679f8c0dd80d1689cdda97b9d7',
            '1/2/2.png' => '49d1e469707577ed310e09f89b0848bf',
            'test-stream.bin' => '4bfee0d8db02fdb73bb2d154ed159459',
        ];

        $this->assertEquals($expected, $hashes, 'Wrong result for hashed directory');
    }
}
