<?php

namespace Stickee\Sync\Test\Feature;

use Illuminate\Support\Facades\Storage;
use LogicException;
use Stickee\Sync\ServiceProvider;
use Stickee\Sync\Test\TestCase;

class FilesApiTest extends TestCase
{
    /**
     * Test getting file hashes via the API
     */
    public function test_get_hashes(): void
    {
        $response = $this->json('POST', '/getFileHashes', ['config_name' => 'sync_test']);

        if ($response->getStatusCode() !== 200) {
            dump($response->getOriginalContent());
        }

        $expected = [
            'hashes' => [
                '0.png' => '49d1e469707577ed310e09f89b0848bf',
                '1/1a.png' => '49d1e469707577ed310e09f89b0848bf',
                '1/1b.png' => 'd7eba7679f8c0dd80d1689cdda97b9d7',
                '1/2/2.png' => '49d1e469707577ed310e09f89b0848bf',
                'test-stream.bin' => '4bfee0d8db02fdb73bb2d154ed159459',
            ]
        ];

        $response->assertOk();
        $response->assertJson($expected);
    }

    /**
     * Test getting files via the API
     */
    public function test_get_files(): void
    {
        $disk = Storage::disk(config('sync-client.directories.sync_test.disk'));
        $expected = $disk->read('test-stream.bin');
        $files = [
            '0.png',
            '1/1a.png',
            '1/1b.png',
            '1/2/2.png',
        ];

        $response = $this->post('/getFiles', ['config_name' => 'sync_test', 'files' => $files]);

        // $response is a StreamedResponse so we can't use getContent()
        ob_start();
        $response->send();
        $body = ob_get_clean();

        if ($response->getStatusCode() !== 200) {
            dump($body);
        }

        $response->assertOk();
        $this->assertEquals($expected, $body, 'Wrong data returned');
    }

    /**
     * Test getting a file we shouldn't be allowed to
     */
    public function test_get_illegal_file(): void
    {
        $this->expectException(LogicException::class);
        $files = ['../README.md'];

        $response = $this->post('/getFiles', ['config_name' => 'sync_test', 'files' => $files]);
        $response->send();
    }
}
