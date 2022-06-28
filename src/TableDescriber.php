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
     * @param string $configType The config type - 'sync-client' or 'sync-server'
     * @param string $configName The key from config('sync-client.tables') or config('sync-server.tables')
     *
     * @return array
     */
    public function describe(string $configType, string $configName): array
    {
        $config = $this->getTableInfo($configType, $configName);
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
