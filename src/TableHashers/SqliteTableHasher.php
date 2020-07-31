<?php

namespace Stickee\Sync\TableHashers;

use Illuminate\Support\Facades\DB;
use Stickee\Sync\Interfaces\TableDescriberInterface;
use Stickee\Sync\Interfaces\TableHasherInterface;
use Stickee\Sync\ServiceProvider;
use Stickee\Sync\Traits\UsesTables;

/**
 */
class SqliteTableHasher implements TableHasherInterface
{
    use UsesTables;

    /**
     * The table describer
     *
     * @var \Stickee\Sync\Interfaces\TableDescriberInterface $tableDescriber
     */
    private $tableDescriber;

    /**
     * The number of records to get from the database at once
     *
     * @var int $chunkSize
     */
    public $chunkSize = 1000;

    /**
     * Constructor
     *
     * @param \Stickee\Sync\Interfaces\TableDescriberInterface $tableDescriber The table describer
     */
    public function __construct(TableDescriberInterface $tableDescriber)
    {
        $this->tableDescriber = $tableDescriber;
    }

    /**
     * Get a hash of the data in a table
     *
     * @param string $configName The key in config('sync.tables')
     *
     * @return string
     */
    public function hash(string $configName): string
    {
        $config = $this->getTableInfo($configName);

        $fields = [];
        $tableDescription = $this->tableDescriber->describe($configName);

        foreach ($tableDescription['columns'] as $column) {
            $fields[] = $column['name'];
        }

        $hash = '';

        DB::connection($config['connection'])
            ->table($config['table'])
            ->select($fields)
            ->orderBy('rowid')
            ->chunk($this->chunkSize, function ($rows) use (&$hash) {
                foreach ($rows as $row) {
                    $row = (array)$row;
                    array_unshift($row, $hash);

                    $row = array_map(function (?string $value) {
                        return $value === null ? ServiceProvider::NULL_VALUE : $value;
                    }, $row);

                    $line = implode('|', $row);
                    $hash = sha1($line);
                }
            });

        return $hash === '' ? ServiceProvider::EMPTY_TABLE_HASH : $hash;
    }
}
