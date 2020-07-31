<?php

namespace Stickee\Sync\TableHashers;

use Illuminate\Support\Facades\DB;
use Stickee\Sync\Interfaces\TableDescriberInterface;
use Stickee\Sync\Interfaces\TableHasherInterface;
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
            ->chunk(1000, function ($rows) use (&$hash) {
                foreach ($rows as $row) {
                    $row = (array)$row;
                    array_unshift($row, $hash);

                    $row = array_map(function (?string $value) {
                        return $value === null ? 'NULL9cf4-973a-4539-a5f2-8d4bde0aNULL' : $value;
                    }, $row);

                    $line = implode('|', $row);
                    $hash = sha1($line);
                }
            });

        return $hash === '' ? '--EMPTY--' : $hash;
    }
}
