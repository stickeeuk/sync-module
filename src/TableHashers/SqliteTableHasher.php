<?php

namespace Stickee\Sync\TableHashers;

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
        $data = $db->query('DESCRIBE ?', [$table], [DB::PARAM_IDENTIFIER]);

        foreach ($data as $row) {
            $fields[] = $row['Field'];
        }

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
