<?php

namespace Stickee\Sync;

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

        $schema = DB::connection($config['connection'])->getSchemaBuilder();

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
