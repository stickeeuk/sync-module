<?php

namespace Stickee\Sync\TableHashers;

use Illuminate\Support\Facades\DB;
use Stickee\Sync\Interfaces\TableDescriberInterface;
use Stickee\Sync\Interfaces\TableHasherInterface;

/**
 */
class MySqlTableHasher implements TableHasherInterface
{
    private $tableDescriber;

    public function __construct(TableDescriberInterface $tableDescriber)
    {
        $this->tableDescriber = $tableDescriber;
    }

    public function hash(string $table, ?string $connection = null): string
    {
        $fields = [];
        $tableDescription = $this->tableDescriber->describe($table, $connection); // todo this is wrong, need connection in the constructor

        foreach ($tableDescription['columns'] as $column) {
            $fields[] = 'IFNULL(' . $column['name'] . ', "NULL9cf4-973a-4539-a5f2-8d4bde0aNULL")';
        }


        DB::statement('SET @crc := ""');

        DB::statement(
            'SELECT MIN(
                (@crc := SHA1(CONCAT_WS(
                    "|", @crc, ' . implode(', ', $fields) . '
                ))) IS NULL
            ) AS discard
            FROM ' . DB::getTablePrefix() . $table . ' USE INDEX(PRIMARY)'
        );

        $result = DB::select('SELECT @crc AS crc');

        return $result[0]->crc;
    }
}
