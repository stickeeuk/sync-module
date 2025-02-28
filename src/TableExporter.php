<?php

namespace Stickee\Sync;

use Illuminate\Support\Facades\DB;
use Stickee\Sync\Traits\UsesTables;

class TableExporter
{
    use UsesTables;

    /**
     * The number of records to get from the database at once
     */
    public int $chunkSize = 1000;

    /**
     * Export a table to a stream
     *
     * @param mixed $stream The stream to write to
     * @param string $configName The key in config('sync-server.tables')
     */
    public function export($stream, string $configName): void
    {
        $config = $this->getTableInfo(Helpers::SERVER_CONFIG, $configName);

        $query = DB::connection($config['connection'])
            ->table($config['table']);

        foreach ($config['primary'] as $key) {
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
