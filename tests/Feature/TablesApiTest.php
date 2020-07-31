<?php

namespace Stickee\Sync\Test\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Stickee\Sync\ServiceProvider;
use Stickee\Sync\Test\TestCase;

class TablesApiTest extends TestCase
{
    /**
     * Hash of the data in the table
     *
     * @var string HASH
     */
    const HASH = 'e022fe6a4e3352603ce26d4c13792f431ab21282';

    /**
     * Expected JSON stream
     *
     * @var string CONTENT
     */
    const CONTENT = '{"id":"1","test_1":"49766366","test_2":"Amet iste laborum eius est dolor dolores.","test_3":null}' . "\n"
            . '{"id":"2","test_1":"1506369","test_2":"Quibusdam sed vel a quo sed fugit facilis.","test_3":null}' . "\n"
            . '{"id":"3","test_1":"4","test_2":"Ipsam sit veniam sed fuga aspernatur natus.","test_3":null}' . "\n"
            . '{"id":"4","test_1":"503083165","test_2":"Perferendis voluptatibus incidunt nostrum quia possimus.","test_3":null}' . "\n"
            . '{"id":"5","test_1":"738286","test_2":"Necessitatibus architecto aut consequatur debitis et id.","test_3":null}' . "\n"
            . '{"id":"6","test_1":"4527","test_2":"Totam temporibus quia ipsam ut iusto iusto.","test_3":null}' . "\n"
            . '{"id":"7","test_1":"43503859","test_2":"Accusantium et a qui ducimus nihil laudantium.","test_3":null}' . "\n"
            . '{"id":"8","test_1":"3764722","test_2":"Cum molestiae vel natus ex dicta hic inventore.","test_3":null}' . "\n"
            . '{"id":"9","test_1":"819253609","test_2":"Quae non quia dicta in aut provident.","test_3":null}' . "\n"
            . '{"id":"10","test_1":"9589","test_2":"Dignissimos error sit labore quos.","test_3":null}' . "\n";

    use DatabaseMigrations {
        runDatabaseMigrations as originalRunDatabaseMigrations;
    }

    /**
     * Run migrations
     */
    public function runDatabaseMigrations(): void
    {
        $this->useSqlite();
        $this->originalRunDatabaseMigrations();
    }

    /**
     * Test getting the hash of a table
     */
    public function test_get_table_hash(): void
    {
        $this->useSqlite();

        $response = $this->json('POST', '/sync/getTableHash', ['config_name' => 'sync_tests']);

        if ($response->getStatusCode() !== 200) {
            dump($response->getOriginalContent());
        }

        $expected = [
            'hash' => self::HASH,
        ];

        $response->assertOk();
        $response->assertJson($expected);
    }

    /**
     * Test getting the data in a table
     */
    public function test_get_table(): void
    {
        $this->useSqlite();

        $response = $this->json('POST', '/sync/getTable', ['config_name' => 'sync_tests']);

        // $response is a StreamedResponse so we can't use getContent()
        ob_start();
        $response->send();
        $body = ob_get_clean();

        if ($response->getStatusCode() !== 200) {
            dump($body);
        }

        $response->assertOk();
        $this->assertEquals(self::CONTENT, gzdecode($body), 'Wrong data returned');
    }

    /**
     * Test getting data when the table has been modified
     */
    public function test_get_table_modified(): void
    {
        $this->useSqlite();

        $response = $this->json('POST', '/sync/getTable', ['config_name' => 'sync_tests', 'hash' => 'abc123']);

        // $response is a StreamedResponse so we can't use getContent()
        ob_start();
        $response->send();
        $body = ob_get_clean();

        if ($response->getStatusCode() !== 200) {
            dump($body);
        }

        $response->assertOk();
        $this->assertEquals(self::CONTENT, gzdecode($body), 'Wrong data returned');
    }

    /**
     * Test getting data when the table has not been modified
     */
    public function test_get_table_not_modified(): void
    {
        $this->useSqlite();

        $response = $this->json('POST', '/sync/getTable', ['config_name' => 'sync_tests', 'hash' => self::HASH]);

        $statusCode = $response->getStatusCode();

        if (($statusCode !== 200) && ($statusCode !== 304)) {
            // $response is a StreamedResponse so we can't use getContent()
            ob_start();
            $response->send();
            $body = ob_get_clean();

            dump($body);
        }

        $this->assertSame(304, $statusCode, 'Wrong data returned');
    }
}
