<?php

namespace Stickee\Sync\Test\Feature;

use ErrorException;
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
    const HASH = 'da937ef9a2991114184c12039310c558ec4ab24c';

    /**
     * Expected JSON stream
     *
     * @var string CONTENT
     */
    const CONTENT = '{"id":1,"test_1":7449171,"test_2":"Animi quos velit et fugiat.","test_3":null,"test_4":"A"}' . "\n"
            . '{"id":2,"test_1":54,"test_2":"Deserunt aut ab provident perspiciatis quo omnis nostrum.","test_3":null,"test_4":"C"}' . "\n"
            . '{"id":3,"test_1":1031881,"test_2":"Incidunt iure odit et et modi ipsum.","test_3":null,"test_4":null}' . "\n"
            . '{"id":4,"test_1":5066,"test_2":"Aut dolores enim non facere tempora ex voluptatem.","test_3":"Quis adipisci molestias fugit deleniti distinctio eum.","test_4":null}' . "\n"
            . '{"id":5,"test_1":7788,"test_2":"Aliquam veniam corporis dolorem mollitia deleniti nemo.","test_3":"Officia est dignissimos neque blanditiis odio veritatis excepturi.","test_4":"A"}' . "\n"
            . '{"id":6,"test_1":91506985,"test_2":"Est alias tenetur ratione.","test_3":null,"test_4":"A"}' . "\n"
            . '{"id":7,"test_1":5439,"test_2":"Modi rerum ex repellendus assumenda et tenetur.","test_3":"Quia perspiciatis deserunt ducimus corrupti et.","test_4":null}' . "\n"
            . '{"id":8,"test_1":3199,"test_2":"Odit doloribus repellat officiis corporis nesciunt ut ratione iure.","test_3":"Ut rem est esse sint.","test_4":null}' . "\n"
            . '{"id":9,"test_1":938658,"test_2":"Doloribus fugiat ut aut deserunt et.","test_3":null,"test_4":null}' . "\n"
            . '{"id":10,"test_1":49895,"test_2":"Dolorem et ut dicta.","test_3":null,"test_4":"A"}' . "\n";

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

        $response = $this->json('POST', '/getTableHash', ['config_name' => 'test_table']);

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

        $response = $this->json('POST', '/getTable', ['config_name' => 'test_table']);

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

        $response = $this->json('POST', '/getTable', ['config_name' => 'test_table', 'hash' => 'abc123']);

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

        $response = $this->json('POST', '/getTable', ['config_name' => 'test_table', 'hash' => self::HASH]);

        $statusCode = $response->getStatusCode();

        if ($statusCode !== Response::HTTP_NOT_MODIFIED) {
            // $response is a StreamedResponse so we can't use getContent()
            ob_start();
            $response->send();
            $body = ob_get_clean();

            if ($statusCode === Response::HTTP_OK) {
                try {
                    $body = gzdecode($body);
                } catch (ErrorException $e) {
                    // Do nothing
                }
            }

            dump($body);
        }

        $this->assertSame(Response::HTTP_NOT_MODIFIED, $statusCode, 'Wrong data returned');
    }
}
