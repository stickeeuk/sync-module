<?php

namespace Stickee\Sync;

use Exception;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Stickee\Sync\Interfaces\TableDescriberInterface;
use Stickee\Sync\Interfaces\TableHasherInterface;
use Stickee\Sync\TableHashers\MySqlTableHasher;
use Stickee\Sync\TableHashers\SqliteTableHasher;
use Stickee\Sync\Traits\ChecksTables;

/**
 */
class TableExporter
{
    use ChecksTables;

    private $connection;

    public $chunkSize = 1000;

    public function __construct(?string $connection = null)
    {
        $this->connection = $connection ?: config('database.default');
    }

    public function export($stream, string $table, array $primary = ['id']): void
    {
        $this->checkTable($table);

        $query = DB::connection($this->connection)
            ->table($table);

        foreach ($primary as $key) {
            $query->orderBy($key);
        }

        // Use gzdecode() to inflate
        $context = deflate_init(ZLIB_ENCODING_GZIP, ['level' => 9]);

        $query->chunk($this->chunkSize, function ($rows) use (&$stream, &$context) {
            $rows = array_map('json_encode', $rows->all());
            fwrite($stream, deflate_add($context, implode("\n", $rows) . "\n", ZLIB_NO_FLUSH));
        });

        fwrite($stream, deflate_add($context, '', ZLIB_FINISH));
    }
}
