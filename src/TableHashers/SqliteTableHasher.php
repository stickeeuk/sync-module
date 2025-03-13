<?php

namespace Stickee\Sync\TableHashers;

use Illuminate\Support\Facades\DB;
use Stickee\Sync\Interfaces\TableDescriberInterface;
use Stickee\Sync\Interfaces\TableHasherInterface;
use Stickee\Sync\ServiceProvider;
use Stickee\Sync\Traits\UsesTables;

class SqliteTableHasher implements TableHasherInterface
{
    use UsesTables;

    /**
     * The number of records to get from the database at once
     */
    public int $chunkSize = 1000;

    /**
     * Constructor
     *
     * @param \Stickee\Sync\Interfaces\TableDescriberInterface $tableDescriber The table describer
     */
    public function __construct(private TableDescriberInterface $tableDescriber)
    {
    }

    /**
     * Get a hash of the data in a table
     *
     * @param string $configType The config type - 'sync-client' or 'sync-server'
     * @param string $configName The key from config('sync-client.tables') or config('sync-server.tables')
     */
    public function hash(string $configType, string $configName): string
    {
        $config = $this->getTableInfo($configType, $configName);
        $fields = [];
        $tableDescription = $this->tableDescriber->describe($configType, $configName);

        foreach ($tableDescription['columns'] as $column) {
            $fields[] = $column['name'];
        }

        $hash = '';

        DB::connection($config['connection'])
            ->table($config['table'])
            ->select($fields)
            ->orderBy('rowid')
            ->chunk($this->chunkSize, function ($rows) use (&$hash): void {
                foreach ($rows as $row) {
                    $row = (array) $row;
                    array_unshift($row, $hash);

                    $row = array_map(fn(?string $value): string => $value ?? ServiceProvider::NULL_VALUE, $row);

                    $line = implode('|', $row);
                    $hash = sha1($line);
                }
            });

        return $hash === '' ? ServiceProvider::EMPTY_TABLE_HASH : $hash;
    }
}
