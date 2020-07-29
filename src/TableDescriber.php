<?php

namespace Stickee\Sync;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Stickee\Sync\Interfaces\TableDescriberInterface;
use Stickee\Sync\Traits\UsesTables;

/**
 */
class TableDescriber implements TableDescriberInterface
{
    use UsesTables;

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
