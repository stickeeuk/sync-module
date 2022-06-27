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

        $stream = fopen('php://memory', 'w+');
        $tableExporter = app(TableExporter::class);

        // Use a small chunk size so we can test the chunking is working
        $tableExporter->chunkSize = 2;

        $tableExporter->export($stream, 'test_table');

        fseek($stream, 0);
        $data = stream_get_contents($stream);
        fclose($stream);

        $expected = '{"id":"1","test_1":"49766366","test_2":"Amet iste laborum eius est dolor dolores.","test_3":null,"test_4":"C"}' . "\n"
        . '{"id":"2","test_1":"20240141","test_2":"A quo sed fugit facilis perferendis dolores molestias.","test_3":"Veniam sed fuga aspernatur natus earum.","test_4":null}' . "\n"
        . '{"id":"3","test_1":"137","test_2":"Incidunt nostrum quia possimus rerum id et necessitatibus architecto.","test_3":"Debitis et id nisi qui id.","test_4":"C"}' . "\n"
        . '{"id":"4","test_1":"31","test_2":"Iusto iusto accusamus iusto similique accusantium et.","test_3":null,"test_4":"B"}' . "\n"
        . '{"id":"5","test_1":"4625766","test_2":"Autem omnis cum molestiae vel natus ex dicta.","test_3":null,"test_4":"C"}' . "\n"
        . '{"id":"6","test_1":"68923564","test_2":"Non quia dicta in.","test_3":null,"test_4":null}' . "\n"
        . '{"id":"7","test_1":"9589","test_2":"Dignissimos error sit labore quos.","test_3":"Repudiandae est nostrum et voluptas consequatur delectus autem.","test_4":null}' . "\n"
        . '{"id":"8","test_1":"5","test_2":"Et perferendis fuga a debitis.","test_3":null,"test_4":null}' . "\n"
        . '{"id":"9","test_1":"239","test_2":"Autem deleniti ut quibusdam et eum.","test_3":null,"test_4":"B"}' . "\n"
        . '{"id":"10","test_1":"78413","test_2":"Nihil aut nisi officiis rerum id tempore voluptate sit.","test_3":"Odit aut voluptas quasi ut.","test_4":null}' . "\n";

        $this->assertEquals($expected, gzdecode($data), 'Wrong data returned');
    }
}
