<?php

namespace Stickee\Sync\TableHashers;

use Illuminate\Support\Facades\DB;
use Stickee\Sync\Interfaces\TableDescriberInterface;
use Stickee\Sync\Interfaces\TableHasherInterface;
use Stickee\Sync\Traits\ChecksTables;

/**
 */
class MySqlTableHasher implements TableHasherInterface
{
    use ChecksTables;

    private $tableDescriber;

    public function __construct(TableDescriberInterface $tableDescriber)
    {
        $this->tableDescriber = $tableDescriber;
    }

    public function hash(string $table): string
    {
        $this->checkTable($table);

        $fields = [];
        $tableDescription = $this->tableDescriber->describe($table);
        $connection = $this->tableDescriber->getConnection();

        foreach ($tableDescription['columns'] as $column) {
            $fields[] = 'IFNULL(' . $column['name'] . ', "NULL9cf4-973a-4539-a5f2-8d4bde0aNULL")';
        }

        $dbConnection = DB::connection($connection);
        $dbConnection->statement('SET @crc := ""');

        $dbConnection->statement(
            'SELECT MIN(
                (@crc := SHA1(CONCAT_WS(
                    "|", @crc, ' . implode(', ', $fields) . '
                ))) IS NULL
            ) AS discard
            FROM ' . $dbConnection->getTablePrefix() . $table . ' USE INDEX(PRIMARY)'
        );

        $result = $dbConnection->select('SELECT @crc AS crc');

        return $result[0]->crc;
    }
}
