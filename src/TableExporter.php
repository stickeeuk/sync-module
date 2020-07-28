<?php

namespace Stickee\Sync;

use Illuminate\Support\Facades\DB;
use Stickee\Sync\Traits\ChecksTables;

/**
 */
class TableExporter
{
    use ChecksTables;

    public $chunkSize = 1000;

    public function export($stream, string $table): void
    {
        $this->checkTable($table);

        $config = config('sync.tables');
        $primary = $config['primary'] ?? ['id'];
        $connection = $config['connection'] ?? config('database.default');

        $query = DB::connection($connection)
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
