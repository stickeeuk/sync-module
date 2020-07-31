<?php

namespace Stickee\Sync\TableHashers;

use Illuminate\Support\Facades\DB;
use Stickee\Sync\Interfaces\TableDescriberInterface;
use Stickee\Sync\Interfaces\TableHasherInterface;
use Stickee\Sync\Traits\UsesTables;

/**
 */
class MySqlTableHasher implements TableHasherInterface
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
            $fields[] = 'IFNULL(' . $column['name'] . ', "NULL9cf4-973a-4539-a5f2-8d4bde0aNULL")';
        }

        $dbConnection = DB::connection($config['connection']);
        $dbConnection->statement('SET @crc := ""');

        $dbConnection->statement(
            'SELECT MIN(
                (@crc := SHA1(CONCAT_WS(
                    "|", @crc, ' . implode(', ', $fields) . '
                ))) IS NULL
            ) AS discard
            FROM ' . $dbConnection->getTablePrefix() . $config['table'] . ' USE INDEX(PRIMARY)'
        );

        $result = $dbConnection->select('SELECT @crc AS crc');

        return $result[0]->crc === '' ? '--EMPTY--' : $result[0]->crc;
    }
}
