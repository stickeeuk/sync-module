<?php

namespace Stickee\Sync\Test\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Stickee\Sync\ServiceProvider;
use Stickee\Sync\Test\TestCase;

class ApiTest extends TestCase
{
    use DatabaseMigrations {
        runDatabaseMigrations as originalRunDatabaseMigrations;
    }

    public function runDatabaseMigrations()
    {
        $this->useSqlite();
        $this->originalRunDatabaseMigrations();
    }

    /**
     *
     * @return void
     */
    public function test_get_table()
    {
        $this->useSqlite();

        ServiceProvider::routes();

        $response = $this->json('POST', '/sync/getTable', ['table' => 'sync_tests']);

        // $response is a StreamedResponse so we can't use getContent()
        ob_start();
        $response->send();
        $body = ob_get_clean();

        if ($response->getStatusCode() !== 200) {
            dump($body);
        }

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

        $response->assertOk();
        $this->assertEquals($expected, gzdecode($body), 'Wrong data returned');
    }
}
