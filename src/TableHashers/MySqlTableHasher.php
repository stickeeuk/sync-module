<?php

namespace Stickee\Sync\TableHashers;

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
            $fields[] = $column['name'];
        }


        dd(\Stickee\Sync\Models\SyncTest::count());

        $db->query('SET @crc := ""');

        $db->query(
            'SELECT MIN(
                (@crc := SHA1(CONCAT_WS(
                    "#", @crc, ?
                ))) IS NULL
            ) AS discard
            FROM ? USE INDEX(PRIMARY)',
            [$fields, $table],
            [DB::PARAM_IDENTIFIER, DB::PARAM_IDENTIFIER]
        );

        $hash = $db->queryOne('SELECT @crc AS crc');

        return $hash['crc'];
    }
}
