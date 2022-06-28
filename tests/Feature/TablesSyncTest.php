<?php

namespace Stickee\Sync\Test\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Stickee\Sync\Client;
use Stickee\Sync\Interfaces\TableHasherInterface;
use Stickee\Sync\TableImporter;
use Stickee\Sync\Test\TestCase;

class TablesSyncTest extends TestCase
{
    use DatabaseMigrations {
        runDatabaseMigrations as originalRunDatabaseMigrations;
    }

    /**
     * Run migrations
     */
    public function runDatabaseMigrations(): void
    {
        $this->useMysql();
        $this->originalRunDatabaseMigrations();
    }

    /**
     * Test syncing a table
     */
    public function test_sync(): void
    {
        $this->useMysql();

        $tableHasher = app(TableHasherInterface::class);

        $srcHash = $tableHasher->hash('sync-server', 'test_table');

        $this->assertEquals(0, DB::table('sync_tests_client')->count());

        // From empty table
        $this->testSync($srcHash);

        // Test extra records are deleted
        DB::table('sync_tests_client')
            ->insert([
                'test_1' => 123,
                'test_2' => 'x',
                'test_3' => 'y',
                'test_4' => null,
            ]);

        $this->testSync($srcHash);

        // Test changed records are updated
        DB::table('sync_tests_client')
            ->update(['test_1' => 123]);

        $this->testSync($srcHash);
    }

    /**
     * Test the table doesn't match before syncing and does after
     */
    private function testSync($srcHash): void
    {
        $tableHasher = app(TableHasherInterface::class);

        $this->assertNotEquals($srcHash, $tableHasher->hash('sync-client', 'test_table'));
        $this->sync();
        $this->assertEquals($srcHash, $tableHasher->hash('sync-client', 'test_table'));

    }

    /**
     * Sync the table
     */
    private function sync(): void
    {
        // TODO would be better to use the Client here
        // $client = app(Client::class);
        // $client->sync();

        $importer = app()->makeWith(TableImporter::class, ['configName' => 'test_table']);
        $importer->initialise();

        $response = $this->json('POST', '/getTable', ['config_name' => 'test_table']);

        // $response is a StreamedResponse so we can't use getContent()
        ob_start();
        $response->send();
        $body = ob_get_clean();

        $f = fopen('php://memory', 'w+');
        fwrite($f, gzdecode($body));
        fseek($f, 0);

        $importer->import($f);
    }
}
