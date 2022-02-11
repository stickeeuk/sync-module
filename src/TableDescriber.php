<?php

namespace Stickee\Sync;

use Doctrine\DBAL\Types\Types;
use Illuminate\Support\Facades\DB;
use Stickee\Sync\Interfaces\TableDescriberInterface;
use Stickee\Sync\Traits\UsesTables;

/**
 */
class TableDescriber implements TableDescriberInterface
{
    use UsesTables;

    /**
     * Get a description of a table
     *
     * @param string $table The table name
     *
     * @return array
     */
    public function describe(string $configName): array
    {
        $config = $this->getTableInfo($configName);
        $connection = DB::connection($config['connection']);

        // Make sure enums will work
        $connection->getDoctrineSchemaManager()
            ->getDatabasePlatform()
            ->registerDoctrineTypeMapping('enum', Types::STRING);

        $schema = $connection->getSchemaBuilder();
        $columns = $schema->getColumnListing($config['table']);
        $result = ['columns' => []];

        foreach ($columns as $column) {
            $type = $schema->getColumnType($config['table'], $column);

            $result['columns'][] = [
                'name' => $column,
                'type' => $type,
            ];
        }

        return $result;
    }
}
