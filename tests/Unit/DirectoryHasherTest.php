<?php

namespace Stickee\Sync\Test\Unit;

use Stickee\Sync\DirectoryHashers\Md5DirectoryHasher;
use Stickee\Sync\DirectoryHashers\NullDirectoryHasher;
use Stickee\Sync\Test\TestCase;

class DirectoryHasherTest extends TestCase
{
    /**
     *
     * @return void
     */
    public function test_null_directory_hasher()
    {
        $directoryHasher = app(NullDirectoryHasher::class);
        $hashes = $directoryHasher->hash('/');

        $expected = [
            '0.png' => '',
            '1/1a.png' => '',
            '1/1b.png' => '',
            '1/2/2.png' => '',
        ];

        $this->assertEquals($expected, $hashes, 'Wrong result for hashed directory');
    }

    /**
     *
     * @return void
     */
    public function test_md5_directory_hasher()
    {
        $directoryHasher = app(Md5DirectoryHasher::class);
        $hashes = $directoryHasher->hash('/');

        $expected = [
            '0.png' => '0094744589fe5af7231dead555afceaf',
            '1/1a.png' => '0094744589fe5af7231dead555afceaf',
            '1/1b.png' => '0094744589fe5af7231dead555afceaf',
            '1/2/2.png' => '0094744589fe5af7231dead555afceaf',
        ];

        $this->assertEquals($expected, $hashes, 'Wrong result for hashed directory');
    }
}
