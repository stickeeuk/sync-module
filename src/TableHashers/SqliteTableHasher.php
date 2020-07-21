<?php

namespace Stickee\Sync\TableHashers;

use Illuminate\Support\Facades\DB;
use Stickee\Sync\Interfaces\TableDescriberInterface;
use Stickee\Sync\Interfaces\TableHasherInterface;

/**
 */
class SqliteTableHasher implements TableHasherInterface
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
            $fields[] = $column['name'];
        }


        $hash = '';

        DB::table($table)
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

        return $hash;
    }
}
