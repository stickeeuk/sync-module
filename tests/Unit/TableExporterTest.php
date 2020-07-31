<?php

namespace Stickee\Sync\Test\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Stickee\Sync\Interfaces\TableDescriberInterface;
use Stickee\Sync\Interfaces\TableHasherInterface;
use Stickee\Sync\Models\SyncTest;
use Stickee\Sync\TableDescribers\MySqlTableDescriber;
use Stickee\Sync\TableDescribers\SqliteTableDescriber;
use Stickee\Sync\TableExporter;
use Stickee\Sync\TableHashers\MySqlTableHasher;
use Stickee\Sync\TableHashers\SqliteTableHasher;
use Stickee\Sync\Test\TestCase;

class TableExporterTest extends TestCase
{
    /**
     * Test the table exporter
     */
    public function test_export_data(): void
    {
        $this->useSqlite();

        $stream = fopen('php://memory', 'w+');
        $tableExporter = app(TableExporter::class);

        // Use a small chunk size so we can test the chunking is working
        $tableExporter->chunkSize = 2;

        $tableExporter->export($stream, 'sync_tests');

        fseek($stream, 0);
        $data = stream_get_contents($stream);
        fclose($stream);

        $expected = '{"id":"1","test_1":"49766366","test_2":"Amet iste laborum eius est dolor dolores.","test_3":null}' . "\n"
            . '{"id":"2","test_1":"1506369","test_2":"Quibusdam sed vel a quo sed fugit facilis.","test_3":null}' . "\n"
            . '{"id":"3","test_1":"4","test_2":"Ipsam sit veniam sed fuga aspernatur natus.","test_3":null}' . "\n"
            . '{"id":"4","test_1":"503083165","test_2":"Perferendis voluptatibus incidunt nostrum quia possimus.","test_3":null}' . "\n"
            . '{"id":"5","test_1":"738286","test_2":"Necessitatibus architecto aut consequatur debitis et id.","test_3":null}' . "\n"
            . '{"id":"6","test_1":"4527","test_2":"Totam temporibus quia ipsam ut iusto iusto.","test_3":null}' . "\n"
            . '{"id":"7","test_1":"43503859","test_2":"Accusantium et a qui ducimus nihil laudantium.","test_3":null}' . "\n"
            . '{"id":"8","test_1":"3764722","test_2":"Cum molestiae vel natus ex dicta hic inventore.","test_3":null}' . "\n"
            . '{"id":"9","test_1":"819253609","test_2":"Quae non quia dicta in aut provident.","test_3":null}' . "\n"
            . '{"id":"10","test_1":"9589","test_2":"Dignissimos error sit labore quos.","test_3":null}' . "\n";

        $this->assertEquals($expected, gzdecode($data), 'Wrong data returned');
    }
}
