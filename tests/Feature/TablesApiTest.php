<?php

namespace Stickee\Sync\Test\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Response;
use Stickee\Sync\ServiceProvider;
use Stickee\Sync\Test\TestCase;

class TablesApiTest extends TestCase
{
    /**
     * Hash of the data in the table
     *
     * @var string HASH
     */
    const HASH = 'ddbb661caec115579989a3a063f75cb8c66061e2';

    /**
     * Expected JSON stream
     *
     * @var string CONTENT
     */
    const CONTENT = '{"id":"1","test_1":"49766366","test_2":"Amet iste laborum eius est dolor dolores.","test_3":null,"test_4":"C"}' . "\n"
            . '{"id":"2","test_1":"20240141","test_2":"A quo sed fugit facilis perferendis dolores molestias.","test_3":"Veniam sed fuga aspernatur natus earum.","test_4":null}' . "\n"
            . '{"id":"3","test_1":"137","test_2":"Incidunt nostrum quia possimus rerum id et necessitatibus architecto.","test_3":"Debitis et id nisi qui id.","test_4":"C"}' . "\n"
            . '{"id":"4","test_1":"31","test_2":"Iusto iusto accusamus iusto similique accusantium et.","test_3":null,"test_4":"B"}' . "\n"
            . '{"id":"5","test_1":"4625766","test_2":"Autem omnis cum molestiae vel natus ex dicta.","test_3":null,"test_4":"C"}' . "\n"
            . '{"id":"6","test_1":"68923564","test_2":"Non quia dicta in.","test_3":null,"test_4":null}' . "\n"
            . '{"id":"7","test_1":"9589","test_2":"Dignissimos error sit labore quos.","test_3":"Repudiandae est nostrum et voluptas consequatur delectus autem.","test_4":null}' . "\n"
            . '{"id":"8","test_1":"5","test_2":"Et perferendis fuga a debitis.","test_3":null,"test_4":null}' . "\n"
            . '{"id":"9","test_1":"239","test_2":"Autem deleniti ut quibusdam et eum.","test_3":null,"test_4":"B"}' . "\n"
            . '{"id":"10","test_1":"78413","test_2":"Nihil aut nisi officiis rerum id tempore voluptate sit.","test_3":"Odit aut voluptas quasi ut.","test_4":null}' . "\n";

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

        $response = $this->json('POST', '/getTableHash', ['config_name' => 'sync_tests']);

        if ($response->getStatusCode() !== Response::HTTP_OK) {
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

        $response = $this->json('POST', '/getTable', ['config_name' => 'sync_tests']);

        // $response is a StreamedResponse so we can't use getContent()
        ob_start();
        $response->send();
        $body = ob_get_clean();

        if ($response->getStatusCode() !== Response::HTTP_OK) {
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

        $response = $this->json('POST', '/getTable', ['config_name' => 'sync_tests', 'hash' => 'abc123']);

        // $response is a StreamedResponse so we can't use getContent()
        ob_start();
        $response->send();
        $body = ob_get_clean();

        if ($response->getStatusCode() !== Response::HTTP_OK) {
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

        $response = $this->json('POST', '/getTable', ['config_name' => 'sync_tests', 'hash' => self::HASH]);

        $statusCode = $response->getStatusCode();

        if (($statusCode !== Response::HTTP_OK) && ($statusCode !== Response::HTTP_NOT_MODIFIED)) {
            // $response is a StreamedResponse so we can't use getContent()
            ob_start();
            $response->send();
            $body = ob_get_clean();

            dump($body);
        }

        $this->assertSame(Response::HTTP_NOT_MODIFIED, $statusCode, 'Wrong data returned');
    }
}
