<?php

namespace Stickee\Sync\TableHashers;

use Illuminate\Support\Facades\DB;
use Stickee\Sync\Interfaces\TableDescriberInterface;
use Stickee\Sync\Interfaces\TableHasherInterface;
use Stickee\Sync\ServiceProvider;
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
     * @param string $configType The config type - 'sync-client' or 'sync-server'
     * @param string $configName The key from config('sync-client.tables') or config('sync-server.tables')
     *
     * @return string
     */
    public function hash(string $configType, string $configName): string
    {
        $config = $this->getTableInfo($configType, $configName);
        $fields = [];
        $tableDescription = $this->tableDescriber->describe($configType, $configName);

        $dbConnection = DB::connection($config['connection']);
        $dbGrammar = $dbConnection->getQueryGrammar();

        foreach ($tableDescription['columns'] as $column) {
            $columnName = $dbGrammar->wrap($column['name']);
            $fields[] = 'IFNULL(' . $columnName . ', "' . ServiceProvider::NULL_VALUE . '")';
        }

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

        return $result[0]->crc === '' ? ServiceProvider::EMPTY_TABLE_HASH : $result[0]->crc;
    }
}
