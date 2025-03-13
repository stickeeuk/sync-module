<?php

namespace Stickee\Sync\Test\Unit;

use Stickee\Sync\TableExporter;
use Stickee\Sync\Test\TestCase;

class TableExporterTest extends TestCase
{
    /**
     * Test the table exporter
     */
    public function test_export_data(): void
    {
        $this->useSqlite();

        $stream = fopen('php://memory', 'w+b');
        $tableExporter = app(TableExporter::class);

        // Use a small chunk size so we can test the chunking is working
        $tableExporter->chunkSize = 2;

        $tableExporter->export($stream, 'test_table');

        fseek($stream, 0);
        $data = stream_get_contents($stream);
        fclose($stream);

        $expected = '{"id":1,"test_1":7449171,"test_2":"Animi quos velit et fugiat.","test_3":null,"test_4":"A"}' . "\n"
            . '{"id":2,"test_1":54,"test_2":"Deserunt aut ab provident perspiciatis quo omnis nostrum.","test_3":null,"test_4":"C"}' . "\n"
            . '{"id":3,"test_1":1031881,"test_2":"Incidunt iure odit et et modi ipsum.","test_3":null,"test_4":null}' . "\n"
            . '{"id":4,"test_1":5066,"test_2":"Aut dolores enim non facere tempora ex voluptatem.","test_3":"Quis adipisci molestias fugit deleniti distinctio eum.","test_4":null}' . "\n"
            . '{"id":5,"test_1":7788,"test_2":"Aliquam veniam corporis dolorem mollitia deleniti nemo.","test_3":"Officia est dignissimos neque blanditiis odio veritatis excepturi.","test_4":"A"}' . "\n"
            . '{"id":6,"test_1":91506985,"test_2":"Est alias tenetur ratione.","test_3":null,"test_4":"A"}' . "\n"
            . '{"id":7,"test_1":5439,"test_2":"Modi rerum ex repellendus assumenda et tenetur.","test_3":"Quia perspiciatis deserunt ducimus corrupti et.","test_4":null}' . "\n"
            . '{"id":8,"test_1":3199,"test_2":"Odit doloribus repellat officiis corporis nesciunt ut ratione iure.","test_3":"Ut rem est esse sint.","test_4":null}' . "\n"
            . '{"id":9,"test_1":938658,"test_2":"Doloribus fugiat ut aut deserunt et.","test_3":null,"test_4":null}' . "\n"
            . '{"id":10,"test_1":49895,"test_2":"Dolorem et ut dicta.","test_3":null,"test_4":"A"}' . "\n";

        $this->assertEquals($expected, gzdecode($data), 'Wrong data returned');
    }
}
